<?php
session_start();
require_once 'includes/db_helper.php';

// Initialize message variables
$message = '';
$messageType = '';

// Check admin login
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit;
}

// Initialize database connection
$conn = getDBConnection();

// Pagination settings
$items_per_page = 9; // 3x3 grid
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $data = [
                        'name' => $_POST['name'],
                        'description' => $_POST['description']
                    ];

                    if (handleCRUD('CREATE', 'categories', $data)) {
                        $message = "Danh mục đã được thêm thành công!";
                        $messageType = "success";
            }
                    break;

                case 'update':
                    $data = [
                        'name' => $_POST['name'],
                        'description' => $_POST['description']
                    ];

                    if (handleCRUD('UPDATE', 'categories', $data, "category_id = " . $_POST['category_id'])) {
                        $message = "Danh mục đã được cập nhật thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'delete':
                    $categoryId = $_POST['category_id'];
                    
                    // Check if category has subcategories
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM subcategories WHERE category_id = ?");
                    $stmt->bind_param("i", $categoryId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = $result->fetch_assoc()['count'];

                    if ($count > 0) {
                        $message = "Không thể xóa danh mục này vì có danh mục con!";
                        $messageType = "danger";
            } else {
                        if (handleCRUD('DELETE', 'categories', null, "category_id = $categoryId")) {
                            $message = "Danh mục đã được xóa thành công!";
                            $messageType = "success";
                        }
                    }
                    break;

                case 'add_sub':
                    $data = [
                        'category_id' => $_POST['category_id'],
                        'name' => $_POST['name'],
                        'description' => $_POST['description']
                    ];

                    if (handleCRUD('CREATE', 'subcategories', $data)) {
                        $message = "Danh mục con đã được thêm thành công!";
                        $messageType = "success";
            }
                    break;

                case 'update_sub':
                    $data = [
                        'category_id' => $_POST['category_id'],
                        'name' => $_POST['name'],
                        'description' => $_POST['description']
                    ];

                    if (handleCRUD('UPDATE', 'subcategories', $data, "subcategory_id = " . $_POST['subcategory_id'])) {
                        $message = "Danh mục con đã được cập nhật thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'delete_sub':
                    $subcategoryId = $_POST['subcategory_id'];
                    
                    // Check if subcategory has products
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE subcategory_id = ?");
                    $stmt->bind_param("i", $subcategoryId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = $result->fetch_assoc()['count'];

                    if ($count > 0) {
                        $message = "Không thể xóa danh mục con này vì có sản phẩm!";
                        $messageType = "danger";
            } else {
                        if (handleCRUD('DELETE', 'subcategories', null, "subcategory_id = $subcategoryId")) {
                            $message = "Danh mục con đã được xóa thành công!";
                            $messageType = "success";
            }
        }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get total number of categories
$total_query = "SELECT COUNT(*) as total FROM categories";
$total_result = $conn->query($total_query);
$total_categories = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_categories / $items_per_page);

// Get paginated categories with their statistics
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM subcategories s WHERE s.category_id = c.category_id) as subcategory_count,
          (SELECT COUNT(*) FROM products p JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
           WHERE s.category_id = c.category_id) as product_count,
          GROUP_CONCAT(s.name SEPARATOR ', ') as subcategories
    FROM categories c
    LEFT JOIN subcategories s ON c.category_id = s.category_id
    GROUP BY c.category_id
          ORDER BY c.name
          LIMIT $offset, $items_per_page";
$categories = $conn->query($query);

// Get all categories for the form
$all_categories = $conn->query("SELECT * FROM categories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Danh Mục - Admin Panel</title>
    <link rel="stylesheet" href="assets/css/admin-style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override default styles to ensure proper layout */
        .admin-container {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background-color: var(--bg-light);
        }

        @media (max-width: 768px) {
            .main-content {
                width: 100%;
                margin-left: 0;
                padding: 1rem;
            }
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .stat-card h3 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 1.2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card .actions {
            position: absolute;
            top: 15px;
            right: 15px;
            display: flex;
            gap: 5px;
        }

        .stat-card .btn {
            padding: 5px 10px;
            font-size: 0.9rem;
        }

        .stat-info {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .stat-info p {
            margin: 5px 0;
            color: #666;
        }

        .stat-numbers {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .stat-item {
            text-align: center;
            flex: 1;
        }

        .stat-item .number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #3498db;
        }

        .stat-item .label {
            font-size: 0.8rem;
            color: #666;
        }

        .subcategories-list {
            font-size: 0.9rem;
            color: #666;
            margin-top: 10px;
            max-height: 60px;
            overflow-y: auto;
        }

        /* Pagination Styling */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #2c3e50;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination a:hover {
            background-color: #e9ecef;
        }

        .pagination .active {
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }

        .pagination .disabled {
            color: #6c757d;
            pointer-events: none;
            background-color: #f8f9fa;
        }

        /* Modal Enhancements */
        .modal-content {
            max-width: 500px;
        }

        .form-group {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .form-group label {
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            margin-top: 5px;
        }

        .card-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-group {
            display: flex;
            gap: 10px;
        }

        .subcategories-preview {
            margin: 15px 0;
        }

        .subcategories-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 8px;
        }

        .subcategory-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9em;
        }

        .more-tag {
            background: #f5f5f5;
            color: #666;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            cursor: pointer;
        }

        .no-subcategories {
            color: #666;
            font-style: italic;
        }

        /* Subcategories Modal Styles */
        #subcategoriesContainer {
            margin: 20px 0;
        }

        .subcategory-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            background: #fff;
            transition: all 0.3s ease;
        }

        .subcategory-item:hover {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .subcategory-info {
            flex: 1;
        }

        .subcategory-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }

        .subcategory-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }

        .subcategory-actions {
            display: flex;
            gap: 5px;
        }

        .modal-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
            }

        .btn-info:hover {
            background-color: #138496;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Danh Mục</h2>
                    <div class="btn-group">
                        <button class="btn btn-primary" onclick="showAddCategoryForm()">
                        <i class="fas fa-plus"></i> Thêm Danh Mục
                    </button>
                        <button class="btn btn-success" onclick="showAddSubcategoryForm()">
                            <i class="fas fa-plus"></i> Thêm Danh Mục Con
                        </button>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="categories-grid">
                    <?php while ($category = $categories->fetch_assoc()): 
                        // Get subcategories for this category
                        $subcategories_query = "SELECT * FROM subcategories WHERE category_id = " . $category['category_id'];
                        $subcategories = $conn->query($subcategories_query);
                    ?>
                        <div class="stat-card">
                            <div class="actions">
                                <button class="btn btn-info" onclick="showSubcategoriesModal(<?php echo htmlspecialchars(json_encode([
                                    'category_id' => $category['category_id'],
                                    'category_name' => $category['name']
                                ])); ?>)">
                                    <i class="fas fa-list"></i>
                                    </button>
                                <button class="btn btn-warning" onclick="showEditCategoryForm(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                <button class="btn btn-danger" onclick="confirmDeleteCategory(<?php echo $category['category_id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            <h3>
                                <?php echo $category['name']; ?>
                            </h3>
                            <div class="stat-info">
                                <p><?php echo $category['description']; ?></p>
                            </div>
                            <div class="subcategories-preview">
                                <strong>Danh mục con:</strong> 
                            <div class="subcategories-list">
                                    <?php 
                                    if ($subcategories->num_rows > 0) {
                                        $shown = 0;
                                        while ($sub = $subcategories->fetch_assoc()) {
                                            if ($shown < 2) {
                                                echo "<span class='subcategory-tag'>" . $sub['name'] . "</span>";
                                                $shown++;
                                            }
                                        }
                                        if ($subcategories->num_rows > 2) {
                                            echo "<span class='more-tag'>+" . ($subcategories->num_rows - 2) . " more</span>";
                                        }
                                    } else {
                                        echo "<span class='no-subcategories'>Chưa có danh mục con</span>";
                                    }
                                    ?>
                                            </div>
                                        </div>
                            <div class="stat-numbers">
                                <div class="stat-item">
                                    <div class="number"><?php echo $category['subcategory_count']; ?></div>
                                    <div class="label">Danh mục con</div>
                                </div>
                                <div class="stat-item">
                                    <div class="number"><?php echo $category['product_count']; ?></div>
                                    <div class="label">Sản phẩm</div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <div class="pagination">
                    <?php if ($total_pages > 1): ?>
                        <?php if ($current_page > 1): ?>
                            <a href="?page=1" title="Trang đầu"><i class="fas fa-angle-double-left"></i></a>
                            <a href="?page=<?php echo $current_page - 1; ?>" title="Trang trước"><i class="fas fa-angle-left"></i></a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-double-left"></i></span>
                            <span class="disabled"><i class="fas fa-angle-left"></i></span>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $current_page - 2);
                        $end_page = min($total_pages, $current_page + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $current_page): ?>
                                <span class="active"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?>" title="Trang sau"><i class="fas fa-angle-right"></i></a>
                            <a href="?page=<?php echo $total_pages; ?>" title="Trang cuối"><i class="fas fa-angle-double-right"></i></a>
                        <?php else: ?>
                            <span class="disabled"><i class="fas fa-angle-right"></i></span>
                            <span class="disabled"><i class="fas fa-angle-double-right"></i></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="categoryModalTitle">Thêm Danh Mục Mới</h2>
            <form id="categoryForm" method="POST">
                <input type="hidden" name="action" id="categoryFormAction" value="add">
                <input type="hidden" name="category_id" id="categoryId">

                <div class="form-group">
                    <label for="categoryName">Tên Danh Mục:</label>
                    <input type="text" id="categoryName" name="name" required>
                </div>

                <div class="form-group">
                    <label for="categoryDescription">Mô Tả:</label>
                    <textarea id="categoryDescription" name="description"></textarea>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('categoryModal')">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subcategory Modal -->
    <div id="subcategoryModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="subcategoryModalTitle">Thêm Danh Mục Con Mới</h2>
            <form id="subcategoryForm" method="POST">
                <input type="hidden" name="action" id="subcategoryFormAction" value="add_sub">
                <input type="hidden" name="subcategory_id" id="subcategoryId">

                <div class="form-group">
                    <label for="parentCategory">Danh Mục Chính:</label>
                    <select name="category_id" id="parentCategory" required>
                        <?php 
                        $categories->data_seek(0);
                        while ($category = $categories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo $category['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="subcategoryName">Tên Danh Mục Con:</label>
                    <input type="text" id="subcategoryName" name="name" required>
                </div>

                <div class="form-group">
                    <label for="subcategoryDescription">Mô Tả:</label>
                    <textarea id="subcategoryDescription" name="description"></textarea>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('subcategoryModal')">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Subcategories Modal -->
    <div id="subcategoriesModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="subcategoriesModalTitle">Danh Mục Con</h2>
            <div id="subcategoriesContainer">
                <!-- Subcategories will be loaded here -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="showAddSubcategoryForm()">
                    <i class="fas fa-plus"></i> Thêm Danh Mục Con
                </button>
                <button class="btn btn-secondary" onclick="closeModal('subcategoriesModal')">Đóng</button>
            </div>
        </div>
    </div>

    <script>
        // Modal handling
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Category functions
        function showAddCategoryForm() {
            document.getElementById('categoryModalTitle').textContent = 'Thêm Danh Mục Mới';
            document.getElementById('categoryFormAction').value = 'add';
            document.getElementById('categoryForm').reset();
            showModal('categoryModal');
        }

        function showEditCategoryForm(category) {
            document.getElementById('categoryModalTitle').textContent = 'Cập Nhật Danh Mục';
            document.getElementById('categoryFormAction').value = 'update';
            document.getElementById('categoryId').value = category.category_id;
            document.getElementById('categoryName').value = category.name;
            document.getElementById('categoryDescription').value = category.description;
            showModal('categoryModal');
                }

        function confirmDeleteCategory(categoryId) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="category_id" value="${categoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Subcategory functions
        function showAddSubcategoryForm() {
            document.getElementById('subcategoryModalTitle').textContent = 'Thêm Danh Mục Con Mới';
            document.getElementById('subcategoryFormAction').value = 'add_sub';
            document.getElementById('subcategoryForm').reset();
            showModal('subcategoryModal');
        }

        function showEditSubcategoryForm(subcategory) {
            document.getElementById('subcategoryModalTitle').textContent = 'Cập Nhật Danh Mục Con';
            document.getElementById('subcategoryFormAction').value = 'update_sub';
            document.getElementById('subcategoryId').value = subcategory.subcategory_id;
            document.getElementById('parentCategory').value = subcategory.category_id;
            document.getElementById('subcategoryName').value = subcategory.name;
            document.getElementById('subcategoryDescription').value = subcategory.description || '';
            
            // Add event listener for form submission
            const form = document.getElementById('subcategoryForm');
            form.onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(form);

                fetch('get_subcategories.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closeModal('subcategoryModal');
                        // Refresh the subcategories list
                        showSubcategoriesModal({
                            category_id: subcategory.category_id,
                            category_name: document.getElementById('subcategoriesModalTitle').textContent.replace('Danh Mục Con của ', '')
                        });
                        // Reload the page to update the main grid
                        location.reload();
                    } else {
                        alert(data.error || 'Có lỗi xảy ra khi cập nhật');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Có lỗi xảy ra khi cập nhật');
                });
            };
            
            showModal('subcategoryModal');
        }

        function confirmDeleteSubcategory(subcategoryId) {
            if (confirm('Bạn có chắc chắn muốn xóa danh mục con này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_sub">
                    <input type="hidden" name="subcategory_id" value="${subcategoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function showSubcategoriesModal(categoryData) {
            const modal = document.getElementById('subcategoriesModal');
            const container = document.getElementById('subcategoriesContainer');
            const title = document.getElementById('subcategoriesModalTitle');
            
            title.textContent = `Danh Mục Con của ${categoryData.category_name}`;
            
            // Fetch subcategories
            fetch(`get_subcategories.php?category_id=${categoryData.category_id}`)
                .then(response => response.json())
                .then(subcategories => {
                    container.innerHTML = subcategories.length ? subcategories.map(sub => `
                        <div class="subcategory-item">
                            <div class="subcategory-info">
                                <h4>${sub.name}</h4>
                                <p>${sub.description || 'Không có mô tả'}</p>
                            </div>
                            <div class="subcategory-actions">
                                <button class="btn btn-warning" onclick="showEditSubcategoryForm(${JSON.stringify(sub)})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger" onclick="confirmDeleteSubcategory(${sub.subcategory_id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('') : '<p class="text-center">Không có danh mục con nào.</p>';
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<p class="text-center text-danger">Có lỗi xảy ra khi tải danh mục con.</p>';
                });

            modal.style.display = 'block';
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }

        // Close modals with close buttons
        document.querySelectorAll('.close').forEach(function(closeBtn) {
            closeBtn.onclick = function() {
                this.closest('.modal').style.display = 'none';
            }
        });
    </script>
</body>
</html> 