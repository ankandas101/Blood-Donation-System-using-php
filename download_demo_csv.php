<?php
// Include necessary files
require_once 'config.php';
require_once 'functions.php';

// Check if the user is logged in and has admin privileges
session_start();
if (isset($_SESSION['user_id']) || $_SESSION['role'] == '1') {
    die("Access denied. Admin privileges required.");
}

// Check if the file exists
$filename = 'user_database_backup_' . date('Y-m-d') . '.csv';
$filepath = 'path/to/csv/files/' . $filename; // Update this path to the actual location of your CSV files

if (!file_exists($filepath)) {
    die("Error: The requested file does not exist.");
}

// Set headers for file download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));

// Output the file
readfile($filepath);
exit();
?>
