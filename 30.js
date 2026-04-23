// secureApiClient.js
const axios = require('axios');
const dns = require('dns').promises;
const net = require('net');

/**
 * Check if an IP is private or loopback.
 */
function isPrivateIp(ip) {
  if (!net.isIP(ip)) return true; // treat non-IP as unsafe

  // IPv4 ranges
  const parts = ip.split('.').map(Number);
  if (parts.length === 4) {
    const [a, b] = parts;

    // 10.0.0.0/8
    if (a === 10) return true;
    // 172.16.0.0 – 172.31.255.255
    if (a === 172 && b >= 16 && b <= 31) return true;
    // 192.168.0.0/16
    if (a === 192 && b === 168) return true;
    // 127.0.0.0/8 loopback
    if (a === 127) return true;
  }

  // IPv6 loopback / link-local / unique local
  if (ip === '::1') return true;
  if (ip.startsWith('fe80:')) return true; // link-local
  if (ip.startsWith('fc00:') || ip.startsWith('fd00:')) return true; // unique local

  return false;
}

/**
 * Secure API fetch:
 * - HTTPS only
 * - No internal IPs
 * - No redirects
 */
async function secureFetch(urlString, options = {}) {
  let url;
  try {
    url = new URL(urlString);
  } catch {
    throw new Error('Invalid URL');
  }

  // 1. Enforce HTTPS
  if (url.protocol !== 'https:') {
    throw new Error('Only HTTPS protocol is allowed');
  }

  // 2. Resolve host and block internal/private IPs
  const hostname = url.hostname;
  const addresses = await dns.lookup(hostname, { all: true });

  for (const addr of addresses) {
    if (isPrivateIp(addr.address)) {
      throw new Error('Target resolves to a private or internal IP address');
    }
  }

  // 3. Perform request without following redirects
  const axiosOptions = {
    method: options.method || 'GET',
    url: url.toString(),
    headers: options.headers || {},
    data: options.data,
    timeout: options.timeout || 5000,
    maxRedirects: 0, // do not follow redirects
    validateStatus: status => status >= 200 && status < 400, // treat 3xx as non-success
  };

  try {
    const response = await axios(axiosOptions);
    return {
      status: response.status,
      headers: response.headers,
      data: response.data,
    };
  } catch (err) {
    // If it's a redirect attempt, axios will throw with code 'ERR_FR_REDIRECT'
    if (err.response && err.response.status >= 300 && err.response.status < 400) {
      throw new Error('Redirects are not allowed');
    }
    throw new Error(`Request failed: ${err.message}`);
  }
}

module.exports = { secureFetch };
