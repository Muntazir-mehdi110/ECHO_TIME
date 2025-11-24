<?php

// pages/login.php - Modern Design
// This is a standalone file, so no header.php or footer.php is included.

// Include necessary files directly.
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start the session if it's not already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check for logout action and clear the session.
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    set_message('You have been logged out successfully.', 'success');
    header('Location: login.php'); // Redirect back to login page
    exit;
}

// Handle form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (login_user($conn, $email, $password)) {
        set_message('Login successful!', 'success');
        $next = isset($_GET['next']) ? $_GET['next'] : '../index.php'; // Default to homepage
        header("Location: $next");
        exit;
    } else {
        set_message('Invalid email or password.', 'error');
        header('Location: login.php'); // Redirect back to login page
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to EchoTime</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff; /* EchoTime blue */
            --secondary-color: #ff9800; /* Orange accent */
            --text-color-dark: #333;
            --text-color-light: #555;
            --bg-color: #f5f7fa;
            --card-bg: #fff;
            --border-color: #e0e0e0;
            --focus-border-color: #007bff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-color-light);
        }

        .login-container {
            display: flex;
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 95%;
            margin: 20px;
        }

        .login-hero {
            flex: 1;
            background-color: var(--primary-color);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-hero h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .login-hero p {
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 30px;
            max-width: 300px;
        }

        .login-hero img {
            max-width: 80%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }

        .login-form-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
            text-decoration: none;
            color: var(--text-color-dark);
        }
        .logo-icon {
            font-size: 2.5rem;
            line-height: 1;
        }
        .logo-text {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }
        .welcome-text h3 {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-color-dark);
            margin-bottom: 8px;
        }
        .welcome-text p {
            font-size: 0.9rem;
            color: var(--text-color-light);
        }

        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }

        .input-field {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: var(--focus-border-color);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.2);
        }

        .forgot-password {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
        }
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
        }
        .forgot-password a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--primary-color);
            color: white;
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

        .toggle-link {
            font-size: 0.9rem;
            color: var(--text-color-light);
        }
        .toggle-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        .toggle-link a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            text-align: left;
            width: 100%;
            box-sizing: border-box;
        }
        .alert.error {
            background-color: #ffe0e0;
            color: #d32f2f;
            border: 1px solid #d32f2f;
        }
        .alert.success {
            background-color: #e0ffe0;
            color: #388e3c;
            border: 1px solid #388e3c;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 450px;
            }
            .login-hero {
                padding: 20px;
                order: -1;
            }
            .login-hero h2 {
                font-size: 2rem;
            }
            .login-hero p {
                font-size: 0.9rem;
            }
            .login-form-section {
                padding: 30px;
            }
            .logo {
                margin-bottom: 20px;
            }
            .welcome-text h3 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-hero">
        <h2>Unleash Your Time, Define Your Style.</h2>
        <p>Explore our exquisite collection of watches and earbuds that elevate your everyday.</p>
        <img src="../assets/images/watch-hero.png" alt="EchoTime Watches Collection">
    </div>

    <div class="login-form-section">
        <a href="../index.php" class="logo">
            <span class="logo-icon">⌚</span>
            <div class="logo-text">EchoTime</div>
        </a>

        <div class="welcome-text">
            <h3>Welcome Back!</h3>
            <p>Please login to your account</p>
        </div>

        <?php display_message(); ?>

        <form method="post" style="width: 100%;">
            <div class="form-group">
                <input class="input-field" type="email" name="email" placeholder="Email address" required>
            </div>
            <div class="form-group">
                <input class="input-field" type="password" name="password" placeholder="Password" required>
            </div>
            <div class="forgot-password">
                <a href="#">Forgot password?</a>
            </div>
            <button class="btn-submit" type="submit">Login</button>
        </form>

        <p class="toggle-link">
            Don't have an account? <a href="register.php">Register here</a>
        </p>
    </div>
</div>

</body>
</html>