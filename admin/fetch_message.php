<?php
// admin/fetch_message.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in() || !is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Message ID is missing.']);
    exit;
}

$message_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$message_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid message ID.']);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT name, email, subject, message, is_read FROM contact_messages WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $message_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$message = mysqli_fetch_assoc($result);

if ($message) {
    // Optionally, mark as read on view
    $update_stmt = mysqli_prepare($conn, "UPDATE contact_messages SET is_read = TRUE WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, 'i', $message_id);
    mysqli_stmt_execute($update_stmt);
    mysqli_stmt_close($update_stmt);

    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Message not found.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>