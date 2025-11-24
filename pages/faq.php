<?php
// pages/faq.php
include '../includes/header.php';
?>

<style>
    /* New styles for improved UI */
    :root {
        --primary-color: #007bff;
        --secondary-color: #ff9800; /* For a pop of color, if desired */
        --text-color-dark: #333;
        --text-color-light: #555;
        --bg-color-light: #f8f9fa;
        --card-bg: #fff;
        --border-color: #e0e0e0;
        --shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .faq-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Poppins', sans-serif;
    }

    .faq-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .faq-header h2 {
        font-size: 2.8rem;
        color: var(--text-color-dark);
        font-weight: 700;
        margin-bottom: 10px;
    }

    .faq-header p {
        font-size: 1.1rem;
        color: var(--text-color-light);
        line-height: 1.6;
    }

    .faq-section {
        margin-bottom: 50px;
    }
    
    .faq-section h3 {
        text-align: center;
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 30px;
        position: relative;
    }

    .faq-section h3::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background-color: var(--primary-color);
        border-radius: 2px;
    }

    .faq-item {
        background-color: var(--card-bg);
        border-radius: 12px;
        box-shadow: var(--shadow);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .faq-question {
        padding: 20px 25px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .faq-question h4 {
        margin: 0;
        font-size: 1.1rem;
        color: var(--text-color-dark);
        font-weight: 600;
    }

    .faq-question:hover {
        background-color: var(--bg-color-light);
    }

    .faq-question .toggle-icon {
        font-size: 1.5rem;
        color: var(--primary-color);
        transition: transform 0.3s ease;
    }

    .faq-answer {
        padding: 0 25px;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease-out, padding 0.4s ease-out;
        color: var(--text-color-light);
    }
    
    .faq-item.active .faq-answer {
        max-height: 500px;
        padding: 20px 25px;
        border-top: 1px solid var(--border-color);
    }

    .faq-answer p {
        margin: 0;
        line-height: 1.8;
    }

    .faq-item.active .faq-question .toggle-icon {
        transform: rotate(45deg);
    }
    
    .contact-cta {
        text-align: center;
        margin-top: 60px;
        padding: 40px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 12px;
    }
    .contact-cta h3 {
        font-size: 1.8rem;
        margin-bottom: 10px;
    }
    .contact-cta p {
        font-size: 1rem;
        margin-bottom: 20px;
    }
    .contact-cta a {
        display: inline-block;
        padding: 12px 30px;
        background-color: var(--secondary-color);
        color: #fff;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.3s ease;
    }
    .contact-cta a:hover {
        background-color: #e68900;
    }
</style>

<div class="faq-container">
    <header class="faq-header">
        <h2>Frequently Asked Questions</h2>
        <p>Find quick answers to common questions about your new watch or earbuds. If you can't find what you're looking for, our team is ready to help!</p>
    </header>

    <div class="faq-section">
        <h3>Watches</h3>
        <div class="faq-item">
            <div class="faq-question">
                <h4>How long does shipping take?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>We process and ship orders within <strong>3-7 business days</strong>. Once shipped, delivery times depend on your location and the shipping method selected at checkout. You will receive a tracking number via email to follow your package's journey.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-question">
                <h4>What is your return policy?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>We offer a <strong>30-day return policy</strong> from the date of delivery. Items must be in their original condition and packaging. Please visit our Returns page for detailed instructions on how to initiate a return.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <h4>Is there a warranty on your watches?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>Yes, all our watches come with a <strong>1-year limited warranty</strong> that covers manufacturing defects. The warranty does not cover damage from normal wear and tear, misuse, or accidental damage. Please refer to our Warranty Policy for full details.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <h4>Are your watches waterproof?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>Many of our watches are water-resistant, but this does not mean they are fully waterproof. The water resistance rating is listed on each product's description page. We do not recommend exposing your watch to significant water exposure unless it is specifically rated for such use.</p>
            </div>
        </div>
    </div>
    
    <div class="faq-section">
        <h3>Earbuds</h3>
        <div class="faq-item">
            <div class="faq-question">
                <h4>How do I pair my earbuds with my phone?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>First, make sure your earbuds are fully charged. Open the charging case to automatically turn them on. Then, go to the Bluetooth settings on your phone and select your earbuds from the list of available devices to connect.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <h4>What is the battery life of the earbuds?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>Our earbuds provide up to <strong>5 hours</strong> of playback on a single charge. The charging case holds multiple charges, extending the total listening time to over 24 hours.</p>
            </div>
        </div>
        
        <div class="faq-item">
            <div class="faq-question">
                <h4>Are the earbuds sweat-resistant?</h4>
                <span class="toggle-icon">+</span>
            </div>
            <div class="faq-answer">
                <p>Yes, all EchoTime earbuds are designed with an IPX4 rating, making them resistant to splashes and sweat. They are perfect for workouts but are not recommended for swimming or showering.</p>
            </div>
        </div>
    </div>
    
    <div class="contact-cta">
        <h3>Still have questions? We're here to help.</h3>
        <p>If you couldn't find the answer you were looking for, our customer support team is ready to assist you.</p>
        <a href="contact.php">Contact Us</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const faqItems = document.querySelectorAll('.faq-item');

        faqItems.forEach(item => {
            const question = item.querySelector('.faq-question');
            question.addEventListener('click', () => {
                const answer = item.querySelector('.faq-answer');
                const isOpen = item.classList.contains('active');

                // Close all other open answers
                faqItems.forEach(otherItem => {
                    if (otherItem !== item && otherItem.classList.contains('active')) {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.faq-answer').classList.remove('open');
                    }
                });
                
                // Toggle the clicked item
                if (!isOpen) {
                    item.classList.add('active');
                    answer.classList.add('open');
                } else {
                    item.classList.remove('active');
                    answer.classList.remove('open');
                }
            });
        });
    });
</script>

<?php include '../includes/footer.php'; ?>