<?php
session_start();
require_once('../config/db_connect.php');

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/auth.php');
    exit;
}

$customer_id = $_SESSION['user']['customer_id'];

// Fetch customer's available discount codes
$customer_codes_sql = "
    SELECT d.*, cdc.collected_at, cdc.expiry_status,
           CASE WHEN cdu.usage_id IS NOT NULL THEN 1 ELSE 0 END as is_used
    FROM discount_codes d
    INNER JOIN customer_discount_codes cdc ON d.code_id = cdc.code_id
    LEFT JOIN customer_discount_usage cdu 
        ON d.code_id = cdu.code_id 
        AND cdu.customer_id = ?
    WHERE cdc.customer_id = ? 
    AND d.end_date >= CURRENT_DATE
    ORDER BY cdc.expiry_status ASC, d.end_date ASC";
$stmt = $conn->prepare($customer_codes_sql);
$stmt->bind_param("ii", $customer_id, $customer_id);
$stmt->execute();
$customer_codes = $stmt->get_result();

// Fetch collectible discount codes
$collectible_codes_sql = "
    SELECT d.*, 
           (d.usage_limit - d.times_used) as remaining_uses,
           CASE WHEN cdc.collection_id IS NOT NULL THEN 1 ELSE 0 END as is_collected
    FROM discount_codes d
    LEFT JOIN customer_discount_codes cdc 
        ON d.code_id = cdc.code_id AND cdc.customer_id = ?
    WHERE d.is_active = 1 
    AND d.end_date >= CURRENT_DATE
    AND (d.usage_limit = 0 OR d.usage_limit > d.times_used)
    ORDER BY d.end_date ASC";
$stmt = $conn->prepare($collectible_codes_sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$collectible_codes = $stmt->get_result();

// Handle code collection
if (isset($_POST['collect_code']) && isset($_POST['code_id'])) {
    $code_id = $_POST['code_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if customer has already collected this code
        $check_collected_sql = "SELECT collection_id FROM customer_discount_codes 
                              WHERE customer_id = ? AND code_id = ?";
        $check_collected_stmt = $conn->prepare($check_collected_sql);
        $check_collected_stmt->bind_param("ii", $customer_id, $code_id);
        $check_collected_stmt->execute();
        $check_collected_result = $check_collected_stmt->get_result();
        
        if ($check_collected_result->num_rows > 0) {
            throw new Exception("Bạn đã thu thập mã giảm giá này rồi!");
        }

        // Check if code is still available
        $check_sql = "SELECT usage_limit, times_used, end_date 
                     FROM discount_codes 
                     WHERE code_id = ? AND is_active = 1 
                     AND end_date >= CURRENT_DATE FOR UPDATE";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $code_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($row = $check_result->fetch_assoc()) {
            if ($row['usage_limit'] == 0 || $row['times_used'] < $row['usage_limit']) {
                // Insert collection record
                $insert_sql = "INSERT INTO customer_discount_codes 
                             (customer_id, code_id, expiry_status, expiry_date) 
                             VALUES (?, ?, 'active', ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iis", $customer_id, $code_id, $row['end_date']);
                $insert_stmt->execute();
                
                $conn->commit();
                $success_message = "Đã thu thập mã giảm giá thành công!";
            } else {
                throw new Exception("Mã giảm giá đã hết lượt sử dụng!");
            }
        } else {
            throw new Exception("Mã giảm giá không hợp lệ!");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
    
    // Refresh the page to update the lists
    header("Location: ma-giam-gia.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã giảm giá - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/discount-codes.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Notification Element -->
    <div id="notification" class="notification">
        <i class="fas"></i>
        <span id="notification-message"></span>
    </div>

    <?php include 'slide-menu.php'; ?>

    <!-- Main Content -->
    <div class="discount-codes-container">
        <!-- My Discount Codes Section -->
        <div class="discount-section">
            <div class="section-header">
                <h2>Kho mã giảm giá của tôi</h2>
                <p>Những mã giảm giá bạn đã thu thập được</p>
            </div>
            <div class="discount-list">
                <?php if ($customer_codes->num_rows > 0): ?>
                    <?php while ($code = $customer_codes->fetch_assoc()): ?>
                        <div class="discount-card">
                            <div class="discount-code"><?php echo htmlspecialchars($code['code']); ?></div>
                            <div class="discount-value">
                                Giảm <?php echo $code['discount_type'] == 'percentage' ? 
                                              htmlspecialchars($code['discount_value']) . '%' : 
                                              number_format($code['discount_value'], 0, ',', '.') . 'đ'; ?>
                            </div>
                            <div class="discount-details">
                                <p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($code['description']); ?></p>
                                <p><i class="fas fa-tag"></i> Đơn tối thiểu: <?php echo number_format($code['min_order_value'], 0, ',', '.'); ?>đ</p>
                                <p><i class="fas fa-clock"></i> Thu thập: <?php echo date('d/m/Y', strtotime($code['collected_at'])); ?></p>
                                <p class="expiry-date"><i class="fas fa-calendar-times"></i> Hết hạn: <?php echo date('d/m/Y', strtotime($code['end_date'])); ?></p>
                                <?php if ($code['is_used']): ?>
                                    <p class="status used"><i class="fas fa-check-circle"></i> Đã sử dụng</p>
                                <?php else: ?>
                                    <p class="status available"><i class="fas fa-circle"></i> Chưa sử dụng</p>
                                <?php endif; ?>
                            </div>
                            <button class="action-btn copy-btn" onclick="copyDiscountCode('<?php echo htmlspecialchars($code['code']); ?>')"
                                    <?php echo $code['is_used'] ? 'disabled' : ''; ?>>
                                <i class="fas fa-copy"></i> Sao chép mã
                            </button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-discounts">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Bạn chưa có mã giảm giá nào trong kho.</p>
                        <p>Hãy thu thập mã giảm giá bên dưới!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Available Discount Codes Section -->
        <div class="discount-section">
            <div class="section-header">
                <h2>Thu thập Mã giảm giá</h2>
                <p>Khám phá và thu thập các mã giảm giá mới</p>
            </div>
            <div class="discount-list">
                <?php if ($collectible_codes->num_rows > 0): ?>
                    <?php while ($code = $collectible_codes->fetch_assoc()): ?>
                        <div class="discount-card">
                            <div class="remaining-uses">
                                Còn <?php echo $code['remaining_uses']; ?> lượt
                            </div>
                            <div class="discount-code"><?php echo htmlspecialchars($code['code']); ?></div>
                            <div class="discount-value">
                                Giảm <?php echo $code['discount_type'] == 'percentage' ? 
                                              htmlspecialchars($code['discount_value']) . '%' : 
                                              number_format($code['discount_value'], 0, ',', '.') . 'đ'; ?>
                            </div>
                            <div class="discount-details">
                                <p><i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($code['description']); ?></p>
                                <p><i class="fas fa-tag"></i> Đơn tối thiểu: <?php echo number_format($code['min_order_value'], 0, ',', '.'); ?>đ</p>
                                <p class="expiry-date"><i class="fas fa-calendar-times"></i> Hết hạn: <?php echo date('d/m/Y', strtotime($code['end_date'])); ?></p>
                            </div>
                            <?php if (!$code['is_collected']): ?>
                                <form method="POST">
                                    <input type="hidden" name="code_id" value="<?php echo $code['code_id']; ?>">
                                    <button type="submit" name="collect_code" class="action-btn collect-btn">
                                        <i class="fas fa-plus-circle"></i> Thu thập mã
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="action-btn collect-btn" disabled>
                                    <i class="fas fa-check-circle"></i> Đã thu thập
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-discounts">
                        <i class="fas fa-ticket-alt"></i>
                        <p>Hiện không có mã giảm giá nào để thu thập.</p>
                        <p>Vui lòng quay lại sau!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    function showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const notificationMessage = document.getElementById('notification-message');
        const icon = notification.querySelector('i');
        
        // Set icon and class based on type
        if (type === 'success') {
            notification.className = 'notification success';
            icon.className = 'fas fa-check-circle';
        } else {
            notification.className = 'notification error';
            icon.className = 'fas fa-exclamation-circle';
        }
        
        // Set message
        notificationMessage.textContent = message;
        
        // Show notification
        setTimeout(() => notification.classList.add('show'), 100);
        
        // Hide notification after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    function copyDiscountCode(code) {
        navigator.clipboard.writeText(code).then(() => {
            showNotification('Đã sao chép mã: ' + code);
        }).catch(err => {
            console.error('Không thể sao chép mã: ', err);
            showNotification('Không thể sao chép mã. Vui lòng thử lại.', 'error');
        });
    }

    // Show notification if there's a message from PHP
    <?php if (isset($success_message)): ?>
        showNotification(<?php echo json_encode($success_message); ?>);
    <?php endif; ?>
    <?php if (isset($error_message)): ?>
        showNotification(<?php echo json_encode($error_message); ?>, 'error');
    <?php endif; ?>
    </script>
</body>
</html> 