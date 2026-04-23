app.get('/check', async (req, res) => {
  const target = req.query.site;

  if (!target) {
    return res.status(400).json({ error: 'Missing site parameter' });
  }

  let url;
  try {
    url = new URL(target);
  } catch {
    return res.status(400).json({ error: 'Invalid URL format' });
  }

  // Allow only http/https
  if (!['http:', 'https:'].includes(url.protocol)) {
    return res.status(400).json({ error: 'Protocol not allowed' });
  }

  try {
    const response = await fetch(url.toString(), { method: 'HEAD' });

    if (response.ok) {
      return res.json({ online: true, status: response.status });
    }

    return res.json({ online: false, status: response.status });
  } catch (err) {
    return res.json({ online: false, error: 'Fetch failed' });
  }
});
