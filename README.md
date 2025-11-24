Echotime is a responsive e-commerce website built with HTML, CSS, JavaScript, PHP, and MySQL.
It includes a full user system, order tracking, profile management, and admin features — ready to run locally or deploy to a PHP hosting environment.

📌 One-line Summary

A responsive e-commerce site (HTML/CSS/JS/PHP/MySQL) with user registration/login, order tracking, user profile, and admin management.

🚀 Features

User registration & login (secure sessions)

Password hashing and validation

User profile page + update profile functionality

Product listing & product detail pages

Add to cart + checkout flow

Order placement and order tracking system (status updates)

Admin dashboard (products & orders)

Input validation & prepared statements

Responsive design (desktop + mobile)

Search & basic filters

Future improvements possible (payments, emails, coupons, etc.)

🛠️ Tech Stack

Frontend: HTML5, CSS3, JavaScript
Backend: PHP
Database: MySQL / MariaDB
Local Environment: XAMPP / WAMP / LAMP


⚙️ Installation (Local Setup)
1. Clone the repo
git clone https://github.com/<Muntazir-mehdi110>/echotime.git
cd echotime

2. Move project to server root

XAMPP: C:/xampp/htdocs/echotime

WAMP: C:/wamp64/www/echotime

Linux: /var/www/html/echotime

3. Create the database

Create a database in phpMyAdmin:

echotime_db

4. Import SQL file

Import:

database/echotime.sql

5. Configure DB connection

Edit config.php:

$db_host = 'localhost';
$db_name = 'echotime_db';
$db_user = 'root';
$db_pass = '';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

6. Run the project

Open in browser:

http://localhost/echotime/

🔐 Setup Notes & Security

Use session_start() on all protected pages

Always use prepared statements

Store passwords using password_hash()

Do NOT upload real DB credentials — use a .env file

Add .env to .gitignore

Sanitize and validate all user inputs

📤 How to Push to GitHub (Quick Guide)
git init
git add .
git commit -m "Initial commit — Echotime e-commerce"
git branch -M main
git remote add origin https://github.com/<your-username>/echotime.git
git push -u origin main

📁 Project Structure
/echotime
│
├─ /assets
│   ├─ /css
│   ├─ /js
│   └─ /images
├─ /includes
│   ├─ header.php
│   ├─ footer.php
│   └─ config.php
├─ /admin
│   └─ admin-dashboard.php
├─ /auth
│   ├─ login.php
│   ├─ register.php
│   └─ logout.php
├─ /cart
│   └─ checkout.php
├─ /database
│   └─ echotime.sql
├─ index.php
└─ README.md

🌍 Deployment Options

Shared Hosting: Upload all files + import SQL

Cloud VPS (DigitalOcean, Vultr, Linode): Use LAMP/LEMP

GitHub Pages: ❌ Not supported (PHP required)

📌 To Do / Future Improvements

Payment gateway (JazzCash / EasyPaisa / Stripe)

Email notifications

Product reviews & ratings

Role-based admin permissions

Secure image upload functionality

Unit tests + security improvements

📄 License

Add MIT License or your preferred license.

👤 Contact

Author: Muntazir Mehdi
Email: muntaazirmehdi3@gmail.com
