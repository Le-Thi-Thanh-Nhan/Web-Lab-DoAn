<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Nếu đã đăng nhập thì chuyển đến trang chủ
if (isset($_SESSION['user'])) {
    header('Location: ../home/index.php');
    exit;
}

// Xử lý đăng nhập AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    try {
        $conn = new mysqli('localhost', 'root', '', 'ql_thucpham');
        if ($conn->connect_error) {
            throw new Exception('Lỗi kết nối database');
        }
        $conn->set_charset("utf8mb4");

        if ($_POST['action'] === 'login') {
            // Xử lý đăng nhập
            if (empty($_POST['username']) || empty($_POST['password'])) {
                throw new Exception('Vui lòng nhập đầy đủ thông tin');
            }

            $username = trim($_POST['username']);
            $password = $_POST['password'];

            // Kiểm tra trong bảng administrators
            $stmt = $conn->prepare("SELECT * FROM administrators WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin && $password === $admin['password']) {
                // Đăng nhập thành công với tài khoản admin
                $_SESSION['admin'] = [
                    'admin_id' => $admin['admin_id'],
                    'username' => $admin['username'],
                    'name' => $admin['name'],
                    'email' => $admin['email']
                ];

                echo json_encode([
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'redirect' => '../admin/index.php'
                ]);
                exit;
            }

            // Nếu không phải admin, kiểm tra trong bảng customers
            $stmt = $conn->prepare("SELECT * FROM customers WHERE username = ? LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if (!$user || $password !== $user['password']) {
                throw new Exception('Tên đăng nhập hoặc mật khẩu không chính xác');
            }

            // Đăng nhập thành công với tài khoản customer
            $_SESSION['user'] = [
                'customer_id' => $user['customer_id'],
                'username' => $user['username'],
                'name' => $user['name'],
                'email' => $user['email']
            ];

            echo json_encode([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'redirect' => '../home/index.php'
            ]);
            exit;

        } elseif ($_POST['action'] === 'register') {
            // Xử lý đăng ký
            if (empty($_POST['username']) || empty($_POST['password']) || 
                empty($_POST['name']) || empty($_POST['email'])) {
                throw new Exception('Vui lòng điền đầy đủ thông tin');
            }

            // Kiểm tra username đã tồn tại
            $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE username = ?");
            $stmt->bind_param("s", $_POST['username']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Tên đăng nhập đã tồn tại');
            }

            // Kiểm tra email đã tồn tại
            $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE email = ?");
            $stmt->bind_param("s", $_POST['email']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception('Email đã được sử dụng');
            }

            // Thêm người dùng mới
            $stmt = $conn->prepare("INSERT INTO customers (username, password, name, email, phone_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", 
                $_POST['username'],
                $_POST['password'],
                $_POST['name'],
                $_POST['email'],
                $_POST['phone']
            );

            if (!$stmt->execute()) {
                throw new Exception('Lỗi khi đăng ký: ' . $stmt->error);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Đăng ký thành công! Vui lòng đăng nhập.',
                'redirect' => 'auth.php'
            ]);
            exit;
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập / Đăng ký - Thực Phẩm Tươi Sạch</title>
    <link rel="stylesheet" href="style_login_register.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .forgot-password {
            text-align: right;
            margin: 10px 0;
        }

        .forgot-password a {
            color: #4CAF50;
            text-decoration: none;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            color: #45a049;
            text-decoration: underline;
        }
    </style>
    <script>
        function showNotification(message, type = 'success') {
            // Remove any existing notifications
            const existingNotification = document.querySelector('.notification');
            if (existingNotification) {
                existingNotification.remove();
            }

            // Create notification element
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                ${message}
                <button class="close-btn" onclick="this.parentElement.remove()">×</button>
            `;

            // Add to document
            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                if (notification && notification.parentElement) {
                    notification.remove();
                }
            }, 3000);
        }

        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Login form submission
            document.getElementById('login-form-element').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'login');

                fetch('auth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                });
            });

            // Register form submission
            document.querySelector('#register-form form').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validate password match
                const password = this.querySelector('#register-password').value;
                const confirmPassword = this.querySelector('#register-confirm-password').value;
                
                if (password !== confirmPassword) {
                    showNotification('Mật khẩu xác nhận không khớp!', 'error');
                    return;
                }

                const formData = new FormData(this);
                formData.append('action', 'register');

                fetch('auth.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Có lỗi xảy ra. Vui lòng thử lại!', 'error');
                });
            });
        });
    </script>
</head>
<body>
    <div class="page-container">
        <div class="container">
            <!-- Logo Section -->
            <div class="logo-section">
                <a href="../home/index.php">
                    <img src="../images/Logo.png" alt="Logo">
                </a>
                <h1>Tươi Sạch Mỗi Ngày</h1>
            </div>

            <!-- Forms Container -->
            <div class="forms-container">
                <!-- Login Form -->
                <div class="form-section" id="login-form">
                    <div class="form-header">
                        <i class="fas fa-sign-in-alt"></i>
                        <h2>Đăng nhập</h2>
                        <p>Chào mừng bạn quay trở lại!</p>
                    </div>

                    <form action="auth.php" method="POST" class="auth-form" id="login-form-element">
                        <div class="form-group">
                            <label for="login-username">
                                <i class="fas fa-user"></i> Tên đăng nhập
                            </label>
                            <input type="text" id="login-username" name="username" required placeholder="Nhập tên đăng nhập">
                        </div>

                        <div class="form-group">
                            <label for="login-password">
                                <i class="fas fa-lock"></i> Mật khẩu
                            </label>
                            <div class="password-input-container">
                                <input type="password" id="login-password" name="password" required placeholder="Nhập mật khẩu">
                                <button type="button" class="toggle-password" onclick="togglePasswordVisibility('login-password', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="forgot-password">
                            <a href="forgot_password.php">Quên mật khẩu?</a>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </form>
                </div>

                <!-- Divider -->
                <div class="form-divider">
                    <div class="divider-line"></div>
                    <span>hoặc</span>
                    <div class="divider-line"></div>
                </div>

                <!-- Register Form -->
                <div class="form-section" id="register-form">
                    <div class="form-header">
                        <i class="fas fa-user-plus"></i>
                        <h2>Đăng ký</h2>
                        <p>Tạo tài khoản mới</p>
                    </div>

                    <form action="auth.php" method="POST" class="auth-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="register-username">
                                    <i class="fas fa-user"></i> Tên đăng nhập
                                </label>
                                <input type="text" id="register-username" name="username" required placeholder="Nhập tên đăng nhập">
                            </div>

                            <div class="form-group">
                                <label for="register-name">
                                    <i class="fas fa-id-card"></i> Họ và tên
                                </label>
                                <input type="text" id="register-name" name="name" required placeholder="Nhập họ và tên">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="register-email">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <input type="email" id="register-email" name="email" required placeholder="Nhập địa chỉ email">
                            </div>

                            <div class="form-group">
                                <label for="register-phone">
                                    <i class="fas fa-phone"></i> Số điện thoại
                                </label>
                                <input type="tel" id="register-phone" name="phone" required placeholder="Nhập số điện thoại">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="register-password">
                                    <i class="fas fa-lock"></i> Mật khẩu
                                </label>
                                <div class="password-input-container">
                                    <input type="password" id="register-password" name="password" required placeholder="Nhập mật khẩu">
                                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('register-password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="register-confirm-password">
                                    <i class="fas fa-lock"></i> Xác nhận mật khẩu
                                </label>
                                <div class="password-input-container">
                                    <input type="password" id="register-confirm-password" name="confirm_password" required placeholder="Nhập lại mật khẩu">
                                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('register-confirm-password', this)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Background Decoration -->
    <div class="bg-decoration"></div>

    <script>
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html> 