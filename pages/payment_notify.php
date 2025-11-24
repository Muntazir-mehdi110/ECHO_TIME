<?php
// pages/payment_notify.php
require '../includes/db.php';

// PayFast sends POST data here after payment
$order_id = $_POST['merchant_order_id'] ?? null;
$status = $_POST['payment_status'] ?? null;

if ($order_id && $status === 'SUCCESS') {
    $stmt = mysqli_prepare($conn, "UPDATE orders SET status='Paid' WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

http_response_code(200); // Respond OK
