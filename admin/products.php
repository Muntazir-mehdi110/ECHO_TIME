<?php

session_start();
// NOTE: Assuming 'db.php' provides $conn and 'functions.php' contains all helper functions.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check for admin login and redirect if unauthorized
if (!is_logged_in() || !is_admin()) { 
    header('Location: login.php');
    exit;
} 

// Handle Product Actions (Add, Edit, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;

    if ($action === 'add' || $action === 'edit') {
       $name = trim($_POST['name']);
$price = (float)$_POST['price']; 
$discount = (float)$_POST['discount'];
$stock = (int)$_POST['stock'];
$category_id = (int)$_POST['category_id'];
$desc = trim($_POST['description']);
$delivery_time = trim($_POST['delivery_time']);
$imgName = $_POST['current_image'] ?? null;
$sku = trim($_POST['sku']);



        // Handle main image upload
        if (!empty($_FILES['image']['name'])) {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $tmp = $_FILES['image']['tmp_name'];
            // Sanitize filename
            $imgName = time() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($_FILES['image']['name']));
            
            if (move_uploaded_file($tmp, $upload_dir . $imgName)) {
                // If image is uploaded successfully, delete old main image file if in edit mode
                if ($action === 'edit' && !empty($_POST['current_image']) && $_POST['current_image'] !== 'product-placeholder.jpg') {
                    @unlink($upload_dir . $_POST['current_image']);
                }
            } else {
                set_message("Error uploading main image.", 'danger');
                header('Location: products.php');
                exit;
            }
        }
        
        if ($action === 'add') {
            $stmt = mysqli_prepare($conn, "INSERT INTO products (category_id, name, sku, description, price, discount, stock, delivery_time, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'isssddiss', $category_id, $name, $sku, $desc, $price, $discount, $stock, $delivery_time, $imgName);
            mysqli_stmt_execute($stmt);
            $productId = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            set_message("Product '$name' added successfully!", 'success');
        } else if ($action === 'edit' && $productId) {
             $stmt = mysqli_prepare($conn, "INSERT INTO products (category_id, name, sku, description, price, discount, stock, delivery_time, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
             mysqli_stmt_bind_param($stmt, 'isssddiss', $category_id, $name, $sku, $desc, $price, $discount, $stock, $delivery_time, $imgName);

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            set_message("Product '$name' updated successfully!", 'success');
        }

        // Handle additional image uploads
        if ($productId && !empty(array_filter($_FILES['images']['name']))) {
            // This function (in functions.php) must handle deleting existing additional images 
            // and saving the new ones if files are provided.
            upload_additional_images($conn, $productId, $_FILES['images']); 
        }
        
    } else if ($action === 'delete' && $productId) {
        $product = get_product($conn, $productId);
        
        if ($product) {
            // 1. Delete additional images files and DB records
            delete_additional_images_files_and_db($conn, $productId); 
            
            // 2. Delete main image file 
            $upload_dir = __DIR__ . '/../uploads/';
            if (!empty($product['image']) && $product['image'] !== 'product-placeholder.jpg') {
                @unlink($upload_dir . $product['image']);
            }

            // 3. Delete product DB record
            $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
            mysqli_stmt_bind_param($stmt, 'i', $productId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            set_message("Product '" . $product['name'] . "' deleted successfully!", 'success');
        }
    }

    header('Location: products.php');
    exit;
}

// Fetch Data for the Page
$products = get_products($conn);
$cats = get_main_categories($conn); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin.css"> 
    <style>
        /* CSS for the modal scroll and image previews */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1000; /* High z-index to ensure visibility */
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.4); 
            display: flex; /* Use flex to easily center modal-content */
            justify-content: center; 
            align-items: center; 
        }
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border: 1px solid #888;
            width: 90%; 
            max-width: 600px;
            border-radius: 10px;
            max-height: 90vh; 
            overflow-y: auto; 
            /* Hide the modal by default, JS will show it */
            display: none; 
        }
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close-btn:hover,
        .close-btn:focus {
            color: #000;
            text-decoration: none;
            cursor: pointer;
        }
        .image-gallery-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-gallery-preview img {
            max-width: 80px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        /* Basic form styles for usability */
        .form-group { margin-bottom: 15px; }
        .input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-primary { background-color: #0d52a0; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; // NOTE: Assuming this file exists ?>

    <div class="admin-main-content">
        <div class="admin-topbar">
            <h2>Manage Products</h2>
            <button class="btn btn-primary" id="addProductBtn"><i class="fas fa-plus-circle"></i> Add New Product</button>
        </div>

        <?php display_message(); ?>

        <div class="products-section">
            <div class="product-list-container">
                <div class="list-header">
                    <h3>Product Catalog</h3>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="productSearch" placeholder="Search products...">
                    </div>
                </div>
                <div class="products-grid">
                    <?php if (empty($products)): ?>
                        <p style="text-align:center;font-style:italic;grid-column: 1 / -1;">No products found.</p>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <div class="product-card" data-name="<?= esc(strtolower($p['name'])) ?>">
                                <div class="product-card-img-container">
                                    <img src="../uploads/<?= esc($p['image'] ?: 'product-placeholder.jpg') ?>" alt="<?= esc($p['name']) ?>" class="product-card-img">
                                </div>
                                <div class="product-actions">
                                    <button class="edit-btn" 
                                        data-id="<?= esc($p['id']) ?>" 
                                        data-name="<?= esc($p['name']) ?>" 
                                        data-price="<?= esc($p['price']) ?>" 
                                        data-discount="<?= esc($p['discount']) ?>" 
                                        data-stock="<?= esc($p['stock']) ?>" 
                                        data-category="<?= esc($p['category_id']) ?>" 
                                        data-desc="<?= esc($p['description']) ?>" 
                                        data-img="<?= esc($p['image'] ?: 'product-placeholder.jpg') ?>">
                                        
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product? This action cannot be undone.');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="product_id" value="<?= esc($p['id']) ?>">
                                        <button type="submit" class="delete-btn">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="product-card-body">
                                    <h5><?= esc(truncate_text($p['name'], 20)) ?></h5>
                                    <p class="price">₹<?= formatPrice($p['price']) ?></p>
                                    <p class="stock">Stock: <?= esc($p['stock']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="productModal" class="modal">
    <div class="modal-content" style="display:none;">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Add New Product</h3>
        <form method="post" enctype="multipart/form-data" id="productForm">
            <input type="hidden" name="action" id="formAction" value="add">
            <input type="hidden" name="product_id" id="productId">
            <input type="hidden" name="current_image" id="currentImage">
            
            <div class="form-group">
                <label for="name">Product Name</label>
                <input class="input" name="name" id="name" placeholder="Enter product name" required>
            </div>
            <div class="form-group">
                <label for="category_id">Category</label>
                <select class="input" name="category_id" id="category_id" required>
                    <option value="">Select Category</option>
                    <?php foreach ($cats as $mc): ?>
                        <optgroup label="<?= esc($mc['name']) ?>">
                            <option value="<?= esc($mc['id']) ?>"><?= esc($mc['name']) ?></option>
                            <?php 
                            // NOTE: get_subcategories needs to handle the category structure correctly
                            foreach (get_subcategories($conn, $mc['id']) as $sc): ?>
                                <option value="<?= esc($sc['id']) ?>">&mdash; <?= esc($sc['name']) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="price">Price (₹)</label>
                <input class="input" name="price" id="price" type="number" step="0.01" min="0.01" placeholder="Enter price" required>
            </div>
            <div class="form-group">
                <label for="discount">Discount (%)</label>
                <input class="input" name="discount" id="discount" type="number" step="0.01" value="0" min="0" max="100">
            </div>
            <div class="form-group">
                <label for="stock">Stock Quantity</label>
                <input class="input" name="stock" id="stock" type="number" min="0" placeholder="Enter stock quantity" required>
            </div>
            <div class="form-group">
               <label for="delivery_time">Delivery Time</label>
               <input class="input" name="delivery_time" id="delivery_time" type="text" placeholder="e.g. 3–5 business days" required>
           </div>
           <div class="form-group">
               <label for="sku">SKU Number</label>
               <input class="input" name="sku" id="sku" placeholder="Enter unique SKU code" required>
           </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="input" name="description" id="description" rows="3" placeholder="Enter product description"></textarea>
            </div>
            
            <div class="form-group">
                <label for="image">Main Product Image</label>
                <input type="file" id="image" name="image" accept="image/*">
                <div id="imagePreview" style="margin-top: 10px;"></div>
            </div>
            
            <hr style="margin: 20px 0;">
            <h4>Additional Product Images (Total 5 slots)</h4>
            <p style="font-size:0.9em; color:#666;">**Warning**: Uploading new images below will **replace** ALL existing additional images.</p>
            
            <div id="additionalImagesContainer" class="form-group" style="display: none;">
                <label>Existing Additional Images (Currently in DB)</label>
                <div id="existingAdditionalImages" class="image-gallery-preview">
                </div>
            </div>
            
            <div class="form-group">
                <label for="image_1">Image Slot 1</label>
                <input type="file" id="image_1" name="images[]" class="additional-image-input" accept="image/*" data-preview-id="preview_1">
                <div id="preview_1" class="image-gallery-preview"></div>
            </div>
            
            <div class="form-group">
                <label for="image_2">Image Slot 2 (Optional)</label>
                <input type="file" id="image_2" name="images[]" class="additional-image-input" accept="image/*" data-preview-id="preview_2">
                <div id="preview_2" class="image-gallery-preview"></div>
            </div>
            
            <div class="form-group">
                <label for="image_3">Image Slot 3 (Optional)</label>
                <input type="file" id="image_3" name="images[]" class="additional-image-input" accept="image/*" data-preview-id="preview_3">
                <div id="preview_3" class="image-gallery-preview"></div>
            </div>
            
            <div class="form-group">
                <label for="image_4">Image Slot 4 (Optional)</label>
                <input type="file" id="image_4" name="images[]" class="additional-image-input" accept="image/*" data-preview-id="preview_4">
                <div id="preview_4" class="image-gallery-preview"></div>
            </div>
            
            <div class="form-group">
                <label for="image_5">Image Slot 5 (Optional)</label>
                <input type="file" id="image_5" name="images[]" class="additional-image-input" accept="image/*" data-preview-id="preview_5">
                <div id="preview_5" class="image-gallery-preview"></div>
            </div>
            
            <button class="btn btn-primary" type="submit" id="submitBtn">Add Product</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('productModal');
        const modalContent = modal.querySelector('.modal-content');
        const addProductBtn = document.getElementById('addProductBtn');
        const closeBtn = document.querySelector('.close-btn');
        const form = document.getElementById('productForm');
        const formAction = document.getElementById('formAction');
        const modalTitle = document.getElementById('modalTitle');
        const submitBtn = document.getElementById('submitBtn');
        const productIdInput = document.getElementById('productId');
        const currentImageInput = document.getElementById('currentImage');
        const mainImageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const existingAdditionalImages = document.getElementById('existingAdditionalImages');
        const additionalImagesContainer = document.getElementById('additionalImagesContainer');
        const additionalImagesInputs = document.querySelectorAll('.additional-image-input');
        const editButtons = document.querySelectorAll('.edit-btn');
        const productSearch = document.getElementById('productSearch');
        const productCards = document.querySelectorAll('.product-card');

        // Function to reset all image inputs and previews
        const resetImageInputs = (actionValue) => {
            // Main image reset
            mainImageInput.value = ''; 
            imagePreview.innerHTML = '';
            
            // Additional images reset
            additionalImagesInputs.forEach(input => {
                input.value = ''; // Clear file input
                document.getElementById(input.dataset.previewId).innerHTML = ''; // Clear preview
            });

            // Set required on main image for 'add' mode
            if (actionValue === 'add') {
                mainImageInput.setAttribute('required', 'required');
            } else {
                mainImageInput.removeAttribute('required'); // Not required if editing (can keep old image)
            }
        };
        
        const resetForm = (action = 'add') => {
            form.reset();
            formAction.value = action;
            modalTitle.textContent = action === 'add' ? 'Add New Product' : 'Edit Product';
            submitBtn.textContent = action === 'add' ? 'Add Product' : 'Save Changes';
            productIdInput.value = '';
            currentImageInput.value = '';
            existingAdditionalImages.innerHTML = '';
            additionalImagesContainer.style.display = 'none';
            resetImageInputs(action);
        };

        const openModal = () => {
            modal.style.display = 'flex';
            modalContent.style.display = 'block';
        };

        const closeModal = () => {
            modal.style.display = 'none';
            modalContent.style.display = 'none';
        };

        // Open modal for adding a new product
        addProductBtn.addEventListener('click', () => {
            resetForm('add');
            openModal();
        });

        // Open modal for editing a product
        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                resetForm('edit');
                
                const { id, name, price, stock, category, desc, img, discount, delivery_time } = button.dataset;
                
                productIdInput.value = id;
                currentImageInput.value = img;
                
                document.getElementById('name').value = name;
                document.getElementById('price').value = price;
                document.getElementById('stock').value = stock;
                document.getElementById('category_id').value = category;
                document.getElementById('description').value = desc;
                document.getElementById('discount').value = discount;
                document.getElementById('delivery_time').value = delivery_time;

                
                imagePreview.innerHTML = `<img src="../uploads/${img}" alt="Current Product Image" style="max-width: 100px;">`;
                
                // Make sure the image is NOT required for edit mode
                mainImageInput.removeAttribute('required');

                additionalImagesContainer.style.display = 'block';

                // AJAX call to fetch additional images
                // NOTE: fetch_product_images.php must be created and placed in the admin folder
                fetch(`fetch_product_images.php?id=${id}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok.');
                        return response.json();
                    })
                    .then(data => {
                        existingAdditionalImages.innerHTML = ''; 
                        if (data && data.images && data.images.length > 0) {
                            data.images.forEach(image => {
                                const imgElement = document.createElement('img');
                                imgElement.src = `../uploads/${image.image_path}`;
                                imgElement.alt = 'Additional Image';
                                imgElement.dataset.imageId = image.id; 
                                existingAdditionalImages.appendChild(imgElement);
                            });
                        } else {
                            existingAdditionalImages.innerHTML = '<p style="font-style:italic;color:#888;">No additional images uploaded.</p>';
                        }
                    })
                    .catch(error => console.error('Error fetching images:', error));
                
                openModal();
            });
        });
        
        // Live preview for the main image
        mainImageInput.addEventListener('change', (e) => {
            imagePreview.innerHTML = ''; 
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const imgElement = document.createElement('img');
                    imgElement.src = event.target.result;
                    imgElement.alt = 'New Main Image Preview';
                    imgElement.style.maxWidth = '100px';
                    imgElement.style.marginTop = '5px';
                    imagePreview.appendChild(imgElement);
                };
                reader.readAsDataURL(file);
            }
        });

        // Live preview for newly selected additional images
        additionalImagesInputs.forEach(input => {
            input.addEventListener('change', (e) => {
                const previewContainer = document.getElementById(e.target.dataset.previewId);
                previewContainer.innerHTML = ''; 
                const file = e.target.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = (event) => {
                        const imgElement = document.createElement('img');
                        imgElement.src = event.target.result;
                        imgElement.alt = 'New Image Preview';
                        previewContainer.appendChild(imgElement);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });


        // Close modal
        closeBtn.addEventListener('click', closeModal);

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                closeModal();
            }
        });

        // Live search/filter functionality
        productSearch.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            productCards.forEach(card => {
                const productName = card.getAttribute('data-name');
                if (productName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
</script>

</body>
</html>