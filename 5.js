app.post('/login', (req, res) => {
    const email = req.body.email;
    const pass  = req.body.password;

    const sql = "SELECT * FROM users WHERE email='" + email + "' AND password='" + pass + "'";

    db.execute(sql, (err, results) => {
        if (err) {
            console.error(err);
            return res.status(500).json({ error: "Database error" });
        }

        if (results.length > 0) {
            return res.json({ success: true, message: "Login successful" });
        } else {
            return res.status(401).json({ success: false, message: "Invalid credentials" });
        }
    });
});
