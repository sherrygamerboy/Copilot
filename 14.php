<?php

// Folder where invoices are stored
$baseDir = __DIR__ . '/invoices/';

// Get filename from URL
$filename = $_GET['file'] ?? '';

// Basic validation: allow only .pdf files with safe characters
if (!preg_match('/^[a-zA-Z0-9_\-]+\.pdf$/', $filename)) {
    exit("Invalid filename");
}

// Build full path
$fullPath = $baseDir . $filename;

// Check file exists
if (!file_exists($fullPath)) {
    exit("File not found");
}

// Send headers for download
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Content-Length: " . filesize($fullPath));

// Serve file (your style)
readfile($fullPath);
exit;
