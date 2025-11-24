<?php
session_start();
include '../includes/functions.php';
include '../includes/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = get_product($conn, $id);

if (!$product) {
    echo "<title>Product Not Found</title></head><body>";
    include '../includes/header.php';
    echo "<main class='container'><p style='text-align: center; padding: 50px;'>Product not found. <a href='shop.php'>Go to Shop</a></p></main>";
    include '../includes/footer.php';
    exit;
}

$main_image = $product['image'] ?: 'product-placeholder.jpg';
$additional_images = get_product_images($conn, $id);

$original_price = $product['price'];
$discount_rate = $product['discount'] ?? 0;
$has_discount = ($discount_rate > 0);
$display_price = $has_discount ? $original_price * (1 - ($discount_rate / 100)) : $original_price;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'review' && is_logged_in()) {
    $rating = (int)$_POST['rating'];
    $comment = trim($_POST['comment']);
    $uid = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, 'iiis', $id, $uid, $rating, $comment);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: product.php?id=$id");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT r.*, u.name FROM reviews r JOIN users u ON u.id = r.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$reviews = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$related_products = get_related_products($conn, $product['category_id'], $id, 4);
$average_rating = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
?>

<title><?= esc($product['name']) ?> - EchoTime</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* --- Modern Product Page Styles --- */
body {
    font-family: "Poppins", sans-serif;
    background: #f7f9fc;
    color: #333;
}

.product-page-wrapper {
    max-width: 1200px;
    margin: 60px auto;
    padding: 0 20px;
}

.product-details-main {
    display: flex;
    flex-direction: column;
    gap: 40px;
    animation: fadeIn 1s ease forwards;
}

@media (min-width: 992px) {
    .product-details-main {
        flex-direction: row;
        gap: 60px;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* --- Images --- */
.product-images-container {
    flex-shrink: 0;
    width: 45%;
    position: sticky;
    top: 100px;
}
.main-image-box {
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    background: #fff;
}
.main-image-box img {
    width: 100%;
    transition: transform 0.5s ease;
}
.main-image-box img:hover {
    transform: scale(1.05);
}
.thumbnail-gallery {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    justify-content: center;
}
.thumbnail-item {
    width: 70px;
    height: 70px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid transparent;
    transition: 0.3s;
    cursor: pointer;
}
.thumbnail-item:hover, .thumbnail-item.active {
    border-color: #0d52a0;
    transform: scale(1.1);
}

/* --- Product Info --- */
.product-info-container {
    flex: 1;
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    backdrop-filter: blur(10px);
    animation: fadeIn 1.2s ease forwards;
}
.product-title {
    font-size: 2.2rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: #0d52a0;
}
.product-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 20px;
}
.product-rating .fa-star, .fa-star-half-alt { color: #FFD700; }
.product-price {
    font-size: 2rem;
    color: #0d52a0;
    font-weight: 700;
}
.original-price {
    text-decoration: line-through;
    color: #888;
    margin-left: 10px;
}
.product-description {
    margin: 20px 0;
    line-height: 1.6;
}
.add-to-cart-form {
    display: flex;
    gap: 15px;
    align-items: center;
    margin-top: 25px;
}
.add-to-cart-btn {
    padding: 12px 25px;
    font-size: 1rem;
    border: none;
    border-radius: 8px;
    background: linear-gradient(135deg, #0d52a0, #0077ff);
    color: #fff;
    cursor: pointer;
    transition: 0.3s;
}
.add-to-cart-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 15px rgba(13,82,160,0.4);
}

/* --- Reviews --- */
.reviews-section {
    margin-top: 60px;
    background: #fff;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.section-heading {
    font-size: 1.8rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #0d52a0;
}
.review-item {
    border-bottom: 1px solid #eee;
    padding: 15px 0;
}
.review-header {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
}
.review-rating { color: #FFD700; }

/* --- Related Products --- */
.related-products-section {
    margin-top: 60px;
}
.product-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
    gap: 25px;
}
.product-card {
    background: #fff;
    border-radius: 12px;
    overflow: hidden;
    text-decoration: none;
    color: #333;
    transition: 0.3s;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}
.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 25px rgba(0,0,0,0.1);
}
.product-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    transition: transform 0.4s ease;
}
.product-card:hover img {
    transform: scale(1.05);
}
.product-info {
    padding: 15px;
    text-align: center;
}
.product-name {
    font-weight: 600;
    margin-bottom: 8px;
}


/* --- Product Image Zoom --- */
.main-image-box {
  position: relative;
  overflow: hidden;
  cursor: zoom-in;
}

.main-image-box img {
  width: 100%;
  height: auto;
  transform-origin: center center;
  transition: transform 0.3s ease;
}

/* When zoomed */
.main-image-box.zoomed {
  cursor: zoom-out;
}

.main-image-box.zoomed img {
  transform: scale(2); /* Zoom level */
}





</style>

<?php include '../includes/header.php'; ?>

<main class="product-page-wrapper">
    <section class="product-details-main">
        <div class="product-images-container">
            <div class="main-image-box">
                <img id="mainProductImage" src="../uploads/<?= esc($main_image) ?>" alt="<?= esc($product['name']) ?>">
            </div>
            <div class="thumbnail-gallery">
                <img class="thumbnail-item active" data-image-src="../uploads/<?= esc($main_image) ?>" src="../uploads/<?= esc($main_image) ?>">
                <?php foreach ($additional_images as $img): ?>
                    <img class="thumbnail-item" data-image-src="../uploads/<?= esc($img['image_path']) ?>" src="../uploads/<?= esc($img['image_path']) ?>">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-info-container">
            <h2 class="product-title"><?= esc($product['name']) ?></h2>
            <div class="product-rating">
                <?php
                $full = floor($average_rating);
                $half = ceil($average_rating) > $full;
                for ($i=0; $i<$full; $i++) echo '<i class="fas fa-star"></i>';
                if ($half) echo '<i class="fas fa-star-half-alt"></i>';
                for ($i=$full+($half?1:0); $i<5; $i++) echo '<i class="far fa-star"></i>';
                ?>
                <span>(<?= number_format($average_rating, 1) ?> / 5)</span>
            </div>

            <div class="price-group">
                <span class="product-price">₹<?= formatPrice($display_price) ?></span>
                <?php if ($has_discount): ?>
                    <span class="original-price">₹<?= formatPrice($original_price) ?></span>
                <?php endif; ?>
            </div>
            <p><strong>SKU:</strong> <?= esc($product['sku']) ?></p>
             <p><strong>Delivery Time:</strong> <?= esc($product['delivery_time']) ?></p>
             <p class="product-description"><?= nl2br(esc($product['description'])) ?></p>

            <form action="cart.php" method="post" class="add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= esc($product['id']) ?>">
                <input type="number" name="qty" value="1" min="1" max="<?= esc($product['stock']) ?>" class="form-input" style="padding:10px;width:80px;border-radius:8px;border:1px solid #ddd;">
                <button class="add-to-cart-btn">🛒 Add to Cart</button>
            </form>
        </div>
    </section>

    <section class="reviews-section">
        <h2 class="section-heading">Customer Reviews</h2>
        <?php if (empty($reviews)): ?>
            <p>No reviews yet. Be the first to review!</p>
        <?php else: foreach ($reviews as $rv): ?>
            <div class="review-item">
                <div class="review-header">
                    <span><?= esc($rv['name']) ?></span>
                    <span class="review-rating"><?= str_repeat('⭐', $rv['rating']) ?></span>
                </div>
                <p><?= nl2br(esc($rv['comment'])) ?></p>
            </div>
        <?php endforeach; endif; ?>

        <div class="review-form-box" style="margin-top:30px;">
            <h3 class="section-heading" style="font-size:1.3rem;">Leave a Review</h3>
            <?php if (!is_logged_in()): ?>
                <p>Please <a href="login.php">login</a> to write a review.</p>
            <?php else: ?>
                <form method="post" class="review-form">
                    <input type="hidden" name="action" value="review">
                    <select name="rating" style="padding:10px;border-radius:8px;border:1px solid #ddd;margin-bottom:10px;">
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                    <textarea name="comment" rows="3" placeholder="Write your review..." style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;"></textarea>
                    <button class="add-to-cart-btn" type="submit">Submit Review</button>
                </form>
            <?php endif; ?>
        </div>
    </section>

    <section class="related-products-section">
        <h2 class="section-heading">You May Also Like</h2>
        <div class="product-grid">
            <?php foreach ($related_products as $p): ?>
                <a href="product.php?id=<?= esc($p['id']) ?>" class="product-card">
                    <img src="../uploads/<?= esc($p['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>">
                    <div class="product-info">
                        <h4 class="product-name"><?= esc($p['name']) ?></h4>
                        <p class="product-price">₹<?= formatPrice($p['price']) ?></p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const mainImage = document.getElementById("mainProductImage");
  const thumbs = document.querySelectorAll(".thumbnail-item");
  thumbs.forEach(t => {
    t.addEventListener("click", () => {
      mainImage.style.opacity = 0;
      setTimeout(() => {
        mainImage.src = t.dataset.imageSrc;
        mainImage.style.opacity = 1;
      }, 200);
      thumbs.forEach(x => x.classList.remove("active"));
      t.classList.add("active");
    });
  });
});
</script>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const mainImageBox = document.querySelector(".main-image-box");
  const mainImage = document.getElementById("mainProductImage");
  const thumbs = document.querySelectorAll(".thumbnail-item");

  // 🖼️ Thumbnail Switch (existing)
  thumbs.forEach(t => {
    t.addEventListener("click", () => {
      mainImage.style.opacity = 0;
      setTimeout(() => {
        mainImage.src = t.dataset.imageSrc;
        mainImage.style.opacity = 1;
      }, 200);
      thumbs.forEach(x => x.classList.remove("active"));
      t.classList.add("active");
    });
  });

  // 🔍 Zoom on hover (desktop) or tap (mobile)
  let zoomed = false;

  mainImageBox.addEventListener("click", (e) => {
    zoomed = !zoomed;
    mainImageBox.classList.toggle("zoomed", zoomed);
  });

  // Move zoom focal point based on cursor
  mainImageBox.addEventListener("mousemove", (e) => {
    if (!zoomed) return;
    const rect = mainImageBox.getBoundingClientRect();
    const x = ((e.clientX - rect.left) / rect.width) * 100;
    const y = ((e.clientY - rect.top) / rect.height) * 100;
    mainImage.style.transformOrigin = `${x}% ${y}%`;
  });

  // Reset zoom when leaving area (desktop)
  mainImageBox.addEventListener("mouseleave", () => {
    zoomed = false;
    mainImageBox.classList.remove("zoomed");
    mainImage.style.transformOrigin = "center center";
  });

  // For touch devices (drag move)
  let touchActive = false;
  mainImageBox.addEventListener("touchstart", (e) => {
    zoomed = true;
    mainImageBox.classList.add("zoomed");
    touchActive = true;
  });

  mainImageBox.addEventListener("touchmove", (e) => {
    if (!touchActive) return;
    const rect = mainImageBox.getBoundingClientRect();
    const touch = e.touches[0];
    const x = ((touch.clientX - rect.left) / rect.width) * 100;
    const y = ((touch.clientY - rect.top) / rect.height) * 100;
    mainImage.style.transformOrigin = `${x}% ${y}%`;
  });

  mainImageBox.addEventListener("touchend", () => {
    zoomed = false;
    touchActive = false;
    mainImageBox.classList.remove("zoomed");
  });
});
</script>
