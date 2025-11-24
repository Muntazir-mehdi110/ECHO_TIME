<?php
session_start();
require '../includes/db.php';
require '../includes/functions.php';
require_login();

// Get cart items
$cart = cart_get();
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Fetch products from database
$ids = array_keys($cart);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

$stmt = mysqli_prepare($conn, "SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
mysqli_stmt_bind_param($stmt, $types, ...$ids);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$cart_items = [];
while ($r = mysqli_fetch_assoc($res)) {
    $cart_items[$r['id']] = $r;
}
mysqli_stmt_close($stmt);

$total = cart_total_amount($conn);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect customer info
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['shipping_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    $account_number = trim($_POST['account_number'] ?? '');
    $order_notes = trim($_POST['order_notes'] ?? '');
    $screenshot_name = '';

    // Validate inputs
    if (empty($full_name)) $error = "⚠️ Please enter your full name.";
    elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = "⚠️ Please enter a valid email.";
    elseif (empty($phone) || !preg_match('/^\d{11}$/', $phone)) $error = "⚠️ Please enter a valid 11-digit phone number.";
    elseif (empty($address)) $error = "⚠️ Please enter your shipping address.";
    elseif (empty($payment_method)) $error = "⚠️ Please select a payment method.";
    elseif (!preg_match('/^\d{11}$/', $account_number)) $error = "⚠️ Please enter a valid 11-digit account/mobile number.";
    elseif (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== 0) $error = "⚠️ Please upload your payment screenshot.";

    if (!$error) {
        // Handle screenshot upload
        $ext = pathinfo($_FILES['payment_screenshot']['name'], PATHINFO_EXTENSION);
        $screenshot_name = 'uploads/screenshots/' . time() . '_' . rand(1000,9999) . '.' . $ext;

        if (!is_dir('../uploads/screenshots')) {
            mkdir('../uploads/screenshots', 0777, true);
        }

        move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], '../' . $screenshot_name);

        // Create order
        $order_id = create_order_from_cart($conn, $_SESSION['user_id'], $address);
        if ($order_id) {
            // Save all customer info
            mysqli_query($conn, "UPDATE orders 
                                 SET full_name='$full_name',
                                     email='$email',
                                     phone='$phone',
                                     payment_method='$payment_method', 
                                     payment_status='Pending Payment', 
                                     total_amount='$total',
                                     account_number='$account_number',
                                     order_notes='$order_notes',
                                     payment_screenshot='$screenshot_name'
                                 WHERE id=$order_id");
            cart_clear();

            // Notify admin via email
            $admin_email = "youremail@example.com"; // replace with your email
            $subject = "New Order #$order_id";
            $message = "New order placed.\nOrder ID: $order_id\nName: $full_name\nEmail: $email\nPhone: $phone\nPayment Method: $payment_method\nTotal: $total PKR\nScreenshot: $screenshot_name";
            mail($admin_email, $subject, $message);

            header("Location: payment_success.php?order_id=$order_id");
            exit;
        } else {
            $error = "❌ Order could not be placed. Try again.";
        }
    }
}

include '../includes/header.php';
?>

<div class="checkout-wrapper" style="max-width:1100px;margin:40px auto;font-family:Poppins;padding:20px;">
    <h2 style="text-align:center;margin-bottom:30px;color:#0d6efd;">Checkout</h2>

    <?php if ($error): ?>
        <div style="background:#ffdcdc;color:#b30000;padding:12px;border-radius:8px;text-align:center;margin-bottom:15px;">
            <?= esc($error) ?>
        </div>
    <?php endif; ?>

    <div style="display:flex;flex-wrap:wrap;gap:30px;">
        <!-- Checkout Form -->
        <div style="flex:2;background:#fff;padding:25px;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,0.05);">
            <form method="post" id="checkoutForm" enctype="multipart/form-data">
                <label><b>Full Name</b></label>
                <input type="text" name="full_name" placeholder="John Doe" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0;" required>

                <label><b>Email</b></label>
                <input type="email" name="email" placeholder="example@mail.com" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0;" required>

                <label><b>Phone Number</b></label>
                <input type="text" name="phone" placeholder="03XXXXXXXXX" maxlength="11" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0;" required>

                <label><b>Shipping Address</b></label>
                <textarea name="shipping_address" rows="4" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0;" required></textarea>

                <label><b>Payment Method</b></label>
                <div style="display:flex;gap:15px;margin:10px 0;flex-wrap:wrap;">
                    <?php
                    $methods = [
                        'JazzCash' => '../assets/images/icons/jazzcash.webp',
                        'Easypaisa' => '../assets/images/icons/easypaisa.webp',
                        'SadaPay' => '../assets/images/icons/sadapay.png'
                    ];
                    
                    foreach ($methods as $name => $img): ?>
                        <label style="flex:1;min-width:120px;padding:15px;border:2px solid #ccc;border-radius:8px;text-align:center;cursor:pointer;">
                            <input type="radio" name="payment_method" value="<?= $name ?>" required> <br>
                            <img src="<?= $img ?>" alt="<?= $name ?>" style="width:50px;"><br>
                            <?= $name ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <label><b>Account / Mobile Number for Payment</b></label>
                <input type="text" name="account_number" placeholder="03XXXXXXXXX" maxlength="11" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0;" required>

                <label><b>Upload Payment Screenshot</b></label>
                <input type="file" name="payment_screenshot" accept="image/*" required>
                <small>Upload the screenshot of your Easypaisa/JazzCash payment</small>

                <label><b>Order Notes (Optional)</b></label>
                <textarea name="order_notes" rows="2" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:8px;margin:10px 0;" placeholder="Any special instructions?"></textarea>

                <div style="background:#f1f1f1;padding:15px;margin:15px 0;border-radius:8px;">
                    <b>Payment Instructions:</b><br>
                    1. Transfer the total amount to your chosen method:<br>
                    - Easypaisa:03059152977 (Shabir) <br>
                    - JazzCash: 03023113739 (Bashir)<br>
                    2. Upload the payment screenshot above.<br>
                    3. Once verified by admin, your order will be shipped.
                </div>

                <button type="submit" style="width:100%;background:#0d6efd;color:#fff;padding:12px;border:none;border-radius:8px;font-weight:600;cursor:pointer;">
                    Place Order
                </button>
            </form>
        </div>

        <!-- Order Summary -->
        <div style="flex:1;background:#f8f9fa;padding:25px;border-radius:10px;">
            <h3>Order Summary</h3>
            <hr>
            <?php foreach ($cart_items as $pid => $p): $qty = $cart[$pid]; ?>
                <div style="display:flex;align-items:center;gap:15px;margin-bottom:10px;">
                    <img src="../uploads/<?= esc($p['image'] ?? 'product-placeholder.jpg') ?>" 
                         alt="<?= esc($p['name']) ?>" style="width:60px;height:60px;border-radius:6px;object-fit:cover;">
                    <div>
                        <b><?= esc($p['name']) ?></b><br>
                        Qty: <?= $qty ?> × PKR <?= formatPrice($p['price']) ?><br>
                        <b>Total:</b> PKR <?= formatPrice($p['price'] * $qty) ?>
                    </div>
                </div>
                <hr>
            <?php endforeach; ?>
            <h3 style="text-align:right;">Total: PKR <span id="totalAmount"><?= formatPrice($total) ?></span></h3>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
