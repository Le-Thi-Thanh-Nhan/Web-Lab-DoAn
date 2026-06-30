<?php
require_once 'sidebar.php';
require_once '../config/db_connect.php';

// Xử lý xóa đánh giá
if (isset($_POST['delete_review'])) {
    $review_id = $_POST['review_id'];
    $delete_query = "DELETE FROM product_reviews WHERE review_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $review_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Đã xóa đánh giá thành công!";
    } else {
        $_SESSION['error'] = "Không thể xóa đánh giá. Vui lòng thử lại!";
    }
    header("Location: reviews.php");
    exit();
}

// Thiết lập phân trang
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Xử lý lọc đánh giá
$where_clause = "1=1";
$params = array();
$types = "";

if (isset($_GET['rating']) && $_GET['rating'] != '') {
    $where_clause .= " AND pr.rating = ?";
    $params[] = $_GET['rating'];
    $types .= "i";
}

if (isset($_GET['product_id']) && $_GET['product_id'] != '') {
    $where_clause .= " AND pr.product_id = ?";
    $params[] = $_GET['product_id'];
    $types .= "i";
}

// Đếm tổng số đánh giá
$count_query = "SELECT COUNT(*) as total FROM product_reviews pr WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Lấy danh sách đánh giá với phân trang
$query = "SELECT pr.*, p.name as product_name, c.name as customer_name 
          FROM product_reviews pr
          JOIN products p ON pr.product_id = p.product_id
          JOIN customers c ON pr.customer_id = c.customer_id
          WHERE $where_clause
          ORDER BY pr.created_at DESC
          LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $types .= "ii";
    $params[] = $items_per_page;
    $params[] = $offset;
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param("ii", $items_per_page, $offset);
}
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách sản phẩm cho filter
$products_query = "SELECT product_id, name FROM products ORDER BY name";
$products = $conn->query($products_query)->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --hover-color: #2980b9;
            --text-light: #ecf0f1;
            --text-dark: #2c3e50;
            --border-color: #eee;
            --bg-light: #f8f9fa;
            --bg-dark: #2c3e50;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
        }

        /* Override default styles to ensure proper layout */
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
        }

        .reviews-container {
            flex: 1;
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            padding: 2rem;
            background-color: var(--bg-light);
            min-height: 100vh;
        }

        .reviews-container h1 {
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 1rem;
        }

        .filters {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .filters form {
            display: flex;
            gap: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .filter-group label {
            color: var(--text-dark);
            font-weight: 500;
            margin-bottom: 0;
        }

        .filter-group select {
            padding: 0.5rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            color: var(--text-dark);
            min-width: 150px;
        }

        .btn-filter {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-filter:hover {
            background: var(--hover-color);
            transform: translateY(-2px);
        }

        .reviews-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .reviews-table th {
            background: var(--primary-color);
            color: white;
            padding: 1rem;
            font-weight: 500;
        }

        .reviews-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .reviews-table tr:hover {
            background: var(--bg-light);
        }

        .rating {
            color: #f1c40f;
            font-size: 1.1rem;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-delete:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .pagination .page-item .page-link {
            color: var(--text-dark);
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .pagination .page-item.active .page-link {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        .pagination .page-item .page-link:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .reviews-container {
                width: 100%;
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="reviews-container">
        <h1>Quản Lý Đánh Giá</h1>
        
        <div class="filters">
            <form method="GET" action="">
                <div class="filter-group">
                    <label for="rating">Đánh giá:</label>
                    <select name="rating" id="rating">
                        <option value="">Tất cả</option>
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['rating']) && $_GET['rating'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?> sao
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="product_id">Sản phẩm:</label>
                    <select name="product_id" id="product_id">
                        <option value="">Tất cả</option>
                        <?php foreach($products as $product): ?>
                            <option value="<?= $product['product_id'] ?>" <?= (isset($_GET['product_id']) && $_GET['product_id'] == $product['product_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($product['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Lọc
                </button>
            </form>
        </div>

        <div class="reviews-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Sản phẩm</th>
                        <th>Khách hàng</th>
                        <th>Đánh giá</th>
                        <th>Nội dung</th>
                        <th>Ngày tạo</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($reviews as $review): ?>
                        <tr>
                            <td><?= $review['review_id'] ?></td>
                            <td><?= htmlspecialchars($review['product_name']) ?></td>
                            <td><?= htmlspecialchars($review['customer_name']) ?></td>
                            <td class="rating">
                                <?php for($i = 0; $i < $review['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                <?php endfor; ?>
                                <?php for($i = $review['rating']; $i < 5; $i++): ?>
                                    <i class="far fa-star"></i>
                                <?php endfor; ?>
                            </td>
                            <td><?= htmlspecialchars($review['comment']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($review['created_at'])) ?></td>
                            <td>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');">
                                    <input type="hidden" name="review_id" value="<?= $review['review_id'] ?>">
                                    <button type="submit" name="delete_review" class="btn-delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=1<?= isset($_GET['rating']) ? '&rating='.$_GET['rating'] : '' ?><?= isset($_GET['product_id']) ? '&product_id='.$_GET['product_id'] : '' ?>">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page-1 ?><?= isset($_GET['rating']) ? '&rating='.$_GET['rating'] : '' ?><?= isset($_GET['product_id']) ? '&product_id='.$_GET['product_id'] : '' ?>">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for($i = $start_page; $i <= $end_page; $i++):
                    ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?><?= isset($_GET['rating']) ? '&rating='.$_GET['rating'] : '' ?><?= isset($_GET['product_id']) ? '&product_id='.$_GET['product_id'] : '' ?>">
                                <?= $i ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page+1 ?><?= isset($_GET['rating']) ? '&rating='.$_GET['rating'] : '' ?><?= isset($_GET['product_id']) ? '&product_id='.$_GET['product_id'] : '' ?>">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $total_pages ?><?= isset($_GET['rating']) ? '&rating='.$_GET['rating'] : '' ?><?= isset($_GET['product_id']) ? '&product_id='.$_GET['product_id'] : '' ?>">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 