<?php
// Get the search query from the URL
$rawQuery = $_GET['search_query'] ?? '';

// Escape it before output to prevent XSS
$safeQuery = htmlspecialchars($rawQuery, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Search Results</title>
</head>
<body>

<h2>You searched for: <?php echo $safeQuery; ?></h2>

<!-- Your search results would go here -->

</body>
</html>
