<?php
session_start();
require_once('../config/db_connect.php');

// Get categories and subcategories
$categories = $conn->query("SELECT * FROM categories");
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$subcategory_id = isset($_GET['subcategory']) ? (int)$_GET['subcategory'] : null;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Build the query based on filters
$query = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
          FROM products p 
          INNER JOIN subcategories s ON p.subcategory_id = s.subcategory_id 
          INNER JOIN categories c ON s.category_id = c.category_id 
          WHERE 1=1";

if ($category_id) {
    $query .= " AND c.category_id = $category_id";
}
if ($subcategory_id) {
    $query .= " AND s.subcategory_id = $subcategory_id";
}
if ($search) {
    $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

// Pagination settings
$items_per_page = 16;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Modify the query to include pagination
$count_query = str_replace("SELECT p.*, c.name", "SELECT COUNT(*)", $query);
$total_items = $conn->query($count_query)->fetch_row()[0];
$total_pages = ceil($total_items / $items_per_page);

$query .= " LIMIT $offset, $items_per_page";
$products = $conn->query($query);
if (!$products) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sản phẩm - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/san-pham.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>
<body>

    <?php include 'slide-menu.php'; ?>

    <!-- Main Content -->
    <div class="header-content">
        <!-- Search Banner -->
        <div class="search-banner">
            <form class="search-form" method="GET" action="san-pham.php">
                <input type="text" 
                       name="search" 
                       placeholder="Tìm kiếm sản phẩm..." 
                       value="<?php echo htmlspecialchars($search); ?>"
                       autocomplete="off"
                       id="searchInput">
                <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
                
                <!-- Search Suggestions -->
                <div class="search-suggestions" id="searchSuggestions">
                    <div class="suggestion-group">
                        <h4>Danh mục</h4>
                        <div id="categorySuggestions"></div>
                    </div>
                    <div class="suggestion-group">
                        <h4>Danh mục con</h4>
                        <div id="subcategorySuggestions"></div>
                    </div>
                    <div class="suggestion-group">
                        <h4>Sản phẩm</h4>
                        <div id="productSuggestions"></div>
                    </div>
                </div>
            </form>
        </div>

        <div class="menu-container">
            <!-- Left Menu - Categories -->
            <aside class="left-menu">
                <div class="menu-header">
                    <h3><i class="fas fa-list"></i> Danh mục sản phẩm</h3>
                </div>
                <div class="menu-body">
                    <ul class="category-list">
                        <li class="category-item">
                            <a href="san-pham.php" class="all-products">
                                <i class="fas fa-th-large"></i>
                                <span>Tất cả sản phẩm</span>
                            </a>
                        </li>
                        <?php 
                        $categories->data_seek(0);
                        $category_count = 0;
                        
                        while ($category = $categories->fetch_assoc()): 
                            $category_count++;
                            $class = $category_count > 8 ? 'hidden-category' : '';
                            $subcategories = $conn->query("SELECT * FROM subcategories WHERE category_id = " . $category['category_id']);
                        ?>
                            <li class="category-item <?php echo $class; ?>" data-id="<?php echo $category['category_id']; ?>">
                                <a href="san-pham.php?category=<?php echo $category['category_id']; ?>">
                                    <i class="<?php echo !empty($category['icon']) ? $category['icon'] : 'fas fa-angle-right'; ?>"></i>
                                    <span><?php echo htmlspecialchars($category['name']); ?></span>
                                    <?php if ($subcategories->num_rows > 0): ?>
                                        <i class="fas fa-chevron-right arrow-icon"></i>
                                    <?php endif; ?>
                                </a>
                                <?php if ($subcategories->num_rows > 0): ?>
                                    <ul class="subcategory-list">
                                        <?php while ($subcategory = $subcategories->fetch_assoc()): ?>
                                            <li class="subcategory-item">
                                                <a href="san-pham.php?category=<?php echo $category['category_id']; ?>&subcategory=<?php echo $subcategory['subcategory_id']; ?>">
                                                    <?php echo htmlspecialchars($subcategory['name']); ?>
                                                </a>
                                            </li>
                                        <?php endwhile; ?>
                                    </ul>
                                <?php endif; ?>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    <?php if ($category_count > 8): ?>
                        <button type="button" class="show-more-btn">
                            <i class="fas fa-chevron-down"></i> Xem thêm
                        </button>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Products Grid -->
            <main class="products-container">
                <h2>Sản phẩm <?php 
                    if ($category_id) {
                        $cat = $conn->query("SELECT name FROM categories WHERE category_id = $category_id")->fetch_assoc();
                        echo "- " . htmlspecialchars($cat['name']);
                    }
                    if ($subcategory_id) {
                        $subcat = $conn->query("SELECT name FROM subcategories WHERE subcategory_id = $subcategory_id")->fetch_assoc();
                        echo " - " . htmlspecialchars($subcat['name']);
                    }
                    if ($search) {
                        echo "- Kết quả tìm kiếm cho '" . htmlspecialchars($search) . "'";
                    }
                ?></h2>
                
                <div class="products-grid">
                    <?php if ($products->num_rows > 0): ?>
                        <?php while ($product = $products->fetch_assoc()):
                            $productId = htmlspecialchars($product['product_id']);
                            $productName = htmlspecialchars($product['name']);
                            $productImage = htmlspecialchars($product['image_url']);
                            $productPrice = number_format($product['price'], 0, ',', '.') . '₫';
                            $productStatus = $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng';
                            $statusClass = $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock';
                        ?>
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo $productImage; ?>" alt="<?php echo $productName; ?>" onerror="this.src='../images/default-product.jpg'">
                                <div class="product-actions">
                                    <button class="action-btn view-btn" onclick="window.location.href='san-pham-chi-tiet.php?id=<?php echo $productId; ?>'" title="Xem sản phẩm"><i class="fas fa-eye"></i></button>
                                    <button class="action-btn cart-btn" onclick="showAddToCartForm(<?php echo $productId; ?>, '<?php echo addslashes($productName); ?>', '<?php echo $productPrice; ?>', '<?php echo $productImage; ?>')" title="Thêm vào giỏ" <?php if ($product['stock_quantity'] <= 0) echo 'disabled'; ?>><i class="fas fa-shopping-cart"></i></button>
                                    <button class="action-btn buy-btn" onclick="showBuyNowForm(<?php echo $productId; ?>, '<?php echo addslashes($productName); ?>', '<?php echo $productPrice; ?>', '<?php echo $productImage; ?>')" title="Mua ngay" <?php if ($product['stock_quantity'] <= 0) echo 'disabled'; ?>><i class="fas fa-bolt"></i></button>
                                    <button class="action-btn wishlist-btn" onclick="addToWishlist(<?php echo $productId; ?>)" title="Thêm yêu thích"><i class="fas fa-heart"></i></button>
                                </div>
                            </div>
                            <div class="product-info">
                                <h4 class="product-name"><?php echo $productName; ?></h4>
                                <div class="product-price-container">
                                    <p class="product-price"><?php echo $productPrice; ?></p>
                                    <span class="product-status <?php echo $statusClass; ?>"><?php echo $productStatus; ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-products">Không tìm thấy sản phẩm nào.</p>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($current_page > 1): ?>
                            <a href="?page=<?php echo $current_page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">Trước</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>" 
                               class="<?php echo $i === $current_page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages): ?>
                            <a href="?page=<?php echo $current_page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . urlencode($_GET['search']) : ''; ?>">Sau</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Đảm bảo jQuery được load trước -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Đặt script popup ở đây, KHÔNG lồng trong DOMContentLoaded -->
    <script>
        // --- Product Card Action Functions ---
        function showAddToCartForm(productId, productName, productPrice, productImage) {
            $('.popup-overlay').remove();
            const form = `
                <div class="popup-overlay">
                    <div class="popup-content product-form">
                        <div class="product-form-header">
                            <h3>Thêm vào giỏ hàng</h3>
                            <button class="close-btn" onclick="closePopup()">&times;</button>
                        </div>
                        <div class="product-form-body">
                            <div class="product-image-section">
                                <img src="${productImage}" alt="${productName}" onerror="this.src='../images/default-product.jpg'">
                            </div>
                            <div class="product-info-section">
                                <h4 class="product-name">${productName}</h4>
                                <p class="product-price">${productPrice}</p>
                                <div class="quantity-selector">
                                    <label>Số lượng:</label>
                                    <div class="quantity-controls">
                                        <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                                        <input type="number" id="quantity" value="1" min="1" max="999">
                                        <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="btn-confirm" onclick="addToCart(${productId}, document.getElementById('quantity').value, this)">
                                        <i class="fas fa-shopping-cart"></i> Thêm vào giỏ
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(form);
        }
        function showBuyNowForm(productId, productName, productPrice, productImage) {
            $('.popup-overlay').remove();
            const form = `
                <div class="popup-overlay">
                    <div class="popup-content product-form">
                        <div class="product-form-header">
                            <h3>Mua ngay</h3>
                            <button class="close-btn" onclick="closePopup()">&times;</button>
                        </div>
                        <div class="product-form-body">
                            <div class="product-image-section">
                                <img src="${productImage}" alt="${productName}" onerror="this.src='../images/default-product.jpg'">
                            </div>
                            <div class="product-info-section">
                                <h4 class="product-name">${productName}</h4>
                                <p class="product-price">${productPrice}</p>
                                <div class="quantity-selector">
                                    <label>Số lượng:</label>
                                    <div class="quantity-controls">
                                        <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
                                        <input type="number" id="quantity" value="1" min="1" max="999">
                                        <button class="qty-btn" onclick="changeQuantity(1)">+</button>
                                    </div>
                                </div>
                                <div class="form-actions">
                                    <button class="btn-confirm buy-now" onclick="buyNow(${productId}, document.getElementById('quantity').value, this)">
                                        <i class="fas fa-bolt"></i> Mua ngay
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(form);
        }
        function changeQuantity(delta) {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value) + delta;
            if (value < 1) value = 1;
            if (value > 999) value = 999;
            input.value = value;
        }
        function closePopup() {
            $('.popup-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        }
        function addToCart(productId, quantity, btn) {
            <?php if (!isset($_SESSION['user']) && !isset($_SESSION['customer_id'])): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                closePopup();
                return;
            <?php endif; ?>
            const button = btn;
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
            $.ajax({
                url: 'add_to_cart.php',
                type: 'POST',
                data: { 
                    product_id: productId, 
                    quantity: quantity 
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        showNotification('Đã thêm sản phẩm vào giỏ hàng!', 'success');
                        closePopup();
                    } else {
                        showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Thêm vào giỏ';
                        }
                    }
                },
                error: function() {
                    showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng!', 'error');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-shopping-cart"></i> Thêm vào giỏ';
                    }
                }
            });
        }
        function buyNow(productId, quantity, btn) {
            <?php if (!isset($_SESSION['user']) && !isset($_SESSION['customer_id'])): ?>
                alert('Vui lòng đăng nhập để mua sản phẩm!');
                closePopup();
                return;
            <?php endif; ?>
            const button = btn;
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
            $.ajax({
                url: 'add_to_cart.php',
                type: 'POST',
                data: { 
                    product_id: productId, 
                    quantity: quantity,
                    buy_now: true
                },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        showNotification('Đang chuyển đến trang thanh toán...', 'success');
                        closePopup();
                        setTimeout(() => {
                            window.location.href = 'thanh-toan.php';
                        }, 1000);
                    } else {
                        showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
                        }
                    }
                },
                error: function() {
                    showNotification('Có lỗi xảy ra khi mua sản phẩm!', 'error');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
                    }
                }
            });
        }
        function addToWishlist(productId) {
            <?php if (!isset($_SESSION['user'])): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào yêu thích!');
                return;
            <?php endif; ?>
            $.ajax({
                url: 'add_to_wishlist.php',
                type: 'POST',
                data: { product_id: productId },
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        showNotification('Đã thêm sản phẩm vào yêu thích!', 'success');
                    } else {
                        showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                    }
                },
                error: function() {
                    showNotification('Có lỗi xảy ra khi thêm vào yêu thích!', 'error');
                }
            });
        }
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 8px;
                    color: white;
                    font-weight: 500;
                    z-index: 9999;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: slideIn 0.3s ease;
                    background: ${type === 'success' ? '#4CAF50' : '#f44336'};
                ">
                    ${message}
                </div>
            `;
            document.body.appendChild(notification);
            setTimeout(() => { notification.remove(); }, 3000);
        }
        // Add CSS for notification animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
    <!-- Giữ lại script.js nếu cần cho các chức năng khác, nhưng không để ghi đè các hàm trên -->
    <script src="script.js"></script>
</body>
</html> 