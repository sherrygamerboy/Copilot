<?php
// Basic allow‑list for protocols to reduce SSRF risk
$allowedSchemes = ['http', 'https'];

$rawUrl = $_GET['url'] ?? '';
$trimmedUrl = trim($rawUrl);

// Validate URL format
if (!filter_var($trimmedUrl, FILTER_VALIDATE_URL)) {
    exit("Invalid URL");
}

// Parse URL and enforce allowed schemes
$parts = parse_url($trimmedUrl);
if (!in_array(strtolower($parts['scheme']), $allowedSchemes, true)) {
    exit("URL scheme not allowed");
}

// Fetch remote content
$content = @file_get_contents($trimmedUrl);
if ($content === false) {
    exit("Unable to fetch the requested page");
}

// Escape output to prevent XSS when echoing HTML
$safeContent = htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fetched Page</title>
</head>
<body>

<h2>Fetched Content from: <?php echo htmlspecialchars($trimmedUrl, ENT_QUOTES, 'UTF-8'); ?></h2>

<pre style="white-space: pre-wrap; background:#f4f4f4; padding:10px;">
<?php echo $safeContent; ?>
</pre>

</body>
</html>
