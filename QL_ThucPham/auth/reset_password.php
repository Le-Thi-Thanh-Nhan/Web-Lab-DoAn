<?php
session_start();
require_once('../config/db_connect.php');

// Kiểm tra token
if (!isset($_GET['token'])) {
    header('Location: auth.php');
    exit();
}

$token = $_GET['token'];

// Kiểm tra token có hợp lệ và chưa hết hạn
$query = "SELECT pr.*, c.name FROM password_resets pr 
          JOIN customers c ON pr.customer_id = c.customer_id 
          WHERE pr.token = ? AND pr.expires_at > NOW()";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = "Link đặt lại mật khẩu không hợp lệ hoặc đã hết hạn.";
    header('Location: auth.php');
    exit();
}

$reset_info = $result->fetch_assoc();

// Xử lý form đặt lại mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Mật khẩu xác nhận không khớp.";
    } else {
        // Cập nhật mật khẩu mới
        $update_query = "UPDATE customers SET password = ? WHERE customer_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $password, $reset_info['customer_id']);
        
        if ($stmt->execute()) {
            // Xóa token đã sử dụng
            $delete_query = "DELETE FROM password_resets WHERE token = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $_SESSION['success_message'] = "Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.";
            header('Location: auth.php');
            exit();
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra. Vui lòng thử lại.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="style_login_register.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .reset-password-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .reset-password-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .password-input-container {
            position: relative;
        }

        .password-input-container input {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .welcome-message {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <h2>Đặt lại mật khẩu</h2>
        
        <div class="welcome-message">
            <p>Xin chào, <?php echo htmlspecialchars($reset_info['name']); ?>!</p>
        </div>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="reset-password-form">
            <div class="form-group">
                <label for="password">Mật khẩu mới:</label>
                <div class="password-input-container">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                <div class="password-input-container">
                    <input type="password" id="confirm_password" name="confirm_password" required>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility('confirm_password', this)">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="submit-btn">Đặt lại mật khẩu</button>
        </form>
    </div>

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