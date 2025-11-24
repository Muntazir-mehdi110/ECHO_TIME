<?php
// includes/footer.php
// compute assets path as in header so this works from root or pages/
$script_dir = str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME']));
$base = rtrim(str_replace('/pages', '', $script_dir), '/');
$assets = ($base === '') ? '/assets' : $base . '/assets';
?>
</main>

<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-column about">
            <h3>EchoTime</h3>
            <p>
                Your destination for premium timepieces. We blend classic craftsmanship with modern technology to bring you watches that stand the test of time.
            </p>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>

        <div class="footer-column links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="pages/shop.php">Shop</a></li>
                <li><a href="pages/about.php">About Us</a></li>
                <li><a href="pages/contact.php">Contact</a></li>
                <li><a href="pages/blog.php">Blog</a></li>
            </ul>
        </div>

        <div class="footer-column contact">
            <h3>Contact Us</h3>
            <p><i class="fas fa-map-marker-alt"></i> karachi , Pakistan</p>
            <p><i class="fas fa-phone"></i>  +92 3283668351</p>
            <p><i class="fas fa-envelope"></i> infoechotime1@gmail.com</p>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2023 EchoTime. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>

<!-- main script -->
<script src="<?= $assets ?>/js/app.js"></script>
</body>
</html>
