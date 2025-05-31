<?php
// api/get_services.php

require_once '../config/dbcon.php'; // Adjust path as needed

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

try {
    $pdo = getDbConnection();

    // Select active services
    $sql = "SELECT service_id, service_name FROM services WHERE is_active = TRUE ORDER BY service_name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200); // OK
    echo json_encode(['success' => true, 'services' => $services]);

} catch (PDOException $e) {
    error_log("Failed to fetch services: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not retrieve services. Please try again later.']);
} catch (Exception $e) {
    error_log("Unexpected error fetching services: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>