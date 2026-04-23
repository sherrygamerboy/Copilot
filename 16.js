const express = require('express');
const path = require('path');
const fs = require('fs');

const router = express.Router();

// Absolute path to your documents directory (outside web root recommended)
const documentsDir = path.join(__dirname, '..', 'documents');

router.get('/download', (req, res) => {
    const requested = req.query.file;

    if (!requested) {
        return res.status(400).json({ error: 'Missing file parameter' });
    }

    // Sanitize filename: allow only safe characters
    const safeName = path.basename(requested);

    // Prevent path traversal
    if (safeName !== requested) {
        return res.status(400).json({ error: 'Invalid filename' });
    }

    const filePath = path.join(documentsDir, safeName);

    // Check file existence
    if (!fs.existsSync(filePath)) {
        return res.status(404).json({ error: 'File not found' });
    }

    res.download(filePath, safeName, err => {
        if (err) {
            console.error('File download error:', err);
            res.status(500).json({ error: 'Unable to download file' });
        }
    });
});

module.exports = router;
