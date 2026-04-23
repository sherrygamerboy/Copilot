<?php
// ---------------------------
// Security & session settings
// ---------------------------

// Force secure cookie settings (adjust for your environment)
$secure   = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
$httponly = true;
$samesite = 'Strict'; // or 'Lax' if needed

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',          // set your domain if needed
    'secure'   => $secure,
    'httponly' => $httponly,
    'samesite' => $samesite,
]);

session_start();

// Mitigate session fixation: ensure a fresh session for each new login
function regenerate_session_id()
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

// Basic CSRF check (example – you’d generate/store token on the login form page)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(400);
        exit('Bad request');
    }
}

// ---------------------------
// Database connection (PDO)
// ---------------------------

$dsn      = 'mysql:host=localhost;dbname=your_database;charset=utf8mb4';
$dbUser   = 'your_db_user';
$dbPass   = 'your_db_password';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    // Do not leak details to the user
    error_log('DB connection error: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal server error');
}

// ---------------------------
// Input handling & validation
// ---------------------------

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$username = $_POST['user'] ?? '';
$password = $_POST['pass'] ?? '';

// Minimal validation (you can harden this further)
$username = trim($username);
$password = trim($password);

if ($username === '' || $password === '') {
    // Generic error to avoid user enumeration
    exit('Invalid username or password');
}

// ---------------------------
// Authentication logic
// ---------------------------

try {
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // Use generic error messages to avoid user enumeration
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Optional: implement rate limiting / lockout here
        exit('Invalid username or password');
    }

    // Optional: rehash if algorithm/cost changed
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update  = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $update->execute([':hash' => $newHash, ':id' => $user['id']]);
    }

    // Successful login: regenerate session ID
    regenerate_session_id();

    // Store only what you need in the session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['username']   = $user['username'];
    $_SESSION['logged_in']  = true;
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $_SESSION['last_activity'] = time();

    echo 'Success';

} catch (PDOException $e) {
    error_log('Auth error: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal server error');
}
