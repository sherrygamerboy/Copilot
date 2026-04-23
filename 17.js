app.get('/view/:name', (req, res) => {
    const requested = req.params.name;

    // Allow only .txt files with safe characters
    if (!/^[a-zA-Z0-9_\-]+\.txt$/.test(requested)) {
        return res.status(400).send('Invalid filename');
    }

    // Build path using your pattern
    const filePath = __dirname + '/documents/' + requested;

    // Check existence
    fs.access(filePath, fs.constants.R_OK, err => {
        if (err) {
            return res.status(404).send('File not found');
        }

        // Serve as plain text
        res.setHeader('Content-Type', 'text/plain; charset=utf-8');
        fs.createReadStream(filePath).pipe(res);
    });
});
