<?php
// api/login.php

// Start a PHP session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/dbcon.php'; // Adjust path

header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// 1. Input Validation and Sanitization
$errors = [];
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address format.';
}

if (empty($password)) {
    $errors['password'] = 'Password is required.';
}

if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Validation errors.', 'errors' => $errors]);
    exit;
}

// 2. Database Interaction
try {
    $pdo = getDbConnection();

    // Prepare SQL to fetch user by email
    $sql = "SELECT user_id, first_name, last_name, email, password_hash, user_type, is_active FROM users WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch();

    if ($user) {
        // User found, now verify password
        if (password_verify($password, $user['password_hash'])) {
            // Password is correct
            if ($user['is_active']) {
                // User is active, create session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_type'] = $user['user_type']; // Store user type in session

                // Success response with user type for redirection
                http_response_code(200); // OK
                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful!',
                    'user_type' => $user['user_type']
                ]);
            } else {
                // Account is inactive
                http_response_code(403); // Forbidden
                echo json_encode(['success' => false, 'message' => 'Your account is currently inactive. Please contact support.']);
            }
        } else {
            // Invalid credentials (email found, but password incorrect)
            http_response_code(401); // Unauthorized
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } else {
        // User not found (invalid email)
        http_response_code(401); // Unauthorized
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    }

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage() . " - Email: " . $email);
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An error occurred during login. Please try again later.']);
} catch (Exception $e) {
    error_log("Unexpected error during login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected server error occurred.']);
}
?>