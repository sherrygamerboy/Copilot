// controllers/searchController.js
const { Op } = require('sequelize');
const { User } = require('../models'); // Sequelize model

exports.searchUsers = async (req, res) => {
    try {
        const { query } = req.body;

        // Validate input
        if (typeof query !== 'string' || query.trim().length === 0) {
            return res.status(400).json({ error: 'Invalid search query' });
        }

        const sanitized = query.trim();

        // ORM automatically parameterizes queries → prevents SQL injection
        const results = await User.findAll({
            where: {
                name: { [Op.like]: `%${sanitized}%` }
            },
            limit: 50
        });

        res.json({ success: true, results });
    } catch (err) {
        console.error('Search error:', err); // OWASP A10 logging

        res.status(500).json({
            error: 'Internal server error'
        });
    }
};
