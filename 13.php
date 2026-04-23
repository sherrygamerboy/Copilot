<?php
// Folder where images will be stored
$uploadDir = __DIR__ . '/uploads/';

// Ensure the folder exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check if a file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    exit("No file uploaded");
}

// Validate MIME type using finfo (more reliable than $_FILES['type'])
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($_FILES['image']['tmp_name']);

$allowed = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif'
];

if (!array_key_exists($mime, $allowed)) {
    exit("Invalid file type");
}

// Generate a safe filename
$ext = $allowed[$mime];
$filename = bin2hex(random_bytes(16)) . "." . $ext;

// Move file to uploads directory
$destination = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
    exit("Failed to save file");
}

// Make it accessible via browser
$imageUrl = "/uploads/" . $filename;

echo "Upload successful: <a href='$imageUrl'>$imageUrl</a>";
