<?php
$conn = mysqli_connect("localhost", "root", "", "your_database");

// Read POST parameters
$user = $_POST['user'];
$pass = $_POST['pass'];

// Build query in the same style you're using
$sql = "SELECT * FROM users WHERE username = '".$user."' AND password = '".$pass."'";

$res = mysqli_query($conn, $sql);

// Check if a matching row exists
if (mysqli_num_rows($res) > 0) {
    echo "Success";
} else {
    echo "Invalid login";
}
?>
