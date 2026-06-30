<?php
session_start();
require_once('../config/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header('Location: ../Login_Register/login.php');
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];

// Fetch customer details
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
}

$stmt->bind_param("i", $customer_id);
if (!$stmt->execute()) {
    die("Lỗi thực thi câu lệnh: " . $stmt->error);
}

$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch cart items with product details
$sql = "SELECT c.*, p.name, p.price, p.image_url, p.stock_quantity 
        FROM carts c 
        JOIN products p ON c.product_id = p.product_id 
        WHERE c.customer_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
}

$stmt->bind_param("i", $customer_id);
if (!$stmt->execute()) {
    die("Lỗi thực thi câu lệnh: " . $stmt->error);
}

$result = $stmt->get_result();
$cart_items = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Fetch customer's unused discount codes
$discount_codes_sql = "
    SELECT 
        d.*, 
        cdc.expiry_status,
        cdc.expiry_date,
        CASE 
            WHEN cdu.usage_id IS NOT NULL THEN 'Đã sử dụng'
            WHEN d.end_date < CURRENT_DATE THEN 'Hết hạn'
            ELSE 'Chưa sử dụng'
        END as usage_status
    FROM discount_codes d
    INNER JOIN customer_discount_codes cdc ON d.code_id = cdc.code_id
    LEFT JOIN customer_discount_usage cdu ON d.code_id = cdu.code_id AND cdu.customer_id = cdc.customer_id
    WHERE cdc.customer_id = ?
        AND d.is_active = 1 
        AND d.end_date >= CURRENT_DATE
        AND cdc.expiry_status = 'active'
        AND NOT EXISTS (
            SELECT 1 
            FROM customer_discount_usage cdu2 
            WHERE cdu2.code_id = d.code_id 
            AND cdu2.customer_id = cdc.customer_id
            AND (cdu2.status = 'completed' OR cdu2.status = 'pending')
        )
    ORDER BY d.end_date ASC";

$stmt = $conn->prepare($discount_codes_sql);
if (!$stmt) {
    die("Lỗi chuẩn bị câu lệnh: " . $conn->error);
}

$stmt->bind_param("i", $customer_id);
if (!$stmt->execute()) {
    die("Lỗi thực thi câu lệnh: " . $stmt->error);
}

$available_discounts = $stmt->get_result();
$stmt->close();

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $shipping_address = $_POST['shipping_address'];
    $payment_method = $_POST['payment_method'];
    $total_amount = $subtotal;
    $applied_discount_id = isset($_POST['applied_discount_id']) ? intval($_POST['applied_discount_id']) : null;
    $applied_discount_amount = isset($_POST['applied_discount_amount']) ? floatval($_POST['applied_discount_amount']) : 0;

    // Start transaction
    $conn->begin_transaction();

    try {
        // Insert order
        $order_sql = "INSERT INTO orders (customer_id, shipping_address, total_amount, payment_method, discount_amount, status) 
                      VALUES (?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($order_sql);
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị câu lệnh đơn hàng: " . $conn->error);
        }

        $stmt->bind_param("issis", $customer_id, $shipping_address, $total_amount, $payment_method, $applied_discount_amount);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi thêm đơn hàng: " . $stmt->error);
        }
        $order_id = $conn->insert_id;
        $stmt->close();

        // Insert order details
        foreach ($cart_items as $item) {
            $detail_sql = "INSERT INTO orderdetails (order_id, product_id, quantity, unit_price, subtotal) 
                          VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($detail_sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh chi tiết đơn hàng: " . $conn->error);
            }

            $subtotal_item = $item['price'] * $item['quantity'];
            $stmt->bind_param("iiiii", $order_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal_item);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi thêm chi tiết đơn hàng: " . $stmt->error);
            }
            $stmt->close();

            // Update product stock
            $update_stock_sql = "UPDATE products 
                               SET stock_quantity = stock_quantity - ?,
                                   sold_quantity = sold_quantity + ?
                               WHERE product_id = ?";
            $stmt = $conn->prepare($update_stock_sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh cập nhật kho: " . $conn->error);
            }

            $stmt->bind_param("iii", $item['quantity'], $item['quantity'], $item['product_id']);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi cập nhật kho: " . $stmt->error);
            }
            $stmt->close();
        }

        // If discount code was used, update its status
        if ($applied_discount_id) {
            // Insert into customer_discount_usage
            $usage_sql = "INSERT INTO customer_discount_usage (customer_id, code_id, order_id, status) 
                         VALUES (?, ?, ?, 'pending')";
            $stmt = $conn->prepare($usage_sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh cập nhật mã giảm giá: " . $conn->error);
            }

            $stmt->bind_param("iii", $customer_id, $applied_discount_id, $order_id);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi cập nhật sử dụng mã giảm giá: " . $stmt->error);
            }
            $stmt->close();

            // Update customer_discount_codes status
            $update_discount_sql = "UPDATE customer_discount_codes 
                                  SET expiry_status = 'used' 
                                  WHERE customer_id = ? AND code_id = ?";
            $stmt = $conn->prepare($update_discount_sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh cập nhật trạng thái mã giảm giá: " . $conn->error);
            }

            $stmt->bind_param("ii", $customer_id, $applied_discount_id);
            if (!$stmt->execute()) {
                throw new Exception("Lỗi cập nhật trạng thái mã giảm giá: " . $stmt->error);
            }
            $stmt->close();
        }

        // Clear cart
        $clear_cart_sql = "DELETE FROM carts WHERE customer_id = ?";
        $stmt = $conn->prepare($clear_cart_sql);
        if (!$stmt) {
            throw new Exception("Lỗi chuẩn bị câu lệnh xóa giỏ hàng: " . $conn->error);
        }

        $stmt->bind_param("i", $customer_id);
        if (!$stmt->execute()) {
            throw new Exception("Lỗi xóa giỏ hàng: " . $stmt->error);
        }
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Redirect to success page
        header("Location: thanh-toan-thanh-cong.php?order_id=" . $order_id);
        exit;

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = $e->getMessage();
        echo "<script>alert('Có lỗi xảy ra: " . addslashes($error_message) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực phẩm Mộc - Thanh toán</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/checkout.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <div class="checkout-container">
        <!-- Customer Information Form -->
        <div class="checkout-form">
            <h2 class="section-title"><i class="fas fa-user"></i> Thông tin đặt hàng</h2>
            <form id="customer-info-form">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Họ tên:</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Số điện thoại:</label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone_number']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-globe"></i> Quốc gia:</label>
                    <select name="country" id="country" required>
                        <option value="vietnam" selected>Việt Nam</option>
                        <option value="other" disabled>Quốc gia khác</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-city"></i> Tỉnh/Thành phố:</label>
                    <select name="city" id="city" required>
                        <option value="">Chọn Tỉnh/Thành phố</option>
                        <option value="hanoi">Hà Nội</option>
                        <option value="hochiminh">TP. Hồ Chí Minh</option>
                        <option value="danang">Đà Nẵng</option>
                        <option value="haiphong">Hải Phòng</option>
                        <option value="cantho">Cần Thơ</option>
                        <option value="angiang">An Giang</option>
                        <option value="bacgiang">Bắc Giang</option>
                        <option value="backan">Bắc Kạn</option>
                        <option value="baclieu">Bạc Liêu</option>
                        <option value="bacninh">Bắc Ninh</option>
                        <option value="bentre">Bến Tre</option>
                        <option value="binhdinh">Bình Định</option>
                        <option value="binhduong">Bình Dương</option>
                        <option value="binhphuoc">Bình Phước</option>
                        <option value="binhthuan">Bình Thuận</option>
                        <option value="camau">Cà Mau</option>
                        <option value="caobang">Cao Bằng</option>
                        <option value="daklak">Đắk Lắk</option>
                        <option value="daknong">Đắk Nông</option>
                        <option value="dienbien">Điện Biên</option>
                        <option value="dongnai">Đồng Nai</option>
                        <option value="dongthap">Đồng Tháp</option>
                        <option value="gialai">Gia Lai</option>
                        <option value="hagiang">Hà Giang</option>
                        <option value="hanam">Hà Nam</option>
                        <option value="hatinh">Hà Tĩnh</option>
                        <option value="haiduong">Hải Dương</option>
                        <option value="haugiang">Hậu Giang</option>
                        <option value="hoabinh">Hòa Bình</option>
                        <option value="hungyen">Hưng Yên</option>
                        <option value="khanhhoa">Khánh Hòa</option>
                        <option value="kiengiang">Kiên Giang</option>
                        <option value="kontum">Kon Tum</option>
                        <option value="laichau">Lai Châu</option>
                        <option value="lamdong">Lâm Đồng</option>
                        <option value="langson">Lạng Sơn</option>
                        <option value="laocai">Lào Cai</option>
                        <option value="longan">Long An</option>
                        <option value="namdinh">Nam Định</option>
                        <option value="nghean">Nghệ An</option>
                        <option value="ninhbinh">Ninh Bình</option>
                        <option value="ninhthuan">Ninh Thuận</option>
                        <option value="phutho">Phú Thọ</option>
                        <option value="phuyen">Phú Yên</option>
                        <option value="quangbinh">Quảng Bình</option>
                        <option value="quangnam">Quảng Nam</option>
                        <option value="quangngai">Quảng Ngãi</option>
                        <option value="quangninh">Quảng Ninh</option>
                        <option value="quangtri">Quảng Trị</option>
                        <option value="soctrang">Sóc Trăng</option>
                        <option value="sonla">Sơn La</option>
                        <option value="tayninh">Tây Ninh</option>
                        <option value="thaibinh">Thái Bình</option>
                        <option value="thainguyen">Thái Nguyên</option>
                        <option value="thanhhoa">Thanh Hóa</option>
                        <option value="thuathienhue">Thừa Thiên Huế</option>
                        <option value="tiengiang">Tiền Giang</option>
                        <option value="travinh">Trà Vinh</option>
                        <option value="tuyenquang">Tuyên Quang</option>
                        <option value="vinhlong">Vĩnh Long</option>
                        <option value="vinhphuc">Vĩnh Phúc</option>
                        <option value="yenbai">Yên Bái</option>
                    </select>
                </div>
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Địa chỉ cụ thể:</label>
                    <textarea name="address" id="address" required placeholder="Số nhà, tên đường, phường/xã, quận/huyện"><?php 
                        $address = htmlspecialchars($customer['address']); 
                        echo $address;
                    ?></textarea>
                    <input type="hidden" name="shipping_address" id="shipping_address">
                </div>
                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave"></i> Phương thức thanh toán:</label>
                    <select name="payment_method" required>
                        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
                        <option value="bank">Chuyển khoản ngân hàng</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary">
            <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Thông tin đơn hàng</h2>
            
            <!-- Cart Items -->
            <div class="cart-items">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" width="50">
                    <div class="item-details">
                        <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p>Số lượng: <?php echo $item['quantity']; ?></p>
                        <p>Giá: <?php echo number_format($item['price'], 0, ',', '.'); ?>đ</p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Discount Codes -->
            <?php if ($available_discounts && $available_discounts->num_rows > 0): ?>
            <div class="discount-section">
                <h3 class="section-title"><i class="fas fa-ticket-alt"></i> Mã giảm giá</h3>
                <div class="discount-options">
                    <?php while ($discount = $available_discounts->fetch_assoc()): ?>
                    <div class="discount-item">
                        <input type="radio" name="discount_code" id="discount_<?php echo $discount['code_id']; ?>" 
                               value="<?php echo $discount['code_id']; ?>" 
                               data-type="<?php echo $discount['discount_type']; ?>"
                               data-value="<?php echo $discount['discount_value']; ?>"
                               data-min="<?php echo $discount['min_order_value']; ?>"
                               data-max="<?php echo $discount['max_discount']; ?>">
                        <label for="discount_<?php echo $discount['code_id']; ?>">
                            <div class="discount-info">
                                <div class="code"><?php echo htmlspecialchars($discount['code']); ?></div>
                                <div class="value">
                                    <?php if ($discount['discount_type'] == 'percentage'): ?>
                                        Giảm <?php echo $discount['discount_value']; ?>%
                                    <?php else: ?>
                                        Giảm <?php echo number_format($discount['discount_value'], 0, ',', '.'); ?>đ
                                    <?php endif; ?>
                                </div>
                                <div class="min-order">
                                    Đơn tối thiểu: <?php echo number_format($discount['min_order_value'], 0, ',', '.'); ?>đ
                                </div>
                                <div class="expiry">
                                    <i class="fas fa-clock"></i>
                                    HSD: <?php echo date('d/m/Y', strtotime($discount['end_date'])); ?>
                                </div>
                            </div>
                        </label>
                    </div>
                    <?php endwhile; ?>
                </div>
                <div class="discount-actions">
                    <button type="button" id="apply-discount" class="btn-apply-discount">
                        <i class="fas fa-check"></i> Áp dụng
                    </button>
                    <button type="button" id="cancel-discount" class="btn-cancel-discount" style="display: none;">
                        <i class="fas fa-times"></i> Hủy áp dụng
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <!-- Order Total -->
            <div class="order-total">
                <div class="total-row">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="total-row discount">
                    <span>Giảm giá:</span>
                    <span id="discount-amount">0đ</span>
                </div>
                <div class="total-row shipping">
                    <span>Phí vận chuyển:</span>
                    <span>Miễn phí</span>
                </div>
                <div class="total-row final">
                    <span>Tổng cộng:</span>
                    <span id="final-total"><?php echo number_format($subtotal, 0, ',', '.'); ?>đ</span>
                </div>
            </div>

            <!-- Place Order Button -->
            <button type="submit" class="btn-place-order" form="customer-info-form">
                <i class="fas fa-check"></i> Đặt hàng
            </button>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Variables to store discount state
        let appliedDiscount = null;
        const subtotal = <?php echo $subtotal; ?>;

        // Function to format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN').format(amount) + 'đ';
        }

        // Function to calculate discount
        function calculateDiscount(discountType, discountValue, minOrder, maxDiscount) {
            let discountAmount = 0;
            if (subtotal >= minOrder) {
                if (discountType === 'percentage') {
                    discountAmount = subtotal * (discountValue / 100);
                    if (maxDiscount && discountAmount > maxDiscount) {
                        discountAmount = maxDiscount;
                    }
                } else {
                    discountAmount = discountValue;
                }
            }
            return discountAmount;
        }

        // Function to update total
        function updateTotal(discountAmount = 0) {
            document.getElementById('discount-amount').textContent = formatCurrency(discountAmount);
            document.getElementById('final-total').textContent = formatCurrency(subtotal - discountAmount);
        }

        // Handle apply discount button
        document.getElementById('apply-discount')?.addEventListener('click', function() {
            const selectedDiscount = document.querySelector('input[name="discount_code"]:checked');
            if (!selectedDiscount) {
                alert('Vui lòng chọn một mã giảm giá');
                return;
            }

            const discountType = selectedDiscount.dataset.type;
            const discountValue = parseFloat(selectedDiscount.dataset.value);
            const minOrder = parseFloat(selectedDiscount.dataset.min);
            const maxDiscount = parseFloat(selectedDiscount.dataset.max);

            if (subtotal < minOrder) {
                alert(`Đơn hàng tối thiểu ${formatCurrency(minOrder)} để sử dụng mã giảm giá này`);
                return;
            }

            const discountAmount = calculateDiscount(discountType, discountValue, minOrder, maxDiscount);
            updateTotal(discountAmount);
            appliedDiscount = selectedDiscount;

            // Update UI
            this.style.display = 'none';
            document.getElementById('cancel-discount').style.display = 'block';
            
            // Disable radio buttons
            document.querySelectorAll('input[name="discount_code"]').forEach(input => {
                input.disabled = true;
            });
        });

        // Handle cancel discount button
        document.getElementById('cancel-discount')?.addEventListener('click', function() {
            updateTotal(0);
            appliedDiscount = null;

            // Update UI
            this.style.display = 'none';
            document.getElementById('apply-discount').style.display = 'block';
            
            // Enable radio buttons and uncheck selected
            document.querySelectorAll('input[name="discount_code"]').forEach(input => {
                input.disabled = false;
                input.checked = false;
            });
        });

        // Form submission handler
        document.getElementById('customer-info-form').addEventListener('submit', function(e) {
            e.preventDefault();

            // Validate form
            const city = document.getElementById('city');
            const address = document.getElementById('address');

            if (!city.value) {
                alert('Vui lòng chọn Tỉnh/Thành phố');
                city.focus();
                return;
            }

            if (!address.value.trim()) {
                alert('Vui lòng nhập địa chỉ cụ thể');
                address.focus();
                return;
            }

            // Combine address fields
            const country = document.getElementById('country');
            const cityText = city.options[city.selectedIndex].text;
            const fullAddress = `${address.value.trim()}, ${cityText}, ${country.value === 'vietnam' ? 'Việt Nam' : country.value}`;

            // Create form data
            const formData = new FormData();
            formData.append('shipping_address', fullAddress);
            formData.append('payment_method', this.payment_method.value);
            formData.append('place_order', '1');

            // Add discount code if applied
            if (appliedDiscount) {
                formData.append('applied_discount_id', appliedDiscount.value);
                const discountAmount = parseInt(document.getElementById('discount-amount').textContent.replace(/[^0-9]/g, ''));
                formData.append('applied_discount_amount', discountAmount);
            }

            // Show loading state
            const submitButton = document.querySelector('.btn-place-order');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            submitButton.disabled = true;

            // Submit the form
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (response.redirected) {
                    window.location.href = response.url;
                } else {
                    return response.text().then(text => {
                        throw new Error(text);
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi đặt hàng. Vui lòng thử lại.');
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        });

        // Auto-select city based on current address if possible
        window.addEventListener('load', function() {
            const citySelect = document.getElementById('city');
            const currentAddress = document.getElementById('address').value.toLowerCase();
            
            for (let option of citySelect.options) {
                if (currentAddress.includes(option.text.toLowerCase())) {
                    citySelect.value = option.value;
                    break;
                }
            }
        });
    </script>
</body>
</html> 