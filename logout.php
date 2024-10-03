<?php
// Start the session
session_start();

// Include the functions file
require_once 'functions.php';

// Call the logout_user function
logout_user();

// Redirect to the home page
header("Location: index.php");
exit();
