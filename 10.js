// routes/updateEmail.js
const express = require('express');
const router = express.Router();
const User = require('../models/User'); // Mongoose model
const validator = require('validator');

router.post('/update-email', async (req, res) => {
    try {
        const { userId, newEmail } = req.body;

        // Validate input
        if (!userId || !newEmail) {
            return res.status(400).json({ error: 'Missing fields' });
        }
        if (!validator.isEmail(newEmail)) {
            return res.status(400).json({ error: 'Invalid email format' });
        }

        const updated = await User.findByIdAndUpdate(
            userId,
            { email: newEmail },
            { new: true }
        );

        if (!updated) {
            return res.status(404).json({ error: 'User not found' });
        }

        res.json({ success: true, user: updated });
    } catch (err) {
        console.error(err);
        res.status(500).json({ error: 'Server error' });
    }
});

module.exports = router;


// --------------------------------------------------------------------------------

// routes/updateEmail.js
const express = require('express');
const router = express.Router();
const validator = require('validator');
const db = require('../db'); // your DB client (e.g., mysql2, pg)

router.post('/update-email', async (req, res) => {
    try {
        const { userId, newEmail } = req.body;

        // Validate input
        if (!userId || !newEmail) {
            return res.status(400).json({ error: 'Missing fields' });
        }
        if (!validator.isEmail(newEmail)) {
            return res.status(400).json({ error: 'Invalid email format' });
        }

        // Parameterized query prevents SQL injection
        const sql = `UPDATE users SET email = ? WHERE id = ?`;
        const params = [newEmail, userId];

        const [result] = await db.execute(sql, params);

        if (result.affectedRows === 0) {
            return res.status(404).json({ error: 'User not found' });
        }

        res.json({ success: true, message: 'Email updated' });
    } catch (err) {
        console.error(err);
        res.status(500).json({ error: 'Server error' });
    }
});

module.exports = router;
