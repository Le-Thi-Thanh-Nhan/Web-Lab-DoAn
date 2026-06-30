<?php
// Khởi tạo session trước khi có bất kỳ output nào
if (!isset($_SESSION)) {
    session_start();
}

// Kiểm tra đăng nhập admin
if (!isset($_SESSION['admin']) || !isset($_SESSION['admin']['admin_id'])) {
    header('Location: ../auth/auth.php');
    exit;
}

// Include các file cần thiết
require_once '../config/dbconnect.php';

// Hàm lấy doanh thu
function getRevenue($conn, $period = 'all') {
    $sql = "SELECT SUM(total_amount) as revenue FROM orders WHERE status = 'completed'";
    
    switch($period) {
        case 'today':
            $sql .= " AND DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $sql .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $sql .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        default:
            break;
    }
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row['revenue'] ?? 0;
}

// Hàm lấy top sản phẩm bán chạy
function getTopProducts($conn) {
    $sql = "SELECT p.product_id, p.name, p.price, p.sold_quantity, 
            (p.price * p.sold_quantity) as total_revenue
            FROM products p 
            WHERE p.sold_quantity > 0
            ORDER BY p.sold_quantity DESC 
            LIMIT 7";
            
    $result = mysqli_query($conn, $sql);
    $products = array();
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $products[] = array(
                'name' => $row['name'],
                'quantity' => $row['sold_quantity'],
                'revenue' => $row['total_revenue']
            );
        }
        mysqli_free_result($result);
    }
    
    return $products;
}

// Hàm lấy dữ liệu cho biểu đồ top sản phẩm
function getTopProductsChartData($conn) {
    $products = getTopProducts($conn);
    $labels = array();
    $quantities = array();
    $revenues = array();
    
    foreach ($products as $product) {
        $labels[] = $product['name'];
        $quantities[] = $product['quantity'];
        $revenues[] = $product['revenue'];
    }
    
    return array(
        'labels' => $labels,
        'quantities' => $quantities,
        'revenues' => $revenues
    );
}

// Lấy doanh thu các khoảng thời gian
$totalRevenue = getRevenue($conn);
$monthlyRevenue = getRevenue($conn, 'month');
$weeklyRevenue = getRevenue($conn, 'week');
$todayRevenue = getRevenue($conn, 'today');

// Lấy top sản phẩm bán chạy
$topProducts = getTopProducts($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Doanh thu</title>
    <!-- Include CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css">
    <style>
        /* Reset header styles that might interfere */
        .navbar {
            padding: 0.5rem 1rem;
            margin: 0;
        }
        
        .topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 260px;
            z-index: 1030;
        }

        /* Main content wrapper */
        .content-wrapper {
            margin-left: 260px;
            padding: 90px 30px 30px;
            min-height: 100vh;
            background-color: #f8f9fa;
            width: calc(100% - 260px);
            box-sizing: border-box;
        }

        .page-title {
            position: fixed;
            top: 15px;
            left: 290px; /* 260px (sidebar) + 30px (padding) */
            z-index: 1040;
            margin: 0;
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            background-color: white;
            padding: 8px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .page-title i {
            color: #0d6efd;
            font-size: 1.4rem;
        }

        .container-fluid {
            padding: 0;
            width: 100%;
            margin: 0;
        }

        /* Stats Overview Cards */
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            position: absolute;
            right: -10px;
            top: 100%;
            transform: translateY(-30%);
            font-size: 4rem;
            opacity: 0.1;
            transition: all 0.3s ease;
        }

        .stat-card:hover .stat-icon {
            opacity: 0.2;
            right: -5px;
        }

        .stat-info {
            position: relative;
            z-index: 1;
        }

        .stat-info h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .stat-info p {
            margin: 0;
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-card.blue .stat-icon {
            color: #0d6efd;
        }

        .stat-card.green .stat-icon {
            color: #198754;
        }

        .stat-card.orange .stat-icon {
            color: #fd7e14;
        }

        .stat-card.red .stat-icon {
            color: #dc3545;
        }

        .stat-card.blue {
            border-left: 4px solid #0d6efd;
        }

        .stat-card.green {
            border-left: 4px solid #198754;
        }

        .stat-card.orange {
            border-left: 4px solid #fd7e14;
        }

        .stat-card.red {
            border-left: 4px solid #dc3545;
        }

        /* Charts Section */
        .charts-section {
            display: grid;
            grid-template-columns: 3fr 2fr;
            gap: 20px;
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .chart-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            height: 400px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .chart-container {
            flex: 1;
            position: relative;
            width: 100%;
            height: calc(100% - 60px); /* Trừ đi chiều cao của header */
            min-height: 300px;
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            height: 40px;
        }

        .chart-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .chart-filters {
            display: flex;
            gap: 10px;
        }

        .filter-btn {
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            background: white;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn.active {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        /* Top Products List */
        .top-products {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .top-products-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        .product-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .product-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #dee2e6;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-name {
            font-weight: 500;
            color: #2c3e50;
        }

        .product-sales {
            color: #6c757d;
        }

        /* Responsive Design */
        @media (max-width: 1400px) {
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-section {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 992px) {
            .charts-section {
                grid-template-columns: 1fr;
            }
            .content-wrapper {
                margin-left: 0;
                width: 100%;
            }
            .topbar {
                left: 0;
            }
        }

        @media (max-width: 768px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }
            .content-wrapper {
                padding: 80px 15px 15px;
            }
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <h1 class="page-title">
            <i class="fas fa-chart-line"></i>
            Quản lý doanh thu
        </h1>
        <div class="container-fluid">
            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-card blue">
                    <div class="stat-info">
                        <h4><?php echo number_format($totalRevenue, 0, ',', '.'); ?>đ</h4>
                        <p>Tổng doanh thu</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                </div>
                <div class="stat-card green">
                    <div class="stat-info">
                        <h4><?php echo number_format($monthlyRevenue, 0, ',', '.'); ?>đ</h4>
                        <p>Doanh thu tháng này</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                </div>
                <div class="stat-card orange">
                    <div class="stat-info">
                        <h4><?php echo number_format($weeklyRevenue, 0, ',', '.'); ?>đ</h4>
                        <p>Doanh thu tuần này</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                </div>
                <div class="stat-card red">
                    <div class="stat-info">
                        <h4><?php echo number_format($todayRevenue, 0, ',', '.'); ?>đ</h4>
                        <p>Doanh thu hôm nay</p>
                    </div>
                    <div class="stat-icon">
                        <i class="fas fa-cash-register"></i>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Biểu đồ doanh thu</h3>
                        <div class="chart-filters">
                            <button class="filter-btn active" data-period="week">7 ngày</button>
                            <button class="filter-btn" data-period="month">30 ngày</button>
                            <button class="filter-btn" data-period="year">365 ngày</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Top Products Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Top Sản Phẩm Bán Chạy</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Products Table -->
            <div class="top-products-table">
                <div class="card">
                    <div class="card-header">
                        <h3>Danh Sách Top Sản Phẩm Bán Chạy</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>STT</th>
                                        <th>Tên Sản Phẩm</th>
                                        <th>Số Lượng Đã Bán</th>
                                        <th>Doanh Thu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($topProducts as $index => $product) {
                                        echo "<tr>";
                                        echo "<td>" . ($index + 1) . "</td>";
                                        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                                        echo "<td>" . number_format($product['quantity']) . "</td>";
                                        echo "<td>" . number_format($product['revenue']) . " VNĐ</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <script>
        // Initialize charts
        let revenueChart, topProductsChart;

        function initCharts() {
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Doanh thu',
                        data: [],
                        fill: false,
                        borderColor: '#0d6efd',
                        tension: 0.1,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 30,
                            top: 20,
                            bottom: 10
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND',
                                        maximumFractionDigits: 0
                                    }).format(value);
                                },
                                font: {
                                    size: 11
                                }
                            },
                            grid: {
                                color: '#e0e0e0'
                            }
                        },
                        x: {
                            grid: {
                                color: '#e0e0e0'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', {
                                        style: 'currency',
                                        currency: 'VND',
                                        maximumFractionDigits: 0
                                    }).format(context.parsed.y);
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Top Products Chart
            const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
            const topProductsData = <?php echo json_encode(getTopProductsChartData($conn)); ?>;
            
            topProductsChart = new Chart(topProductsCtx, {
                type: 'bar',
                data: {
                    labels: topProductsData.labels,
                    datasets: [{
                        label: 'Số lượng bán ra',
                        data: topProductsData.quantities,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        yAxisID: 'y'
                    }, {
                        label: 'Doanh thu',
                        data: topProductsData.revenues,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Số lượng'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN');
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Doanh thu (VNĐ)'
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString('vi-VN') + ' VNĐ';
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.datasetIndex === 0) {
                                        label += context.parsed.y.toLocaleString('vi-VN') + ' sản phẩm';
                                    } else {
                                        label += new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' })
                                            .format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Function to update revenue chart
        function updateRevenueChart(period) {
            fetch('get_revenue_data.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'period=' + period
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error:', data.error);
                    return;
                }
                revenueChart.data.labels = data.labels;
                revenueChart.data.datasets[0].data = data.values;
                revenueChart.update();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Initialize charts when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            initCharts();
            updateRevenueChart('week'); // Load initial data
        });

        // Handle filter buttons
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                updateRevenueChart(this.dataset.period);
            });
        });
    </script>
</body>
</html> 