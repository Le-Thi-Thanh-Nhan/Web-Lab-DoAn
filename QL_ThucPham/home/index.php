<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Simple database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'ql_thucpham';

try {
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    echo "<h1>Database Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running</li>";
    echo "<li>MySQL service is started</li>";
    echo "<li>Database 'ql_thucpham' exists</li>";
    echo "</ul>";
    exit;
}

// Helper function to format price
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . '₫';
}

// Function to render a product card
function renderProductCard($product) {
    $productId = htmlspecialchars($product['product_id']);
    $productName = htmlspecialchars($product['name']);
    $productImage = htmlspecialchars($product['image_url']);
    $productPrice = formatPrice($product['price']);
    $productStatus = $product['stock_quantity'] > 0 ? 'Còn hàng' : 'Hết hàng';
    $statusClass = $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock';
    
    $html = '<div class="product-card">';
    $html .= '<div class="product-image">';
    $html .= '<img src="' . $productImage . '" alt="' . $productName . '" onerror="this.src=\'../images/default-product.jpg\'">';
    
    // Quick action buttons overlay
    $html .= '<div class="product-actions">';
    $html .= '<button class="action-btn view-btn" onclick="window.location.href=\'san-pham-chi-tiet.php?id=' . $productId . '\'" title="Xem sản phẩm"><i class="fas fa-eye"></i></button>';
    $html .= '<button class="action-btn cart-btn" onclick="showAddToCartForm(' . $productId . ', \'' . addslashes($productName) . '\', \'' . $productPrice . '\', \'' . $productImage . '\')" title="Thêm vào giỏ"><i class="fas fa-shopping-cart"></i></button>';
    $html .= '<button class="action-btn buy-btn" onclick="showBuyNowForm(' . $productId . ', \'' . addslashes($productName) . '\', \'' . $productPrice . '\', \'' . $productImage . '\')" title="Mua ngay"><i class="fas fa-bolt"></i></button>';
    $html .= '<button class="action-btn wishlist-btn" onclick="addToWishlist(' . $productId . ')" title="Thêm yêu thích"><i class="fas fa-heart"></i></button>';
    $html .= '</div>';
    
    $html .= '</div>';
    $html .= '<div class="product-info">';
    $html .= '<h4 class="product-name">' . $productName . '</h4>';
    $html .= '<div class="product-price-container">';
    $html .= '<p class="product-price">' . $productPrice . '</p>';
    $html .= '<span class="product-status ' . $statusClass . '">' . $productStatus . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

// Get customer name for welcome message
$customerName = '';
if (isset($_SESSION['user'])) {
    $customer_query = "SELECT name FROM customers WHERE customer_id = ?";
    $stmt = $conn->prepare($customer_query);
    $stmt->bind_param("i", $_SESSION['user']['customer_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        $customerName = $customer['name'];
    }
}

// Get random categories for slides 3, 4, 5
$categories = [];
$category_query = "SELECT category_id, name, description FROM categories ORDER BY RAND() LIMIT 3";
$category_result = $conn->query($category_query);
if ($category_result && $category_result->num_rows > 0) {
    while ($category = $category_result->fetch_assoc()) {
        $categories[] = $category;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/banner-fix.css">
    <link rel="stylesheet" href="css/global-fix.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/8.4.5/swiper-bundle.min.css">
    <style>
        .banner-slider-container {
            width: 100%;
            height: 550px;
            position: relative;
            overflow: hidden;
        }
        
        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .banner-slide.active {
            opacity: 1;
        }
        
        .banner-content {
            text-align: left;
            color: white;
            padding: 40px;
            max-width: 800px;
            display: flex;
            flex-direction: column;
            gap: 30px;
            justify-content: center;
            height: 100%;
        }
        
        .banner-content.right {
            margin-left: auto;
            margin-right: 40px;
        }
        
        .banner-content.left {
            margin-right: auto;
            margin-left: 40px;
        }
        
        .banner-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transform: translateY(-50px);
            transition: all 0.8s ease;
            order: 1;
            white-space: nowrap;
            color: #e0e0e0;
        }
        
        .banner-title.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .banner-description {
            font-size: 1.2rem;
            margin: 0;
            line-height: 1.6;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            opacity: 0;
            transform: translateY(-50px);
            transition: all 0.8s ease;
            transition-delay: 0.75s;
            order: 2;
            white-space: nowrap;
            color: #e0e0e0;
        }
        
        .banner-description.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .banner-button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(45deg, #4CAF50, #388E3C);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.3);
            opacity: 0;
            transform: translateY(-50px);
            transition: all 0.8s ease;
            transition-delay: 1.5s;
            order: 3;
            align-self: center;
            white-space: nowrap;
        }
        
        .banner-button.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .banner-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
            background: linear-gradient(45deg, #388E3C, #4CAF50);
            color: white;
            text-decoration: none;
        }
        
        .banner-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 10;
        }
        
        .banner-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .banner-indicator.active {
            background: white;
            transform: scale(1.2);
        }
        
        @media (max-width: 768px) {
            .banner-slider-container {
                height: 350px;
            }
            
            .banner-content {
                padding: 20px;
            }
            
            .banner-title {
                font-size: 1.8rem;
            }
            
            .banner-description {
                font-size: 1rem;
            }
            
            .banner-button {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }
        
        /* Product Card Styles */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .product-image {
            position: relative;
            padding-top: 70%;
            background: #f8f9fa;
            overflow: hidden;
        }
        
        .product-image img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 10px;
            transition: transform 0.3s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.05);
        }
        
        /* Quick Action Buttons */
        .product-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .product-card:hover .product-actions {
            opacity: 1;
            transform: translateX(0);
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: white;
            color: #4CAF50;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
            z-index: 11;
            position: relative;
        }
        
        .action-btn:hover {
            background: #4CAF50;
            color: white;
            transform: scale(1.1);
        }
        
        .action-btn:active {
            transform: scale(0.95);
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-size: 14px;
            color: #333;
            margin: 0 0 8px 0;
            line-height: 1.3;
            height: 36px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .product-price-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .product-price {
            color: #e74c3c;
            font-weight: 600;
            font-size: 16px;
            margin: 0;
        }
        
        .product-status {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .product-status.in-stock {
            background: #d4edda;
            color: #155724;
        }
        
        .product-status.out-of-stock {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Responsive Grid */
        @media (max-width: 1200px) {
            .featured-products .products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        @media (max-width: 992px) {
            .featured-products .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .featured-products .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .featured-products .products-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Popup Form Styles */
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .popup-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }
        
        .popup-content h3 {
            color: #2E7D32;
            margin-bottom: 15px;
            font-size: 1.5rem;
        }
        
        .popup-content p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .popup-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-confirm, .btn-cancel {
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-confirm {
            background: #4CAF50;
            color: white;
        }
        
        .btn-confirm:hover {
            background: #388E3C;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #f5f5f5;
            color: #666;
            border: 1px solid #ddd;
        }
        
        .btn-cancel:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }
        
        /* New styles for product form popup */
        .product-form {
            max-width: 500px;
            width: 95%;
            padding: 0;
            text-align: left;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .product-form-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            background: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
        }

        .product-form-header h3 {
            margin: 0;
            color: #2E7D32;
            font-size: 1.4rem;
            font-weight: 600;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-btn:hover {
            background: #e9ecef;
            color: #495057;
        }

        .product-form-body {
            padding: 25px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .product-image-section {
            flex: 0 0 120px;
            height: 120px;
            overflow: hidden;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: white;
            border: 1px solid #e9ecef;
        }

        .product-image-section img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 8px;
        }

        .product-info-section {
            flex: 1;
            min-width: 0;
        }

        .product-info-section .product-name {
            font-size: 1.3rem;
            color: #212529;
            margin-bottom: 8px;
            line-height: 1.3;
            font-weight: 600;
        }

        .product-info-section .product-price {
            font-size: 1.2rem;
            color: #dc3545;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .quantity-selector {
            margin-bottom: 25px;
        }

        .quantity-selector label {
            display: block;
            font-size: 0.95rem;
            color: #495057;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
            background: white;
            width: fit-content;
        }

        .qty-btn {
            width: 36px;
            height: 36px;
            border: none;
            background: #f8f9fa;
            color: #495057;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .qty-btn:hover {
            background: #e9ecef;
            color: #212529;
        }

        .qty-btn:active {
            background: #dee2e6;
        }

        .quantity-controls input {
            width: 50px;
            height: 36px;
            border: none;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            color: #212529;
            background: white;
            border-left: 1px solid #dee2e6;
            border-right: 1px solid #dee2e6;
        }

        .quantity-controls input:focus {
            outline: none;
            background: #f8f9fa;
        }

        .quantity-controls input::-webkit-inner-spin-button,
        .quantity-controls input::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .form-actions {
            display: flex;
            justify-content: center;
        }

        .btn-confirm {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 140px;
            justify-content: center;
        }

        .btn-confirm.buy-now {
            background: #28a745;
            color: white;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-confirm.buy-now:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(40, 167, 69, 0.4);
        }

        .btn-confirm:not(.buy-now) {
            background: #007bff;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .btn-confirm:not(.buy-now):hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
        }
    </style>
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <!-- Banner Section -->
    <section class="banner-container">
        <div class="banner-slider-container">
            <!-- Slide 1: Welcome -->
            <div class="banner-slide active" style="background-image: url('../images/banner0.jpg');">
                <div class="banner-content">
                    <h2 class="banner-title show">Chào mừng <?php echo $customerName ? $customerName : 'bạn'; ?> đến với Thực phẩm Mộc</h2>
                    <p class="banner-description show">Các thực phẩm bạn cần cho cuộc sống ăn uống hằng ngày đều có ở đây</p>
                </div>
            </div>
            
            <!-- Slide 2: Discount Codes -->
            <div class="banner-slide" style="background-image: url('../images/banner1.jpg');">
                <div class="banner-content right">
                    <h2 class="banner-title">Mã giảm giá đã ra mắt</h2>
                    <p class="banner-description">Nhanh tay sở hữu những thực phẩm với giá rẻ nào</p>
                    <a href="ma-giam-gia.php" class="banner-button">Xem mã giảm giá</a>
                </div>
            </div>
            
            <!-- Slide 3: Category 1 -->
            <div class="banner-slide" style="background-image: url('../images/banner2.jpg');">
                <div class="banner-content right">
                    <h2 class="banner-title"><?php echo isset($categories[0]) ? htmlspecialchars($categories[0]['name']) : 'Danh mục sản phẩm'; ?></h2>
                    <p class="banner-description"><?php echo isset($categories[0]) ? htmlspecialchars($categories[0]['description']) : 'Khám phá các sản phẩm chất lượng'; ?></p>
                    <a href="san-pham.php" class="banner-button">Xem sản phẩm</a>
                </div>
                <div class="banner-embossed-image" style="position: absolute; left: 50px; top: 50%; transform: translateY(-50%); width: 500px; height: 500px; z-index: 2;">
                    <img src="../images/embossed_img1.jpg" alt="Embossed Image 1" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            
            <!-- Slide 4: Category 2 -->
            <div class="banner-slide" style="background-image: url('../images/banner3.jpg');">
                <div class="banner-content left">
                    <h2 class="banner-title"><?php echo isset($categories[1]) ? htmlspecialchars($categories[1]['name']) : 'Danh mục sản phẩm'; ?></h2>
                    <p class="banner-description"><?php echo isset($categories[1]) ? htmlspecialchars($categories[1]['description']) : 'Khám phá các sản phẩm chất lượng'; ?></p>
                    <a href="san-pham.php" class="banner-button">Xem sản phẩm</a>
                </div>
                <div class="banner-embossed-image" style="position: absolute; right: 50px; top: 50%; transform: translateY(-50%); width: 500px; height: 500px; z-index: 2;">
                    <img src="../images/embossed_img2.jpg" alt="Embossed Image 2" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            
            <!-- Slide 5: Category 3 -->
            <div class="banner-slide" style="background-image: url('../images/banner2.jpg');">
                <div class="banner-content right">
                    <h2 class="banner-title"><?php echo isset($categories[2]) ? htmlspecialchars($categories[2]['name']) : 'Danh mục sản phẩm'; ?></h2>
                    <p class="banner-description"><?php echo isset($categories[2]) ? htmlspecialchars($categories[2]['description']) : 'Khám phá các sản phẩm chất lượng'; ?></p>
                    <a href="san-pham.php" class="banner-button">Xem sản phẩm</a>
                </div>
                <div class="banner-embossed-image" style="position: absolute; left: 50px; top: 50%; transform: translateY(-50%); width: 500px; height: 500px; z-index: 2;">
                    <img src="../images/embossed_img3.jpg" alt="Embossed Image 3" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
            </div>
            
            <!-- Banner Indicators -->
            <div class="banner-indicators">
                <div class="banner-indicator active" data-slide="0"></div>
                <div class="banner-indicator" data-slide="1"></div>
                <div class="banner-indicator" data-slide="2"></div>
                <div class="banner-indicator" data-slide="3"></div>
                <div class="banner-indicator" data-slide="4"></div>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section style="padding: 40px 0; background: #f9f9f9;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h2 style="text-align: center; color: #2E7D32; margin-bottom: 30px;">Sản phẩm nổi bật</h2>
            <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px;">
                <?php 
                try {
                    // Get featured products
                    $featured_query = "SELECT * FROM products WHERE stock_quantity > 0 ORDER BY RAND() LIMIT 10";
                    $featured_products = $conn->query($featured_query);
                    
                    if ($featured_products && $featured_products->num_rows > 0) {
                        while ($product = $featured_products->fetch_assoc()) {
                            echo renderProductCard($product);
                        }
                    } else {
                        echo "<div style='grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;'>";
                        echo "<p>Chưa có sản phẩm nào.</p>";
                        echo "</div>";
                    }
                } catch (Exception $e) {
                    echo "<div style='grid-column: 1 / -1; text-align: center; padding: 40px; color: #d32f2f;'>";
                    echo "<p>Lỗi tải sản phẩm: " . $e->getMessage() . "</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="gioi-thieu" style="background: #f9f9f9; padding: 40px 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <h2 style="text-align: center; color: #2E7D32; margin-bottom: 30px;">Giới thiệu về chúng tôi</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                <div>
                    <h3 style="color: #2E7D32;">Nguồn gốc minh bạch</h3>
                    <p>Mọi sản phẩm đều có nguồn gốc rõ ràng, được kiểm soát chặt chẽ từ khâu sản xuất đến phân phối.</p>
                </div>
                <div>
                    <h3 style="color: #2E7D32;">An toàn vệ sinh</h3>
                    <p>Đạt tiêu chuẩn vệ sinh an toàn thực phẩm, được bảo quản trong điều kiện tốt nhất.</p>
                </div>
                <div>
                    <h3 style="color: #2E7D32;">Giá cả hợp lý</h3>
                    <p>Cam kết mang đến giá trị tốt nhất cho khách hàng với mức giá cạnh tranh trên thị trường.</p>
                </div>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
    
    <script>
        // Banner Slider functionality
        $(document).ready(function() {
            let currentSlide = 0;
            const slides = $('.banner-slide');
            const indicators = $('.banner-indicator');
            const totalSlides = slides.length;
            
            function showSlide(index) {
                // Hide all slides and reset animations
                slides.removeClass('active');
                indicators.removeClass('active');
                
                // Reset all text animations
                $('.banner-title, .banner-description, .banner-button').removeClass('show');
                
                // Show current slide
                slides.eq(index).addClass('active');
                indicators.eq(index).addClass('active');
                
                // Animate text for the new slide with staggered timing
                const newSlideContent = slides.eq(index).find('.banner-content');
                
                // Title appears first (0s delay)
                setTimeout(() => {
                    newSlideContent.find('.banner-title').addClass('show');
                }, 500);
                
                // Description appears second (0.75s delay)
                setTimeout(() => {
                    newSlideContent.find('.banner-description').addClass('show');
                }, 1250);
                
                // Button appears third (1.5s delay)
                setTimeout(() => {
                    newSlideContent.find('.banner-button').addClass('show');
                }, 2000);
            }
            
            function nextSlide() {
                currentSlide = (currentSlide + 1) % totalSlides;
                showSlide(currentSlide);
            }
            
            // Auto slide every 7 seconds
            setInterval(nextSlide, 7000);
            
            // Click on indicators
            indicators.click(function() {
                currentSlide = $(this).data('slide');
                showSlide(currentSlide);
            });
        });
        
        // Product action functions
        function showAddToCartForm(productId, productName, productPrice, productImage) {
            // Remove any existing popup
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
                                    <button class="btn-confirm" onclick="addToCart(${productId}, document.getElementById('quantity').value)">
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
            // Remove any existing popup
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
                                    <button class="btn-confirm buy-now" onclick="buyNow(${productId}, document.getElementById('quantity').value)">
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

        function addToCart(productId, quantity) {
            console.log('Adding to cart:', productId, 'Quantity:', quantity);
            
            // Check if user is logged in
            <?php if (!isset($_SESSION['user']) && !isset($_SESSION['customer_id'])): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                closePopup();
                return;
            <?php endif; ?>
            
            // Disable button to prevent double click
            const button = event.target.closest('.btn-confirm');
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
                    console.log('Add to cart response:', response);
                    if(response.success) {
                        showNotification('Đã thêm sản phẩm vào giỏ hàng!', 'success');
                        closePopup();
                    } else {
                        showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                        // Re-enable button on error
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Thêm vào giỏ';
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Add to cart error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showNotification('Có lỗi xảy ra khi thêm vào giỏ hàng!', 'error');
                    // Re-enable button on error
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-shopping-cart"></i> Thêm vào giỏ';
                    }
                }
            });
        }
        
        function buyNow(productId, quantity) {
            console.log('Buy now:', productId, 'Quantity:', quantity);
            
            // Check if user is logged in
            <?php if (!isset($_SESSION['user']) && !isset($_SESSION['customer_id'])): ?>
                alert('Vui lòng đăng nhập để mua sản phẩm!');
                closePopup();
                return;
            <?php endif; ?>
            
            // Disable button to prevent double click
            const button = event.target.closest('.btn-confirm');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            }
            
            // First add to cart, then redirect to checkout
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
                    console.log('Buy now response:', response);
                    if(response.success) {
                        showNotification('Đang chuyển đến trang thanh toán...', 'success');
                        closePopup();
                        // Redirect to checkout page
                        setTimeout(() => {
                            window.location.href = 'thanh-toan.php';
                        }, 1000);
                    } else {
                        showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                        // Re-enable button on error
                        if (button) {
                            button.disabled = false;
                            button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Buy now error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showNotification('Có lỗi xảy ra khi mua sản phẩm!', 'error');
                    // Re-enable button on error
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-bolt"></i> Mua ngay';
                    }
                }
            });
        }
        
        function addToWishlist(productId) {
            console.log('Adding to wishlist:', productId);
            
            // Check if user is logged in
            <?php if (!isset($_SESSION['user'])): ?>
                alert('Vui lòng đăng nhập để thêm sản phẩm vào yêu thích!');
                return;
            <?php endif; ?>
            
            $.ajax({
                url: 'add_to_wishlist.php',
                type: 'POST',
                data: { 
                    product_id: productId 
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Add to wishlist response:', response);
                    if(response.success) {
                        showNotification('Đã thêm sản phẩm vào yêu thích!', 'success');
                    } else {
                        showNotification(response.message || 'Có lỗi xảy ra!', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Add to wishlist error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showNotification('Có lỗi xảy ra khi thêm vào yêu thích!', 'error');
                }
            });
        }
        
        // Notification function
        function showNotification(message, type) {
            // Create notification element
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
            
            // Add to body
            document.body.appendChild(notification);
            
            // Remove after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Add CSS for notification animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
    
    <?php
    // Close database connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>