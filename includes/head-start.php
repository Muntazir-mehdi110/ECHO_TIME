<?php
// includes/head-start.php

// 1. Setup session and includes
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// 2. Compute base path for assets and links
$script_dir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME']));
// Assumes 'pages' is one level below the project root
$base = rtrim(str_replace('/pages', '', $script_dir), '/');
$assets = ($base === '') ? '/assets' : $base . '/assets';

// 3. Login Redirection Logic
$current_page = basename($_SERVER['PHP_SELF']);
// Add all pages that do NOT require a login
$allowed_pages = ['login.php', 'register.php', 'cart.php', 'product.php', 'shop.php', 'watches.php', 'earbuds.php', 'contact.php'];

if (!is_logged_in() && !in_array($current_page, $allowed_pages)) {
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $assets ?>/css/style.css"> 
    

<?php
// includes/header.php
// Assumes $base, is_logged_in(), and cart_count() are defined in the file that includes this one.
?>
<header class="site-header">
    <div class="header-top">
        <div class="container header-inner">
            <div class="brand">
                <a href="<?= $base ?>/index.php" class="logo">
                    <span class="logo-icon">⌚</span>
                    <div>
                        <div class="logo-text">EchoTime</div>
                    </div>
                </a>
            </div>

            <form class="search-form" action="<?= $base ?>/pages/shop.php" method="get">
                <input type="text" name="q" placeholder="Search">
            </form>

            <div class="header-info-group">
                <span class="header-contact">📞+92 3283668351</span>
                <div class="header-socials">
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                </div>
            </div>

            <div class="mobile-header-icons">
                <button class="icon-btn" id="openSearchModal"><i class="fas fa-search"></i></button>
                <a href="#" class="icon-btn"><i class="far fa-heart"></i></a>
                <a href="<?= $base ?>/pages/cart.php" class="icon-btn cart">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge"><?= cart_count() ?></span>
                </a>
                <button class="mobile-nav-toggle" id="mobileNavToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="header-bottom">
        <div class="container nav-row">
            <nav class="nav-links">
                <a href="<?= $base ?>/index.php">Home</a>
                <a href="<?= $base ?>/pages/shop.php">Shop</a>
                <a href="<?= $base ?>/pages/watches.php">Watches</a>
                <a href="<?= $base ?>/pages/earbuds.php">Earbuds</a>
                <a href="<?= $base ?>/pages/contact.php">Contact</a>
            </nav>
            <div class="nav-actions">
                <a href="<?= $base ?>/pages/cart.php" class="icon-btn desktop-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge"><?= cart_count() ?></span>
                </a>
                <?php if (is_logged_in()): ?>
                    <a href="<?= $base ?>/pages/profile.php" class="icon-btn"><i class="fas fa-user-circle"></i></a>
                    <a href="<?= $base ?>/pages/login.php?action=logout" class="icon-btn"><i class="fas fa-sign-out-alt"></i></a>
                <?php else: ?>
                    <a href="<?= $base ?>/pages/login.php" class="icon-btn"><i class="fas fa-user"></i></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="mobile-nav" id="mobileNav">
        <a href="<?= $base ?>/index.php">Home</a>
        <a href="<?= $base ?>/pages/shop.php">Shop</a>
        <a href="<?= $base ?>/pages/watches.php">Watches</a>
        <a href="<?= $base ?>/pages/earbuds.php">Earbuds</a>
        <a href="<?= $base ?>/pages/contact.php">Contact</a>
        <a href="<?= $base ?>/pages/cart.php"><i class="fas fa-shopping-cart"></i>Cart (<?= cart_count() ?>)</a>
        <?php if (is_logged_in()):?>
            <a href="<?= $base?>/pages/profile.php"><i class="fas fa-user-circle"></i>Profile</a>
            <a href="<?= $base?>/pages/login.php?action=logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
        <?php else:?>
            <a href="<?=$base?>/pages/login.php"><i class="fas fa-user"></i>Login</a>
        <?php endif;?>
    </nav>
</header>

<div class="search-modal" id="searchModal">
    <div class="search-modal-content">
        <button class="close-btn" id="closeSearchModal">&times;</button>
        <form action="<?= $base ?>/pages/shop.php" method="get">
            <input type="text" name="q" placeholder="Search products...">
        </form>
    </div>
</div>

<script>
// Header/Mobile Navigation/Search Modal JS
document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("mobileNavToggle");
    const mobileNav = document.getElementById("mobileNav");

    if (toggle && mobileNav) {
        toggle.addEventListener("click", () => {
            mobileNav.classList.toggle("active");
            toggle.classList.toggle("active");
        });
    }

    const openSearch = document.getElementById("openSearchModal");
    const searchModal = document.getElementById("searchModal");
    const closeSearch = document.getElementById("closeSearchModal");

    if (openSearch && searchModal && closeSearch) {
        openSearch.addEventListener("click", (e) => {
            e.preventDefault();
            searchModal.classList.add("active");
        });
        closeSearch.addEventListener("click", () => {
            searchModal.classList.remove("active");
        });
        searchModal.addEventListener("click", (e) => {
            if (e.target === searchModal) searchModal.classList.remove("active");
        });
    }
});
</script>