<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// ✅ Clear cart after successful payment
if (function_exists('cart_clear')) {
    cart_clear();
} else {
    if (isset($_SESSION['cart'])) unset($_SESSION['cart']);
}

// ✅ Get order ID from query
$order_id = $_GET['order_id'] ?? 0;

if ($order_id) {
    // ✅ Update order payment info
    $stmt = mysqli_prepare($conn, "UPDATE orders SET payment_method = 'PayFast', payment_status = 'paid' WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$payment_method = 'Cash on Delivery';
$payment_status = 'pending';


// Optional: verify order in DB
$order = null;
if ($order_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM orders WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $order_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
}

include '../includes/header.php';
?>

<style>
body {
    background: linear-gradient(135deg, #f8fbff, #dbe8ff);
    font-family: 'Poppins', sans-serif;
    overflow-x: hidden;
    margin: 0;
}
.success-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 85vh;
    text-align: center;
    padding: 20px;
}
.success-card {
    background: #fff;
    border-radius: 18px;
    padding: 40px 30px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    max-width: 500px;
    position: relative;
    animation: fadeInUp 0.8s ease-out;
}
.success-icon {
    width: 90px;
    height: 90px;
    background: #4caf50;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    margin: 0 auto 20px;
    animation: popIn 0.7s ease-out;
}
.success-title {
    font-size: 1.9rem;
    color: #0d52a0;
    font-weight: 700;
    margin-bottom: 10px;
}
.success-message {
    color: #555;
    margin-bottom: 25px;
    font-size: 1rem;
}
.order-summary {
    background: #f5f8ff;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 25px;
    text-align: left;
}
.order-summary p {
    margin: 5px 0;
    color: #333;
}
.btn-primary {
    background: linear-gradient(135deg, #0d52a0, #1c88ff);
    color: #fff;
    border: none;
    padding: 12px 22px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}
.btn-primary:hover {
    background: linear-gradient(135deg, #1c88ff, #0d52a0);
    transform: scale(1.05);
}
.btn-outline {
    border: 2px solid #0d52a0;
    color: #0d52a0;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 10px;
    display: inline-block;
    transition: all 0.3s ease;
}
.btn-outline:hover {
    background: #0d52a0;
    color: #fff;
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes popIn {
    0% { transform: scale(0); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<div class="success-container">
    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        <h2 class="success-title">Payment Successful 🎉</h2>
        <p class="success-message">
            Thank you for your purchase! Your order has been placed successfully.
        </p>

        <?php if ($order): ?>
            <div class="order-summary">
                <p><strong>Order ID:</strong> #<?= esc($order['id']) ?></p>
                <p><strong>Total Amount:</strong> ₹<?= formatPrice($order['total_amount']) ?></p>
                <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>
                <p><strong>Placed On:</strong> <?= date('M d, Y', strtotime($order['created_at'])) ?></p>
            </div>
        <?php endif; ?>

        <a href="orders.php" class="btn-primary">Track My Order</a>
        <a href="shop.php" class="btn-outline">Continue Shopping</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
setTimeout(() => {
    const duration = 1.8 * 1000;
    const end = Date.now() + duration;

    (function frame() {
        confetti({
            particleCount: 4,
            angle: 60,
            spread: 55,
            origin: { x: 0 },
            colors: ['#0d52a0', '#4caf50', '#ffc107']
        });
        confetti({
            particleCount: 4,
            angle: 120,
            spread: 55,
            origin: { x: 1 },
            colors: ['#0d52a0', '#4caf50', '#ffc107']
        });

        if (Date.now() < end) {
            requestAnimationFrame(frame);
        }
    })();
}, 400);
</script>

<?php include '../includes/footer.php'; ?>
