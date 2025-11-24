<?php
// admin/orders.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Ensure admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Filters
$search_query = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING) ?: '';
$status_filter = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_STRING) ?: 'all';

// Build SQL
$sql = "SELECT o.*, u.name AS customer_name, u.email 
        FROM orders o 
        JOIN users u ON u.id = o.user_id";

$conditions = [];
$params = [];
$param_types = '';

if (!empty($search_query)) {
    // if user typed numeric id, we want to allow search by id as well
    $conditions[] = "(u.name LIKE ? OR u.email LIKE ? OR o.id = ?)";
    $params[] = '%' . $search_query . '%';
    $params[] = '%' . $search_query . '%';
    // cast to int for id search; if not numeric it's 0 (no match)
    $params[] = (int)$search_query;
    $param_types .= 'ssi';
}

if (!empty($status_filter) && $status_filter !== 'all') {
    $conditions[] = "o.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= " ORDER BY o.created_at DESC";

// Execute
$stmt = mysqli_prepare($conn, $sql);
$orders = [];
if ($stmt) {
    if (!empty($params)) {
        // bind_param requires variables; build array of references
        $bind_names[] = $param_types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
        unset($bind_names);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $orders[] = $r;
    }
    mysqli_stmt_close($stmt);
} else {
    $error = "Database query failed: " . mysqli_error($conn);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Orders</title>
<link rel="stylesheet" href="admin.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<style>
/* small in-file helpers — move to admin.css if you want */
.text-success { color: #2e7d32; font-weight:600; }
.text-warning { color: #f57c00; font-weight:600; }
.data-table { width:100%; border-collapse: collapse; }
.data-table th, .data-table td { padding:10px 12px; border-bottom:1px solid #efefef; text-align:left; }
.status { padding:6px 10px; border-radius:8px; font-weight:600; font-size:0.9em; color:#fff; }
.status.pending { background:#ffb300; }
.status.shipped { background:#1e90ff; }
.status.delivered { background:#43a047; }
.status.cancelled { background:#d32f2f; }
.action-buttons .btn { display:inline-block; padding:8px 12px; border-radius:8px; text-decoration:none; margin-right:6px; }
.btn-primary { background:#0d52a0; color:#fff; }
.filter-select, .search-input { padding:8px 10px; border-radius:6px; border:1px solid #ddd; }
.search-form { display:flex; gap:8px; align-items:center; }
.table-controls { display:flex; gap:12px; align-items:center; }
</style>
</head>
<body>
<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="admin-main-content">
        <div class="admin-topbar">
            <h2>Manage Orders</h2>
        </div>

        <div class="dashboard-section">
            <div class="table-header" style="display:flex;justify-content:space-between;align-items:center;">
                <h3>All Orders</h3>
                <div class="table-controls">
                    <form action="orders.php" method="GET" class="search-form" style="margin:0;">
                        <input type="text" name="search" placeholder="Search orders..." value="<?= esc($search_query) ?>" class="search-input">
                        <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                        <select name="status" onchange="this.form.submit()" class="filter-select" style="margin-left:8px;">
                            <option value="all" <?= $status_filter == 'all' ? 'selected' : '' ?>>All Statuses</option>
                            <option value="pending" <?= $status_filter == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="shipped" <?= $status_filter == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $status_filter == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $status_filter == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </form>
                </div>
            </div>

            <?php if (!empty($error)): ?>
                <p class="error-message"><?= esc($error) ?></p>
            <?php else: ?>
                <div class="table-container" style="margin-top:18px;">
                    <table class="data-table">
                        <thead>
                           <tr>
                             <th>Order #</th>
                             <th>Customer</th>
                             <th>Total</th>
                             <th>Payment</th>
                             <th>Status</th>
                             <th>Date</th>
                             <th>Actions</th>
                           </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($orders)): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><?= esc($o['id']) ?></td>
                                    <td><?= esc($o['customer_name'] ?? 'Unknown') ?> (<?= esc($o['email'] ?? '') ?>)</td>
                                    <td>₹<?= formatPrice($o['total_amount'] ?? 0) ?></td>
                                    <td>
                                        <?= esc(ucfirst($o['payment_method'] ?? 'Not specified')) ?><br>
                                        <small class="<?= (isset($o['payment_status']) && strtolower($o['payment_status']) === 'paid') ? 'text-success' : 'text-warning' ?>">
                                            <?= esc(ucfirst($o['payment_status'] ?? 'Pending')) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php $st = strtolower($o['status'] ?? 'pending'); ?>
                                        <span class="status <?= esc($st) ?>"><?= esc(ucfirst($o['status'] ?? 'Pending')) ?></span>
                                    </td>
                                    <td><?= esc(date('Y-m-d H:i', strtotime($o['created_at'] ?? '1970-01-01 00:00:00'))) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="order_detail.php?id=<?= esc($o['id']) ?>" class="btn btn-primary">View</a>
                                            <!-- future: edit status / export / refund buttons can go here -->
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align:center;">No orders found.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
