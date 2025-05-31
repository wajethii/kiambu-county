<?php
// api/register_customer.php

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

// Helper function for sanitization and validation
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

// Validate fields
if ($msg = validateInput($firstName, 'First Name', 2, 100)) $errors['first_name'] = $msg;
if ($msg = validateInput($lastName, 'Last Name', 2, 100)) $errors['last_name'] = $msg;
if ($msg = validateInput($password, 'Password', 8)) $errors['password'] = $msg;

// Email validation
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address format.';
}

// Phone number validation (basic regex check)
if (empty($phoneNumber)) {
    $errors['phone_number'] = 'Phone number is required.';
} elseif (!preg_match('/^\+254[0-9]{9}$/', $phoneNumber)) {
    $errors['phone_number'] = 'Phone number must be in the format +2547XXXXXXXX.';
}

// Password confirmation
if ($password !== $confirmPassword) {
    $errors['confirm_password'] = 'Passwords do not match.';
}

// If there are validation errors, return them
if (!empty($errors)) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'Validation errors.', 'errors' => $errors]);
    exit;
}

// 2. Hash the Password (CRUCIAL SECURITY STEP)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
if ($passwordHash === false) {
    // Log error if hashing fails (shouldn't happen often)
    error_log("Password hashing failed for email: " . $email);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An internal server error occurred during password processing.']);
    exit;
}

// 3. Database Interaction
try {
    $pdo = getDbConnection(); // Get the PDO connection from dbcon.php

    // Check for existing email or phone number to prevent duplicates
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR phone_number = :phone_number AND is_active = TRUE");
    $stmtCheck->bindParam(':email', $email);
    $stmtCheck->bindParam(':phone_number', $phoneNumber);
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'Email address or phone number already registered.']);
        exit;
    }


    // Prepare the SQL Statement for insertion
    $sql = "INSERT INTO users (first_name, last_name, email, phone_number, password_hash, user_type)
            VALUES (:first_name, :last_name, :email, :phone_number, :password_hash, 'Customer')";

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':first_name', $firstName);
    $stmt->bindParam(':last_name', $lastName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone_number', $phoneNumber);
    $stmt->bindParam(':password_hash', $passwordHash);

    // Execute the statement
    $stmt->execute();

    // Success response
    http_response_code(201); // Created
    echo json_encode(['success' => true, 'message' => 'Customer registered successfully!']);

} catch (PDOException $e) {
    // Log the specific database error for debugging
    error_log("Customer registration failed: " . $e->getMessage() . " - SQL: " . $stmt->queryString);
    // Provide a generic, user-friendly error message
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'An error occurred during registration. Please try again later.']);
} catch (Exception $e) {
    // Catch any other unexpected exceptions
    error_log("Unexpected error during customer registration: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred. Please try again later.']);
}
?>