<?php 
// Only include the header file.
// The header file should handle including db.php, auth.php, and functions.php.
include 'includes/header.php';

// Fetch a promotion to be used for "Today's Deals"
$deal = get_active_deal($conn);

// Fetch new arrivals to display on the homepage
$new_arrivals = get_new_arrivals($conn, 8);

?>

<link rel="stylesheet" href="assets/css/style.css">



<section class="hero-banner">
    <video autoplay muted loop playsinline class="bg-video">
        <source src="https://www.pexels.com/download/video/4824611/" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="video-overlay"></div>
    <div class="banner-content">
        <h1>Welcome to Watch Nest</h1>
        <p>Explore our exclusive collection of premium smartwatches and high-fidelity earbuds.</p>
        <a href="pages/shop.php" class="btn btn-primary">Shop Now</a>
    </div>
</section>

<section class="services-sec">
    <div class="service-grid">
        <div class="service-card">
            <div class="icon-box"><i class="fas fa-truck-fast"></i></div>
            <h3>Free Shipping</h3>
            <p>Free shipping on all orders</p>
        </div>
        <div class="service-card">
            <div class="icon-box"><i class="fas fa-headset"></i></div>
            <h3>Support 24/7</h3>
            <p>Contact us 24 hrs a day</p>
        </div>
        <div class="service-card">
            <div class="icon-box"><i class="fas fa-shield-halved"></i></div>
            <h3>Payment Secure</h3>
            <p>100% secure payment options</p>
        </div>
    </div>
</section>


<?php if ($deal): ?>
<section class="todays-deals-sec">
  <div class="deal-header">
    <h2 class="section-title">🔥 Today’s Hot Deals</h2>
    <div class="timer-box">
      <span>Ends In:</span>
      <div id="countdown-timer" data-end-date="<?= esc($deal['end_date']) ?>">
        <span class="time-part" id="days">00</span>d :
        <span class="time-part" id="hours">00</span>h :
        <span class="time-part" id="minutes">00</span>m :
        <span class="time-part" id="seconds">00</span>s
      </div>
    </div>
    <div class="deal-nav">
      <button class="nav-btn prev-btn"><i class="fas fa-chevron-left"></i></button>
      <button class="nav-btn next-btn"><i class="fas fa-chevron-right"></i></button>
    </div>
  </div>

  <div class="deal-products-grid">
    <?php foreach (get_deal_products($conn, $deal['id']) as $p): ?>
      <div class="product-card">
        <div class="product-img">
          <a href="pages/product.php?id=<?= esc($p['id']) ?>">
            <img src="uploads/<?= esc($p['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>">
          </a>
          <span class="discount-badge">Deal</span>
        </div>
        <div class="product-info">
          <h4 class="product-name"><?= esc($p['name']) ?></h4>
                         <?php if (!empty($p['discount']) && $p['discount'] > 0): 
                                    $discounted_price = $p['price'] - ($p['price'] * $p['discount'] / 100);
                                ?>
                                    <p class="product-price">
                                        <span style="color:#ff5e5e; font-weight:700;">₹<?= formatPrice($discounted_price) ?></span>
                                        <del style="color:#999; font-size:0.9rem;">₹<?= formatPrice($p['price']) ?></del>
                                    </p>
                                <?php else: ?>
                                    <p class="product-price">₹<?= formatPrice($p['price']) ?></p>
                                <?php endif; ?>
          <a href="pages/cart.php?action=add&id=<?= esc($p['id']) ?>" class="btn-buy">
            <i class="fas fa-cart-plus"></i> Add to Cart
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>


<section class="categories-sec">
    <h2 class="section-title">Shop by Categories</h2>
    <div class="cat-grid">
        <div class="cat-card">
            <a href="pages/watches.php">
                <img class="cat-img" src="https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=1200&q=80" alt="Luxury Watch" loading="lazy">
                <div class="cat-overlay">
                    <h3>Watches</h3>
                    <p>Classic • Sport • Smart</p>
                    <span class="cat-btn">Explore Now</span>
                </div>
            </a>
        </div>
        <div class="cat-card">
            <a href="pages/earbuds.php">
                <img class="cat-img" src="https://images.unsplash.com/photo-1722439667098-f32094e3b1d4?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OHx8ZWFyYnVkc3xlbnwwfHwwfHx8MA%3D%3D" alt="Premium Earbuds" loading="lazy">
                <div class="cat-overlay">
                    <h3>Earbuds</h3>
                    <p>Wireless • Noise Cancelling • Sleek</p>
                    <span class="cat-btn">Shop Now</span>
                </div>
            </a>
        </div>
    </div>
</section>

<section class="new-arrivals">
  <h2 class="section-title">New Arrivals</h2>
  <?php if (empty($new_arrivals)): ?>
    <p>No new products found at the moment. Please check back later!</p>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($new_arrivals as $p): ?>
        <div class="product-card">
          <a href="pages/product.php?id=<?=esc($p['id'])?>" class="product-link">
            <div class="product-img">
              <img src="uploads/<?=esc($p['image']?:'product-placeholder.jpg')?>" alt="<?=esc($p['name'])?>">
              <span class="new-badge">New</span>
              <a href="#" class="quick-view-btn">Quick View</a>
            </div>
            <div class="product-info">
              <h4 class="product-name"><?=esc($p['name'])?></h4>
              <?php if (!empty($p['discount']) && $p['discount'] > 0): 
                                    $discounted_price = $p['price'] - ($p['price'] * $p['discount'] / 100);
                                ?>
                                    <p class="product-price">
                                        <span style="color:#ff5e5e; font-weight:700;">₹<?= formatPrice($discounted_price) ?></span>
                                        <del style="color:#999; font-size:0.9rem;">₹<?= formatPrice($p['price']) ?></del>
                                    </p>
                                <?php else: ?>
                                    <p class="product-price">₹<?= formatPrice($p['price']) ?></p>
                                <?php endif; ?>
              <span class="rating">
            </div>
          </a>
          <div class="product-actions">
            <a href="pages/cart.php?action=add&id=<?=esc($p['id'])?>" class="btn btn-buy">Add to Cart</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>


<!-- special gift -->
<!-- 
<section class="watch-banner">
    <div class="banner-content-container">
        <div class="text-content">
            <h2 class="section-title">EXCLUSIVE SMARTWATCH</h2>
            <p>Experience the perfect blend of style and technology. Explore our limited-edition collection today.</p>
            <a href="pages/shop.php" class="btn btn-shop-now">Shop Now</a>
        </div>
    </div>
</section> -->




<section class="watch-banner-grid">
    <div class="banner-item left">
        <div class="banner-content">
            <h2>Watch Shop</h2>
            <p>Hues Collection For You</p>
            <a href="pages/shop.php" class="btn btn-buy">BUY NOW</a>
        </div>
    </div>
    <div class="banner-item right">
        <div class="banner-content">
            <h2>Watch Shop</h2>
            <p>Hues Collection For You</p>
            <a href="pages/shop.php" class="btn btn-buy">BUY NOW</a>
        </div>
    </div>
</section>



<!-- GETIN BEST SELLING PRODUCTS -->
<section class="products-list-section">
  <h2 class="section-title">Best Sellers</h2>
  <?php
  $best_sellers = get_best_sellers($conn);
  if (empty($best_sellers)):
  ?>
    <p>No best-selling products found at the moment. Please check back later!</p>
  <?php else: ?>
    <div class="product-grid">
        <?php foreach ($best_sellers as $p): ?>
            <div class="product-card">
                <a href="pages/product.php?id=<?=esc($p['id'])?>" class="product-link">
                    <div class="product-img">
                        <img src="uploads/<?=esc($p['image']?:'product-placeholder.jpg')?>" alt="<?=esc($p['name'])?>">
                        
                        <?php 
                          $regular_price = $p['regular_price'] ?? $p['price'];
                          if (isset($p['sale_price']) && $p['sale_price'] > 0 && $p['sale_price'] < $regular_price): 
                            $discount_percentage = round((($regular_price - $p['sale_price']) / $regular_price) * 100);
                        ?>
                          <span class="discount-badge">-<?= $discount_percentage ?>%</span>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h4 class="product-name"><?=esc($p['name'])?></h4>
                        <?php if (!empty($p['discount']) && $p['discount'] > 0): 
                                    $discounted_price = $p['price'] - ($p['price'] * $p['discount'] / 100);
                                ?>
                                    <p class="product-price">
                                        <span style="color:#ff5e5e; font-weight:700;">₹<?= formatPrice($discounted_price) ?></span>
                                        <del style="color:#999; font-size:0.9rem;">₹<?= formatPrice($p['price']) ?></del>
                                    </p>
                                <?php else: ?>
                                    <p class="product-price">₹<?= formatPrice($p['price']) ?></p>
                                <?php endif; ?>
                    </div>
                </a>
                <a href="pages/cart.php?action=add&id=<?=esc($p['id'])?>" class="btn btn-buy">Add to Cart</a>
            </div>
        <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<!-- LATEST BLOGS -->


<section class="latest-blogs-section">
    <div class="container">
        <h2 class="section-title">Latest Blogs</h2>
        <div class="blog-grid">
            <?php
            $blogs = get_latest_blogs($conn);
            if ($blogs):
                foreach ($blogs as $blog):
            ?>
                <div class="blog-card">
                    <a href="pages/blog_post.php?id=<?= esc($blog['id']) ?>">
                        <div class="blog-img">
                            <img src="assets/images/<?= esc($blog['image'] ?: 'default_blog_image.jpg') ?>" alt="<?= esc($blog['title']) ?>">
                        </div>
                        <div class="blog-info">
                            <h4 class="blog-title"><?= esc($blog['title']) ?></h4>
                            <p class="blog-author-date">
                                by <?= esc($blog['author']) ?> · <?= date('M j, Y', strtotime($blog['created_at'])) ?>
                            </p>
                        </div>
                    </a>
                </div>
            <?php endforeach; else: ?>
                <p>No blog posts found at the moment.</p>
            <?php endif; ?>
        </div>
    </div>
</section>




<?php 
// Only include the footer file
include 'includes/footer.php'; 
?>
<script src="assets/js/app.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const timer = document.getElementById("countdown-timer");
  if (!timer) return;

  const endDate = new Date(timer.dataset.endDate).getTime();

  function updateCountdown() {
    const now = new Date().getTime();
    const diff = endDate - now;

    if (diff <= 0) {
      timer.innerHTML = "Deal Ended";
      return clearInterval(interval);
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    timer.querySelector("#days").textContent = String(days).padStart(2, '0');
    timer.querySelector("#hours").textContent = String(hours).padStart(2, '0');
    timer.querySelector("#minutes").textContent = String(minutes).padStart(2, '0');
    timer.querySelector("#seconds").textContent = String(seconds).padStart(2, '0');
  }

  updateCountdown();
  const interval = setInterval(updateCountdown, 1000);
});
</script>
