<?php
// pages/contact.php
include '../includes/header.php';
// Include your database configuration file
// include '../includes/db_config.php';

$message = '';
$message_type = '';
$name = '';
$email = '';
$subject = '';
$message_content = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate form inputs
    $name = filter_var($_POST['name'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $subject = filter_var($_POST['subject'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $message_content = filter_var($_POST['message'] ?? '', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($name) || empty($email) || empty($subject) || empty($message_content)) {
        $message = 'Please fill out all the fields.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';
    } else {
        // Prepare and execute the SQL query using prepared statements to prevent SQL injection
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind parameters to the statement
            $stmt->bind_param("ssss", $name, $email, $subject, $message_content);
            
            if ($stmt->execute()) {
                $message = 'Thank you for your message! We will get back to you shortly.';
                $message_type = 'success';
                // Reset form fields after successful submission
                $name = $email = $subject = $message_content = '';
            } else {
                $message = 'Error saving your message. Please try again later.';
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'An unexpected error occurred. Please try again later.';
            $message_type = 'error';
        }
    }
}
?>

<style>
    /* General Styles for the Contact Page */
    :root {
        --primary-color: #007bff;
        --text-color-dark: #333;
        --text-color-light: #555;
        --bg-color-light: #f8f9fa;
        --card-bg: #fff;
        --border-color: #e9ecef;
        --shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .contact-container {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
        font-family: 'Poppins', sans-serif;
    }

    .contact-header {
        text-align: center;
        margin-bottom: 50px;
    }

    .contact-header h2 {
        font-size: 3rem;
        color: var(--text-color-dark);
        font-weight: 700;
        margin-bottom: 10px;
    }

    .contact-header p {
        font-size: 1.1rem;
        color: var(--text-color-light);
        max-width: 600px;
        margin: 0 auto;
    }
    
    .contact-grid {
        display: flex;
        gap: 40px;
        flex-wrap: wrap;
    }

    .contact-form-section, .contact-info-section {
        flex: 1;
        min-width: 300px;
        background-color: var(--card-bg);
        padding: 40px;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }

    .contact-form h3, .contact-info-section h3 {
        font-size: 2rem;
        color: var(--primary-color);
        font-weight: 600;
        margin-bottom: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
    }

    .btn-submit {
        display: block;
        width: 100%;
        padding: 15px;
        background-color: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .btn-submit:hover {
        background-color: #0056b3;
    }

    .contact-info-item {
        margin-bottom: 25px;
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .contact-info-item i {
        font-size: 1.5rem;
        color: var(--primary-color);
        margin-top: 5px;
    }
    
    .contact-info-item p, .contact-info-item a {
        margin: 0;
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-color-light);
        text-decoration: none;
    }
    
    .contact-info-item a:hover {
        color: var(--primary-color);
    }
    
    .social-links {
        display: flex;
        gap: 20px;
        margin-top: 20px;
    }
    
    .social-links a {
        font-size: 1.5rem;
        color: var(--text-color-light);
        transition: color 0.3s ease;
    }
    
    .social-links a:hover {
        color: var(--primary-color);
    }

    .map-container {
        margin-top: 50px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow);
    }

    .map-container iframe {
        width: 100%;
        height: 450px;
        border: 0;
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

    @media (max-width: 768px) {
        .contact-grid {
            flex-direction: column;
        }
    }
</style>

<div class="contact-container">
    <header class="contact-header">
        <h2>Get in Touch</h2>
        <p>We'd love to hear from you! Please fill out the form below or use the contact information provided.</p>
    </header>

    <?php if ($message): ?>
        <div class="message-box <?= esc($message_type) ?>">
            <?= esc($message) ?>
        </div>
    <?php endif; ?>

    <div class="contact-grid">
        <div class="contact-form-section">
            <h3>Send a Message</h3>
            <form action="contact.php" method="POST" class="contact-form">
                <div class="form-group">
                    <input type="text" name="name" class="form-control" placeholder="Your Name" value="<?= esc($name) ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Your Email" value="<?= esc($email) ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="subject" class="form-control" placeholder="Subject" value="<?= esc($subject) ?>">
                </div>
                <div class="form-group">
                    <textarea name="message" class="form-control" rows="6" placeholder="Your Message"><?= esc($message_content) ?></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        </div>

        <div class="contact-info-section">
            <h3>Contact Details</h3>
            <div class="contact-info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <p>EchoTime Inc.</p>
                    <p> Karachi Pakistan</p>
                </div>
            </div>
            <div class="contact-info-item">
                <i class="fas fa-envelope"></i>
                <p><a href="mailto:support@echotime.com"> infoechotime1@gmail.com</a></p>
            </div>
            <div class="contact-info-item">
                <i class="fas fa-phone-alt"></i>
                <p><a href="tel:+1234567890">+92 3283668351</a></p>
            </div>

            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </div>
    
    <div class="map-container">
        
    </div>
</div>

<?php include '../includes/footer.php'; ?>