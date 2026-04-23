<?php
class ProfileRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        // Enforce safe PDO settings
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo = $pdo;
    }

    // CREATE
    public function createProfile(string $name, string $email, string $bio): int
    {
        $sql = "INSERT INTO profiles (name, email, bio) VALUES (:name, :email, :bio)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':bio'   => $bio,
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    // READ (single)
    public function getProfileById(int $id): ?array
    {
        $sql = "SELECT id, name, email, bio FROM profiles WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // UPDATE
    public function updateProfile(int $id, string $name, string $email, string $bio): bool
    {
        $sql = "UPDATE profiles
                SET name = :name, email = :email, bio = :bio
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name'  => $name,
            ':email' => $email,
            ':bio'   => $bio,
            ':id'    => $id,
        ]);
    }

    // DELETE
    public function deleteProfile(int $id): bool
    {
        $sql = "DELETE FROM profiles WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}

// ----------------------------------------------------------------

<?php
// db.php – create PDO instance
$pdo = new PDO(
    'mysql:host=localhost;dbname=your_db;charset=utf8mb4',
    'db_user',
    'db_pass',
    [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
);

$repo = new ProfileRepository($pdo);

// Helper: trim + basic sanitization
function sanitize_string(?string $value): string
{
    $value = trim((string)$value);
    // Remove control characters; keep normal text
    return filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
}

// Helper: validate email
function validate_email(?string $email): ?string
{
    $email = trim((string)$email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
}


// ----------------------------------------------------------------

<?php
// update_profile.php

require 'db.php'; // includes $pdo, $repo, helpers

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// 1. Collect raw input
$idRaw    = $_POST['id']   ?? null;
$nameRaw  = $_POST['name'] ?? null;
$emailRaw = $_POST['email'] ?? null;
$bioRaw   = $_POST['bio']  ?? null;

// 2. Validate and sanitize BEFORE DB sink

// ID: must be a positive integer
$id = filter_var($idRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    exit('Invalid ID');
}

// Name: required, length‑limited
$name = sanitize_string($nameRaw);
if ($name === '' || mb_strlen($name) > 100) {
    exit('Invalid name');
}

// Email: required, valid format
$email = validate_email($emailRaw);
if ($email === null || mb_strlen($email) > 255) {
    exit('Invalid email');
}

// Bio: optional, length‑limited, sanitized
$bio = sanitize_string($bioRaw);
if (mb_strlen($bio) > 1000) {
    exit('Bio too long');
}

// 3. Call repository (SQL injection prevented by prepared statements)
$ok = $repo->updateProfile($id, $name, $email, $bio);

if ($ok) {
    echo 'Profile updated successfully';
} else {
    http_response_code(500);
    echo 'Failed to update profile';
}

// ----------------------------------------------------------------

<?php
$profile = $repo->getProfileById($id);
if ($profile): ?>
    <h1><?= htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p>Email: <?= htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') ?></p>
    <p>Bio: <?= nl2br(htmlspecialchars($profile['bio'], ENT_QUOTES, 'UTF-8')) ?></p>
<?php endif; ?>
