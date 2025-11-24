<?php
// admin/admin_settings_ajax.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Set the response header to JSON
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

// Check for POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize and validate input
$site_name = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_STRING);
$contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
$address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

// Additional validation
if (empty($site_name) || $contact_email === false || empty($address)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required and must be valid.']);
    exit;
}

// Update each setting in the database using a transaction for safety
mysqli_begin_transaction($conn);
try {
    $update_queries = [
        'site_name' => $site_name,
        'contact_email' => $contact_email,
        'address' => $address
    ];

    foreach ($update_queries as $name => $value) {
        $stmt = mysqli_prepare($conn, "UPDATE settings SET setting_value = ? WHERE setting_name = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt, 'ss', $value, $name);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'message' => 'Settings updated successfully!']);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conn);
exit;
?>