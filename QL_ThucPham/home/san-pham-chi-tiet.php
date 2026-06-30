<?php
session_start();
require_once('../config/db_connect.php');

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header("Location: san-pham.php");
    exit;
}

// Fetch product details with category and subcategory information
$query = "SELECT p.*, c.category_id, c.name as category_name, s.subcategory_id, s.name as subcategory_name
          FROM products p 
          JOIN subcategories s ON p.subcategory_id = s.subcategory_id
          JOIN categories c ON s.category_id = c.category_id
          WHERE p.product_id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
}

if (!$stmt->bind_param("i", $product_id)) {
    die("Lỗi bind param: " . $stmt->error);
}

if (!$stmt->execute()) {
    die("Lỗi thực thi câu lệnh: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Lỗi lấy kết quả: " . $stmt->error);
}

$product = $result->fetch_assoc();
$stmt->close();

// Redirect if product not found
if (!$product) {
    header("Location: san-pham.php");
    exit;
}

// Check if product is in wishlist
$in_wishlist = false;
if (isset($_SESSION['user'])) {
    $customer_id = $_SESSION['user']['customer_id'];
    $stmt = $conn->prepare("SELECT 1 FROM wishlists WHERE customer_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $customer_id, $product_id);
    $stmt->execute();
    $in_wishlist = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

// After fetching product details, get review statistics
$review_stats = [
    'average_rating' => 0,
    'total_reviews' => 0,
    'five_star' => 0,
    'four_star' => 0,
    'three_star' => 0,
    'two_star' => 0,
    'one_star' => 0
];

$stats_query = "SELECT 
    ROUND(AVG(rating), 1) as average_rating,
    COUNT(*) as total_reviews,
    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
    FROM product_reviews 
    WHERE product_id = ?";

$stats_stmt = $conn->prepare($stats_query);
if (!$stats_stmt) {
    die("Lỗi chuẩn bị câu lệnh thống kê: " . $conn->error);
}

if (!$stats_stmt->bind_param("i", $product_id)) {
    die("Lỗi bind param thống kê: " . $stats_stmt->error);
}

if (!$stats_stmt->execute()) {
    die("Lỗi thực thi thống kê: " . $stats_stmt->error);
}

$stats_result = $stats_stmt->get_result();
if (!$stats_result) {
    die("Lỗi lấy kết quả thống kê: " . $stats_stmt->error);
}

$stats = $stats_result->fetch_assoc();
$stats_stmt->close();

if ($stats) {
    $review_stats = [
        'average_rating' => $stats['average_rating'] ?: 0,
        'total_reviews' => $stats['total_reviews'] ?: 0,
        'five_star' => $stats['five_star'] ?: 0,
        'four_star' => $stats['four_star'] ?: 0,
        'three_star' => $stats['three_star'] ?: 0,
        'two_star' => $stats['two_star'] ?: 0,
        'one_star' => $stats['one_star'] ?: 0
    ];
}

// Get user's review if exists
$user_review = null;
if (isset($_SESSION['user'])) {
    $review_stmt = $conn->prepare("SELECT * FROM product_reviews WHERE customer_id = ? AND product_id = ?");
    $review_stmt->bind_param("ii", $_SESSION['user']['customer_id'], $product_id);
    $review_stmt->execute();
    $user_review = $review_stmt->get_result()->fetch_assoc();
    $review_stmt->close();
}

// Get recent reviews
$recent_reviews_query = "SELECT pr.*, c.name as customer_name 
                        FROM product_reviews pr 
                        JOIN customers c ON pr.customer_id = c.customer_id 
                        WHERE pr.product_id = ? 
                        ORDER BY pr.created_at DESC 
                        LIMIT 5";
$recent_stmt = $conn->prepare($recent_reviews_query);
$recent_stmt->bind_param("i", $product_id);
$recent_stmt->execute();
$recent_reviews = $recent_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recent_stmt->close();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="css/product-detail.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <!-- Alert Container -->
    <div class="alert-container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle success-icon"></i>
                <div class="alert-message"><?php echo $success_message; ?></div>
                <button type="button" class="alert-close" onclick="closeAlert(this.parentElement)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress">
                    <div class="alert-progress-bar"></div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error" role="alert">
                <i class="fas fa-exclamation-circle error-icon"></i>
                <div class="alert-message"><?php echo $error_message; ?></div>
                <button type="button" class="alert-close" onclick="closeAlert(this.parentElement)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="alert-progress">
                    <div class="alert-progress-bar"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Main Content -->
    <div class="product-detail-container">
        <!-- Breadcrumb -->
        <div class="product-breadcrumb">
            <a href="index.php">Trang chủ</a>
            <i class="fas fa-chevron-right"></i>
            <a href="san-pham.php?category=<?php echo $product['category_id']; ?>"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <a href="san-pham.php?category=<?php echo $product['category_id']; ?>&subcategory=<?php echo $product['subcategory_id']; ?>"><?php echo htmlspecialchars($product['subcategory_name']); ?></a>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($product['name']); ?></span>
        </div>

        <div class="product-content">
            <!-- Product Gallery -->
            <div class="product-gallery">
                <div class="product-image">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         onerror="this.src='../images/default-product.jpg'">
                </div>
                <button type="button" 
                        class="wishlist-btn <?php echo $in_wishlist ? 'active' : ''; ?>"
                        onclick="toggleWishlist(<?php echo $product_id; ?>)">
                    <i class="<?php echo $in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                </button>
            </div>

            <!-- Product Info -->
            <div class="product-info">
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                
                <div class="product-meta">
                    <div class="product-rating">
                        <div class="stars">
                            <?php
                            $rating = $review_stats['average_rating'];
                            $fullStars = floor($rating);
                            $halfStar = round($rating - $fullStars, 1) >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $fullStars) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($halfStar && $i == $fullStars + 1) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="rating-info">
                            <span class="rating-value"><?php echo number_format($review_stats['average_rating'], 1); ?></span>
                            <span class="rating-count">(<?php echo $review_stats['total_reviews']; ?> đánh giá)</span>
                            <span class="rating-divider">|</span>
                            <span class="sales-count"><i class="fas fa-shopping-cart"></i> Đã bán: <?php echo number_format($product['sold_quantity']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="product-price">
                    <div class="price-wrapper">
                        <i class="fas fa-tag"></i>
                        <div class="price-info">
                            <span class="price"><?php echo number_format($product['price'], 0, ',', '.'); ?>đ</span>
                        </div>
                    </div>
                </div>

                <?php
                $stock_class = '';
                $stock_text = '';
                if ($product['stock_quantity'] > 10) {
                    $stock_class = 'in-stock';
                    $stock_text = 'Còn ' . $product['stock_quantity'] . ' sản phẩm';
                } elseif ($product['stock_quantity'] > 0) {
                    $stock_class = 'low-stock';
                    $stock_text = 'Sắp hết hàng (còn ' . $product['stock_quantity'] . ' sản phẩm).';
                } else {
                    $stock_class = 'out-stock';
                    $stock_text = 'Hết hàng';
                }
                ?>
                
                <div class="product-stock <?php echo $stock_class; ?>">
                    <i class="fas fa-box"></i>
                    <?php echo $stock_text; ?>
                </div>

                <div class="quantity-selector">
                    <label for="quantity">Số lượng:</label>
                    <div class="quantity-controls">
                        <button type="button" onclick="updateQuantity('decrease')" class="quantity-btn">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" 
                               id="quantity" 
                               value="1" 
                               min="1" 
                               max="<?php echo $product['stock_quantity']; ?>"
                               onchange="validateQuantity(this)"
                               class="quantity-input">
                        <button type="button" onclick="updateQuantity('increase')" class="quantity-btn">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <div class="product-actions">
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $product_id; ?>)">
                        <i class="fas fa-cart-plus"></i>
                        Thêm vào giỏ
                    </button>
                    <button class="buy-now-btn" onclick="buyNow(<?php echo $product_id; ?>)">
                        <i class="fas fa-bolt"></i>
                        Mua ngay
                    </button>
                </div>

                <div class="product-origin">
                    <i class="fas fa-map-marker-alt"></i>
                    Xuất xứ: <?php echo htmlspecialchars($product['origin'] ?: 'Đang cập nhật'); ?>
                </div>
            </div>
        </div>

        <!-- Product Description -->
        <div class="product-description">
            <h3>Mô tả sản phẩm</h3>
            <div class="description-content">
                <?php echo nl2br(htmlspecialchars($product['description'] ?: 'Chưa có mô tả chi tiết.')); ?>
            </div>
        </div>

        <!-- Reviews Section -->
        <div id="review-section" class="reviews-section">
            <div class="reviews-header">
                <h3>Đánh giá sản phẩm</h3>
                <div class="rating-summary">
                    <div class="average-rating">
                        <div class="big-rating"><?php echo number_format($review_stats['average_rating'], 1); ?></div>
                        <div class="rating-stars">
                            <?php
                            $rating = $review_stats['average_rating'];
                            $fullStars = floor($rating);
                            $halfStar = round($rating - $fullStars, 1) >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $fullStars) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($halfStar && $i == $fullStars + 1) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="total-reviews"><?php echo $review_stats['total_reviews']; ?> đánh giá</div>
                    </div>
                    
                    <div class="rating-bars">
                        <?php
                        // Calculate rating counts
                        $rating_counts = array_fill(1, 5, 0);
                        foreach ($recent_reviews as $review) {
                            $rating_counts[$review['rating']]++;
                        }
                        
                        // Display rating bars from 5 to 1 stars
                        for ($i = 5; $i >= 1; $i--):
                            $count = $rating_counts[$i];
                            $percentage = $review_stats['total_reviews'] > 0 ? ($count / $review_stats['total_reviews']) * 100 : 0;
                        ?>
                        <div class="rating-bar">
                            <div class="star-label">
                                <?php echo $i; ?> <i class="fas fa-star"></i>
                            </div>
                            <div class="progress-bar">
                                <div class="progress" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="review-count"><?php echo $count; ?></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <?php if ($review_stats['total_reviews'] > 0): ?>
                <div class="recent-reviews">
                    <?php foreach ($recent_reviews as $review): ?>
                        <div class="review-item">
                            <div class="review-header">
                                <div class="reviewer-info">
                                    <span class="reviewer-name"><?php echo htmlspecialchars($review['customer_name']); ?></span>
                                    <div class="review-rating">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $review['rating']) {
                                                echo '<i class="fas fa-star"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="review-date">
                                    <i class="far fa-clock"></i>
                                    <?php echo date('d/m/Y', strtotime($review['created_at'])); ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <?php if (!empty($review['comment'])): ?>
                                    <p class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-reviews">
                    <i class="far fa-comment-dots"></i>
                    <p>Chưa có đánh giá nào cho sản phẩm này</p>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user'])): ?>
                <?php
                // Check if user has purchased this product
                $check_purchase = $conn->prepare("
                    SELECT 1 
                    FROM orders o 
                    JOIN orderdetails od ON o.order_id = od.order_id 
                    WHERE o.customer_id = ? 
                    AND od.product_id = ? 
                    AND o.status = 'Completed'
                    LIMIT 1
                ");
                $check_purchase->bind_param("ii", $_SESSION['user']['customer_id'], $product_id);
                $check_purchase->execute();
                $has_purchased = $check_purchase->get_result()->num_rows > 0;
                $check_purchase->close();
                ?>

                <?php if ($has_purchased): ?>
                <div class="review-form-container">
                    <h3 class="review-form-title">
                        <?php echo $user_review ? 'Chỉnh sửa đánh giá của bạn' : 'Viết đánh giá của bạn'; ?>
                    </h3>
                    <div class="review-form">
                        <div class="rating-input">
                            <span class="rating-label">Đánh giá:</span>
                            <div class="star-rating" id="starRating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo ($user_review && $i <= $user_review['rating']) ? 'fas' : 'far'; ?> fa-star"
                                       data-rating="<?php echo $i; ?>"
                                       onmouseover="highlightStars(this)"
                                       onmouseout="resetStars()"
                                       onclick="setRating(this)"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <textarea id="reviewComment" 
                                  class="review-textarea" 
                                  placeholder="Chia sẻ trải nghiệm của bạn về sản phẩm..."><?php echo $user_review ? htmlspecialchars($user_review['comment']) : ''; ?></textarea>
                        <button type="button" class="submit-review" onclick="submitReview()">
                            <i class="fas fa-paper-plane"></i>
                            <?php echo $user_review ? 'Cập nhật đánh giá' : 'Gửi đánh giá'; ?>
                        </button>
                    </div>
                </div>
                <?php else: ?>
                <div class="review-notice">
                    <p><i class="fas fa-info-circle"></i> Bạn chỉ có thể đánh giá sản phẩm sau khi mua và nhận hàng thành công.</p>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // Function to show notification
    function showNotification(message, type = 'success') {
        const container = document.querySelector('.alert-container');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.setAttribute('role', 'alert');

        alert.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} ${type}-icon"></i>
            <div class="alert-message">${message}</div>
            <button type="button" class="alert-close" onclick="closeAlert(this.parentElement)">
                <i class="fas fa-times"></i>
            </button>
            <div class="alert-progress">
                <div class="alert-progress-bar"></div>
            </div>
        `;

        container.appendChild(alert);

        // Set up progress bar animation
        const progressBar = alert.querySelector('.alert-progress-bar');
        const duration = 5000; // 5 seconds

        // Start progress bar animation
        setTimeout(() => {
            progressBar.style.width = '100%';
            progressBar.style.transitionDuration = '0s';
            
            setTimeout(() => {
                progressBar.style.width = '0%';
                progressBar.style.transitionDuration = `${duration}ms`;
            }, 50);
        }, 0);

        // Auto close alert
        setTimeout(() => {
            closeAlert(alert);
        }, duration);
    }

    // Function to close notification
    function closeAlert(alert) {
        if (alert) {
            alert.classList.add('fade-out');
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.remove();
                }
            }, 300);
        }
    }

    // Initialize alerts on page load
    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('.alert');
        const duration = 5000;

        alerts.forEach(alert => {
            const progressBar = alert.querySelector('.alert-progress-bar');
            if (progressBar) {
                // Reset and start progress bar animation
                progressBar.style.width = '100%';
                progressBar.style.transitionDuration = '0s';
                
                setTimeout(() => {
                    progressBar.style.width = '0%';
                    progressBar.style.transitionDuration = `${duration}ms`;
                }, 50);

                // Auto close alert
                setTimeout(() => {
                    closeAlert(alert);
                }, duration);
            }
        });
    });

    // Quantity control functions
    function updateQuantity(action) {
        const input = document.getElementById('quantity');
        const currentValue = parseInt(input.value);
        const maxValue = parseInt(input.getAttribute('max'));

        if (action === 'increase' && currentValue < maxValue) {
            input.value = currentValue + 1;
        } else if (action === 'decrease' && currentValue > 1) {
            input.value = currentValue - 1;
        }
        validateQuantity(input);
    }

    function validateQuantity(input) {
        const value = parseInt(input.value);
        const max = parseInt(input.getAttribute('max'));
        
        if (isNaN(value) || value < 1) {
            input.value = 1;
        } else if (value > max) {
            input.value = max;
            showNotification('Số lượng đã vượt quá số lượng có sẵn!', 'error');
        }
    }

    // Handle wishlist toggle
    function toggleWishlist(productId) {
        <?php if (!isset($_SESSION['user'])): ?>
            window.location.href = '../auth/auth.php';
            return;
        <?php endif; ?>

        const btn = document.querySelector('.wishlist-btn');
        const icon = btn.querySelector('i');
        const isActive = btn.classList.contains('active');
        
        fetch(isActive ? 'remove_from_wishlist.php' : 'add_to_wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                btn.classList.toggle('active');
                icon.className = isActive ? 'far fa-heart' : 'fas fa-heart';
                showNotification(data.message);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Đã xảy ra lỗi. Vui lòng thử lại!', 'error');
        });
    }

    function addToCart(productId) {
        <?php if (!isset($_SESSION['user'])): ?>
            window.location.href = '../auth/auth.php';
            return;
        <?php endif; ?>

        const quantity = document.getElementById('quantity').value;
        
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message);
                // Update cart count in header if needed
                if (typeof updateCartCount === 'function') {
                    updateCartCount(data.cart_count);
                }
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Đã xảy ra lỗi. Vui lòng thử lại!', 'error');
        });
    }

    function buyNow(productId) {
        <?php if (!isset($_SESSION['user'])): ?>
            window.location.href = '../auth/auth.php';
            return;
        <?php endif; ?>

        const quantity = document.getElementById('quantity').value;
        
        // First add to cart
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=${productId}&quantity=${quantity}&buy_now=1`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to checkout
                window.location.href = 'thanh-toan.php';
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Đã xảy ra lỗi. Vui lòng thử lại!', 'error');
        });
    }

    // Review functionality
    let selectedRating = <?php echo $user_review ? $user_review['rating'] : 0; ?>;
    const starRating = document.getElementById('starRating');

    function highlightStars(star) {
        const rating = parseInt(star.dataset.rating);
        const stars = starRating.querySelectorAll('i');
        stars.forEach(s => {
            if (parseInt(s.dataset.rating) <= rating) {
                s.classList.remove('far');
                s.classList.add('fas', 'hover');
            } else {
                s.classList.remove('fas', 'hover');
                s.classList.add('far');
            }
        });
    }

    function resetStars() {
        const stars = starRating.querySelectorAll('i');
        stars.forEach(star => {
            const rating = parseInt(star.dataset.rating);
            if (rating <= selectedRating) {
                star.classList.remove('far');
                star.classList.add('fas', 'active');
            } else {
                star.classList.remove('fas', 'active', 'hover');
                star.classList.add('far');
            }
        });
    }

    function setRating(star) {
        selectedRating = parseInt(star.dataset.rating);
        resetStars();
    }

    function submitReview() {
        if (!selectedRating) {
            showNotification('Vui lòng chọn số sao đánh giá', 'error');
            return;
        }

        const comment = document.getElementById('reviewComment').value.trim();
        if (!comment) {
            showNotification('Vui lòng nhập nội dung đánh giá', 'error');
            return;
        }

        fetch('process_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `product_id=<?php echo $product_id; ?>&rating=${selectedRating}&comment=${encodeURIComponent(comment)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification(data.message);
                if (data.stats) {
                    updateReviewStats(data.stats);
                }
                // Reload page after a short delay to show updated reviews
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showNotification(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
        });
    }

    function updateReviewStats(stats) {
        // Update average rating
        document.querySelector('.rating-value').textContent = parseFloat(stats.average_rating).toFixed(1);
        
        // Update total reviews
        document.querySelector('.total-reviews').textContent = `${stats.total_reviews} đánh giá`;
        
        // Update rating bars
        const bars = document.querySelectorAll('.rating-bar');
        const starKeys = ['five_star', 'four_star', 'three_star', 'two_star', 'one_star'];
        bars.forEach((bar, index) => {
            const key = starKeys[index];
            const count = stats[key] || 0;
            const percent = (stats.total_reviews > 0) ? (count / stats.total_reviews * 100) : 0;
            bar.querySelector('.progress').style.width = `${percent}%`;
            bar.querySelector('.review-count').textContent = count;
        });
    }
    </script>
</body>
</html>