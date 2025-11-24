<?php
// admin/reports.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Fetch data for the most sold products report
// This query joins order_items with products to sum up quantities sold
$most_sold_query = "
    SELECT 
        p.name, 
        SUM(oi.quantity) AS total_sold, 
        SUM(oi.price * oi.quantity) AS total_revenue
    FROM 
        order_items oi
    JOIN 
        products p ON oi.product_id = p.id
    GROUP BY 
        p.id
    ORDER BY 
        total_sold DESC
    LIMIT 10
";
$most_sold_result = mysqli_query($conn, $most_sold_query);
$most_sold_products = [];
if ($most_sold_result) {
    while ($row = mysqli_fetch_assoc($most_sold_result)) {
        $most_sold_products[] = $row;
    }
}

// Fetch data for customer demographics (e.g., total customers, admins)
$customer_demographics_query = "
    SELECT
        role,
        COUNT(id) AS total
    FROM
        users
    GROUP BY
        role
";
$demographics_result = mysqli_query($conn, $customer_demographics_query);
$demographics_data = [];
if ($demographics_result) {
    while ($row = mysqli_fetch_assoc($demographics_result)) {
        $demographics_data[$row['role']] = $row['total'];
    }
}
$total_customers = $demographics_data['customer'] ?? 0;
$total_admins = $demographics_data['admin'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Reports</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="admin-main-content">
        <div class="admin-topbar">
            <h2>Analytics & Reports</h2>
        </div>

        <div class="dashboard-section">
            <h3>Key Performance Indicators</h3>
            <div class="kpi-grid">
                <div class="kpi-card purple">
                    <h4>Total Customers</h4>
                    <span class="kpi-value"><?= esc($total_customers) ?></span>
                </div>
                <div class="kpi-card green">
                    <h4>Total Admins</h4>
                    <span class="kpi-value"><?= esc($total_admins) ?></span>
                </div>
                <div class="kpi-card red">
                    <h4>Total Products Sold</h4>
                    <span class="kpi-value"><?= esc(array_sum(array_column($most_sold_products, 'total_sold'))) ?></span>
                </div>
                <div class="kpi-card blue">
                    <h4>Total Revenue</h4>
                    <span class="kpi-value">₹<?= esc(number_format(array_sum(array_column($most_sold_products, 'total_revenue')), 2)) ?></span>
                </div>
            </div>
        </div>

        <div class="dashboard-section">
            <h3>Top 10 Most Sold Products</h3>
            <div class="table-container">
                <?php if (empty($most_sold_products)): ?>
                    <p>No product sales data to display yet.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Name</th>
                                <th>Quantity Sold</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($most_sold_products as $index => $product): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><?= esc($product['name']) ?></td>
                                    <td><?= esc($product['total_sold']) ?></td>
                                    <td>₹<?= esc(number_format($product['total_revenue'], 2)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>


<style>
    /* Add to your existing admin.css file */

/* Key Performance Indicators (KPI) styles */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.kpi-card {
    background-color: var(--card-bg);
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s;
    border-left: 5px solid;
}

.kpi-card:hover {
    transform: translateY(-5px);
}

.kpi-card.purple { border-left-color: var(--primary-color); }
.kpi-card.green { border-left-color: var(--success-color); }
.kpi-card.red { border-left-color: var(--danger-color); }
.kpi-card.blue { border-left-color: var(--info-color); }

.kpi-card h4 {
    margin: 0 0 10px 0;
    font-size: 1rem;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.kpi-card .kpi-value {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Specific KPI value colors */
.kpi-card.green .kpi-value { color: var(--success-color); }
.kpi-card.red .kpi-value { color: var(--danger-color); }
.kpi-card.blue .kpi-value { color: var(--info-color); }
</style>