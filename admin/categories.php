<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Check for admin login
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit;
}

// Handle Category Actions via POST (Add, Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = trim($_POST['name']);
    $action = $_POST['action'];
    $response = ['success' => false, 'message' => ''];

    if ($name === '') {
        set_message('Category name cannot be empty.', 'error');
        header('Location: categories.php');
        exit;
    }

    if ($action === 'add') {
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, parent_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, 'si', $name, $parent_id);
        if (mysqli_stmt_execute($stmt)) {
            $response = ['success' => true, 'message' => "Category '$name' added successfully!"];
        } else {
            $response['message'] = "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);

    } elseif ($action === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = mysqli_prepare($conn, "UPDATE categories SET name = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'si', $name, $id);
        if (mysqli_stmt_execute($stmt)) {
            $response = ['success' => true, 'message' => "Category '$name' updated successfully!"];
        } else {
            $response['message'] = "Error: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }

    set_message($response['message'], $response['success'] ? 'success' : 'error');
    header('Location: categories.php');
    exit;
}

// Handle Delete via GET
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "DELETE FROM categories WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    set_message("Category deleted successfully!", 'success');
    header('Location: categories.php');
    exit;
}

// Fetch all categories
$all_categories = get_all_categories($conn);
$cats_by_id = [];
foreach ($all_categories as $cat) {
    $cats_by_id[$cat['id']] = $cat;
}
$main_cats = get_main_categories($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Categories - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="admin.css">
<style>
.data-table th, .data-table td { text-align: left; }
</style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="admin-main-content">
    <div class="admin-topbar">
        <h2>Manage Categories</h2>
        <button class="btn btn-primary" id="addCatBtn"><i class="fas fa-plus-circle"></i> Add New Category</button>
    </div>

    <?php display_message(); ?>

    <div class="table-container card">
        <div class="list-header">
            <h3>Category Tree</h3>
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="catSearch" placeholder="Search categories...">
            </div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Actions</th>
                </tr>
            </thead>
<tbody id="categoryTableBody">
<?php if (empty($main_cats)): ?>
    <tr><td colspan="3" style="text-align:center;font-style:italic;">No categories found.</td></tr>
<?php else: ?>
    <?php foreach ($main_cats as $mc): ?>
        <tr class="category-row" data-name="<?= esc(strtolower($mc['name'])) ?>" data-id="<?= esc($mc['id']) ?>">
            <td><strong><?= esc($mc['name']) ?></strong></td>
            <td>-</td>
            <td class="action-buttons">
                <button class="btn btn-edit edit-btn"
                        data-id="<?= esc($mc['id']) ?>"
                        data-name="<?= esc($mc['name']) ?>"
                        data-parent-id="">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a href="categories.php?action=delete&id=<?= esc($mc['id']) ?>"
                   onclick="return confirm('Delete this category?')"
                   class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete
                </a>
            </td>
        </tr>

        <?php
        $subcats = get_subcategories($conn, $mc['id']);
        if (!empty($subcats)):
            foreach ($subcats as $sc):
        ?>
        <tr class="subcategory-row"
            data-name="<?= esc(strtolower($sc['name'])) ?>"
            data-id="<?= esc($sc['id']) ?>"
            data-parent-id="<?= esc($sc['parent_id']) ?>">
            <td>&nbsp;&nbsp;&nbsp;&nbsp;↳ <?= esc($sc['name']) ?></td>
            <td>
                <?php
                if (!empty($sc['parent_id']) && isset($cats_by_id[$sc['parent_id']])) {
                    echo esc($cats_by_id[$sc['parent_id']]['name']);
                } else {
                    echo '<em style="color:red;">No parent</em>';
                }
                ?>
            </td>
            <td class="action-buttons">
                <button class="btn btn-edit edit-btn"
                        data-id="<?= esc($sc['id']) ?>"
                        data-name="<?= esc($sc['name']) ?>"
                        data-parent-id="<?= esc($sc['parent_id']) ?>">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <a href="categories.php?action=delete&id=<?= esc($sc['id']) ?>"
                   onclick="return confirm('Delete this subcategory?')"
                   class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Delete
                </a>
            </td>
        </tr>
        <?php endforeach; endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
        </table>
    </div>
</div>

<!-- Modal for Add/Edit -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h3 id="modalTitle">Add New Category</h3>
        <form method="post" id="categoryForm">
            <input type="hidden" name="action" id="formAction">
            <input type="hidden" name="id" id="categoryId">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input class="input" type="text" name="name" id="name" placeholder="e.g., Smartwatches" required>
            </div>
            <div class="form-group">
                <label for="parent_id">Parent Category</label>
                <select class="input" name="parent_id" id="parent_id">
                    <option value="">-- No Parent (Main Category) --</option>
                    <?php foreach ($main_cats as $mc): ?>
                        <option value="<?= esc($mc['id']) ?>"><?= esc($mc['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary" type="submit" id="submitBtn">Add Category</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('categoryModal');
    const addCatBtn = document.getElementById('addCatBtn');
    const closeBtn = document.querySelector('.close-btn');
    const form = document.getElementById('categoryForm');
    const modalTitle = document.getElementById('modalTitle');
    const submitBtn = document.getElementById('submitBtn');
    const categorySearch = document.getElementById('catSearch');
    const categoryRows = document.querySelectorAll('.category-row, .subcategory-row');

    addCatBtn.addEventListener('click', () => {
        modalTitle.textContent = 'Add New Category';
        submitBtn.textContent = 'Add Category';
        document.getElementById('formAction').value = 'add';
        form.reset();
        modal.style.display = 'flex';
    });

    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const { id, name, parentId } = e.currentTarget.dataset;
            modalTitle.textContent = 'Edit Category';
            submitBtn.textContent = 'Save Changes';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('categoryId').value = id;
            document.getElementById('name').value = name;
            document.getElementById('parent_id').value = parentId;
            modal.style.display = 'flex';
        });
    });

    closeBtn.addEventListener('click', () => modal.style.display = 'none');
    window.onclick = e => { if (e.target === modal) modal.style.display = 'none'; };

    categorySearch.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        categoryRows.forEach(row => {
            const categoryName = row.dataset.name;
            row.style.display = categoryName.includes(searchTerm) ? 'table-row' : 'none';
        });
    });
});
</script>

</body>
</html>
