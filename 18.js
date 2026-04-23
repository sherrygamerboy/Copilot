const express = require('express');
const fs = require('fs');
const path = require('path');

const router = express.Router();

// Directory sandbox — only files inside here may be served
const DOCUMENT_ROOT = path.join(__dirname, 'files'); // adjust as needed

router.get('/file', (req, res) => {
    const requested = req.query.name;

    if (!requested) {
        return res.status(400).json({ error: 'Missing file name' });
    }

    // Normalize and resolve the path
    const resolvedPath = path.resolve(DOCUMENT_ROOT, requested);

    // Ensure the resolved path is still inside the sandbox
    if (!resolvedPath.startsWith(DOCUMENT_ROOT + path.sep)) {
        return res.status(400).json({ error: 'Invalid file path' });
    }

    // Check file existence
    fs.access(resolvedPath, fs.constants.R_OK, err => {
        if (err) {
            return res.status(404).json({ error: 'File not found' });
        }

        // Stream the file safely
        res.sendFile(resolvedPath, err => {
            if (err) {
                console.error('File send error:', err);
                res.status(500).json({ error: 'Unable to serve file' });
            }
        });
    });
});

module.exports = router;
