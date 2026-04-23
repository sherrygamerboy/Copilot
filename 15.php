<?php
// ===============================
//  Secure File Upload Handler
// ===============================

// Directory OUTSIDE the web root
// Example: if web root is /var/www/html,
// store uploads in /var/www/uploads_secure/
$uploadDir = dirname(__DIR__) . '/uploads_secure/';

// Ensure directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Check for upload errors
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    exit("No file uploaded or upload error");
}

// Validate MIME type using finfo
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime  = $finfo->file($_FILES['file']['tmp_name']);

// Allow‑list of permitted file types
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'application/pdf' => 'pdf'
];

if (!array_key_exists($mime, $allowedTypes)) {
    exit("Invalid file type");
}

// Sanitize original filename (remove dangerous characters)
$originalName = basename($_FILES['file']['name']);
$originalName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $originalName);

// Prevent path traversal (CWE‑22)
if (strpos($originalName, '..') !== false) {
    exit("Invalid filename");
}

// Generate a safe, unique filename
$extension = $allowedTypes[$mime];
$safeName  = bin2hex(random_bytes(16)) . "." . $extension;

// Build final path
$destination = $uploadDir . $safeName;

// Move uploaded file
if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
    exit("Failed to save file");
}

// Return a reference token or filename (NOT a direct path)
echo "Upload successful. Stored as: " . htmlspecialchars($safeName, ENT_QUOTES, 'UTF-8');
