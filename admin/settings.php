<?php
// admin/settings.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

$error = '';

// Fetch all settings from the database
$settings = [];
$result = mysqli_query($conn, "SELECT setting_name, setting_value FROM settings");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_name']] = $row['setting_value'];
    }
} else {
    $error = "Failed to load settings: " . mysqli_error($conn);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="admin-main-content">
        <div class="admin-topbar">
            <h2>Website Settings</h2>
        </div>

        <div class="dashboard-section">
            <h3>General Settings</h3>
            
            <div id="status-message"></div>

            <?php if (!empty($error)): ?>
                <div class="error-message"><?= esc($error) ?></div>
            <?php endif; ?>

            <form id="settings-form" class="settings-form">
                <div class="form-group">
                    <label for="site_name">Website Name</label>
                    <input type="text" id="site_name" name="site_name" value="<?= esc($settings['site_name'] ?? '') ?>" class="input" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_email">Contact Email</label>
                    <input type="email" id="contact_email" name="contact_email" value="<?= esc($settings['contact_email'] ?? '') ?>" class="input" required>
                </div>

                <div class="form-group">
                    <label for="address">Company Address</label>
                    <textarea id="address" name="address" rows="4" class="input"><?= esc($settings['address'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" id="save-btn">Save Settings</button>
                    <span class="loading-spinner" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('settings-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const saveBtn = document.getElementById('save-btn');
    const spinner = document.querySelector('.loading-spinner');
    const statusMessage = document.getElementById('status-message');

    // Show loading state
    saveBtn.disabled = true;
    saveBtn.style.opacity = '0.7';
    spinner.style.display = 'inline-block';
    statusMessage.textContent = '';
    statusMessage.className = '';

    const formData = new FormData(form);

    fetch('admin_settings_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            statusMessage.textContent = data.message;
            statusMessage.className = 'success-message';
        } else {
            statusMessage.textContent = data.message;
            statusMessage.className = 'error-message';
        }
    })
    .catch(error => {
        statusMessage.textContent = 'An unexpected error occurred. Please try again.';
        statusMessage.className = 'error-message';
        console.error('Error:', error);
    })
    .finally(() => {
        // Reset loading state
        saveBtn.disabled = false;
        saveBtn.style.opacity = '1';
        spinner.style.display = 'none';
    });
});
</script>

</body>
</html>


<style>
    /*

</style>