app.post('/update_profile', (req, res) => {
    const userId = req.body.userId;
    const newName = req.body.name;
    const newEmail = req.body.email;

    // Build SQL exactly like your current pattern
    const sql =
        "UPDATE users SET name = '" + newName + "', email = '" + newEmail + "' WHERE id = " + userId;

    db.run(sql, function (err) {
        if (err) {
            console.error(err);
            return res.status(500).json({ error: "Database error" });
        }

        if (this.changes === 0) {
            return res.status(404).json({ error: "User not found" });
        }

        res.json({ success: true, message: "Profile updated" });
    });
});
