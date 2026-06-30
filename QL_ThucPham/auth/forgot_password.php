<?php
session_start();
require_once('../config/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    
    // Kiểm tra email trong database
    $query = "SELECT customer_id, name FROM customers WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Tạo token ngẫu nhiên
        $token = bin2hex(random_bytes(32));
        
        // Xóa token cũ nếu có
        $delete_query = "DELETE FROM password_resets WHERE customer_id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $user['customer_id']);
        $stmt->execute();
        
        // Lưu token mới
        $insert_query = "INSERT INTO password_resets (customer_id, token) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("is", $user['customer_id'], $token);
        
        if ($stmt->execute()) {
            // Tạo link đặt lại mật khẩu
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;
            
            // Cấu hình email
            $to = $email;
            $subject = "Đặt lại mật khẩu - Thực Phẩm Mộc";
            $message = "Xin chào " . $user['name'] . ",\n\n";
            $message .= "Bạn đã yêu cầu đặt lại mật khẩu tại Thực Phẩm Mộc.\n";
            $message .= "Vui lòng click vào link sau để đặt lại mật khẩu của bạn:\n\n";
            $message .= $reset_link . "\n\n";
            $message .= "Link này sẽ hết hạn sau 1 giờ.\n\n";
            $message .= "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.\n\n";
            $message .= "Trân trọng,\nThực Phẩm Mộc";
            
            $headers = "From: no-reply@thucphammoc.com";
            
            if (mail($to, $subject, $message, $headers)) {
                $_SESSION['success_message'] = "Hướng dẫn đặt lại mật khẩu đã được gửi đến email của bạn.";
            } else {
                $_SESSION['error_message'] = "Không thể gửi email. Vui lòng thử lại sau.";
            }
        } else {
            $_SESSION['error_message'] = "Có lỗi xảy ra. Vui lòng thử lại sau.";
        }
    } else {
        $_SESSION['error_message'] = "Email không tồn tại trong hệ thống.";
    }
    
    header("Location: forgot_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="style_login_register.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <style>
        .forgot-password-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .forgot-password-form {
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

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 15px;
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

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: #4CAF50;
            text-decoration: none;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="forgot-password-container">
        <h2>Quên mật khẩu</h2>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success_message'];
                    unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="forgot-password-form">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required placeholder="Nhập email của bạn">
            </div>
            <button type="submit" class="submit-btn">Gửi yêu cầu đặt lại mật khẩu</button>
        </form>

        <div class="back-to-login">
            <a href="auth.php">Quay lại đăng nhập</a>
        </div>
    </div>
</body>
</html> 