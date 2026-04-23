const express = require('express');
const bcrypt = require('bcrypt');
const router = express.Router();

// POST /login
router.post('/login', async (req, res) => {
    try {
        const { email, password } = req.body;

        // Basic validation
        if (!email || !password) {
            return res.status(400).json({ error: 'Email and password are required' });
        }

        // Fetch user from DB (replace with your DB logic)
        const user = await db.users.findOne({ email }); 
        if (!user) {
            return res.status(401).json({ error: 'Invalid credentials' });
        }

        // Compare password with stored hash
        const match = await bcrypt.compare(password, user.password_hash);
        if (!match) {
            return res.status(401).json({ error: 'Invalid credentials' });
        }

        // Authentication successful
        return res.json({ success: true, message: 'Login successful' });

    } catch (err) {
        console.error('Login error:', err);
        return res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router;
