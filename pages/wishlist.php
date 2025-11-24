<?php
// pages/wishlist.php
include '../includes/header.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_login(); // Ensure user is logged in to view their wishlist

$user_id = $_SESSION['user_id'];
$wishlist_items = [];
$message = '';
$message_type = '';

// Handle actions (remove from wishlist, add to cart)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_from_wishlist'])) {
        $product_id = $_POST['product_id'];
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND product_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
        if (mysqli_stmt_execute($stmt)) {
            $message = "Product removed from wishlist.";
            $message_type = "success";
        } else {
            $message = "Failed to remove product. Please try again.";
            $message_type = "error";
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['add_to_cart_from_wishlist'])) {
        $product_id = $_POST['product_id'];
        // This function should be defined in functions.php
        add_to_cart($product_id); 
        $message = "Product added to cart!";
        $message_type = "success";
    }
}

// Fetch current wishlist items for the user
$sql = "
    SELECT p.id, p.name, p.price, p.image
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    WHERE w.user_id = ?
";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $wishlist_items[] = $row;
}
mysqli_stmt_close($stmt);
?>

<style>
    /* Wishlist Page Styles */
    :root {
        --primary-color: #007bff;
        --text-color-dark: #333;
        --text-color-light: #555;
        --bg-color-light: #f8f9fa;
        --card-bg: #fff;
        --border-color: #e9ecef;
        --shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .wishlist-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Poppins', sans-serif;
    }

    .wishlist-header {
        text-align: center;
        margin-bottom: 40px;
    }

    .wishlist-header h2 {
        font-size: 2.5rem;
        color: var(--text-color-dark);
        font-weight: 700;
    }
    
    .wishlist-empty {
        text-align: center;
        padding: 50px;
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        color: var(--text-color-light);
    }
    
    .wishlist-empty a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .wishlist-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .wishlist-item {
        display: flex;
        align-items: center;
        gap: 20px;
        background-color: var(--card-bg);
        padding: 20px;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: transform 0.2s ease;
    }

    .wishlist-item:hover {
        transform: translateY(-5px);
    }

    .wishlist-item-image {
        width: 100px;
        height: 75px;
        object-fit: cover;
        border-radius: 8px;
    }

    .wishlist-item-details {
        flex-grow: 1;
    }

    .wishlist-item-details h4 {
        margin: 0;
        font-size: 1.2rem;
        color: var(--text-color-dark);
    }

    .wishlist-item-details p {
        margin: 5px 0 0;
        font-size: 1rem;
        color: var(--text-color-light);
    }

    .wishlist-item-price {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary-color);
    }

    .wishlist-item-actions {
        display: flex;
        gap: 10px;
    }

    .btn {
        padding: 10px 15px;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        transition: background-color 0.3s ease, color 0.3s ease;
        cursor: pointer;
    }

    .btn-cart {
        background-color: var(--primary-color);
        color: #fff;
        border: none;
    }

    .btn-cart:hover {
        background-color: #0056b3;
    }

    .btn-remove {
        background-color: #dc3545;
        color: #fff;
        border: none;
    }

    .btn-remove:hover {
        background-color: #c82333;
    }

    .message-box {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        text-align: center;
        font-weight: 600;
    }

    .message-box.success {
        background-color: #d4edda;
        color: #155724;
    }

    .message-box.error {
        background-color: #f8d7da;
        color: #721c24;
    }

    @media (max-width: 600px) {
        .wishlist-item {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<div class="wishlist-container">
    <header class="wishlist-header">
        <h2>Your Wishlist</h2>
    </header>

    <?php if ($message): ?>
        <div class="message-box <?= htmlspecialchars($message_type) ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($wishlist_items)): ?>
        <div class="wishlist-empty">
            <p>Your wishlist is empty. Start adding items you love!</p>
            <a href="shop.php">Browse our products</a>
        </div>
    <?php else: ?>
        <div class="wishlist-grid">
            <?php foreach ($wishlist_items as $item): ?>
                <div class="wishlist-item">
                    <img src="../assets/images/<?= htmlspecialchars($item['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="wishlist-item-image">
                    <div class="wishlist-item-details">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p class="wishlist-item-price">₹<?= formatPrice($item['price']) ?></p>
                    </div>
                    <div class="wishlist-item-actions">
                        <form method="post" action="wishlist.php">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                            <button type="submit" name="add_to_cart_from_wishlist" class="btn btn-cart">
                                <i class="fas fa-shopping-cart"></i> Add to Cart
                            </button>
                        </form>
                        <form method="post" action="wishlist.php">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($item['id']) ?>">
                            <button type="submit" name="remove_from_wishlist" class="btn btn-remove">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>