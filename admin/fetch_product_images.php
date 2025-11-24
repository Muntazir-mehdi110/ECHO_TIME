<?php
// admin/fetch_product_images.php

session_start();
require_once __DIR__ . '/../includes/db.php'; 
require_once __DIR__ . '/../includes/functions.php'; 

// Set the header to indicate a JSON response
header('Content-Type: application/json');

// Basic Security Checks
if (!is_logged_in() || !is_admin()) {
    http_response_code(403); // Forbidden
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

// Get the Product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($productId === 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing product ID.']);
    exit;
}

// Fetch Additional Images from the Database (Assumes a table named 'product_images')
$stmt = mysqli_prepare($conn, "SELECT id, image_path FROM product_images WHERE product_id = ? ORDER BY id ASC");
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$images = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Return the result as JSON
echo json_encode([
    'success' => true,
    'images' => $images
]);

exit;
?>