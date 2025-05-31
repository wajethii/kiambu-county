<?php
// api/add_service_offering.php

require_once '../config/dbcon.php'; // Adjust path as needed

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// 1. Authentication (CRUCIAL FOR PROVIDER-SPECIFIC ACTIONS)
// For MVP, we'll simulate a logged-in provider.
// In a real app, you'd verify a session, JWT token, etc.
// For now, let's assume `provider_id` is passed from the client for testing.
// In a production environment, NEVER trust client-provided provider_id directly.
// It must come from an authenticated session.
$providerId = $_POST['provider_id'] ?? null; // Simulate: replace with actual session data
if (!isset($providerId) || !is_numeric($providerId) || $providerId <= 0) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Provider not authenticated.']);
    exit;
}

// 2. Input Validation and Sanitization
$errors = [];

function validateInput($input, $fieldName, $minLength = 0, $maxLength = 255) {
    $input = trim($input);
    if (empty($input) && $minLength > 0) {
        return "{$fieldName} is required.";
    }
    if (strlen($input) < $minLength || strlen($input) > $maxLength) {
        return "{$fieldName} must be between {$minLength} and {$maxLength} characters.";
    }
    return '';
}

$serviceId = $_POST['service_id'] ?? '';
$price = $_POST['price'] ?? '';
$availabilityStatus = $_POST['availability_status'] ?? 'Available'; // Default

// Validate fields
if (empty($serviceId) || !is_numeric($serviceId) || $serviceId <= 0) {
    $errors['service_id'] = 'Please select a valid service.';
}
if (empty($price) || !is_numeric($price) || $price < 0) {
    $errors['price'] = 'Price must be a non-negative number.';
} else {
    $price = round((float)$price, 2); // Ensure it's a float with 2 decimal places
}

// Validate availability status (must be one of the ENUM values)
$allowedStatuses = ['Available', 'Busy', 'Offline'];
if (!in_array($availabilityStatus, $allowedStatuses)) {
    $errors['availability_status'] = 'Invalid availability status.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Validation errors.', 'errors' => $errors]);
    exit;
}

try {
    $pdo = getDbConnection();

    // Verify provider exists and is active
    $stmtProviderCheck = $pdo->prepare("SELECT provider_id FROM service_providers WHERE provider_id = :provider_id AND is_active = TRUE");
    $stmtProviderCheck->bindParam(':provider_id', $providerId);
    $stmtProviderCheck->execute();
    if (!$stmtProviderCheck->fetch()) {
        http_response_code(403); // Forbidden
        echo json_encode(['success' => false, 'message' => 'Invalid or inactive provider account.']);
        exit;
    }

    // Verify service exists and is active
    $stmtServiceCheck = $pdo->prepare("SELECT service_id FROM services WHERE service_id = :service_id AND is_active = TRUE");
    $stmtServiceCheck->bindParam(':service_id', $serviceId);
    $stmtServiceCheck->execute();
    if (!$stmtServiceCheck->fetch()) {
        http_response_code(404); // Not Found
        echo json_encode(['success' => false, 'message' => 'Selected service not found or is inactive.']);
        exit;
    }

    // Check for duplicate offering (a provider cannot offer the same service twice)
    $stmtDuplicate = $pdo->prepare("SELECT COUNT(*) FROM service_offerings WHERE service_id = :service_id AND provider_id = :provider_id AND is_active = TRUE");
    $stmtDuplicate->bindParam(':service_id', $serviceId);
    $stmtDuplicate->bindParam(':provider_id', $providerId);
    $stmtDuplicate->execute();
    if ($stmtDuplicate->fetchColumn() > 0) {
        http_response_code(409); // Conflict
        echo json_encode(['success' => false, 'message' => 'You are already offering this service. Please update the existing offering instead.']);
        exit;
    }

    // Insert into `service_offerings` table
    $sql = "INSERT INTO service_offerings (service_id, provider_id, price, availability_status)
            VALUES (:service_id, :provider_id, :price, :availability_status)";
    $stmt = $pdo->prepare($sql);

    $stmt->bindParam(':service_id', $serviceId);
    $stmt->bindParam(':provider_id', $providerId);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':availability_status', $availabilityStatus);

    $stmt->execute();

    http_response_code(201); // Created
    echo json_encode(['success' => true, 'message' => 'Service offering added successfully!']);

} catch (PDOException $e) {
    error_log("Add service offering failed: " . $e->getMessage() . " - SQL: " . $stmt->queryString);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding the service offering. Please try again later.']);
} catch (Exception $e) {
    error_log("Unexpected error adding service offering: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>