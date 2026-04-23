<?php

// Assume $db is your mysqli connection and get_raw() is available

// Get user input
$userId = $_POST['id'] ?? '';
$bio    = $_POST['bio'] ?? '';

// Basic validation
if ($userId === '' || $bio === '') {
    exit("Missing fields");
}

// Escape input to avoid SQL injection
$escapedBio = mysqli_real_escape_string($this->db, $bio);
$escapedId  = (int)$userId; // cast to int for safety

// Build query using your helper style
$query = "UPDATE profiles SET bio = '" . $escapedBio . "' WHERE id = " . $escapedId;

// Execute
$result = $this->get_raw($query);

if ($result) {
    echo "Bio updated";
} else {
    echo "Update failed";
}
