<?php
// admin/messages.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Handle message status update
if (isset($_GET['action']) && $_GET['action'] === 'read' && isset($_GET['id'])) {
    $message_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    if ($message_id) {
        $stmt = mysqli_prepare($conn, "UPDATE contact_messages SET is_read = TRUE WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $message_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        // Redirect to prevent re-marking on page refresh
        header('Location: messages.php');
        exit;
    }
}

// Fetch all messages from the database
$messages_query = "SELECT id, name, email, subject, message, is_read, created_at FROM contact_messages ORDER BY created_at DESC";
$messages_result = mysqli_query($conn, $messages_query);
$messages = [];
if ($messages_result) {
    while ($row = mysqli_fetch_assoc($messages_result)) {
        $messages[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <div class="admin-main-content">
        <div class="admin-topbar">
            <h2>All Messages</h2>
        </div>

        <div class="dashboard-section">
            <h3>Recent Messages</h3>
           <div class="table-container">
    <?php if (empty($messages)): ?>
        <p>No messages to display.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Received On</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr class="<?= $message['is_read'] ? 'read-message' : 'unread-message' ?>" data-message-id="<?= esc($message['id']) ?>">
                        <td><?= esc($message['name']) ?></td>
                        <td><?= esc($message['email']) ?></td>
                        <td><?= esc($message['subject']) ?></td>
                        <td><?= esc(date('M d, Y', strtotime($message['created_at']))) ?></td>
                        <td>
                            <span class="status <?= $message['is_read'] ? 'read' : 'unread' ?>">
                                <?= $message['is_read'] ? 'Read' : 'Unread' ?>
                            </span>
                        </td>
                        <td>
                            <a href="#" class="btn-outline-info view-message-btn" data-message-id="<?= esc($message['id']) ?>" title="View Message">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if (!$message['is_read']): ?>
                            <a href="messages.php?action=read&id=<?= esc($message['id']) ?>" class="btn-outline-info mark-read-btn" title="Mark as Read">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div id="messageModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3>Message Details</h3>
        <div id="modal-content-body">
            <p><strong>From:</strong> <span id="modal-name"></span></p>
            <p><strong>Email:</strong> <span id="modal-email"></span></p>
            <p><strong>Subject:</strong> <span id="modal-subject"></span></p>
            <hr>
            <p id="modal-message"></p>
        </div>
        <div class="form-actions" style="text-align: right;">
            <a href="#" class="btn btn-primary" id="modal-mark-read-btn">Mark as Read</a>
        </div>
    </div>
</div>

</div>

</body>
</html>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('messageModal');
    const closeBtn = document.querySelector('.close-btn');
    const viewButtons = document.querySelectorAll('.view-message-btn');
    const modalMarkReadBtn = document.getElementById('modal-mark-read-btn');

    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const messageId = this.getAttribute('data-message-id');
            
            // Set the data-id for the "Mark as Read" button inside the modal
            modalMarkReadBtn.setAttribute('data-id', messageId);

            fetch(`fetch_message.php?id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const msg = data.message;
                        document.getElementById('modal-name').textContent = msg.name;
                        document.getElementById('modal-email').textContent = msg.email;
                        document.getElementById('modal-subject').textContent = msg.subject;
                        document.getElementById('modal-message').textContent = msg.message;
                        
                        // Show/hide the "Mark as Read" button based on status
                        if (msg.is_read) {
                            modalMarkReadBtn.style.display = 'none';
                        } else {
                            modalMarkReadBtn.style.display = 'inline-block';
                        }

                        // Update the status visually on the main table
                        const row = document.querySelector(`tr[data-message-id="${messageId}"]`);
                        if (row) {
                            row.classList.remove('unread-message');
                            row.classList.add('read-message');
                            const statusSpan = row.querySelector('.status');
                            if (statusSpan) {
                                statusSpan.textContent = 'Read';
                                statusSpan.classList.remove('unread');
                                statusSpan.classList.add('read');
                            }
                        }

                        modal.style.display = 'flex';
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching message:', error);
                    alert('Could not fetch message details.');
                });
        });
    });

    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    modalMarkReadBtn.addEventListener('click', function(e) {
        e.preventDefault();
        const messageId = this.getAttribute('data-id');
        window.location.href = `messages.php?action=read&id=${messageId}`;
    });

    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});
</script>