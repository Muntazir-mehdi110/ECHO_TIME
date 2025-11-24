<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Ensure admin access
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Validate order ID
$order_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$order_id || $order_id <= 0) {
    $error_message = "Invalid or missing Order ID.";
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $new_status = $_POST['status'];
    $update_sql = "UPDATE orders SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, 'si', $new_status, $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    $success_message = "✅ Order status updated successfully!";
}

// Fetch order info
if (empty($error_message)) {
    $sql = "SELECT o.*, u.name AS customer_name, u.email 
            FROM orders o
            JOIN users u ON u.id = o.user_id
            WHERE o.id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);

    if (!$order) {
        $error_message = "Order not found or may have been deleted.";
    }
}

// Fetch ordered items (only if order found)
$items = [];
if (empty($error_message)) {
    $item_sql = "SELECT * FROM order_items WHERE order_id = ?";
    $item_stmt = mysqli_prepare($conn, $item_sql);
    mysqli_stmt_bind_param($item_stmt, 'i', $order_id);
    mysqli_stmt_execute($item_stmt);
    $item_res = mysqli_stmt_get_result($item_stmt);
    while ($row = mysqli_fetch_assoc($item_res)) {
        $items[] = $row;
    }
    mysqli_stmt_close($item_stmt);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Order Details</title>
<link rel="stylesheet" href="admin.css">
<style>
body { font-family: Arial, sans-serif; background:#f8f9fa; margin:0; }
.admin-main-content { padding:20px; }
.error-box, .success-box { padding:15px; border-radius:8px; margin:20px 0; font-weight:500; }
.error-box { background:#ffe6e6; color:#b71c1c; }
.success-box { background:#e6ffe6; color:#1b5e20; }
.order-summary { background:#fff; padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
.order-items table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 2px 6px rgba(0,0,0,0.05); }
.order-items th, .order-items td { padding:12px 15px; border-bottom:1px solid #f0f0f0; text-align:left; }
.order-items th { background:#0d6efd; color:#fff; }
.btn-update { padding:10px 18px; background:#0d6efd; color:white; border:none; border-radius:6px; cursor:pointer; font-size:15px; }
.btn-update:hover { background:#0b5ed7; }
select { padding:8px 10px; border-radius:6px; border:1px solid #ccc; margin-right:10px; }
a { color:#0d47a1; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="admin-main-content">
        <h2>Order Details</h2>

        <?php if (!empty($error_message)): ?>
            <div class="error-box">
                ❌ <?= esc($error_message) ?><br><br>
                <a href="orders.php">← Return to Orders</a>
            </div>
            <?php exit; ?>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="success-box"><?= esc($success_message) ?></div>
        <?php endif; ?>

        <div class="order-summary">
            <h3>Customer Info</h3>
            <p><strong>Name:</strong> <?= esc($order['customer_name']) ?></p>
            <p><strong>Email:</strong> <?= esc($order['email']) ?></p>
            <p><strong>Payment Method:</strong> <?= esc(ucfirst($order['payment_method'] ?? 'N/A')) ?></p>
            <p><strong>Payment Status:</strong> <?= esc(ucfirst($order['payment_status'] ?? 'Pending')) ?></p>
            <p><strong>Total Amount:</strong> ₹<?= formatPrice($order['total_amount'] ?? 0) ?></p>
            <p><strong>Order Date:</strong> <?= esc(date('Y-m-d H:i', strtotime($order['created_at'] ?? ''))) ?></p>
        </div>

        <div class="order-items">
            <h3>Ordered Items</h3>
            <?php if (!empty($items)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= esc($item['id']) ?></td>
                                <td><?= esc($item['quantity']) ?></td>
                                <td>₹<?= formatPrice($item['price']) ?></td>
                                <td>₹<?= formatPrice($item['quantity'] * $item['price']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="padding:10px;">No items found for this order.</p>
            <?php endif; ?>
        </div>

        <form method="POST" style="margin-top:25px;">
            <label><strong>Update Order Status:</strong></label>
            <select name="status">
                <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
            <button type="submit" class="btn-update">Update Status</button>
        </form>

        <p style="margin-top:20px;"><a href="orders.php">← Back to Orders</a></p>
    </div>
</div>

</body>
</html>
