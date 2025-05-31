<?php
// api/register_provider.php

// Include the database connection file
require_once '../config/dbcon.php'; // Adjust path if dbcon.php is in a different location

header('Content-Type: application/json'); // API will respond with JSON

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// 1. Input Validation and Sanitization
$errors = [];

// Helper function for sanitization and validation (can be shared/reused)
function validateInput($input, $fieldName, $minLength = 0, $maxLength = 255) {
    $input = trim($input);
    if (empty($input)) {
        return "{$fieldName} is required.";
    }
    if (strlen($input) < $minLength || strlen($input) > $maxLength) {
        return "{$fieldName} must be between {$minLength} and {$maxLength} characters.";
    }
    return ''; // No error
}

$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';
$email = $_POST['email'] ?? '';
$phoneNumber = $_POST['phone_number'] ?? '';
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$businessName = $_POST['business_name'] ?? ''; // Optional field
$bio = $_POST['bio'] ?? '';
$locationData = $_POST['location_data'] ?? '';

// Validate user fields
if ($msg = validateInput($firstName, 'First Name', 2, 100)) $errors['first_name'] = $msg;
if ($msg = validateInput($lastName, 'Last Name', 2, 100)) $errors['last_name'] = $msg;
if ($msg = validateInput($password, 'Password', 8)) $errors['password'] = $msg;

// Email validation
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address format.';
}

// Phone number validation
if (empty($phoneNumber)) {
    $errors['phone_number'] = 'Phone number is required.';
} elseif (!preg_match('/^\+254[0-9]{9}$/', $phoneNumber)) {
    $errors['phone_number'] = 'Phone number must be in the format +2547XXXXXXXX.';
}

// Password confirmation
if ($password !== $confirmPassword) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

// Validate provider specific fields
if ($msg = validateInput($bio, 'Bio', 20, 1000)) $errors['bio'] = $msg; // Min 20 chars for a decent bio
if ($msg = validateInput($locationData, 'Primary Service Area', 2, 255)) $errors['location_data'] = $msg;

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Validation errors.', 'errors' => $errors]);
    exit;
}

// 2. Hash the Password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    error_log("Password hashing failed for email: " . $email);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred during password processing.']);
    exit;
}

// 3. Database Interaction
try {
    $pdo = getDbConnection(); // Get the PDO connection from dbcon.php
    $pdo->beginTransaction(); // Start a transaction for atomicity

    // Check for existing email or phone number first
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR phone_number = :phone_number AND is_active = TRUE");
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->bindParam(':phone_number', $phoneNumber);
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
        $pdo->rollBack(); // Rollback any changes (though none yet for this transaction)
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'Email address or phone number already registered.']);
        exit;
    }

    // Insert into `users` table
    $sqlUser = "INSERT INTO users (first_name, last_name, email, phone_number, password_hash, user_type)
                VALUES (:first_name, :last_name, :email, :phone_number, :password_hash, 'Service Provider')";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->bindParam(':first_name', $firstName);
    $stmtUser->bindParam(':last_name', $lastName);
    $stmtUser->bindParam(':email', $email);
    $stmtUser->bindParam(':phone_number', $phoneNumber);
    $stmtUser->bindParam(':password_hash', $passwordHash);
    $stmtUser->execute();

    $userId = $pdo->lastInsertId(); // Get the ID of the newly created user

    // Insert into `service_providers` table
    $sqlProvider = "INSERT INTO service_providers (user_id, business_name, bio, location_data)
                    VALUES (:user_id, :business_name, :bio, :location_data)";
    $stmtProvider = $pdo->prepare($sqlProvider);
    $stmtProvider->bindParam(':user_id', $userId);
    $stmtProvider->bindParam(':business_name', $businessName);
    $stmtProvider->bindParam(':bio', $bio);
    $stmtProvider->bindParam(':location_data', $locationData);
    $stmtProvider->execute();

    $pdo->commit(); // Commit the transaction if all inserts were successful

    // Success response
    http_response_code(201); // Created
    echo json_encode(['success' => true, 'message' => 'Service provider registered successfully!']);

} catch (PDOException $e) {
    $pdo->rollBack(); // Rollback changes if any part of the transaction fails
    error_log("Service provider registration failed: " . $e->getMessage() . " - SQL: " . ($stmtUser->queryString ?? $stmtProvider->queryString ?? 'Unknown SQL'));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again later.']);
} catch (Exception $e) {
    $pdo->rollBack(); // Catch any other unexpected exceptions
    error_log("Unexpected error during service provider registration: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']);
}
?>