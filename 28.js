const express = require('express');
const axios = require('axios');
const router = express.Router();

// Allow‑listed protocols to reduce SSRF risk
const allowedProtocols = ['http:', 'https:'];

router.get('/fetch', async (req, res) => {
  const targetUrl = req.query.targetUrl;

  if (!targetUrl) {
    return res.status(400).json({ error: 'Missing targetUrl parameter' });
  }

  let parsed;
  try {
    parsed = new URL(targetUrl);
  } catch {
    return res.status(400).json({ error: 'Invalid URL format' });
  }

  // Enforce allowed protocols
  if (!allowedProtocols.includes(parsed.protocol)) {
    return res.status(400).json({ error: 'Protocol not allowed' });
  }

  try {
    const response = await axios.get(targetUrl, {
      timeout: 5000,
      validateStatus: () => true, // forward non-200 responses
    });

    res.status(response.status).send(response.data);
  } catch (err) {
    console.error('Fetch error:', err.message);
    res.status(502).json({ error: 'Failed to fetch remote resource' });
  }
});

module.exports = router;
