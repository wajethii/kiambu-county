<?php
// api/logout.php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page or homepage
header("Location: ../index.html"); // Adjust the path to your login page
exit;
