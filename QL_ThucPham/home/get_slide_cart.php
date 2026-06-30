<?php
session_start();
require_once('../config/db_connect.php');

header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['user'])) {
    echo '<div class="slide-cart-content"><div class="slide-cart-empty">Vui lòng đăng nhập để xem giỏ hàng.</div></div>';
    echo '<div class="slide-cart-footer"></div>';
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];
$sql = "SELECT c.*, p.name, p.price, p.image_url FROM carts c JOIN products p ON c.product_id = p.product_id WHERE c.customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($cart_items)) {
    echo '<div class="slide-cart-content"><div class="slide-cart-empty">Giỏ hàng của bạn đang trống.</div></div>';
    echo '<div class="slide-cart-footer"></div>';
    exit;
}

$total = 0;
echo '<div class="slide-cart-content">';
foreach ($cart_items as $item) {
    $img = !empty($item['image_url']) ? htmlspecialchars($item['image_url']) : '../images/Logo.png';
    $name = htmlspecialchars($item['name']);
    $price = number_format($item['price'], 0, ',', '.');
    $qty = intval($item['quantity']);
    $subtotal = $item['price'] * $qty;
    $total += $subtotal;
    echo '<div class="slide-cart-product">';
    echo '<img class="slide-cart-product-img" src="' . $img . '" alt="' . $name . '">';
    echo '<div class="slide-cart-product-info">';
    echo '<div class="slide-cart-product-title">' . $name . '</div>';
    echo '<div class="slide-cart-product-price">' . $price . 'đ <span class="slide-cart-product-qty">' . $qty . '</span></div>';
    echo '</div>';
    echo '<button class="slide-cart-product-remove" title="Xóa sản phẩm">&times;</button>';
    echo '</div>';
}
echo '</div>';
echo '<div class="slide-cart-footer">';
echo '<div class="slide-cart-total">Tổng cộng: ' . number_format($total, 0, ',', '.') . 'đ</div>';
echo '<a href="thanh-toan.php" class="slide-cart-checkout-btn">Thanh toán</a>';
echo '</div>'; 