<?php
include '../includes/header.php';
// require '../includes/functions.php';
require '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $id = (int)$_POST['id'];
        $qty = isset($_POST['qty']) ? max(1, (int)$_POST['qty']) : 1;
        cart_add($id, $qty);
        header('Location: cart.php');
        exit;
    } elseif ($action === 'update') {
        foreach ($_POST['qty'] as $pid => $q) {
            $pid = (int)$pid;
            $q = max(0, (int)$q);
            cart_update($pid, $q);
        }
        header('Location: cart.php');
        exit;
    } elseif ($action === 'remove') {
        $id = (int)$_POST['id'];
        cart_remove($id);
        header('Location: cart.php');
        exit;
    }
}

// Get cart items
$cart = cart_get();
$cart_items = [];

if (!empty($cart)) {
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    $stmt = mysqli_prepare($conn, "SELECT id, name, price, image FROM products WHERE id IN ($placeholders)");
    mysqli_stmt_bind_param($stmt, $types, ...$ids);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($r = mysqli_fetch_assoc($res)) {
        $cart_items[$r['id']] = $r;
    }
    mysqli_stmt_close($stmt);
}

$total = cart_total_amount($conn);
?>

<style>
.cart-container {
    /* width:100% ; */
    margin: 40px auto;
    padding: 0 15px;
    font-family: 'Poppins', sans-serif;
}
.cart-header h2 {
    text-align: center;
    color: #0d6efd;
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 30px;
}
.cart-table {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.cart-table th, .cart-table td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}
.cart-table th {
    background: #f8f9fa;
    font-weight: 600;
}
.product-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.product-info img {
    width: 70px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
    border: 1px solid #ddd;
}
.qty-input {
    width: 70px;
    text-align: center;
    padding: 6px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.btn {
    display: inline-block;
    padding: 10px 18px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: 0.3s;
}
.btn-primary {
    background: #0d6efd;
    color: #fff;
    border: 1px solid #0d6efd;
}
.btn-primary:hover { background: #0b5ed7; }
.btn-outline {
    background: transparent;
    border: 1px solid #0d6efd;
    color: #0d6efd;
}
.btn-outline:hover {
    background: #0d6efd;
    color: #fff;
}
.cart-summary {
    margin-top: 30px;
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    text-align: right;
}
.cart-summary h3 {
    color: #333;
}
.cart-empty {
    text-align: center;
    background: #fff;
    padding: 50px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}
</style>

<div class="cart-container">
    <div class="cart-header">
        <h2>Your Shopping Cart</h2>
    </div>

    <?php if (empty($cart_items)): ?>
        <div class="cart-empty">
            <h4>Your cart is empty.</h4>
            <p><a href="shop.php" style="color:#0d6efd;text-decoration:none;font-weight:600;">Start shopping</a> to find something you’ll love!</p>
        </div>
    <?php else: ?>
        <form method="post" id="cartForm">
            <input type="hidden" name="action" value="update">
            <table class="cart-table" id="cartTable">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price (PKR)</th>
                        <th>Quantity</th>
                        <th>Subtotal (PKR)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $pid => $p): 
                        $qty = $cart[$pid];
                        $subtotal = $p['price'] * $qty;
                    ?>
                    <tr data-id="<?= $pid ?>">
                        <td>
                            <div class="product-info">
                                <img src="../uploads/<?= esc($p['image'] ?? 'placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>">
                                <span><?= esc($p['name']) ?></span>
                            </div>
                        </td>
                        <td><?= formatPrice($p['price']) ?></td>
                        <td><input type="number" class="qty-input" name="qty[<?= $pid ?>]" value="<?= $qty ?>" min="1"></td>
                        <td class="subtotal"><?= formatPrice($subtotal) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="id" value="<?= $pid ?>">
                                <button type="submit" style="background:none;border:none;color:#dc3545;font-size:18px;cursor:pointer;">✖</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="cart-summary">
                <h3>Total: PKR <span id="cartTotal"><?= formatPrice($total) ?></span></h3>
                <button class="btn btn-primary" type="submit">Update Cart</button>
                <a href="shop.php" class="btn btn-outline">Continue Shopping</a>
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<!-- ✅ JS for live total calculation -->
<script>
document.querySelectorAll('.qty-input').forEach(input => {
    input.addEventListener('input', () => {
        const row = input.closest('tr');
        const price = parseFloat(row.children[1].textContent.replace(/,/g, ''));
        const qty = parseInt(input.value) || 0;
        const subtotalCell = row.querySelector('.subtotal');
        const subtotal = price * qty;
        subtotalCell.textContent = subtotal.toLocaleString('en-PK', { minimumFractionDigits: 2 });
        updateTotal();
    });
});

function updateTotal() {
    let total = 0;
    document.querySelectorAll('.subtotal').forEach(cell => {
        total += parseFloat(cell.textContent.replace(/,/g, '')) || 0;
    });
    document.getElementById('cartTotal').textContent = total.toLocaleString('en-PK', { minimumFractionDigits: 2 });
}
</script>

<?php include '../includes/footer.php'; ?>
