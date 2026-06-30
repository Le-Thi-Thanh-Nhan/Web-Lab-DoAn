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

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $data = [
                        'subcategory_id' => $_POST['subcategory_id'],
                        'name' => $_POST['name'],
                        'description' => $_POST['description'],
                        'price' => $_POST['price'],
                        'stock_quantity' => $_POST['stock_quantity']
                    ];

                    // Handle image upload
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $targetDir = "../images/products/";
                        $fileName = uniqid() . "." . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $targetFile = $targetDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                            $data['image_url'] = '../images/products/' . $fileName;
                        }
                    }

                    if (handleCRUD('CREATE', 'products', $data)) {
                        $message = "Sản phẩm đã được thêm thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'update':
                    $data = [
                        'subcategory_id' => $_POST['subcategory_id'],
                        'name' => $_POST['name'],
                        'description' => $_POST['description'],
                        'price' => $_POST['price'],
                        'stock_quantity' => $_POST['stock_quantity']
                    ];

                    // Handle image upload for update
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                        $targetDir = "../images/products/";
                        $fileName = uniqid() . "." . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                        $targetFile = $targetDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                            $data['image_url'] = '../images/products/' . $fileName;
                            
                            // Delete old image
                            $oldImage = $_POST['old_image'];
                            if ($oldImage && file_exists($oldImage)) {
                                unlink($oldImage);
                            }
                        }
                    }

                    if (handleCRUD('UPDATE', 'products', $data, "product_id = " . $_POST['product_id'])) {
                        $message = "Sản phẩm đã được cập nhật thành công!";
                        $messageType = "success";
                    }
                    break;

                case 'delete':
                    $productId = $_POST['product_id'];
                    
                    // Get image path before deleting
                    $conn = getDBConnection();
                    $stmt = $conn->prepare("SELECT image_url FROM products WHERE product_id = ?");
                    $stmt->bind_param("i", $productId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $product = $result->fetch_assoc();
                    
                    if (handleCRUD('DELETE', 'products', null, "product_id = $productId")) {
                        // Delete product image
                        if ($product['image_url'] && file_exists($product['image_url'])) {
                            unlink($product['image_url']);
                        }
                        $message = "Sản phẩm đã được xóa thành công!";
                        $messageType = "success";
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Lỗi: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Get total number of products
$conn = getDBConnection();
$total_query = "SELECT COUNT(*) as total FROM products";
$total_result = $conn->query($total_query);
$total_products = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $items_per_page);

// Get paginated products with category information
$query = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
          FROM products p 
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
          JOIN categories c ON s.category_id = c.category_id 
          ORDER BY p.created_at DESC
          LIMIT $offset, $items_per_page";
$products = $conn->query($query);

// Get all categories and subcategories for the form
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$subcategories = $conn->query("SELECT * FROM subcategories ORDER BY name");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Sản Phẩm - Admin Panel</title>
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

        /* Enhanced Form Styling */
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
            margin-bottom: 8px;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            background-color: #fff;
            border: 1px solid #ced4da;
            transition: border-color 0.2s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
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

        /* Table Enhancements */
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        table {
            margin: 0;
        }

        table th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: 500;
        }

        table td {
            vertical-align: middle;
        }

        /* Product Image */
        table img {
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quản Lý Sản Phẩm</h2>
                    <button class="btn btn-primary" onclick="showAddForm()">
                        <i class="fas fa-plus"></i> Thêm Sản Phẩm
                    </button>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Hình Ảnh</th>
                                <th>Tên Sản Phẩm</th>
                                <th>Danh Mục</th>
                                <th>Giá</th>
                                <th>Số Lượng</th>
                                <th>Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $product['product_id']; ?></td>
                                    <td>
                                        <img src="<?php echo $product['image_url']; ?>" alt="<?php echo $product['name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td><?php echo $product['name']; ?></td>
                                    <td><?php echo $product['category_name'] . ' > ' . $product['subcategory_name']; ?></td>
                                    <td><?php echo number_format($product['price'], 0, ',', '.') . 'đ'; ?></td>
                                    <td><?php echo $product['stock_quantity']; ?></td>
                                    <td>
                                        <button class="btn btn-warning" onclick="showEditForm(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger" onclick="confirmDelete(<?php echo $product['product_id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Add pagination after the table -->
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

    <!-- Product Modal -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle">Thêm Sản Phẩm Mới</h2>
            <form id="productForm" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="product_id" id="productId">
                <input type="hidden" name="old_image" id="oldImage">

                <div class="form-group">
                    <label for="subcategory_id">Danh Mục:</label>
                    <select name="subcategory_id" id="subcategory_id" required>
                        <?php 
                        $subcategories->data_seek(0);
                        while ($subcategory = $subcategories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $subcategory['subcategory_id']; ?>">
                                <?php echo $subcategory['name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Tên Sản Phẩm:</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="description">Mô Tả:</label>
                    <textarea id="description" name="description"></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Giá:</label>
                    <input type="number" id="price" name="price" min="0" step="1000" required>
                </div>

                <div class="form-group">
                    <label for="stock_quantity">Số Lượng Tồn:</label>
                    <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
                </div>

                <div class="form-group">
                    <label for="image">Hình Ảnh:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal handling
        const modal = document.getElementById('productModal');
        const closeBtn = document.getElementsByClassName('close')[0];
        const form = document.getElementById('productForm');

        function showAddForm() {
            document.getElementById('modalTitle').textContent = 'Thêm Sản Phẩm Mới';
            document.getElementById('formAction').value = 'add';
            form.reset();
            modal.style.display = 'block';
        }

        function showEditForm(product) {
            document.getElementById('modalTitle').textContent = 'Cập Nhật Sản Phẩm';
            document.getElementById('formAction').value = 'update';
            document.getElementById('productId').value = product.product_id;
            document.getElementById('subcategory_id').value = product.subcategory_id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('stock_quantity').value = product.stock_quantity;
            document.getElementById('oldImage').value = product.image_url;
            modal.style.display = 'block';
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        function confirmDelete(productId) {
            if (confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal with close button
        closeBtn.onclick = closeModal;

        // Handle form submission
        form.onsubmit = function() {
            return true; // Add any validation if needed
        }
    </script>
</body>
</html> 