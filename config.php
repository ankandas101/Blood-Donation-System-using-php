<?php

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'blood_donation_db');

// Establish database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to UTF-8
mysqli_set_charset($conn, "utf8");

// Application settings
define('SITE_NAME', 'Blood Donation System');
define('BASE_URL', 'http://localhost/bld/'); // Update this with your actual base URL

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session configuration
// Move these settings to php.ini or .htaccess to avoid the warning
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 1); // Enable this if using HTTPS

// Time zone setting
date_default_timezone_set('UTC'); // Change this to your preferred timezone

// Define user roles
define('ROLE_ADMIN', 'admin');
define('ROLE_MODERATOR','moderator');
define('ROLE_DONOR', 'donor');
define('ROLE_RECIPIENT', 'recipient');

// Other constants
define('MIN_DONATION_INTERVAL_DAYS', 56);
define('MIN_AGE_TO_DONATE', 18);
define('MAX_AGE_TO_DONATE', 65);

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Function to handle database errors
function handle_db_error($error_message) {
    error_log("Database Error: " . $error_message);
    die("An error occurred. Please try again later.");
}

// Removed the check_user_access function to avoid redeclaration

?>
