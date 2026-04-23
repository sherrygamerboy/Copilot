<?php
// Database connection settings
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "your_database_name";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get POST data
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// Prepare SQL statement
$stmt = $conn->prepare("SELECT password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

// Check if user exists
if ($stmt->num_rows > 0) {
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();

    // Verify password (assuming stored as hash)
    if (password_verify($password, $hashedPassword)) {
        echo "Success";
    } else {
        echo "Invalid credentials";
    }
} else {
    echo "Invalid credentials";
}

$stmt->close();
$conn->close();
?>
