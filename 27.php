<?php
// -------- Secure PHP Proxy (SSRF / CWE-918 mitigated) --------

// 1. Define allowed domains (exact hosts)
$allowedHosts = [
    'api.example.com',
    'cdn.example.com',
    'data.trusted.org',
];

// 2. Get and validate URL
$rawUrl = $_GET['url'] ?? '';
$url    = trim($rawUrl);

if (!filter_var($url, FILTER_VALIDATE_URL)) {
    http_response_code(400);
    exit('Invalid URL');
}

$parts = parse_url($url);
$scheme = strtolower($parts['scheme'] ?? '');
$host   = strtolower($parts['host'] ?? '');

if (!in_array($scheme, ['http', 'https'], true)) {
    http_response_code(400);
    exit('Unsupported URL scheme');
}

// 3. Enforce allow-listed domains
if (!in_array($host, $allowedHosts, true)) {
    http_response_code(403);
    exit('Domain not allowed');
}

// 4. Resolve host and block private/loopback IPs
$ip = gethostbyname($host);
if ($ip === $host || !filter_var($ip, FILTER_VALIDATE_IP)) {
    http_response_code(400);
    exit('Unable to resolve host');
}

// Block private, loopback, link-local, etc.
if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
    http_response_code(403);
    exit('IP range not allowed');
}

// 5. Fetch remote resource (use stream context with timeout)
$context = stream_context_create([
    'http' => [
        'method'  => 'GET',
        'timeout' => 5,
        'header'  => "User-Agent: SecureProxy/1.0\r\n",
    ],
    'https' => [
        'method'  => 'GET',
        'timeout' => 5,
        'header'  => "User-Agent: SecureProxy/1.0\r\n",
    ],
]);

$content = @file_get_contents($url, false, $context);
if ($content === false) {
    http_response_code(502);
    exit('Failed to fetch remote resource');
}

// 6. Optionally forward content-type if available
$contentType = 'application/octet-stream';
if (isset($http_response_header)) {
    foreach ($http_response_header as $headerLine) {
        if (stripos($headerLine, 'Content-Type:') === 0) {
            $contentType = trim(substr($headerLine, strlen('Content-Type:')));
            break;
        }
    }
}

header('Content-Type: ' . $contentType);
echo $content;
