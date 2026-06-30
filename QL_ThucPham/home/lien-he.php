<?php
session_start();
require_once('../config/db_connect.php');

// Kiểm tra và lấy thông tin người dùng nếu đã đăng nhập
$user_info = [
    'name' => '',
    'phone_number' => '',
    'email' => '',
    'address' => ''
];

if (isset($_SESSION['user'])) {
    $user_info = [
        'name' => $_SESSION['user']['name'] ?? '',
        'phone_number' => $_SESSION['user']['phone_number'] ?? '',
        'email' => $_SESSION['user']['email'] ?? '',
        'address' => $_SESSION['user']['address'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/lien-he.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="script.js" defer></script>
    <style>
        /* Reset notification styles */
        #notificationContainer * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 350px;
            pointer-events: none;
        }

        .notification {
            background: #ffffff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: flex-start;
            gap: 12px;
            pointer-events: auto;
            border-left: 4px solid #4CAF50;
            margin-bottom: 10px;
            transform: translateX(120%);
            animation: slideIn 0.5s forwards;
        }

        .notification.error {
            border-left-color: #f44336;
        }

        .notification i {
            font-size: 20px;
            color: #4CAF50;
            margin-top: 2px;
        }

        .notification.error i {
            color: #f44336;
        }

        .notification span {
            flex: 1;
            color: #333;
            font-size: 14px;
            line-height: 1.4;
            font-weight: 500;
        }

        .notification .close-btn {
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin-left: 8px;
            font-size: 18px;
        }

        .notification .close-btn:hover {
            color: #333;
            transform: scale(1.1);
        }

        .notification-progress {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 0 0 4px 4px;
            overflow: hidden;
        }

        .notification-progress-bar {
            height: 100%;
            background: #4CAF50;
            width: 100%;
            transition: width 5s linear;
        }

        .notification.error .notification-progress-bar {
            background: #f44336;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(120%);
                opacity: 0;
            }
        }

        .notification.fade-out {
            animation: slideOut 0.3s forwards;
        }
    </style>
</head>
<body>
    <?php include 'slide-menu.php'; ?>

    <!-- Notification Container -->
    <div id="notificationContainer" class="notification-container"></div>

    <!-- Contact Section -->
    <section class="contact-section">
        <h2>Liên hệ với chúng tôi</h2>
        
        <div class="support-form">
            <h3><i class="fas fa-headset"></i> Gửi Đơn Tư Vấn Hỗ Trợ</h3>
            <p class="form-description">Hãy để lại thông tin của bạn, chúng tôi sẽ liên hệ trong thời gian sớm nhất!</p>
            
            <form id="supportForm" autocomplete="on">
                <?php if (isset($_SESSION['user'])): ?>
                    <input type="hidden" name="customer_id" value="<?php echo $_SESSION['user']['customer_id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="name"><i class="fas fa-user"></i> Họ tên:</label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="<?php echo htmlspecialchars($user_info['name']); ?>" 
                               required
                               autocomplete="name">
                    </div>
                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Số điện thoại:</label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo htmlspecialchars($user_info['phone_number']); ?>" 
                               required
                               autocomplete="tel"
                               pattern="(0|\+84)(\s|\.)?((3[2-9])|(5[689])|(7[06-9])|(8[1-689])|(9[0-46-9]))(\d)(\s|\.)?(\d{3})(\s|\.)?(\d{3})"
                               title="Vui lòng nhập số điện thoại hợp lệ (VD: 0912345678)">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email:</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="<?php echo htmlspecialchars($user_info['email']); ?>" 
                               required
                               autocomplete="email">
                    </div>
                    <div class="form-group">
                        <label for="address"><i class="fas fa-map-marker-alt"></i> Địa chỉ:</label>
                        <input type="text" 
                               id="address" 
                               name="address" 
                               value="<?php echo htmlspecialchars($user_info['address']); ?>" 
                               required
                               autocomplete="street-address">
                    </div>
                </div>
                <div class="form-group full-width">
                    <label for="message"><i class="fas fa-comment-alt"></i> Nội dung cần hỗ trợ:</label>
                    <textarea id="message" 
                              name="message" 
                              required 
                              placeholder="Hãy mô tả chi tiết vấn đề bạn cần được hỗ trợ..."
                              autocomplete="off"></textarea>
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Gửi Yêu Cầu
                </button>
            </form>
        </div>

        <div class="contact-info">
            <div class="contact-item">
                <i class="fas fa-envelope"></i>
                <h3>Email</h3>
                <p>info@thucphammoc.com</p>
                <p>support@thucphammoc.com</p>
            </div>
            <div class="contact-item">
                <i class="fab fa-facebook-messenger"></i>
                <h3>Zalo & Messenger</h3>
                <p>Zalo: 0852225XXX</p>
                <p>FB: Thực phẩm mộc</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-phone"></i>
                <h3>Điện thoại</h3>
                <p>Hotline: 0852225XXX</p>
                <p>Tel: 028.3XXX.XXX</p>
            </div>
            <div class="contact-item">
                <i class="fas fa-map-marker-alt"></i>
                <h3>Địa chỉ</h3>
                <p>123 Đường ABC, Phường XYZ</p>
                <p>Quận 1, TP. Hồ Chí Minh</p>
            </div>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
    function showNotification(message, type = 'success') {
        const container = document.getElementById('notificationContainer');
        if (!container) {
            console.error('Notification container not found');
            return;
        }

        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        
        // Create progress bar
        const progress = document.createElement('div');
        progress.className = 'notification-progress';
        const progressBar = document.createElement('div');
        progressBar.className = 'notification-progress-bar';
        progress.appendChild(progressBar);
        
        // Set notification content
        notification.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
            <button class="close-btn" onclick="removeNotification(this.parentElement)">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        // Add progress bar
        notification.appendChild(progress);
        
        // Add to container
        container.appendChild(notification);

        // Force reflow to trigger animation
        notification.offsetHeight;

        // Start progress bar animation
        requestAnimationFrame(() => {
            progressBar.style.width = '0';
        });

        // Auto remove after 5 seconds
        const timeout = setTimeout(() => {
            removeNotification(notification);
        }, 5000);

        // Store timeout ID on the element
        notification.dataset.timeoutId = timeout;

        // Limit number of notifications
        const maxNotifications = 3;
        while (container.children.length > maxNotifications) {
            const firstNotification = container.firstChild;
            // Clear timeout of removed notification
            if (firstNotification.dataset.timeoutId) {
                clearTimeout(Number(firstNotification.dataset.timeoutId));
            }
            container.removeChild(firstNotification);
        }
    }

    function removeNotification(notification) {
        if (!notification) return;

        // Clear timeout if exists
        if (notification.dataset.timeoutId) {
            clearTimeout(Number(notification.dataset.timeoutId));
        }
        
        notification.classList.add('fade-out');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
            }, 300);
    }

    document.getElementById('supportForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Disable submit button
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
        
        try {
        // Get form data
        const formData = new FormData(this);
        
        // Send form data using fetch
            const response = await fetch('process_support.php', {
            method: 'POST',
            body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Server response:', data); // Debug log

            if (data && data.success) {
                showNotification(data.message || 'Gửi yêu cầu thành công!', 'success');
                this.reset();
            } else {
                showNotification(data.message || 'Có lỗi xảy ra khi gửi yêu cầu.', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('Không thể kết nối đến máy chủ. Vui lòng thử lại sau.', 'error');
        } finally {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Gửi Yêu Cầu';
        }
    });

    // Debug: Test notification system
    window.addEventListener('load', () => {
        console.log('Page loaded, notification container:', document.getElementById('notificationContainer'));
    });
    </script>
</body>
</html> 