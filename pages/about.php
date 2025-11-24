<?php
// pages/about.php
include '../includes/header.php';
?>

<style>
    :root {
        --primary-color: #007bff;
        --navbar-color: #004085;
        --text-color-dark: #333;
        --text-color-light: #555;
        --bg-color-light: #f8f9fa;
        --card-bg: #fff;
        --border-color: #e9ecef;
        --shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .about-container {
        max-width: 1000px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Poppins', sans-serif;
    }

    .about-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .about-header h2 {
        font-size: 3rem;
        color: var(--text-color-dark);
        font-weight: 700;
        margin-bottom: 10px;
    }

    .about-header p {
        font-size: 1.1rem;
        color: var(--text-color-light);
        max-width: 600px;
        margin: 0 auto;
    }

    .about-section {
        display: flex;
        align-items: center;
        gap: 50px;
        margin-bottom: 60px;
        padding: 30px;
        background-color: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--shadow);
    }

    .about-section.reverse-row {
        flex-direction: row-reverse;
    }

    .about-image {
        flex: 1;
        min-width: 300px;
    }

    .about-image img {
        width: 100%;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        object-fit: cover;
        height: 100%;
    }

    .about-content {
        flex: 1.2;
    }

    .about-content h3 {
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 15px;
    }

    .about-content p {
        font-size: 1rem;
        line-height: 1.8;
        color: var(--text-color-light);
        margin-bottom: 20px;
    }

    .about-values {
        text-align: center;
        margin-bottom: 60px;
    }

    .about-values h3 {
        font-size: 2.2rem;
        color: var(--text-color-dark);
        font-weight: 700;
        margin-bottom: 30px;
    }

    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 30px;
    }

    .value-item {
        background-color: var(--card-bg);
        padding: 30px;
        border-radius: 12px;
        box-shadow: var(--shadow);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .value-item:hover {
        transform: translateY(-10px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .value-item i {
        font-size: 2.5rem;
        color: var(--navbar-color);
        margin-bottom: 15px;
        transition: color 0.3s ease;
    }

    .value-item:hover i {
        color: var(--primary-color);
    }

    .value-item h4 {
        font-size: 1.25rem;
        color: var(--text-color-dark);
        font-weight: 600;
        margin-bottom: 10px;
    }

    .value-item p {
        font-size: 0.95rem;
        color: var(--text-color-light);
    }

    .cta-banner {
        text-align: center;
        background: var(--primary-color);
        color: white;
        padding: 50px 20px;
        border-radius: 12px;
    }

    .cta-banner h3 {
        font-size: 2rem;
        margin-bottom: 15px;
    }

    .cta-banner a {
        display: inline-block;
        padding: 12px 30px;
        background-color: #fff;
        color: var(--primary-color);
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }

    .cta-banner a:hover {
        background-color: #f0f8ff;
    }

    @media (max-width: 768px) {
        .about-section, .about-section.reverse-row {
            flex-direction: column;
        }
        .about-image, .about-content {
            min-width: unset;
            width: 100%;
        }
    }
</style>

<div class="about-container">
    <header class="about-header">
        <h2>About EchoTime</h2>
        <p>EchoTime is your destination for quality watches and innovative earbuds. We believe that technology and style should go hand in hand, and our curated collection reflects that philosophy.</p>
    </header>

    <!-- Watches Section -->
    <section class="about-section">
        <div class="about-image">
            <img src="https://images.pexels.com/photos/190819/pexels-photo-190819.jpeg?auto=compress&cs=tinysrgb&w=1200&q=80" alt="A collection of elegant watches">
        </div>
        <div class="about-content">
            <h3>Our Story</h3>
            <p>Founded with a passion for craftsmanship and cutting-edge technology, EchoTime was created to fill a gap in the market for products that are both functional and fashionable. We started with a small collection of timepieces and have since expanded to include a range of premium earbuds, all chosen for their superior quality and design.</p>
            <p>Our journey is about bringing you products that not only enhance your daily life but also reflect your personal style. We are a team of enthusiasts dedicated to providing you with the best products and an exceptional shopping experience.</p>
        </div>
    </section>

    <!-- Earbuds Section -->
    <section class="about-section reverse-row">
        <div class="about-image">
            <img src="https://images.pexels.com/photos/3394658/pexels-photo-3394658.jpeg?auto=compress&cs=tinysrgb&w=1200&q=80" alt="Wireless earbuds and a phone">
        </div>
        <div class="about-content">
            <h3>Our Mission</h3>
            <p>Our mission is simple: to offer a meticulously selected collection of watches and earbuds that meet the highest standards of quality, performance, and aesthetic appeal. We strive to be more than just a store; we aim to be a source of inspiration for those who value style and innovation.</p>
            <p>We are committed to providing transparent information, excellent customer support, and a seamless shopping experience from start to finish. Your satisfaction is our top priority.</p>
        </div>
    </section>

    <!-- Values Section -->
    <section class="about-values">
        <h3>Our Core Values</h3>
        <div class="values-grid">
            <div class="value-item">
                <i class="fas fa-gem"></i>
                <h4>Quality & Craftsmanship</h4>
                <p>We hand-pick every product to ensure it meets our strict standards for durability, functionality, and design excellence.</p>
            </div>
            <div class="value-item">
                <i class="fas fa-heart"></i>
                <h4>Customer-Centric Service</h4>
                <p>Your experience matters. We are dedicated to providing responsive and helpful support every step of the way.</p>
            </div>
            <div class="value-item">
                <i class="fas fa-lightbulb"></i>
                <h4>Innovation & Style</h4>
                <p>We stay ahead of the curve, curating a collection that features the latest trends and technological advancements.</p>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <div class="cta-banner">
        <h3>Ready to define your time and style?</h3>
        <p>Explore our exclusive collection and find the perfect piece for you.</p>
        <a href="shop.php">Shop Now</a>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
