<?php

// pages/register.php - Modern Design
// This is a standalone file, so no header.php or footer.php is included.

// Include necessary files directly.
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Start the session if it's not already started.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $res = register_user($conn, $name, $email, $password);

    if ($res === true) {
        set_message('Registration successful. Please login.', 'success');
        header('Location: login.php'); // Redirect to login after successful registration
        exit;
    } else {
        set_message($res, 'error');
        header('Location: register.php'); // Redirect back to register on error
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for EchoTime</title>
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

        .register-container {
            display: flex;
            background-color: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 95%;
            margin: 20px;
        }

        .register-hero {
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

        .register-hero h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .register-hero p {
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 30px;
            max-width: 300px;
        }

        .register-hero img {
            max-width: 80%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }

        .register-form-section {
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
            .register-container {
                flex-direction: column;
                max-width: 450px;
            }
            .register-hero {
                padding: 20px;
                order: -1;
            }
            .register-hero h2 {
                font-size: 2rem;
            }
            .register-hero p {
                font-size: 0.9rem;
            }
            .register-form-section {
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

<div class="register-container">
    <div class="register-hero">
        <h2>Join EchoTime Today!</h2>
        <p>Create your account to unlock exclusive deals, track your orders, and explore our full range of watches and earbuds.</p>
        <img src="../assets/images/watch-hero.png" alt="EchoTime Watches Collection">
    </div>

    <div class="register-form-section">
        <a href="../index.php" class="logo">
            <span class="logo-icon">⌚</span>
            <div class="logo-text">EchoTime</div>
        </a>

        <div class="welcome-text">
            <h3>Start Your Journey!</h3>
            <p>Create your new EchoTime account</p>
        </div>

        <?php display_message(); ?>

        <form method="post" style="width: 100%;">
            <div class="form-group">
                <input class="input-field" type="text" name="name" placeholder="Full name" required>
            </div>
            <div class="form-group">
                <input class="input-field" type="email" name="email" placeholder="Email address" required>
            </div>
            <div class="form-group">
                <input class="input-field" type="password" name="password" placeholder="Password" required>
            </div>
            <button class="btn-submit" type="submit">Register</button>
        </form>

        <p class="toggle-link">
            Already have an account? <a href="login.php">Login here</a>
        </p>
    </div>
</div>

</body>
</html>