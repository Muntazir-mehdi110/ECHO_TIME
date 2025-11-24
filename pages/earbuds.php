<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Category: Earbuds = 13
$category_id = 13;

// Handle sorting
$sort = $_GET['sort'] ?? 'latest';
switch ($sort) {
    case 'price_asc':
        $order_by = 'price ASC';
        break;
    case 'price_desc':
        $order_by = 'price DESC';
        break;
    default:
        $order_by = 'id DESC';
}

// Step 1: Get all subcategories of Earbuds
$subcategories = [];
$sub_query = mysqli_query($conn, "SELECT id FROM categories WHERE parent_id = $category_id");
while ($row = mysqli_fetch_assoc($sub_query)) {
    $subcategories[] = $row['id'];
}

// Step 2: Combine parent + subcategory IDs
$all_category_ids = array_merge([$category_id], $subcategories);

// Step 3: Prepare placeholders for SQL IN()
$placeholders = implode(',', array_fill(0, count($all_category_ids), '?'));
$types = str_repeat('i', count($all_category_ids));

// Step 4: Fetch all products for these categories
$products = [];
$sql = "SELECT * FROM products WHERE category_id IN ($placeholders) ORDER BY $order_by";
$stmt = mysqli_prepare($conn, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, $types, ...$all_category_ids);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $products[] = $row;
    }
    mysqli_stmt_close($stmt);
}

include __DIR__ . '/../includes/header.php';
?>

<main class="shop-page">
    <div class="shop-header">
        <h2>🎧 Explore Our Premium Earbuds</h2>
        <form method="get" class="filter-form">
            <label>Sort by:</label>
            <select name="sort" onchange="this.form.submit()">
                <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low → High</option>
                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High → Low</option>
            </select>
        </form>
    </div>

    <?php if (empty($products)): ?>
        <p class="no-products">No earbuds found in this category.</p>
    <?php else: ?>
        <div class="products-grid">
            <?php foreach ($products as $p): 
                $is_discounted = isset($p['discount']) && $p['discount'] > 0;
                $sale_price = $p['price'];
                if ($is_discounted) {
                    $sale_price = $p['price'] * (1 - $p['discount'] / 100);
                }
            ?>
                <div class="product-card">
                    <a href="product.php?id=<?= esc($p['id']) ?>" class="product-img">
                        <img src="../uploads/<?= esc($p['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>">
                        <?php if ($is_discounted): ?>
                            <span class="sale-badge">-<?= (int)$p['discount'] ?>%</span>
                        <?php endif; ?>
                    </a>
                    <div class="product-info">
                        <h3><?= esc($p['name']) ?></h3>

                        <div class="price-container">
                            <?php if ($is_discounted): ?>
                                <span class="original-price">₹<?= formatPrice($p['price']) ?></span>
                                <span class="sale-price">₹<?= formatPrice($sale_price) ?></span>
                            <?php else: ?>
                                <span class="price">₹<?= formatPrice($p['price']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="buttons">
                            <form action="cart.php" method="post">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?= esc($p['id']) ?>">
                                <button type="submit" class="btn-cart">
                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                </button>
                            </form>
                            <a href="product.php?id=<?= esc($p['id']) ?>" class="btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
/* ---------- Base ---------- */
.shop-page {
    max-width: 1250px;
    margin: 0 auto;
    padding: 40px 20px;
    font-family: "Poppins", sans-serif;
    background-color: #f9fafc;
}

/* ---------- Header & Filter ---------- */
.shop-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 25px;
    gap: 15px;
}

.shop-header h2 {
    color: #0d52a0;
    font-size: 1.9rem;
    font-weight: 700;
    text-align: center;
}

.filter-form {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-form select {
    padding: 8px 14px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background: #fff;
    font-size: 0.95rem;
    cursor: pointer;
}

/* ---------- Product Grid ---------- */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 25px;
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
}

/* ---------- Product Card ---------- */
.product-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 5px 18px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    position: relative;
}

.product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

/* Image */
.product-img {
    position: relative;
    display: block;
    width: 100%;
    aspect-ratio: 1 / 1.1;
    overflow: hidden;
    background: #f8f9fc;
}

.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.4s ease;
}

.product-card:hover .product-img img {
    transform: scale(1.05);
}

/* Sale Badge */
.sale-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background-color: #e63946;
    color: white;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

/* ---------- Info ---------- */
.product-info {
    padding: 16px;
    text-align: center;
}

.product-info h3 {
    font-size: 1.05rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 8px;
}

/* Price */
.price-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
}

.price {
    color: #e63946;
    font-size: 1.1rem;
    font-weight: 600;
}

.original-price {
    color: #999;
    font-size: 0.95rem;
    text-decoration: line-through;
    font-weight: 500;
}

.sale-price {
    color: #e63946;
    font-size: 1.1rem;
    font-weight: 700;
}

/* ---------- Buttons ---------- */
.buttons {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.btn-cart, .btn-view {
    padding: 8px 14px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.3s ease;
    cursor: pointer;
}

.btn-cart {
    background: linear-gradient(135deg, #0d52a0, #0077ff);
    color: #fff;
    border: none;
}

.btn-cart:hover {
    background: #084082;
}

.btn-view {
    border: 1px solid #0d52a0;
    color: #0d52a0;
}

.btn-view:hover {
    background: #0d52a0;
    color: #fff;
}

/* ---------- No Products ---------- */
.no-products {
    text-align: center;
    color: #777;
    font-size: 1rem;
    padding: 60px 0;
}
</style>
