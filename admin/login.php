<?php
// admin/login.php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login_user($conn, $_POST['email'], $_POST['password'])) {
        if (is_admin()) header('Location: index.php');
        else {
            session_unset();
            $err = "You are not authorized to access the admin panel.";
        }
    } else {
        $err = "Invalid credentials.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EchoTime</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-login.css">
</head>
<body class="animated-bg">

<div class="login-container">
    <div class="login-box">
        <div class="login-header">
            <i class="fas fa-lock login-icon"></i>
            <h2>Admin Login</h2>
        </div>
        
        <?php if (!empty($err)): ?>
            <div class="alert alert-danger">
                <?= esc($err) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input class="input" type="email" id="email" name="email" placeholder="admin@echotime.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input class="input" type="password" id="password" name="password" placeholder="Enter password" required>
            </div>
            <button class="btn btn-primary btn-full-width" type="submit">Login</button>
        </form>
    </div>
</div>

</body>
</html>

<style>
    /* General Body Styles with Animation */
body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f0f2f5;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    /* Removed the fixed background color */
}

/* New Animated Background */
.animated-bg {
    background: linear-gradient(135deg, #f5f7fa, #c3cfe2, #e8eaf6);
    background-size: 400% 400%;
    animation: gradientAnimation 15s ease infinite;
}

@keyframes gradientAnimation {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

/* Login Container */
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    height: 100%;
}

/* Login Box with subtle animation */
.login-box {
    background: #ffffff;
    padding: 3rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
    text-align: center;
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.8s ease-out forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Login Header and Icon */
.login-header {
    margin-bottom: 2rem;
}

.login-icon {
    font-size: 3rem;
    color: #4CAF50; /* A pleasant brand color */
    margin-bottom: 0.5rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
    100% {
        transform: scale(1);
    }
}

h2 {
    color: #333;
    font-weight: 700;
    margin: 0;
}

/* Form Styles */
.login-form {
    display: flex;
    flex-direction: column;
}

.form-group {
    margin-bottom: 1.5rem;
    text-align: left;
}

label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #555;
}

/* Enhanced Input Styles */
.input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 1rem;
    box-sizing: border-box;
    transition: border-color 0.3s, box-shadow 0.3s;
}

.input:focus {
    outline: none;
    border-color: #4CAF50;
    box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
}

/* Button Styles with hover effect */
.btn-primary {
    background-color: #4CAF50;
    color: #fff;
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s, box-shadow 0.3s;
}

.btn-primary:hover {
    background-color: #45a049;
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-full-width {
    width: 100%;
}

/* Alert Message Styles */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    text-align: center;
    font-weight: 600;
    animation: slideInFromTop 0.5s ease-out;
}

@keyframes slideInFromTop {
    from {
        transform: translateY(-20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>