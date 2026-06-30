<?php
if (!isset($_SESSION)) {
    session_start();
}

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../config/dbconnect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $period = $_POST['period'] ?? 'week';
    $data = getRevenueData($conn, $period);
    echo json_encode($data);
}

function getRevenueData($conn, $period) {
    $labels = array();
    $values = array();
    
    switch($period) {
        case 'week':
            $sql = "SELECT 
                    DATE(created_at) as date,
                    DATE_FORMAT(created_at, '%d/%m') as formatted_date,
                    CASE DAYOFWEEK(created_at)
                        WHEN 1 THEN 'Chủ nhật'
                        WHEN 2 THEN 'Thứ hai'
                        WHEN 3 THEN 'Thứ ba'
                        WHEN 4 THEN 'Thứ tư'
                        WHEN 5 THEN 'Thứ năm'
                        WHEN 6 THEN 'Thứ sáu'
                        WHEN 7 THEN 'Thứ bảy'
                    END as day_name,
                    SUM(total_amount) as revenue
                    FROM orders
                    WHERE status = 'Completed'
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                    GROUP BY DATE(created_at)
                    ORDER BY date ASC";
            break;
            
        case 'month':
            $sql = "SELECT 
                    DATE(date_start) as date,
                    MIN(DATE_FORMAT(date_start, '%d/%m')) as start_date,
                    MAX(DATE_FORMAT(date_end, '%d/%m')) as end_date,
                    CONCAT('Tuần ', week_number) as week_name,
                    SUM(revenue) as revenue
                    FROM (
                        SELECT 
                            created_at as date_start,
                            DATE_ADD(created_at, INTERVAL 6 DAY) as date_end,
                            WEEK(created_at) - WEEK(DATE_SUB(created_at, INTERVAL DAYOFMONTH(created_at)-1 DAY)) + 1 as week_number,
                            total_amount as revenue
                        FROM orders
                        WHERE status = 'Completed'
                        AND created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
                    ) as weekly_data
                    GROUP BY YEARWEEK(date_start)
                    ORDER BY date ASC";
            break;
            
        case 'year':
            $sql = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-01') as date,
                    DATE_FORMAT(created_at, '%m/%Y') as formatted_date,
                    CASE MONTH(created_at)
                        WHEN 1 THEN 'Tháng 1'
                        WHEN 2 THEN 'Tháng 2'
                        WHEN 3 THEN 'Tháng 3'
                        WHEN 4 THEN 'Tháng 4'
                        WHEN 5 THEN 'Tháng 5'
                        WHEN 6 THEN 'Tháng 6'
                        WHEN 7 THEN 'Tháng 7'
                        WHEN 8 THEN 'Tháng 8'
                        WHEN 9 THEN 'Tháng 9'
                        WHEN 10 THEN 'Tháng 10'
                        WHEN 11 THEN 'Tháng 11'
                        WHEN 12 THEN 'Tháng 12'
                    END as month_name,
                    SUM(total_amount) as revenue
                    FROM orders
                    WHERE status = 'Completed'
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                    GROUP BY YEAR(created_at), MONTH(created_at)
                    ORDER BY date ASC";
            break;
    }
    
    $result = $conn->query($sql);
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $label = '';
            switch($period) {
                case 'week':
                    $label = $row['day_name'] . ' (' . $row['formatted_date'] . ')';
                    break;
                case 'month':
                    $label = $row['week_name'] . ' (' . $row['start_date'] . ' - ' . $row['end_date'] . ')';
                    break;
                case 'year':
                    $label = $row['month_name'] . ' (' . $row['formatted_date'] . ')';
                    break;
            }
            $labels[] = $label;
            $values[] = (float)$row['revenue'];
        }
    }
    
    // Nếu không có dữ liệu, thêm giá trị mặc định
    if (empty($labels)) {
        $labels[] = 'Không có dữ liệu';
        $values[] = 0;
    }
    
    return array(
        'labels' => $labels,
        'values' => $values
    );
}

// Xử lý yêu cầu lấy dữ liệu top sản phẩm
if (isset($_POST['action']) && $_POST['action'] === 'top_products') {
    $sql = "SELECT 
                p.name,
                SUM(od.quantity) as quantity,
                SUM(od.quantity * od.price) as revenue,
                (SUM(od.quantity * od.price) / (
                    SELECT SUM(od2.quantity * od2.price)
                    FROM orderdetails od2
                    JOIN orders o2 ON o2.order_id = od2.order_id
                    WHERE o2.status = 'completed'
                ) * 100) as percentage
            FROM orderdetails od
            JOIN products p ON p.product_id = od.product_id
            JOIN orders o ON o.order_id = od.order_id
            WHERE o.status = 'completed'
            GROUP BY p.product_id
            ORDER BY revenue DESC
            LIMIT 7";
            
    $result = $conn->query($sql);
    $products = array();
    
    while($row = $result->fetch_assoc()) {
        $products[] = array(
            'name' => $row['name'],
            'quantity' => (int)$row['quantity'],
            'revenue' => (float)$row['revenue'],
            'percentage' => round((float)$row['percentage'], 2)
        );
    }
    
    echo json_encode(array('products' => $products));
}

$conn->close();
?> 