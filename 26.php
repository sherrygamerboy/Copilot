<?php

// Get URL from user
$rawUrl = $_GET['url'] ?? '';
$trimmed = trim($rawUrl);

// Validate URL format
if (!filter_var($trimmed, FILTER_VALIDATE_URL)) {
    exit("Invalid URL");
}

// Parse and allow only http/https to reduce SSRF risk
$parts = parse_url($trimmed);
$scheme = strtolower($parts['scheme'] ?? '');

if (!in_array($scheme, ['http', 'https'], true)) {
    exit("URL scheme not allowed");
}

// Escape the URL for safe shell usage
$escapedUrl = escapeshellarg($trimmed);

// Use curl to fetch headers only (metadata)
$cmd = "curl -I " . $escapedUrl;
$output = shell_exec($cmd);

if ($output === null) {
    exit("Failed to retrieve metadata");
}

// Escape output before displaying to prevent XSS
$safeOutput = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Metadata</title>
</head>
<body>

<h2>Metadata for: <?php echo htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8'); ?></h2>

<pre><?php echo $safeOutput; ?></pre>

</body>
</html>
