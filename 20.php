<?php
// Get the username from the URL
$rawUsername = $_GET['username'] ?? '';

// Escape before output to prevent XSS
$safeUsername = htmlspecialchars($rawUsername, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
</head>
<body>

<div>Profile for: <?php echo $safeUsername; ?></div>

</body>
</html>
