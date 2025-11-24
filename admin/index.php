<?php
// admin/index.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) { 
    header('Location: login.php');
    exit;
}

// Fetch some data for the dashboard KPIs
$total_products = get_count($conn, 'products');
$total_orders = get_count($conn, 'orders');
$total_customers = get_count($conn, 'users');
$today_sales = get_daily_sales($conn, 'orders', 'created_at');

// Fetch new KPI: Out of Stock Products
$out_of_stock_products = get_count_where($conn, 'products', 'stock_quantity <= 0');

// Fetch unread messages for notifications
$recent_messages = [];
$unread_notifications_count = 0;
$stmt = mysqli_prepare($conn, "SELECT name, message, created_at FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 5");
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_messages[] = $row;
    }
    mysqli_stmt_close($stmt);
}
$unread_notifications_count = count($recent_messages);

// Fetch recent orders
$recent_orders = [];
$stmt = mysqli_prepare($conn, "SELECT id, user_id, total_amount, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_orders[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Fetch recent customers
$recent_customers = [];
$stmt = mysqli_prepare($conn, "SELECT id, name, email, created_at FROM users WHERE role = 'user' ORDER BY created_at DESC LIMIT 5");
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        $recent_customers[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Fetch data for the sales chart
$sales_by_category = [];
$stmt = $conn->prepare("
    SELECT c.name, SUM(oi.quantity * p.price) as total_sales
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    GROUP BY c.name
    ORDER BY total_sales DESC
");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sales_by_category[] = $row;
    }
    $stmt->close();
}

$chart_labels = json_encode(array_column($sales_by_category, 'name'));
$chart_data = json_encode(array_column($sales_by_category, 'total_sales'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="admin.css">
    <style>
      
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>


    <div class="admin-main-content">
        <div class="admin-topbar">
    <h2>Welcome, <?= esc($_SESSION['user_name'] ?? 'Admin') ?>!</h2>
    <div class="topbar-actions">
        <div class="notification-dropdown">
            <a href="#" class="notification-icon" id="notification-bell">
                <i class="fas fa-bell"></i>
                <?php if ($unread_notifications_count > 0): ?>
                    <span class="notification-count"><?= esc($unread_notifications_count) ?></span>
                <?php endif; ?>
            </a>
            <div class="dropdown-content" id="notification-list">
                <h4 class="dropdown-header">Recent Messages</h4>
                <ul class="message-list-dropdown">
                    <?php if (empty($recent_messages)): ?>
                        <li><p>No new messages.</p></li>
                    <?php else: ?>
                        <?php foreach ($recent_messages as $message): ?>
                            <li class="dropdown-item">
                                <div class="message-info">
                                    <strong class="user-info"><?= esc($message['name']) ?>:</strong>
                                    <span class="message-text"><?= esc(truncate_text($message['message'], 50)) ?></span>
                                </div>
                                <small class="message-time"><?= esc(date('M d, Y', strtotime($message['created_at']))) ?></small>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <div class="dropdown-footer">
                    <a href="messages.php">View All Messages</a>
                </div>
                </div>
        </div>
        
        <a href="profile.php" class="user-profile">
            <i class="fas fa-user-circle"></i> Profile
        </a>
    </div>
</div>

        <div class="kpi-grid">
            <div class="kpi-card">
                <h4>Total Products</h4>
                <div class="kpi-value"><?= esc($total_products) ?></div>
            </div>
            <div class="kpi-card">
                <h4>Total Orders</h4>
                <div class="kpi-value"><?= esc($total_orders) ?></div>
            </div>
            <div class="kpi-card">
                <h4>Total Customers</h4>
                <div class="kpi-value"><?= esc($total_customers) ?></div>
            </div>
            <div class="kpi-card green">
                <h4>Today's Sales</h4>
                <div class="kpi-value">₹<?= formatPrice($today_sales) ?></div>
            </div>
            <div class="kpi-card red">
                <h4>Out of Stock</h4>
                <div class="kpi-value"><?= esc($out_of_stock_products) ?></div>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h3>Product Sales by Category</h3>
            <canvas id="salesChart"></canvas>
        </div>

        <div class="dashboard-section">
            <h3>Recent Orders</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer ID</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr><td colspan="5">No recent orders.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?= esc($order['id']) ?></td>
                                <td><?= esc($order['user_id']) ?></td>
                                <td>₹<?= formatPrice($order['total_amount']) ?></td>
                                <td><span class="status <?= strtolower(esc($order['status'])) ?>"><?= esc(ucfirst($order['status'])) ?></span></td>
                                <td><?= esc(date('M d, Y', strtotime($order['created_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="dashboard-section">
            <h3>New Customers</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_customers)): ?>
                            <tr><td colspan="4">No new customers.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_customers as $customer): ?>
                            <tr>
                                <td><?= esc($customer['id']) ?></td>
                                <td><?= esc($customer['name']) ?></td>
                                <td><?= esc($customer['email']) ?></td>
                                <td><?= esc(date('M d, Y', strtotime($customer['created_at']))) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Notification Dropdown Logic ---
        const bellIcon = document.getElementById('notification-bell');
        const dropdownList = document.getElementById('notification-list');

        if (bellIcon && dropdownList) {
            bellIcon.addEventListener('click', function(e) {
                e.preventDefault();
                dropdownList.classList.toggle('show');
            });

            document.addEventListener('click', function(e) {
                const isClickInside = dropdownList.contains(e.target) || bellIcon.contains(e.target);
                if (!isClickInside) {
                    dropdownList.classList.remove('show');
                }
            });
        }

        // --- Chart.js Integration ---
        const ctx = document.getElementById('salesChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= $chart_labels ?>,
                    datasets: [{
                        label: 'Total Sales by Category (₹)',
                        data: <?= $chart_data ?>,
                        backgroundColor: '#3498db',
                        borderColor: '#2980b9',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Sales Amount (₹)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Product Category'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    });
</script>

</body>
</html>