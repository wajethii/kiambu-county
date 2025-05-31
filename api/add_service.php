<?php
// api/add_service.php

require_once '../config/dbcon.php'; // Adjust path as needed

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Input Validation and Sanitization
$errors = [];

function validateInput($input, $fieldName, $minLength = 0, $maxLength = 255) {
    $input = trim($input);
    if (empty($input)) {
        return "{$fieldName} is required.";
    }
    if (strlen($input) < $minLength || strlen($input) > $maxLength) {
        return "{$fieldName} must be between {$minLength} and {$maxLength} characters.";
    }
    return '';
}

$serviceName = $_POST['service_name'] ?? '';
$description = $_POST['description'] ?? '';
$category = $_POST['category'] ?? '';
$basePrice = $_POST['base_price'] ?? null; // Can be null if not provided

// Validate fields
if ($msg = validateInput($serviceName, 'Service Name', 3, 100)) $errors['service_name'] = $msg;
if ($msg = validateInput($description, 'Description', 10, 1000)) $errors['description'] = $msg;
if ($msg = validateInput($category, 'Category', 3, 100)) $errors['category'] = $msg;

// Validate base price if provided
if ($basePrice !== null && $basePrice !== '') {
    if (!is_numeric($basePrice) || $basePrice < 0) {
        $errors['base_price'] = 'Base Price must be a non-negative number.';
    } else {
        $basePrice = round((float)$basePrice, 2); // Ensure it's a float with 2 decimal places
    }
} else {
    $basePrice = null; // Ensure null if empty
}


if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation errors.', 'errors' => $errors]);
    exit;
}

try {
    $pdo = getDbConnection();

    // Check if service name already exists (and is active)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM services WHERE service_name = :service_name AND is_active = TRUE");
    $stmtCheck->bindParam(':service_name', $serviceName);
    $stmtCheck->execute();
    if ($stmtCheck->fetchColumn() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'A service with this name already exists.']);
        exit;
    }

    $sql = "INSERT INTO services (service_name, description, category, base_price)
            VALUES (:service_name, :description, :category, :base_price)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':service_name', $serviceName);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':category', $category);
    // Bind base_price, allowing null
    if ($basePrice === null) {
        $stmt->bindValue(':base_price', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindParam(':base_price', $basePrice);
    }

    $stmt->execute();

    http_response_code(201); // Created
    echo json_encode(['success' => true, 'message' => 'Service added successfully!']);

} catch (PDOException $e) {
    error_log("Add service failed: " . $e->getMessage() . " - SQL: " . $stmt->queryString);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the service. Please try again later.']);
} catch (Exception $e) {
    error_log("Unexpected error adding service: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>