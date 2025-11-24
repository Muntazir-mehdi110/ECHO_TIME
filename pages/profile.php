<?php
// Start the session at the very beginning of the file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include '../includes/header.php';
require_login();


// --- Handle Settings Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    // Sanitize and validate inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $user_id = $_SESSION['user_id'];

    $profile_picture_path = '';
    $file_upload_error = false;

    // Check if a new profile picture was uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = '../uploads/profile_pictures/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_info = pathinfo($_FILES['profile_picture']['name']);
        $file_extension = strtolower($file_info['extension']);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($file_extension, $allowed_extensions)) {
            $new_file_name = uniqid('profile_', true) . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $profile_picture_path = 'uploads/profile_pictures/' . $new_file_name;
            } else {
                set_message('Error uploading file.', 'error'); // NEW
                $file_upload_error = true;
            }
        } else {
            set_message('Invalid file type. Only JPG, PNG, and GIF are allowed.', 'error'); // NEW
            $file_upload_error = true;
        }
    }

    // Prepare and execute the update query
    if (!$file_upload_error) { // Only proceed if there was no file upload error
        $query_parts = [];
        $params = [];
        $types = '';
        
        // Add name, phone, and address to the query
        $query_parts[] = 'name = ?';
        $params[] = $name;
        $types .= 's';

        $query_parts[] = 'phone = ?';
        $params[] = $phone;
        $types .= 's';

        $query_parts[] = 'address = ?';
        $params[] = $address;
        $types .= 's';

        // Add profile picture if a new one was uploaded
        if (!empty($profile_picture_path)) {
            $query_parts[] = 'profile_picture = ?';
            $params[] = $profile_picture_path;
            $types .= 's';
        }
        
        $params[] = $user_id;
        $types .= 'i';

        $query = "UPDATE users SET " . implode(', ', $query_parts) . " WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);

        if (mysqli_stmt_execute($stmt)) {
            set_message('Profile updated successfully!', 'success'); // NEW
        } else {
            set_message('Error updating profile: ' . mysqli_error($conn), 'error'); // NEW
        }
        mysqli_stmt_close($stmt);
    }
}

// Determine the current page/section
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Fetch user details and other data
$stmt = mysqli_prepare($conn, "SELECT name, email, phone, address, created_at, profile_picture FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

// Fetch order summary for the dashboard
$total_orders_q = mysqli_prepare($conn, "SELECT COUNT(*) FROM orders WHERE user_id = ?");
mysqli_stmt_bind_param($total_orders_q, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($total_orders_q);
mysqli_stmt_bind_result($total_orders_q, $total_orders);
mysqli_stmt_fetch($total_orders_q);
mysqli_stmt_close($total_orders_q);

$pending_orders_q = mysqli_prepare($conn, "SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'Pending'");
mysqli_stmt_bind_param($pending_orders_q, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($pending_orders_q);
mysqli_stmt_bind_result($pending_orders_q, $pending_orders);
mysqli_stmt_fetch($pending_orders_q);
mysqli_stmt_close($pending_orders_q);

$completed_orders_q = mysqli_prepare($conn, "SELECT COUNT(*) FROM orders WHERE user_id = ? AND status = 'Completed'");
mysqli_stmt_bind_param($completed_orders_q, 'i', $_SESSION['user_id']);
mysqli_stmt_execute($completed_orders_q);
mysqli_stmt_bind_result($completed_orders_q, $completed_orders);
mysqli_stmt_fetch($completed_orders_q);
mysqli_stmt_close($completed_orders_q);

// Fetch order list for the 'orders' page
if ($page === 'orders') {
    $orders_q = mysqli_prepare($conn, "SELECT id, total_price, status, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($orders_q, 'i', $_SESSION['user_id']);
    mysqli_stmt_execute($orders_q);
    $orders_res = mysqli_stmt_get_result($orders_q);
}
?>

<style>

/* ========================================================= */
/* === Modern Professional Dashboard & Profile Styling === */
/* ========================================================= */

:root {
    --primary: #0d52a0;
    --primary-light: #1e73e8;
    --primary-dark: #072e66;
    --accent: #3b82f6;
    --white: #ffffff;
    --bg-light: #f5f7fa;
    --bg-glass: rgba(255, 255, 255, 0.6);
    --text-dark: #1f2937;
    --text-light: #6b7280;
    --shadow-soft: 0 4px 30px rgba(0, 0, 0, 0.05);
    --shadow-strong: 0 8px 32px rgba(0, 0, 0, 0.15);
    --border-light: rgba(255, 255, 255, 0.25);
}

/* --- Global Body --- */
body {
    background: linear-gradient(135deg, #e3f2fd, #f0f4ff);
    font-family: "Poppins", "Inter", sans-serif;
    color: var(--text-dark);
    min-height: 100vh;
}

/* --- Dashboard Layout --- */
.dashboard-page-container {
    padding: 40px 20px;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

/* --- Sidebar Navigation --- */
.dashboard-sidebar {
    background: var(--bg-glass);
    backdrop-filter: blur(15px);
    border-radius: 16px;
    box-shadow: var(--shadow-soft);
    padding: 25px 0;
    height: fit-content;
    position: sticky;
    top: 100px;
    border: 1px solid var(--border-light);
}

.dashboard-nav {
    display: flex;
    flex-direction: column;
}

.dashboard-nav .nav-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 25px;
    text-decoration: none;
    color: var(--text-light);
    font-weight: 500;
    transition: all 0.3s ease;
}

.dashboard-nav .nav-item:hover {
    background: rgba(13, 82, 160, 0.08);
    color: var(--primary);
}

.dashboard-nav .nav-item.active {
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    color: var(--white);
    border-left: 4px solid var(--white);
    font-weight: 600;
}

.dashboard-nav i {
    font-size: 1.1rem;
}

/* --- Main Content --- */
.dashboard-main-content {
    background: var(--bg-glass);
    backdrop-filter: blur(20px);
    border-radius: 18px;
    box-shadow: var(--shadow-strong);
    padding: 40px;
    border: 1px solid var(--border-light);
}

/* --- Dashboard Header --- */
.dashboard-title {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-dark);
    border-bottom: 2px solid rgba(13, 82, 160, 0.2);
    padding-bottom: 10px;
    margin-bottom: 30px;
}

/* --- Summary Cards --- */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.summary-card {
    background: var(--white);
    border-radius: 16px;
    box-shadow: var(--shadow-soft);
    padding: 25px;
    display: flex;
    align-items: center;
    gap: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.summary-card:hover {
    transform: translateY(-6px);
    box-shadow: var(--shadow-strong);
}

.summary-icon {
    font-size: 2.5rem;
}

.summary-label {
    color: var(--text-light);
}

.summary-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-dark);
}

/* --- Profile Section --- */
.profile-card-new {
    background: linear-gradient(135deg, #ffffff, #f9fbff);
    border-radius: 20px;
    box-shadow: var(--shadow-strong);
    overflow: hidden;
    animation: fadeIn 0.6s ease-in-out;
}

.profile-header-new {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: var(--white);
    text-align: center;
    padding: 50px 20px;
}

.profile-avatar-box {
    width: 140px;
    height: 140px;
    background-color: var(--white);
    border-radius: 50%;
    margin: 0 auto 20px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
}

.profile-picture {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-details-new {
    padding: 30px 40px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.detail-item-new {
    display: flex;
    align-items: center;
    gap: 15px;
    background: var(--white);
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: var(--shadow-soft);
    transition: background 0.3s ease;
}

.detail-item-new:hover {
    background: #f1f5ff;
}

.detail-label-new {
    font-weight: 600;
    color: var(--primary);
}

.icon-new {
    font-size: 1.2rem;
    color: var(--primary);
}

/* --- Buttons --- */
.btn-primary {
    background: linear-gradient(90deg, var(--primary), var(--primary-light));
    color: var(--white);
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(90deg, var(--primary-dark), var(--primary));
    transform: translateY(-2px);
}

.btn-secondary {
    background-color: #6c757d;
    color: var(--white);
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* --- Responsive Design --- */
@media (max-width: 992px) {
    .dashboard-grid {
        grid-template-columns: 1fr;
    }
    .dashboard-sidebar {
        position: static;
        margin-bottom: 20px;
    }
    .dashboard-main-content {
        padding: 25px;
    }
}

/* --- Animation --- */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}


</style>
<div class="dashboard-page-container">
    <div class="dashboard-grid">
        <aside class="dashboard-sidebar">
            <nav class="dashboard-nav">
                <a href="profile.php?page=dashboard" class="nav-item <?= ($page == 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
                <a href="orders.php" class="nav-item <?= ($page == 'orders') ? 'active' : '' ?>">
                    <i class="fas fa-box-open"></i>
                    <span>My Orders</span>
                </a>
                <a href="profile.php?page=settings" class="nav-item <?= ($page == 'settings') ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </nav>
        </aside>

        <section class="dashboard-main-content">
            <?php if ($page === 'dashboard'): ?>
                <h2 class="dashboard-title">Dashboard</h2>
                <div class="summary-grid">
                    <div class="summary-card total-orders-card">
                        <div class="summary-icon"><i class="fas fa-shopping-bag"></i></div>
                        <div class="summary-content">
                            <p class="summary-label">Total Orders</p>
                            <h3 class="summary-number"><?= esc($total_orders) ?></h3>
                        </div>
                    </div>
                    <div class="summary-card pending-card">
                        <div class="summary-icon"><i class="fas fa-hourglass-half"></i></div>
                        <div class="summary-content">
                            <p class="summary-label">Pending Orders</p>
                            <h3 class="summary-number"><?= esc($pending_orders) ?></h3>
                        </div>
                    </div>
                    <div class="summary-card completed-card">
                        <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="summary-content">
                            <p class="summary-label">Completed Orders</p>
                            <h3 class="summary-number"><?= esc($completed_orders) ?></h3>
                        </div>
                    </div>
                </div>
                <div class="full-width-card">
                    <div class="profile-card-new">
                        <div class="profile-header-new">
                            <div class="profile-avatar-box">
                                <?php if (!empty($user['profile_picture'])): ?>
                                    <img src="../<?= esc($user['profile_picture']) ?>" alt="Profile Picture" class="profile-picture">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <h2><?=esc($user['name'])?></h2>
                            <p>Member since: <?=esc($user['created_at'])?></p>
                        </div>
                        <div class="profile-details-new">
                            <div class="detail-item-new">
                                <i class="fas fa-envelope icon-new"></i>
                                <div class="detail-content">
                                    <span class="detail-label-new">Email</span>
                                    <p class="detail-value-new"><?=esc($user['email'])?></p>
                                </div>
                            </div>
                            <div class="detail-item-new">
                                <i class="fas fa-phone icon-new"></i>
                                <div class="detail-content">
                                    <span class="detail-label-new">Phone</span>
                                    <p class="detail-value-new"><?=esc($user['phone'])?></p>
                                </div>
                            </div>
                            <div class="detail-item-new">
                                <i class="fas fa-map-marker-alt icon-new"></i>
                                <div class="detail-content">
                                    <span class="detail-label-new">Address</span>
                                    <p class="detail-value-new"><?=nl2br(esc($user['address']))?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php elseif ($page === 'orders'): ?>
                <h2 class="dashboard-title">My Orders</h2>
                <div class="order-table-container">
                    <?php if (mysqli_num_rows($orders_res) > 0): ?>
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($order = mysqli_fetch_assoc($orders_res)): ?>
                                <tr>
                                    <td>#<?= esc($order['id']) ?></td>
                                    <td><?= date('F j, Y', strtotime(esc($order['created_at']))) ?></td>
                                    <td><span class="status-badge status-<?= strtolower(esc($order['status'])) ?>"><?= esc($order['status']) ?></span></td>
                                    <td>$<?= number_format(esc($order['total_price']), 2) ?></td>
                                    <td>
                                        <a href="#order-details-modal" class="btn-sm btn-view" onclick="showModal(<?= esc($order['id']) ?>)"><i class="fas fa-eye"></i> View</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open empty-icon"></i>
                            <p>You haven't placed any orders yet.</p>
                            <a href="../shop/all_products.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($page === 'settings'): ?>
                <h2 class="dashboard-title">Account Settings</h2>
                <?php display_message(); // NEW ?>
                <div class="full-width-card">
                    <div class="profile-card-new settings-card">
                        <form action="profile.php?page=settings" method="POST" enctype="multipart/form-data">
                            <div class="profile-header-new">
                                <div class="profile-avatar-box">
                                    <?php if (!empty($user['profile_picture'])): ?>
                                        <img src="../<?= esc($user['profile_picture']) ?>" alt="Profile Picture" class="profile-picture">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="form-group-custom">
                                    <label for="profile_picture" class="btn btn-secondary">
                                        <i class="fas fa-camera"></i> Change Photo
                                    </label>
                                    <input type="file" id="profile_picture" name="profile_picture" style="display:none;" onchange="previewFile(this);">
                                </div>
                                <h2 class="form-title">Update Your Information</h2>
                            </div>
                            <div class="profile-details-new">
                                <div class="form-group-full">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" value="<?=esc($user['name'])?>" required>
                                </div>
                                <div class="form-group-full">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" value="<?=esc($user['email'])?>" disabled>
                                    <small class="form-text text-muted">Email cannot be changed.</small>
                                </div>
                                <div class="form-group-full">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?=esc($user['phone'])?>">
                                </div>
                                <div class="form-group-full">
                                    <label for="address">Shipping Address</label>
                                    <textarea id="address" name="address" rows="4"><?=esc($user['address'])?></textarea>
                                </div>
                                <div class="form-group-full form-submit-btn">
                                    <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<div id="order-details-modal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <div id="modal-body">
            <p>Loading order details...</p>
        </div>
    </div>
</div>

<!-- <?php include '../includes/footer.php'; ?> -->

<script>
    function previewFile(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = input.closest('form').querySelector('.profile-picture');
                if (img) {
                    img.src = e.target.result;
                } else {
                    const avatarBox = input.closest('form').querySelector('.profile-avatar-box');
                    avatarBox.innerHTML = '<img src="' + e.target.result + '" alt="Profile Picture" class="profile-picture">';
                }
            }
            reader.readAsDataURL(file);
        }
    }

    // --- Modal Logic for Order Details (New) ---
    function showModal(orderId) {
        const modal = document.getElementById('order-details-modal');
        const modalBody = document.getElementById('modal-body');
        modal.style.display = 'block';

        modalBody.innerHTML = '<p>Loading order details...</p>';
        
        // Simulating an AJAX call to fetch detailed order data
        // In a real application, you would make a fetch() request to a PHP endpoint like 'fetch_order_details.php?id=' + orderId
        setTimeout(() => {
            modalBody.innerHTML = `
                <h3>Order #${orderId}</h3>
                <p><strong>Status:</strong> Shipped</p>
                <p><strong>Total:</strong> $125.00</p>
                <p><strong>Shipping Address:</strong><br>
                123 E-commerce St.<br>
                Tech City, 12345</p>
                <h4>Items:</h4>
                <ul>
                    <li>Product A (x2) - $50.00</li>
                    <li>Product B (x1) - $75.00</li>
                </ul>
            `;
        }, 800);
    }
    
    function closeModal() {
        const modal = document.getElementById('order-details-modal');
        modal.style.display = 'none';
    }

    // Close modal when user clicks outside of it
    window.onclick = function(event) {
        const modal = document.getElementById('order-details-modal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>
