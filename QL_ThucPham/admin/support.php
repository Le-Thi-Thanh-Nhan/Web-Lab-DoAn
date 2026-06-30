<?php
session_start();
require_once('../config/db_connect.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header('Location: ../auth/auth.php');
    exit();
}

// Handle deletion
if (isset($_POST['delete_support'])) {
    $support_id = $_POST['support_id'];
    $delete_query = "DELETE FROM support WHERE support_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $support_id);
    $stmt->execute();
}

// Fetch all support requests
$query = "SELECT * FROM support ORDER BY created_at DESC";
$result = $conn->query($query);

if (!$result) {
    die("Lỗi truy vấn: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hỗ trợ - Admin</title>
    <link rel="stylesheet" href="assets/css/admin.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <h1>Quản lý đơn hỗ trợ</h1>

        <div class="support-list">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên khách hàng</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['support_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['phone']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td>
                                <button onclick="viewSupport(<?php 
                                    echo htmlspecialchars(json_encode([
                                        'id' => $row['support_id'],
                                        'name' => $row['name'],
                                        'email' => $row['email'],
                                        'phone' => $row['phone'],
                                        'message' => $row['message']
                                    ])); 
                                ?>)" class="view-btn">
                                    <i class="fas fa-eye"></i> Xem
                                </button>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="support_id" value="<?php echo $row['support_id']; ?>">
                                    <button type="submit" name="delete_support" class="delete-btn" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hỗ trợ này?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Không có đơn hỗ trợ nào.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal for viewing support details -->
    <div id="supportModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Chi tiết đơn hỗ trợ</h2>
            <div class="support-details">
                <p><strong>ID:</strong> <span id="support-id"></span></p>
                <p><strong>Tên khách hàng:</strong> <span id="support-name"></span></p>
                <p><strong>Email:</strong> <span id="support-email"></span></p>
                <p><strong>Số điện thoại:</strong> <span id="support-phone"></span></p>
                <p><strong>Nội dung:</strong></p>
                <div id="support-message" class="message-content"></div>
            </div>
        </div>
    </div>

    <style>
        .support-list {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px;
        }

        .support-list table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
        }

        .support-list th, .support-list td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .support-list th {
            background-color: #2c3e50;
            color: #fff;
            font-weight: 600;
        }

        .support-list tr:hover {
            background-color: #f5f5f5;
        }

        .view-btn, .delete-btn {
            padding: 8px 15px;
            margin: 0 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .view-btn {
            background-color: #4CAF50;
            color: white;
        }

        .view-btn:hover {
            background-color: #45a049;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        .delete-btn:hover {
            background-color: #da190b;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 25px;
            border: none;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            position: relative;
            animation: slideIn 0.3s;
        }

        @keyframes slideIn {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }

        .close:hover {
            color: #333;
        }

        .support-details {
            margin-top: 20px;
        }

        .support-details p {
            margin: 10px 0;
            line-height: 1.6;
        }

        .message-content {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-top: 15px;
            white-space: pre-wrap;
            line-height: 1.6;
            border: 1px solid #eee;
        }

        .main-content h1 {
            color: #333;
            margin-bottom: 30px;
            padding: 20px;
            border-bottom: 2px solid #eee;
        }

        .no-supports {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 16px;
            background: #f9f9f9;
            border-radius: 8px;
            margin: 20px;
        }
    </style>

    <script>
        const modal = document.getElementById("supportModal");
        const span = document.getElementsByClassName("close")[0];

        function viewSupport(data) {
            document.getElementById("support-id").textContent = data.id;
            document.getElementById("support-name").textContent = data.name;
            document.getElementById("support-email").textContent = data.email;
            document.getElementById("support-phone").textContent = data.phone;
            document.getElementById("support-message").textContent = data.message;
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html> 