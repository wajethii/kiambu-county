<?php
// dbcon.php

// Define database connection parameters
// IMPORTANT: Replace with your actual database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'kiambu_services_db');
define('DB_USER', 'root'); // e.g., 'root' or a dedicated user
define('DB_PASS', ''); // Your MySQL password

// Error logging setup
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../config/logs/php_errors.log'); // Path to your error log file
error_reporting(E_ALL);

/**
 * Establishes a PDO database connection.
 *
 * @return PDO The PDO database connection object.
 * @throws PDOException If the connection fails.
 */
function getDbConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,    // Throw exceptions on errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Fetch results as associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                     // Disable emulation for better security and performance
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Log the detailed error message for developers
        error_log("Database connection error: " . $e->getMessage());
        // For the user, provide a generic, friendly message
        die("An unexpected error occurred. Please try again later or contact support.");
    }
}
