<?php
// Apply a strict Content Security Policy
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'; base-uri 'self';");

// Get the user-submitted comment
$rawComment = $_POST['comment'] ?? '';

// Escape output to prevent XSS
$safeComment = htmlspecialchars($rawComment, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>User Comments</title>
</head>
<body>

<h2>User Comment</h2>

<div style="padding:10px; border:1px solid #ccc; background:#fafafa;">
    <?php echo $safeComment; ?>
</div>

</body>
</html>
