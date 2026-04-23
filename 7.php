<?php
function searchProfilesByName(PDO $pdo, string $name): void
{
    // Prepare a LIKE query with wildcard matching
    $stmt = $pdo->prepare("SELECT name FROM profiles WHERE name LIKE :name");
    $stmt->execute([':name' => '%' . $name . '%']);

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<ul>";
    foreach ($results as $row) {
        echo "<li>" . htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul>";
}
?>

<!-- ----------------------------------------------------------------- -->

<?php
// Example usage after form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';

    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=localhost;dbname=your_database;charset=utf8mb4",
        "db_user",
        "db_pass",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    searchProfilesByName($pdo, $name);
}
?>
