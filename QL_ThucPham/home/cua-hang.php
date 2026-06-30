<?php
session_start();
require_once('../config/db_connect.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cửa Hàng - Thực Phẩm Mộc</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/cua-hang.css">
    <link rel="icon" type="image/png" href="../images/Logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stores-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .stores-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 40px;
            font-size: 32px;
            position: relative;
        }

        .stores-container h2::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #27ae60;
            margin: 15px auto;
            border-radius: 2px;
        }

        .stores-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .store-item {
            background: #fff;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .store-item:hover {
            transform: translateY(-5px);
        }

        .store-item h3 {
            color: #27ae60;
            margin-bottom: 20px;
            font-size: 22px;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }

        .store-item p {
            margin-bottom: 15px;
            color: #666;
            line-height: 1.6;
        }

        .store-item strong {
            color: #333;
            margin-right: 5px;
        }

        .store-map {
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
        }

        .store-map iframe {
            width: 100%;
            height: 300px;
            border: none;
            border-radius: 10px;
        }

        @media (max-width: 768px) {
            .stores-container {
                margin: 20px auto;
            }

            .stores-list {
                grid-template-columns: 1fr;
            }

            .store-item {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <?php include 'slide-menu.php'; ?>
    
    <!-- Main Content -->
    <main class="stores-container">
        <h2>Hệ Thống Cửa Hàng</h2>
        <div class="stores-list">
            <div class="store-item">
                <h3><i class="fas fa-store"></i> Chi nhánh 1 - Hà Nội</h3>
                <p><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> 123 Đường Láng, Đống Đa, Hà Nội</p>
                <p><i class="fas fa-phone"></i> <strong>Điện thoại:</strong> 024.1234.5678</p>
                <p><i class="fas fa-clock"></i> <strong>Giờ mở cửa:</strong> 7:00 - 22:00</p>
                <div class="store-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3724.6963478821584!2d105.81676081476167!3d21.007025386010126!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ac828a8d097f%3A0x7649a8c8bbe0e4a!2zxJDGsOG7nW5nIEzDoW5nLCDEkOG7kW5nIMSQYSwgSMOgIE7hu5lpLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1624512345678!5m2!1svi!2s" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <div class="store-item">
                <h3><i class="fas fa-store"></i> Chi nhánh 2 - TP.HCM</h3>
                <p><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> 456 Lê Văn Sỹ, Quận 3, TP.HCM</p>
                <p><i class="fas fa-phone"></i> <strong>Điện thoại:</strong> 028.1234.5678</p>
                <p><i class="fas fa-clock"></i> <strong>Giờ mở cửa:</strong> 7:00 - 22:00</p>
                <div class="store-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.3241899009125!2d106.67797231476343!3d10.786840892314446!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f2d8c1df6d1%3A0x8d1c4c9f7b4b4b4b!2zTMOqIFbEg24gU-G7uSwgUXXhuq1uIDMsIFRow6BuaCBwaOG7kSBI4buTIENow60gTWluaCwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1624512345678!5m2!1svi!2s" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>

            <div class="store-item">
                <h3><i class="fas fa-store"></i> Chi nhánh 3 - Đà Nẵng</h3>
                <p><i class="fas fa-map-marker-alt"></i> <strong>Địa chỉ:</strong> 789 Nguyễn Văn Linh, Hải Châu, Đà Nẵng</p>
                <p><i class="fas fa-phone"></i> <strong>Điện thoại:</strong> 0236.1234.5678</p>
                <p><i class="fas fa-clock"></i> <strong>Giờ mở cửa:</strong> 7:00 - 22:00</p>
                <div class="store-map">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3834.1076171847697!2d108.20762931475275!3d16.060372988887794!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314219b4239d8e51%3A0x96e408c6b0419760!2zTmd1eeG7hW4gVsSDbiB
                    TGluaCwgxJDDoCBO4bq1bmcsIFZp4buHdCBOYW0!5e0!3m2!1svi!2s!4v1624512345678!5m2!1svi!2s" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="script.js"></script>
</body>
</html> 