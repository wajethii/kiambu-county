<?php
// create_admin.php

// Start a PHP session if not already started (not strictly needed for this script, but good practice if you're including other files that use sessions)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include your database connection file
require_once '../config/dbcon.php'; // Adjust path if dbcon.php is in a different location (e.g., '../dbcon.php')

// --- ADMIN USER DETAILS ---
// CHANGE THESE TO YOUR DESIRED ADMIN CREDENTIALS!
$adminFirstName = 'Admin';
$adminLastName = 'User';
$adminEmail = 'admin@kiambuservices.com'; // **CRITICAL: Change this email**
$adminPassword = '@dmin2025'; // **CRITICAL: Change this to a very strong password**
$adminUserType = 'Admin'; // Ensure this matches a user_type in your database schema

// --------------------------

header('Content-Type: text/html; charset=utf-8'); // Set header for plain text output in browser

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin User Creation</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 flex items-center justify-center min-h-screen p-4'>
    <div class='bg-white p-8 rounded-lg shadow-lg text-center w-full max-w-md'>";

// Hash the password
$passwordHash = password_hash($adminPassword, PASSWORD_DEFAULT);

if ($passwordHash === false) {
    error_log("Password hashing failed for admin user creation.");
    echo "<h1 class='text-3xl font-bold text-red-600 mb-4'>Error!</h1>";
    echo "<p class='text-red-500'>Failed to hash the admin password. Please check your PHP configuration.</p>";
    echo "</div></body></html>";
    exit;
}

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // Check if an admin with this email already exists
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email AND user_type = :user_type");
    $stmtCheck->bindParam(':email', $adminEmail);
    $stmtCheck->bindParam(':user_type', $adminUserType);
    $stmtCheck->execute();

    if ($stmtCheck->fetchColumn() > 0) {
        $pdo->rollBack();
        echo "<h1 class='text-3xl font-bold text-yellow-600 mb-4'>Warning!</h1>";
        echo "<p class='text-yellow-500'>An admin user with the email '{$adminEmail}' already exists. No new admin user was created.</p>";
    } else {
        // Insert the new admin user
        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, user_type, is_active)
                VALUES (:first_name, :last_name, :email, :password_hash, :user_type, TRUE)"; // Set is_active to TRUE for admin

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':first_name', $adminFirstName);
        $stmt->bindParam(':last_name', $adminLastName);
        $stmt->bindParam(':email', $adminEmail);
        $stmt->bindParam(':password_hash', $passwordHash);
        $stmt->bindParam(':user_type', $adminUserType);
        $stmt->execute();

        $pdo->commit();
        echo "<h1 class='text-3xl font-bold text-green-600 mb-4'>Success!</h1>";
        echo "<p class='text-green-500'>Admin user '{$adminEmail}' created successfully.</p>";
    }

} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Failed to create admin user: " . $e->getMessage());
    echo "<h1 class='text-3xl font-bold text-red-600 mb-4'>Error!</h1>";
    echo "<p class='text-red-500'>An error occurred while creating the admin user: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p class='text-gray-500 text-sm mt-2'>Check your PHP error log for more details.</p>";
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Unexpected error during admin user creation: " . $e->getMessage());
    echo "<h1 class='text-3xl font-bold text-red-600 mb-4'>Error!</h1>";
    echo "<p class='text-red-500'>An unexpected server error occurred.</p>";
    echo "<p class='text-gray-500 text-sm mt-2'>Check your PHP error log for more details.</p>";
}

echo "<p class='text-gray-600 mt-6'><strong>Remember to delete or rename this script after use for security!</strong></p>";
echo "</div></body></html>";
?>