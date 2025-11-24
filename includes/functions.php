<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* ========== Utility & Helper Functions ========== */

if (!function_exists('esc')) {
    /**
     * Escapes special characters in a string for HTML to prevent XSS attacks.
     * @param string $s The string to escape.
     * @return string The escaped string.
     */
    function esc($s) {
        return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatPrice')) {
    /**
     * Formats a number as currency with two decimal places and thousands separators.
     * @param float $n The number to format.
     * @return string The formatted price string.
     */
    function formatPrice($n) {
        return number_format((float)$n, 2, '.', ',');
    }
}

if (!function_exists('truncate_text')) {
    /**
     * Truncates a string to a specified length and appends an ellipsis.
     * @param string $text The text to truncate.
     * @param int $length The maximum length of the string.
     * @return string The truncated string.
     */
    function truncate_text($text, $length = 100) {
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length) . '...';
        }
        return $text;
    }
}

if (!function_exists('set_message')) {
    /**
     * Sets a session message to be displayed on the next page load.
     * @param string $message The message content.
     * @param string $type The message type (e.g., 'success', 'error', 'info').
     */
    function set_message($message, $type = 'success') {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
}

if (!function_exists('display_message')) {
    /**
     * Displays a session message and clears it from the session.
     */
    function display_message() {
        if (isset($_SESSION['message']) && !empty($_SESSION['message'])) {
            $message = esc($_SESSION['message']);
            $type = esc($_SESSION['message_type']);
            echo '<div class="alert alert-' . $type . '">' . $message . '</div>';
            
            // Clear the session variables after displaying
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
    }
}

/* ========== Auth Functions ========== */

if (!function_exists('is_logged_in')) {
    /**
     * Checks if a user is logged in.
     * @return bool True if a user session exists, false otherwise.
     */
    function is_logged_in() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('is_admin')) {
    /**
     * Checks if the logged-in user has 'admin' role.
     * @return bool True if the user is an admin, false otherwise.
     */
    function is_admin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
}

if (!function_exists('require_login')) {
    /**
     * Redirects to the login page if the user is not authenticated.
     */
    function require_login() {
        if (!is_logged_in()) {
            header("Location: ../pages/login.php?next=" . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
    }
}

if (!function_exists('login_user')) {
    /**
     * Authenticates a user based on email and password.
     * @param mysqli $conn The database connection.
     * @param string $email The user's email.
     * @param string $password The user's password.
     * @return bool True on successful login, false otherwise.
     */
    function login_user($conn, $email, $password) {
        $stmt = mysqli_prepare($conn, "SELECT id, name, password, role FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            return true;
        }
        return false;
    }
}

if (!function_exists('register_user')) {
    /**
     * Registers a new user.
     * @param mysqli $conn The database connection.
     * @param string $name The user's name.
     * @param string $email The user's email.
     * @param string $password The user's password.
     * @return bool|string True on success, or an error message string on failure.
     */
    function register_user($conn, $name, $email, $password) {
        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if (mysqli_fetch_assoc($res)) {
            mysqli_stmt_close($stmt);
            return "Email already registered.";
        }
        mysqli_stmt_close($stmt);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name,email,password) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, 'sss', $name, $email, $hash);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return true;
        } else {
            $err = mysqli_error($conn);
            mysqli_stmt_close($stmt);
            return "Registration failed: $err";
        }
    }
}

/* ========== Data Retrieval Functions ========== */

if (!function_exists('get_count')) {
    /**
     * Gets the total count of rows from a specified table.
     * @param mysqli $conn The database connection.
     * @param string $table The table name.
     * @return int The number of rows.
     */
    function get_count($conn, $table) {
        $stmt = mysqli_prepare($conn, "SELECT COUNT(*) as count FROM `$table`");
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $row['count'] ?? 0;
        }
        return 0;
    }
}

if (!function_exists('get_count_where')) {
    /**
     * Gets the total count of rows from a specified table based on a WHERE clause.
     * @param mysqli $conn The database connection.
     * @param string $table The table name.
     * @param string $where_clause The SQL WHERE clause.
     * @return int The number of rows matching the condition.
     */
    function get_count_where($conn, $table, $where_clause) {
        $sql = "SELECT COUNT(*) as count FROM `$table` WHERE $where_clause";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $row['count'] ?? 0;
        }
        return 0;
    }
}

if (!function_exists('get_daily_sales')) {
    /**
     * Gets today's sales total from the orders table.
     * @param mysqli $conn The database connection.
     * @param string $table The table name (e.g., 'orders').
     * @param string $date_column The column containing the creation date.
     * @return float The total sales for the current day.
     */
    function get_daily_sales($conn, $table, $date_column) {
        $today = date('Y-m-d');
        $stmt = mysqli_prepare($conn, "SELECT SUM(total_amount) as total FROM `$table` WHERE DATE($date_column) = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $today);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            mysqli_stmt_close($stmt);
            return $row['total'] ?? 0;
        }
        return 0;
    }
}

if (!function_exists('get_subcategories')) {
    /**
     * Fetches subcategories for a given parent category ID.
     * @param mysqli $conn The database connection.
     * @param int $parent_id The ID of the parent category.
     * @return array A list of subcategories.
     */
    function get_subcategories($conn, $parent_id) {
        $stmt = mysqli_prepare($conn, "SELECT id, name FROM categories WHERE parent_id = ? ORDER BY name");
        mysqli_stmt_bind_param($stmt, 'i', $parent_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        mysqli_stmt_close($stmt);
        return $rows;
    }
}

if (!function_exists('fetch_all')) {
    /**
     * Executes a raw SQL query and fetches all results as an associative array.
     * @param mysqli $conn The database connection.
     * @param string $sql The SQL query string.
     * @return array A list of rows.
     */
    function fetch_all($conn, $sql) {
        $res = mysqli_query($conn, $sql);
        $rows = [];
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
            mysqli_free_result($res);
        }
        return $rows; 
    }
}

if (!function_exists('get_product')) {
    /**
     * Fetches a single product by its ID.
     * @param mysqli $conn The database connection.
     * @param int $id The product ID.
     * @return array|null The product row or null if not found.
     */
    function get_product($conn, $id) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $product = mysqli_fetch_assoc($res); // Use $product for clarity
        mysqli_stmt_close($stmt);
        return $product;
    }
}

if (!function_exists('get_active_deal')) {
    /**
     * Fetches a single active deal from the promotions table.
     * @param mysqli $conn The database connection.
     * @return array|null The deal row or null if none is found.
     */
    function get_active_deal($conn) {
        $current_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("SELECT * FROM promotions WHERE start_date <= ? AND end_date >= ? AND status = 'active' ORDER BY end_date ASC LIMIT 1");
        $stmt->bind_param("ss", $current_time, $current_time);
        $stmt->execute();
        $result = $stmt->get_result();
        $deal = $result->fetch_assoc();
        $stmt->close();
        return $deal;
    }
}

if (!function_exists('get_deal_products')) {
    /**
     * Fetches products related to a specific deal.
     * @param mysqli $conn The database connection.
     * @param int $deal_id The ID of the promotion.
     * @return array A list of products associated with the deal.
     */
    function get_deal_products($conn, $deal_id) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE promotion_id = ? AND status = 'active' ORDER BY created_at DESC");
        $stmt->bind_param("i", $deal_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        $stmt->close();
        return $products;
    }
}

if (!function_exists('get_best_sellers')) {
    /**
     * Fetches the best-selling products.
     * @param mysqli $conn The database connection.
     * @param int $limit The number of products to return.
     * @return array A list of best-selling products.
     */
    function get_best_sellers($conn, $limit = 8) {
        $stmt = $conn->prepare("
            SELECT p.*, SUM(oi.quantity) as total_sold
            FROM products p
            JOIN order_items oi ON p.id = oi.product_id
            GROUP BY p.id
            ORDER BY total_sold DESC
            LIMIT ?
        ");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $best_sellers = [];
        while ($row = $result->fetch_assoc()) {
            $best_sellers[] = $row;
        }
        $stmt->close();
        return $best_sellers;
    }
}

if (!function_exists('get_new_arrivals')) {
    /**
     * Fetches the newest products (new arrivals).
     * @param mysqli $conn The database connection.
     * @param int $limit The number of products to return.
     * @return array A list of new products.
     */
    function get_new_arrivals($conn, $limit = 8) {
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $new_arrivals = [];
        while ($row = $result->fetch_assoc()) {
            $new_arrivals[] = $row;
        }
        $stmt->close();
        return $new_arrivals;
    }
}

if (!function_exists('get_featured_products')) {
    /**
     * Fetches featured products.
     * @param mysqli $conn The database connection.
     * @param int $limit The number of products to return.
     * @return array A list of featured products.
     */
    function get_featured_products($conn, $limit = 4) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE is_featured = 1 ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $featured_products = [];
        while ($row = $result->fetch_assoc()) {
            $featured_products[] = $row;
        }
        $stmt->close();
        return $featured_products;
    }
}

/* ========== Blog Functions ========== */

if (!function_exists('get_latest_blogs')) {
    /**
     * Fetches the latest blog posts.
     * @param mysqli $conn The database connection.
     * @param int $limit The number of blog posts to return.
     * @return array A list of blogs.
     */
    function get_latest_blogs($conn, $limit = 3) {
        $stmt = $conn->prepare("SELECT * FROM blogs ORDER BY created_at DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $blogs = [];
        while ($row = $result->fetch_assoc()) {
            $blogs[] = $row;
        }
        $stmt->close();
        return $blogs;
    }
}

/* ========== Shopping Cart Functions (Session Based) ========== */

if (!function_exists('cart_add')) {
    /**
     * Adds or updates a product in the cart.
     * @param int $product_id The ID of the product.
     * @param int $qty The quantity to add.
     */
    function cart_add($product_id, $qty = 1) {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $qty;
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
}

if (!function_exists('cart_update')) {
    /**
     * Updates the quantity of a product in the cart.
     * @param int $product_id The ID of the product.
     * @param int $qty The new quantity.
     */
    function cart_update($product_id, $qty) {
        if (!isset($_SESSION['cart'])) return;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }
}

if (!function_exists('cart_remove')) {
    /**
     * Removes a product from the cart.
     * @param int $product_id The ID of the product to remove.
     */
    function cart_remove($product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

if (!function_exists('cart_count')) {
    /**
     * Gets the total number of items in the cart.
     * @return int The total item count.
     */
    function cart_count() {
        $c = 0;
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
            return 0;
        }

        foreach ($_SESSION['cart'] as $item) {
            // Check if the item is an array and contains the 'quantity' key (robust check)
            if (is_array($item) && isset($item['quantity'])) {
                $c += $item['quantity'];
            } else {
                // Fallback for single-value quantities
                $c += (int)$item;
            }
        }
        return $c;
    }
}

if (!function_exists('cart_total_amount')) {
    /**
     * Calculates the total amount of all items in the cart.
     * @param mysqli $conn The database connection.
     * @return float The total amount.
     */
    function cart_total_amount($conn) {
        $total = 0.0;
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return $total;
        
        $ids = array_keys($_SESSION['cart']);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $types = str_repeat('i', count($ids));
        
        $sql = "SELECT id, price FROM products WHERE id IN ($placeholders)";
        $stmt = mysqli_prepare($conn, $sql);
        
        if (!empty($ids)) {
            // Dynamically bind parameters using the splat operator
            mysqli_stmt_bind_param($stmt, $types, ...$ids);
        } else {
            mysqli_stmt_close($stmt);
            return $total;
        }

        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($r = mysqli_fetch_assoc($res)) {
            $total += $r['price'] * $_SESSION['cart'][$r['id']];
        }
        mysqli_stmt_close($stmt);
        return $total;
    }
}

if (!function_exists('cart_get')) {
    // Get cart items from session
    function cart_get() {
        return $_SESSION['cart'] ?? [];
    }
}

if (!function_exists('cart_clear')) {
    // Clear all cart data
    function cart_clear() {
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']);
        }
    }
}


/* ========== Order Functions ========== */

if (!function_exists('create_order_from_cart')) {
    /**
     * Creates an order from the user's shopping cart.
     * @param mysqli $conn The database connection.
     * @param int $user_id The ID of the user placing the order.
     * @param string $shipping_address The shipping address for the order.
     * @return int|bool The new order ID on success, or false on failure.
     */
    function create_order_from_cart($conn, $user_id, $shipping_address) {
        if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) return false;
        mysqli_begin_transaction($conn);
        try {
            $total = cart_total_amount($conn);
            $stmt = mysqli_prepare($conn, "INSERT INTO orders (user_id, total_amount, status, shipping_address) VALUES (?, ?, 'pending', ?)");
            mysqli_stmt_bind_param($stmt, 'ids', $user_id, $total, $shipping_address);
            mysqli_stmt_execute($stmt);
            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);

            foreach ($_SESSION['cart'] as $product_id => $qty) {
                $product = get_product($conn, $product_id);
                if (!$product) throw new Exception("Product not found.");
                
                // Insert order item
                $stmt2 = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt2, 'iiid', $order_id, $product_id, $qty, $product['price']);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);
                
                // Update product stock
                $stmt3 = mysqli_prepare($conn, "UPDATE products SET stock = stock - ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt3, 'ii', $qty, $product_id);
                mysqli_stmt_execute($stmt3);
                mysqli_stmt_close($stmt3);
            }

            mysqli_commit($conn);
            unset($_SESSION['cart']);
            return $order_id;
        } catch (Exception $e) {
            mysqli_roll_back($conn);
            // Optionally, log the error $e->getMessage()
            return false;
        }
    }
}

if (!function_exists('get_related_products')) {
    function get_related_products($conn, $category_id, $current_product_id, $limit = 4) {
        $stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE category_id = ? AND id != ? ORDER BY RAND() LIMIT ?");
        mysqli_stmt_bind_param($stmt, 'iii', $category_id, $current_product_id, $limit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $products = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = $row;
        }
        mysqli_stmt_close($stmt);
        return $products;
    }
}

if (!function_exists('get_order_items_preview')) {
    /**
     * Fetches the first few items and the total count for an order preview.
     * Assumes 'order_items' table links to 'products' table.
     */
    function get_order_items_preview($conn, $orderId, $limit = 3) {
        
        // 1. Get preview items (limited)
        $stmt_preview = mysqli_prepare($conn, 
            "SELECT oi.product_id, p.name, p.image 
             FROM order_items oi
             JOIN products p ON oi.product_id = p.id
             WHERE oi.order_id = ?
             LIMIT ?"
        );
        mysqli_stmt_bind_param($stmt_preview, 'ii', $orderId, $limit);
        mysqli_stmt_execute($stmt_preview);
        $result_preview = mysqli_stmt_get_result($stmt_preview);
        $preview_items = mysqli_fetch_all($result_preview, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt_preview);
        
        // 2. Get total item count (total quantity of products)
        $stmt_count = mysqli_prepare($conn, 
            "SELECT SUM(quantity) as total_items FROM order_items WHERE order_id = ?"
        );
        mysqli_stmt_bind_param($stmt_count, 'i', $orderId);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $total_count = mysqli_fetch_assoc($result_count)['total_items'] ?? 0;
        mysqli_stmt_close($stmt_count);
        
        // Combine for a single return value if needed, but returning just the preview is fine for the front-end rendering logic
        return $preview_items;
    }
}

if (!function_exists('get_all_categories')) {
    function get_all_categories($conn) {
        $result = mysqli_query($conn, "SELECT * FROM categories ORDER BY parent_id, name");
        if ($result === false) {
            return [];
        }
        $categories = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        return $categories;
    }
}

if (!function_exists('get_category_name')) {
    function get_category_name($con, $category_id) {
        if (empty($category_id)) {
            return 'All Products';
        }

        $query = "SELECT name FROM categories WHERE id = ?";
        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['name'];
        } else {
            return 'Unknown Category';
        }
    }
}

/* ========== Product Image Functions ========== */

if (!function_exists('get_product_images')) {
    /**
     * Fetches additional images for a specific product.
     * @param mysqli $conn The database connection.
     * @param int $productId The ID of the product.
     * @return array Array of image rows.
     */
    function get_product_images($conn, $productId) {
        $stmt = mysqli_prepare($conn, "SELECT image_path FROM product_images WHERE product_id = ? ORDER BY created_at ASC");
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $images = mysqli_fetch_all($result, MYSQLI_ASSOC);
        mysqli_stmt_close($stmt);
        return $images;
    }
}

if (!function_exists('get_product_additional_images')) {
    /**
     * Fetches all additional images for a product.
     * NOTE: This function is required by the AJAX call in the provided JS.
     * @param mysqli $conn The database connection.
     * @param int $productId The ID of the product.
     * @return array A list of additional image records (id, product_id, image_path).
     */
    function get_product_additional_images($conn, $productId) {
        $stmt = mysqli_prepare($conn, "SELECT id, image_path FROM product_images WHERE product_id = ? ORDER BY id ASC");
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) {
            $rows[] = $r;
        }
        mysqli_stmt_close($stmt);
        return $rows;
    }
}

if (!function_exists('delete_additional_images_files_and_db')) {
    /**
     * Deletes all additional image files and their database records for a given product.
     * @param mysqli $conn The database connection.
     * @param int $productId The ID of the product.
     */
    function delete_additional_images_files_and_db($conn, $productId) {
        // NOTE: __DIR__ relative to includes/functions.php should be correct for admin/products.php
        $upload_dir = __DIR__ . '/../uploads/'; 

        // 1. Fetch existing image paths
        $stmt = mysqli_prepare($conn, "SELECT image_path FROM product_images WHERE product_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $images_to_delete = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $images_to_delete[] = $row['image_path'];
        }
        mysqli_stmt_close($stmt);

        // 2. Delete files
        foreach ($images_to_delete as $image_path) {
            if (!empty($image_path)) {
                // Use @ to suppress file-not-found warnings if a record exists but the file doesn't
                @unlink($upload_dir . $image_path); 
            }
        }

        // 3. Delete database records
        $stmt = mysqli_prepare($conn, "DELETE FROM product_images WHERE product_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $productId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}


if (!function_exists('upload_additional_images')) {
    /**
     * Handles the upload of additional product images. It deletes old images first 
     * if new files are provided, then saves the new ones.
     * @param mysqli $conn The database connection.
     * @param int $productId The ID of the product.
     * @param array $files The $_FILES['images'] array.
     * @param int $max_uploads The maximum number of images allowed.
     */
    function upload_additional_images($conn, $productId, $files = [], $max_uploads = 5) {
        // Define upload directory
        $upload_dir = __DIR__ . '/../uploads/';

        // Ensure upload directory exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // 1. Delete old images from DB and filesystem (using the dedicated function)
        delete_additional_images_files_and_db($conn, $productId);
        
        // 2. Handle new uploads
        if (isset($files['tmp_name']) && is_array($files['tmp_name'])) {
            $upload_count = 0;
            foreach ($files['tmp_name'] as $key => $tmp_name) {
                // Only process files that were actually uploaded and are not empty slots
                if ($files['error'][$key] === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                    if ($upload_count >= $max_uploads) break;

                    // Secure and unique filename
                    $filename = time() . '_' . $key . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($files['name'][$key]));
                    $destination = $upload_dir . $filename;

                    // Validate image type (basic check)
                    $check = @getimagesize($tmp_name); // Suppress warning if not an image
                    if ($check !== false) {
                        if (move_uploaded_file($tmp_name, $destination)) {
                            // Save filename to DB
                            $stmt = mysqli_prepare($conn, "INSERT INTO product_images (product_id, image_path) VALUES (?, ?)");
                            mysqli_stmt_bind_param($stmt, 'is', $productId, $filename);
                            mysqli_stmt_execute($stmt);
                            mysqli_stmt_close($stmt);
                            $upload_count++;
                        }
                    }
                }
            }
        }
    }
}

if (!function_exists('get_main_categories')) {
    /**
     * Fetches main categories (those with parent_id IS NULL or 0).
     * @param mysqli $conn The database connection.
     * @return array A list of main categories.
     */
    function get_main_categories($conn) {
        // Corrected SQL to check for NULL or 0 as parent ID
        $stmt = mysqli_prepare($conn, "SELECT id, name FROM categories WHERE parent_id IS NULL OR parent_id = 0 ORDER BY name");
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $rows = [];
        while ($r = mysqli_fetch_assoc($res)) $rows[] = $r;
        mysqli_stmt_close($stmt);
        return $rows;
    }
}


if (!function_exists('get_products')) {
    /**
     * Fetches products with various optional filters (Admin/Catalog view).
     * This version is robust for all filtering parameters.
     * @param mysqli $conn The database connection.
     * @param int|null $category_id Optional category ID to filter by.
     * @param string|null $q Optional search term to filter by (keyword search).
     * @param float|null $min_price Minimum price filter.
     * @param float|null $max_price Maximum price filter.
     * @param string|null $product_type Secondary keyword filter (e.g., tags).
     * @return array A list of products.
     */
    function get_products($conn, $category_id = null, $q = null, $min_price = null, $max_price = null, $product_type = null) {
        $sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        $types = "";

        // Category Filter
        if (!empty($category_id)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }
        
        // Product Type Filter (using the more general term 'product_type' for secondary search)
        if (!empty($product_type)) {
             $sql .= " AND (LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(p.tags) LIKE ?)";
             $likeType = "%" . strtolower($product_type) . "%";
             $params[] = $likeType;
             $params[] = $likeType;
             $params[] = $likeType;
             $types .= "sss";
        }

        // Keyword Search (Primary search term)
        if (!empty($q)) {
            $sql .= " AND (LOWER(p.name) LIKE ? OR LOWER(p.description) LIKE ? OR LOWER(p.tags) LIKE ?)";
            $likeQ = "%" . strtolower($q) . "%";
            $params[] = $likeQ;
            $params[] = $likeQ;
            $params[] = $likeQ;
            $types .= "sss";
        }

        // Price Filters
        if (!empty($min_price)) {
            $sql .= " AND p.price >= ?";
            $params[] = $min_price;
            $types .= "d";
        }
        if (!empty($max_price)) {
            $sql .= " AND p.price <= ?";
            $params[] = $max_price;
            $types .= "d";
        }

        // Default Admin order
        $sql .= " ORDER BY p.name ASC";

        $stmt = $conn->prepare($sql);
        
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }
}
?>