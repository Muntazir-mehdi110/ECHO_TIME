<?php
include '../includes/header.php';

// Sanitize inputs
$q = isset($_GET['q']) ? trim($_GET['q']) : null;
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$product_type = isset($_GET['product_type']) ? trim($_GET['product_type']) : null;

// Fetch products and categories
$products = get_products($conn, $category_id, $q, $min_price, $max_price, $product_type);
$main_cats = get_main_categories($conn);
?>

<div class="shop-page-container">
    <div class="sidebar-wrapper" id="filterSidebar">
        <!-- Close button removed per user request. The sidebar can now be closed by clicking on the dark overlay (the sidebar-wrapper itself). -->
        <aside class="sidebar">
            <div class="filter-group">
                <h3><i class="fas fa-layer-group me-2"></i>Categories</h3>
                <ul>
                    <li><a href="shop.php" class="<?= $category_id === null ? 'active' : '' ?>">All</a></li>
                    <?php foreach ($main_cats as $mc): ?>
                        <li>
                            <strong><?= esc($mc['name']) ?></strong>
                            <ul>
                                <?php foreach (get_subcategories($conn, $mc['id']) as $sub): ?>
                                    <li><a href="shop.php?category=<?= $sub['id'] ?>" class="<?= $category_id === $sub['id'] ? 'active' : '' ?>"><?= esc($sub['name']) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="filter-group">
                <h3><i class="fas fa-tags me-2"></i>Price Range</h3>
                <form action="shop.php" method="GET">
                    <?php if ($q): ?><input type="hidden" name="q" value="<?= esc($q) ?>"><?php endif; ?>
                    <?php if ($product_type): ?><input type="hidden" name="product_type" value="<?= esc($product_type) ?>"><?php endif; ?>
                    <input type="hidden" name="category" value="<?= esc($category_id) ?>">

                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Min ₹" value="<?= esc($min_price) ?>">
                        <span>-</span>
                        <input type="number" name="max_price" placeholder="Max ₹" value="<?= esc($max_price) ?>">
                    </div>
                    <button type="submit" class="apply-btn">Apply Filters</button>
                </form>
            </div>

            <div class="filter-group">
                <h3><i class="fas fa-clock me-2"></i>Product Type</h3>
                <ul>
                    <li><a href="shop.php?product_type=watch" class="<?= $product_type === 'watch' ? 'active' : '' ?>">Watches</a></li>
                    <li><a href="shop.php?product_type=earbud" class="<?= $product_type === 'earbud' ? 'active' : '' ?>">Earbuds</a></li>
                </ul>
            </div>
        </aside>
    </div>

    <section class="products-section">
        <div class="shop-header">
            <div>
                <h3><?= $category_id ? esc(get_category_name($conn, $category_id)) : 'All Products' ?></h3>
                <?php if ($q): ?><p>Showing results for “<strong><?= esc($q) ?></strong>”</p><?php endif; ?>
            </div>

            <!-- 🔹 Mobile / Tablet Filter Button (FIXED: Now correctly placed inside shop-header and linked to JS) -->
            <button id="openSidebar" class="filter-btn-mobile">
                <i class="fas fa-filter"></i> Filters
            </button>
        </div>

        <?php if (empty($products)): ?>
            <p class="no-products-msg">No products found. Try different filters.</p>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <a href="product.php?id=<?= esc($p['id']) ?>" class="product-link">
                            <div class="product-img-wrapper">
                                <img src="../uploads/<?= esc($p['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>">
                                <?php if (!empty($p['discount']) && $p['discount'] > 0): ?>
                                    <!-- Discount badge now uses (int) cast to remove decimals -->
                                    <span class="discount-badge">-<?= (int)$p['discount'] ?>% OFF</span>
                                <?php endif; ?>
                                <div class="hover-overlay"><i class="fas fa-eye"></i></div>
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
                            </div>
                        </a>
                        <div class="product-actions">
                            <form action="cart.php" method="post" class="flex-fill">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="id" value="<?= esc($p['id']) ?>">
                                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-cart-plus me-1"></i>Add</button>
                            </form>
                            <a class="btn btn-outline" href="product.php?id=<?= esc($p['id']) ?>"><i class="fas fa-info-circle me-1"></i>View</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../includes/footer.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
:root {
    --primary:#0d52a0;
    --primary-dark:#0a3b7a;
    --accent:#ff5e5e;
    --light:#fff;
    --bg-light:#f6f8fb;
    --text:#333;
    --border:#e1e1e1;
    --shadow:0 4px 15px rgba(0,0,0,0.08);
}

body { background: var(--bg-light); font-family: "Poppins", sans-serif; }

.shop-page-container { display:grid; grid-template-columns:280px 1fr; gap:25px; padding:40px 25px; max-width:1500px; margin:auto; }

.sidebar-wrapper { position:sticky; top:30px; } /* Changed to sticky to keep wrapper behavior */
.sidebar { background:var(--light); border-radius:12px; box-shadow:var(--shadow); padding:20px; }

.filter-group { margin-bottom:30px; }
.filter-group h3 { font-size:1.2rem; font-weight:600; color:var(--primary); border-bottom:2px solid var(--border); padding-bottom:10px; margin-bottom:15px; }
.filter-group ul { list-style:none; margin:0; padding:0; }
.filter-group li a { color:var(--text); text-decoration:none; display:block; padding:8px 10px; border-radius:6px; transition:0.3s; }
.filter-group li a:hover { background:rgba(13,82,160,0.1); padding-left:15px; }
.filter-group li a.active { background:var(--primary); color:#fff; font-weight:600; }

.price-inputs { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:15px 0; }
.price-inputs input { 
    flex:1; 
    padding:8px 10px; 
    border:1px solid var(--border); 
    border-radius:6px; 
    box-sizing: border-box; /* FIX: Prevents input from overflowing sidebar on desktop */
}
.apply-btn { width:100%; background:linear-gradient(90deg, var(--primary), var(--primary-dark)); color:#fff; border:none; border-radius:6px; padding:10px; font-weight:600; cursor:pointer; }
.apply-btn:hover { filter:brightness(1.1); }

.products-section { position: relative; }
.shop-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; padding-bottom:10px; border-bottom:1px solid var(--border); }
.products-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(240px,1fr)); gap:25px; }

.product-card { background:#fff; border-radius:12px; box-shadow:var(--shadow); overflow:hidden; transition:transform .3s ease, box-shadow .3s ease; }
.product-card:hover { transform:translateY(-5px); box-shadow:0 8px 25px rgba(0,0,0,0.12); }

.product-img-wrapper { width:100%; height:280px; overflow:hidden; border-bottom:1px solid var(--border); position:relative; }
.product-img-wrapper img { width:100%; height:100%; object-fit:contain; background-color:#f9f9f9; }
.discount-badge { position:absolute; top:10px; left:10px; background:#ff5e5e; color:#fff; font-size:0.85rem; font-weight:600; padding:5px 8px; border-radius:6px; box-shadow:0 2px 6px rgba(0,0,0,0.15); }

.hover-overlay { position:absolute; inset:0; display:flex; justify-content:center; align-items:center; background:rgba(13,82,160,0.3); color:#fff; font-size:1.5rem; opacity:0; transition:0.3s; }
.product-card:hover .hover-overlay { opacity:1; }

.product-info { padding:15px; text-align:center; }
.product-name { font-weight:600; font-size:1.05rem; color:var(--text); margin-bottom:5px; }
.product-price { color:var(--accent); font-weight:700; font-size:1.1rem; }

.product-actions { display:flex; gap:10px; padding:0 15px 15px; }
.btn { border-radius:8px; padding:10px; font-weight:600; cursor:pointer; border:1px solid transparent; }
.btn-primary { background:var(--primary); color:#fff; border-color:var(--primary); }
.btn-outline { background:#fff; color:var(--primary); border:1px solid var(--primary); }

.no-products-msg { text-align:center; color:#888; font-size:1.2rem; padding:60px 0; }

/* 🔹 Mobile filter button styling */
.filter-btn-mobile {
    display: none;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 10px 16px;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    box-shadow: var(--shadow);
}

.filter-btn-mobile i {
    margin-right: 6px;
}

.filter-btn-mobile:hover {
    background: var(--primary-dark);
}

.price-inputs {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 15px 0;
    flex-wrap: wrap; /* ✅ allows inputs to wrap on smaller widths */
}

.price-inputs input {
    flex: 1 1 45%; /* ✅ each input takes 45% width to fit neatly */
    min-width: 100px; /* ✅ keeps them readable even if sidebar is narrow */
    padding: 8px 10px;
    border: 1px solid var(--border);
    border-radius: 6px;
    box-sizing: border-box;
}
.price-inputs span {
    flex: 0 0 auto;
    text-align: center;
    font-weight: bold;
}


@media (max-width: 600px) {
  .price-inputs {
    flex-direction: column;
    align-items: stretch;
  }
  .price-inputs span {
    display: none;
  }
}


/* Show filter button only on tablets & mobiles */
@media (max-width: 991px) {
    .filter-btn-mobile {
        display: flex;
        align-items: center;
        gap: 6px;
    }
}


@media (max-width: 991px) {
    .shop-page-container { grid-template-columns:1fr; padding:20px 15px; }
    /* Mobile Sidebar styles are kept to ensure the sidebar can still be viewed on small screens */
    .sidebar-wrapper { position: fixed; top: 0; left: -100%; width: 100%; height: 100%; background: rgba(0,0,0,0.6); transition: left 0.3s ease; z-index: 1000; }
    .sidebar-wrapper.active { left: 0; }
    .sidebar { position: absolute; top: 0; left: 0; height: 100%; width: 80%; max-width: 300px; overflow-y: auto; }
    .close-sidebar-btn { 
        /* The close button HTML was removed, but keeping the CSS here just in case 
           a developer later reintroduces a button with this class. */
        position: fixed; top: 15px; left: 265px; background: var(--light); color: var(--primary); border: none; padding: 5px 10px; border-radius: 5px; z-index: 1001; 
    }
    .products-grid { grid-template-columns: repeat(2, 1fr); gap: 15px; }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const sidebar = document.getElementById('filterSidebar');
    const openBtn = document.getElementById('openSidebar');
    const body = document.body;

    const openSidebar = () => {
        if (sidebar) {
            sidebar.classList.add('active');
            body.style.overflow = 'hidden';
        }
    };

    const closeSidebar = () => {
        if (sidebar && sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
            body.style.overflow = '';
        }
    };

    // Open sidebar on filter icon click (FIX: Event listener is now active)
    if (openBtn) openBtn.addEventListener('click', openSidebar);

    // Close sidebar when clicking outside (on the overlay)
    if (sidebar) {
        sidebar.addEventListener('click', (e) => {
            if (e.target === sidebar) closeSidebar();
        });
    }
});
</script>
