<?php
// admin/customers.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Handle search and filter parameters
$search_query = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$role_filter = filter_input(INPUT_GET, 'role', FILTER_SANITIZE_STRING);

// Build the SQL query with search and filter conditions
$sql = "SELECT id, name, email, created_at, role FROM users";
$conditions = [];
$params = [];
$param_types = '';

if (!empty($search_query)) {
    $conditions[] = "(name LIKE ? OR email LIKE ?)";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= 'ss';
}

if (!empty($role_filter) && $role_filter !== 'all') {
    // Corrected condition to match 'customer' role
    $conditions[] = "role = ?";
    $params[] = $role_filter;
    $param_types .= 's';
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY created_at DESC";

// Fetch users based on the constructed query
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $users = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $users[] = $r;
    }
    mysqli_stmt_close($stmt);
} else {
    $users = [];
    $error = "Database query failed: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="admin-container">

    <?php include 'sidebar.php'; ?>

    <div class="admin-main-content">
        <div class="admin-topbar">
            <h2>Manage Customers</h2>
            <div class="topbar-actions">
            </div>
        </div>

        <div class="dashboard-section">
            <div class="table-header">
                <h3>All Customers</h3>
                <div class="table-controls">
                    <form action="customers.php" method="GET" class="filter-form">
                        <div class="search-form">
                            <input type="text" name="search" placeholder="Search customers..." value="<?= esc($search_query) ?>" class="search-input">
                            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
                        </div>
                        <div class="filter-dropdown">
                            <select name="role" onchange="this.form.submit()" class="filter-select">
                                <option value="all" <?= $role_filter == 'all' ? 'selected' : '' ?>>All Roles</option>
                                <option value="customer" <?= $role_filter == 'customer' ? 'selected' : '' ?>>Customers</option>
                                <option value="admin" <?= $role_filter == 'admin' ? 'selected' : '' ?>>Admins</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <p class="error-message"><?= esc($error) ?></p>
            <?php elseif (empty($users)): ?>
                <p>No customers to display.</p>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?= esc($u['id']) ?></td>
                                    <td><?= esc($u['name']) ?></td>
                                    <td><?= esc($u['email']) ?></td>
                                    <td>
                                        <span class="status <?= strtolower(esc($u['role'])) ?>">
                                            <?= ucfirst(esc($u['role'])) ?>
                                        </span>
                                    </td>
                                    <td><?= esc(date('Y-m-d H:i', strtotime($u['created_at']))) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="edit_user.php?id=<?= esc($u['id']) ?>" class="btn btn-outline-info"><i class="fas fa-edit"></i> Edit</a>
                                            <a href="delete_user.php?id=<?= esc($u['id']) ?>" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt"></i> Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>