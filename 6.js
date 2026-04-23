//-------------------------------
//code-1
//-------------------------------

// authMiddleware.js
const jwt = require('jsonwebtoken');

const JWT_SECRET = process.env.JWT_SECRET;          // strong, random, not checked into VCS
const JWT_ISSUER = 'your-app-name';
const JWT_AUDIENCE = 'your-app-clients';

// Ensure secret is set at startup
if (!JWT_SECRET) {
    throw new Error('JWT_SECRET is not set');
}

function authMiddleware(required = true) {
    return (req, res, next) => {
        const authHeader = req.headers['authorization'] || '';
        const token = authHeader.startsWith('Bearer ')
            ? authHeader.slice(7).trim()
            : null;

        if (!token) {
            if (!required) return next(); // allow optional auth if explicitly configured
            return res.status(401).json({ error: 'Missing authorization token' });
        }

        jwt.verify(
            token,
            JWT_SECRET,
            {
                algorithms: ['HS256'],      // whitelist algorithms
                issuer: JWT_ISSUER,
                audience: JWT_AUDIENCE,
            },
            (err, payload) => {
                if (err) {
                    // Do NOT fall through on error
                    return res.status(401).json({ error: 'Invalid or expired token' });
                }

                // Attach claims to request (minimal data)
                req.user = {
                    id: payload.sub,
                    roles: payload.roles || [],
                };

                // Optional: basic defense-in-depth checks
                if (!req.user.id) {
                    return res.status(401).json({ error: 'Invalid token subject' });
                }

                return next();
            }
        );
    };
}

module.exports = authMiddleware;

//--------------------------------
//code-2
//-------------------------------
// login route example (simplified)
const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');

app.post('/login', async (req, res) => {
    const { email, password } = req.body;

    // Look up user in DB (pseudo-code)
    const user = await db.users.findOne({ email });
    if (!user) {
        return res.status(401).json({ error: 'Invalid credentials' });
    }

    const ok = await bcrypt.compare(password, user.password_hash);
    if (!ok) {
        return res.status(401).json({ error: 'Invalid credentials' });
    }

    const token = jwt.sign(
        {
            sub: user.id,
            roles: user.roles || [],
        },
        process.env.JWT_SECRET,
        {
            algorithm: 'HS256',
            issuer: 'your-app-name',
            audience: 'your-app-clients',
            expiresIn: '15m',
        }
    );

    res.json({ token });
});


//-------------------------------
//code-3
//-------------------------------
const auth = require('./authMiddleware');

// Protected route
app.get('/profile', auth(), (req, res) => {
    // req.user is guaranteed to exist here if token is valid
    res.json({ userId: req.user.id, roles: req.user.roles });
});

// Public route with optional auth (e.g., personalize if logged in)
app.get('/public', auth(false), (req, res) => {
    if (req.user) {
        return res.json({ message: 'Hello, authenticated user', userId: req.user.id });
    }
    res.json({ message: 'Hello, guest' });
});
