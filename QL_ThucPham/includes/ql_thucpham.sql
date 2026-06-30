-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 20, 2025 at 05:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `admin_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` VALUES
('1', 'admin1', 'password_1', 'Nguyễn Văn A', 'admin1@example.com', '0365784561', '2025-05-25 19:11:01', '2025-05-27 22:53:46'),
('2', 'admin2', 'password_2', 'Trần Thị B', 'admin2@example.com', '0396754123', '2025-05-25 19:11:01', '2025-05-27 23:03:35'),
('3', 'admin3', 'password_3', 'Lê Văn C', 'admin3@example.com', '0965845624', '2025-05-25 19:11:01', '2025-05-28 23:54:43'),
('4', 'admin4', 'password_4', 'Phạm Thị D', 'admin4@example.com', '0396456789', '2025-05-25 19:11:01', '2025-05-29 01:30:38'),
('5', 'admin5', 'hashed_password_5', 'Hoàng Văn E', 'admin5@example.com', '0375415236', '2025-05-25 19:11:01', '2025-05-27 22:54:49'),
('6', 'admin6', 'hashed_password_6', 'Đỗ Thị F', 'admin6@example.com', '0964852156', '2025-05-25 19:11:01', '2025-05-27 22:54:38'),
('7', 'admin7', 'hashed_password_7', 'Vũ Văn G', 'admin7@example.com', '0968224153', '2025-05-25 19:11:01', '2025-05-27 22:54:58');

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`cart_id`),
  UNIQUE KEY `unique_cart_item` (`customer_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`),
  CONSTRAINT `carts_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` VALUES
('82', '3', '164', '1', '2025-06-05 04:49:08'),
('124', '1', '159', '1', '2025-07-20 22:34:11');

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` VALUES
('1', 'Trái cây', 'Các trái cây nhập khẩu', '2025-05-26 12:11:01', '2025-05-26 12:11:01'),
('2', 'Rau - Củ - Nấm', 'Các loại rau củ nấm tươi sống', '2025-05-26 12:11:01', '0000-00-00 00:00:00'),
('3', 'Thực phẩm tươi sống', 'Các loại thịt hải sản tươi sống', '2025-05-25 12:11:01', '2025-05-26 12:11:01'),
('4', 'Thực phẩm chất béo', 'Phô mai - Trứng - Sữa các loại', '2025-05-25 12:11:01', '2025-05-26 12:11:01'),
('5', 'Đồ uống không cồn', 'các loại đồ uống nhẹ không có cồn', '2025-05-25 12:11:01', '2025-05-26 12:11:01'),
('6', 'Đồ uống có cồn', 'các loại rượu bia', '2025-05-25 12:11:01', '2025-05-26 12:11:01'),
('7', 'Đồ ăn vặt', 'Bánh - Kẹo - Snack', '2025-05-25 12:11:01', '2025-05-26 12:11:01'),
('8', 'Mứt - Mật ong', 'Thực phẩm ngọt', '2025-05-25 19:11:01', '2025-05-26 20:05:53'),
('9', 'Các loại gia vị', 'Gia vị nấu ăn - gia vị tổng hợp sẵn', '2025-05-25 19:11:01', '2025-05-28 01:15:10'),
('10', 'Hạt - Ngũ cốc', 'Các loạị hạt,ngũ cốc', '2025-05-25 19:11:01', '2025-05-26 22:57:02'),
('11', 'Đồ khô - Đồ hộp - Đồ ngâm', 'Các loại thực phẩm tích trữ lâu dài', '2025-05-25 19:11:01', '2025-05-27 10:36:53'),
('12', 'Sản phẩm Gạo', 'Gạo và sản phẩm từ Gạo', '2025-05-25 19:11:01', '2025-05-26 23:04:56'),
('13', 'Bột - Mì - Pasta', 'Các loại thực phẩm dùng chế biến dạng bột', '2025-05-25 19:11:01', '2025-05-26 23:05:01'),
('14', 'Đồ ướp/chế biến sẵn', 'Các loại thịt nguội được chế biến', '2025-05-25 12:11:01', '2025-05-26 23:05:05'),
('15', 'Đồ chay', 'Các loại đồ ăn chay được chế biến', '2025-05-25 12:11:01', '2025-05-26 23:05:52');

--
-- Table structure for table `customer_discount_codes`
--

CREATE TABLE `customer_discount_codes` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `code_id` int(11) NOT NULL,
  `collected_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_status` enum('active','expired','used') NOT NULL DEFAULT 'active',
  `expiry_date` datetime NOT NULL,
  `last_checked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`collection_id`),
  UNIQUE KEY `unique_customer_code_collection` (`customer_id`,`code_id`),
  KEY `code_id` (`code_id`),
  KEY `expiry_status_index` (`expiry_status`),
  CONSTRAINT `customer_discount_codes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `customer_discount_codes_ibfk_2` FOREIGN KEY (`code_id`) REFERENCES `discount_codes` (`code_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `customer_discount_codes`
--

INSERT INTO `customer_discount_codes` VALUES
('1', '1', '1', '2025-05-25 12:11:01', 'active', '2025-12-31 23:59:00', '2025-05-25 12:11:01'),
('2', '1', '2', '2025-05-25 12:11:01', 'used', '2025-08-31 23:59:00', '2025-05-25 12:11:01'),
('3', '1', '4', '2025-05-29 21:56:33', 'used', '2026-05-28 00:00:00', '2025-05-29 21:56:33'),
('4', '2', '1', '2025-05-25 12:11:01', 'used', '2025-12-31 23:59:00', '2025-05-28 15:05:17'),
('5', '2', '2', '2025-05-25 12:11:01', 'used', '2025-08-31 23:59:00', '2025-05-28 14:57:06'),
('6', '2', '4', '2025-05-29 21:56:33', 'used', '2026-05-28 00:00:00', '2025-05-31 12:24:14'),
('7', '3', '2', '2025-05-31 13:43:05', 'active', '2025-08-31 23:59:00', '2025-05-31 13:43:05'),
('8', '3', '1', '2025-05-31 13:43:07', 'active', '2025-12-31 23:59:00', '2025-05-31 13:43:07'),
('9', '3', '4', '2025-05-31 13:43:09', 'active', '2026-05-28 00:00:00', '2025-05-31 13:43:09'),
('10', '1', '5', '2025-06-06 22:38:29', 'used', '2025-06-20 00:00:00', '2025-06-06 22:38:29'),
('11', '2', '5', '2025-06-07 18:08:12', 'active', '2025-06-20 00:00:00', '2025-06-07 18:08:12'),
('12', '17', '2', '2025-06-09 14:52:49', 'used', '2025-08-31 23:59:00', '2025-06-09 14:52:49'),
('13', '17', '5', '2025-06-09 14:52:51', 'active', '2025-06-20 00:00:00', '2025-06-09 14:52:51'),
('14', '17', '1', '2025-07-20 22:50:08', 'used', '2025-12-31 23:59:00', '2025-07-20 22:50:08'),
('15', '17', '4', '2025-07-20 22:50:13', 'active', '2026-05-28 00:00:00', '2025-07-20 22:50:13');

--
-- Table structure for table `customer_discount_usage`
--

CREATE TABLE `customer_discount_usage` (
  `usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `code_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','cancelled','expired') NOT NULL DEFAULT 'pending',
  `status_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`usage_id`),
  UNIQUE KEY `unique_customer_code` (`customer_id`,`code_id`),
  KEY `code_id` (`code_id`),
  KEY `order_id` (`order_id`),
  KEY `status_index` (`status`),
  CONSTRAINT `customer_discount_usage_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `customer_discount_usage_ibfk_2` FOREIGN KEY (`code_id`) REFERENCES `discount_codes` (`code_id`) ON DELETE CASCADE,
  CONSTRAINT `customer_discount_usage_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `customer_discount_usage`
--

INSERT INTO `customer_discount_usage` VALUES
('17', '3', '1', '11', '2025-05-31 13:43:51', 'pending', '2025-05-31 13:43:51'),
('18', '1', '1', '15', '2025-06-03 05:59:28', 'pending', '2025-06-03 05:59:28'),
('19', '1', '4', NULL, '2025-06-05 05:26:23', 'pending', '2025-06-05 05:26:23'),
('20', '1', '2', '18', '2025-06-05 05:54:16', 'pending', '2025-06-05 05:54:16'),
('21', '1', '5', '21', '2025-06-07 17:23:23', 'pending', '2025-06-07 17:23:23'),
('22', '17', '2', '30', '2025-06-09 14:54:31', 'pending', '2025-06-09 14:54:31'),
('23', '17', '1', '36', '2025-07-20 22:51:29', 'pending', '2025-07-20 22:51:29');

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` VALUES
('1', 'user0', 'alacrity1123', 'Cao Văn Đức', 'anydanys1@gmail.com', '0358964218', 'Sầm Sơn - Thanh Hóa', '2025-05-26 18:38:40', '2025-05-31 03:31:52'),
('2', 'user1', 'Password_1', 'Nguyễn Văn An', 'nva@example.com', '0356824715', 'Hai Bà Trưng - Hà Nội - Việt Nam', '2025-05-26 18:38:40', '2025-05-31 19:33:31'),
('3', 'user2', 'password_2', 'Trần Thị Bình', 'ttb@example.com', '0123456788', 'TP.HCM', '2025-05-26 18:38:40', '2025-05-27 22:51:26'),
('14', 'user4', 'password_4', 'Lê Việt Khoa', 'lvk@gmail.com', '0123456685', 'Hà Nội', '2025-05-27 11:47:33', '2025-05-27 22:51:04'),
('15', 'user5', '$2y$10$x.kElMM9A/U2JZpnJ71y4.Y4CJAFPnrNRIUD.CW9ncvFRuhI/vfDa', 'Cao Tuấn Vinh', 'ctv@gmail.com', '0123456685', 'Thanh Hóa', '2025-05-28 01:16:29', '2025-05-28 01:16:29'),
('16', 'user6', 'user6', 'Lê Ngọc Đức', 'user6@gmail.com', '0123456798', '', '2025-05-29 08:29:06', '2025-05-29 08:32:14'),
('17', 'user10', 'password10', 'Cao Văn Đàm', 'cvdm@gmail.com', '0375026456', NULL, '2025-06-09 14:51:15', '2025-06-09 14:51:15');

--
-- Table structure for table `discount_codes`
--

CREATE TABLE `discount_codes` (
  `code_id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `times_used` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`code_id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `discount_codes`
--

INSERT INTO `discount_codes` VALUES
('1', 'WELCOME10', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', '10.00', '100000.00', '100000.00', '2025-05-01 00:00:00', '2025-12-31 23:59:00', '100', '11', '1', '2025-05-28 16:49:40', '2025-06-03 05:59:28'),
('2', 'SUMMER25', 'Giảm 25% cho mùa hè', 'percentage', '25.00', '200000.00', '200000.00', '2025-06-01 00:00:00', '2025-08-31 23:59:00', '50', '7', '1', '2025-05-28 16:49:40', '2025-05-31 13:38:31'),
('4', 'CHAOMUNG', 'Trang web online sắp hoàn thành, giảm 50.000đ cho đơn hàng từ 100.000đ', 'fixed', '50000.00', '100000.00', '50000.00', '2025-05-28 00:00:00', '2026-05-28 00:00:00', '100', '4', '1', '2025-05-30 04:56:33', '2025-05-31 13:38:34'),
('5', '15THANG6', 'Sale giữa tháng 6, giảm giá 50% đơn hàng tối thiểu 300k', 'percentage', '50.00', '300000.00', '0.00', '2025-06-10 07:00:00', '2025-06-20 00:00:00', '50', '0', '1', '2025-06-06 22:38:01', '2025-06-06 22:39:00');

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `recipient_type` varchar(50) NOT NULL DEFAULT 'all',
  `recipient_id` int(11) DEFAULT NULL,
  `notification_type` enum('system','order','promotion','support') NOT NULL DEFAULT 'system',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` VALUES
('3', 'Có đơn hàng mới cần xử lý!', 'Đơn hàng #67890 vừa được tạo. Vui lòng kiểm tra và xử lý.', 'admin', '4', 'order', '2025-05-25 17:44:23', NULL),
('4', 'Chào mừng khách hàng đến với Thực phẩm Mộc!', 'Cảm ơn bạn đã ghé thăm cửa hàng thực phẩm trực tuyến của chúng tôi.', 'all', NULL, 'system', '2025-05-25 17:44:23', '2025-07-19 05:59:55'),
('6', 'Đơn hàng mới', 'Bạn có đơn hàng mới cần được xử lý', 'admin', '1', 'order', '2025-05-25 19:11:01', NULL),
('7', 'Giao hàng đang được duyệt', 'Đơn hàng của bạn đang chờ duyệt', 'admin', '3', 'order', '2025-05-25 19:11:01', NULL),
('10', 'Chào mừng bạn mới !', 'Tặng mã giảm giá 50k cho khách hàng mới\r\n- Đơn hàng tối thiểu từ 100k', 'all', NULL, 'promotion', '2025-05-25 19:11:01', '2025-07-19 05:56:40'),
('11', 'Cập nhật thông tin', 'Vui lòng cập nhật thông tin cá nhân', 'customer', '3', 'system', '2025-05-25 19:11:01', NULL),
('12', 'Thông báo bảo trì thanh toán trực tuyến', 'Tạm thời ngừng dịchvụ thanh toán trực tuyến!', 'all', NULL, 'system', '2025-05-25 19:11:01', '2025-07-19 05:59:54'),
('14', 'Sản phẩm mới trong tháng 6', 'Một số sản phẩm mới được thêm vào', 'all', NULL, 'promotion', '2025-05-25 19:11:01', '2025-07-19 05:56:40'),
('15', 'Sale giữa tháng', 'Mã giảm giá 50% đơn hàng đang có giới hạn, mau nhanh tay thu thập!', 'all', NULL, 'promotion', '2025-05-27 06:49:13', '2025-07-19 05:56:40'),
('17', 'Đang giao hàng', 'Đơn hàng #3 của bạn đang được giao', 'customer', '1', 'order', '2025-05-28 18:51:30', '2025-07-19 05:59:51');

--
-- Table structure for table `orderdetails`
--

CREATE TABLE `orderdetails` (
  `order_detail_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`order_detail_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `orderdetails_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  CONSTRAINT `orderdetails_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `orderdetails`
--

INSERT INTO `orderdetails` VALUES
('1', '1', '192', '1', '25000.00', '25000.00'),
('2', '1', '191', '4', '20000.00', '80000.00'),
('3', '2', '188', '1', '35000.00', '35000.00'),
('4', '3', '161', '1', '180000.00', '180000.00'),
('5', '4', '156', '1', '25000.00', '25000.00'),
('6', '5', '192', '1', '25000.00', '25000.00'),
('7', '5', '174', '1', '12000.00', '12000.00'),
('8', '5', '181', '1', '25000.00', '25000.00'),
('9', '5', '179', '1', '30000.00', '30000.00'),
('10', '5', '163', '1', '350000.00', '350000.00'),
('11', '6', '154', '1', '20000.00', '20000.00'),
('12', '6', '159', '3', '45000.00', '135000.00'),
('13', '6', '164', '1', '400000.00', '400000.00'),
('14', '6', '189', '5', '45000.00', '225000.00'),
('15', '6', '195', '3', '35000.00', '105000.00'),
('16', '7', '195', '1', '35000.00', '35000.00'),
('17', '7', '153', '3', '15000.00', '45000.00'),
('18', '8', '157', '1', '30000.00', '30000.00'),
('19', '8', '158', '1', '22000.00', '22000.00'),
('20', '9', '154', '12', '20000.00', '240000.00'),
('21', '10', '191', '6', '20000.00', '120000.00'),
('22', '10', '185', '5', '35000.00', '175000.00'),
('23', '10', '162', '2', '200000.00', '400000.00'),
('24', '11', '162', '5', '200000.00', '1000000.00'),
('25', '12', '192', '1', '25000.00', '25000.00'),
('26', '13', '156', '1', '25000.00', '25000.00'),
('27', '14', '192', '12', '25000.00', '300000.00'),
('28', '15', '194', '9', '50000.00', '450000.00'),
('29', '16', '164', '1', '400000.00', '400000.00'),
('30', '18', '178', '11', '35000.00', '385000.00'),
('31', '19', '169', '1', '15000.00', '15000.00'),
('32', '19', '155', '1', '18000.00', '18000.00'),
('33', '19', '154', '1', '20000.00', '20000.00'),
('34', '19', '182', '1', '22000.00', '22000.00'),
('35', '20', '192', '1', '25000.00', '25000.00'),
('36', '21', '180', '1', '75000.00', '75000.00'),
('37', '21', '194', '1', '50000.00', '50000.00'),
('38', '21', '161', '1', '180000.00', '180000.00'),
('39', '22', '186', '1', '8000.00', '8000.00'),
('40', '23', '180', '1', '75000.00', '75000.00'),
('41', '24', '193', '1', '40000.00', '40000.00'),
('42', '25', '157', '1', '30000.00', '30000.00'),
('43', '27', '154', '1', '20000.00', '20000.00'),
('44', '28', '176', '1', '48000.00', '48000.00'),
('45', '29', '192', '18', '25000.00', '450000.00'),
('46', '30', '194', '1', '50000.00', '50000.00'),
('47', '30', '168', '1', '120000.00', '120000.00'),
('48', '30', '183', '1', '15000.00', '15000.00'),
('49', '30', '164', '1', '400000.00', '400000.00'),
('50', '31', '164', '5', '400000.00', '2000000.00'),
('51', '32', '192', '1', '25000.00', '25000.00'),
('52', '33', '153', '1', '15000.00', '15000.00'),
('53', '34', '153', '1', '15000.00', '15000.00'),
('54', '35', '188', '1', '35000.00', '35000.00'),
('55', '35', '161', '1', '180000.00', '180000.00'),
('56', '35', '169', '1', '15000.00', '15000.00'),
('57', '35', '172', '4', '45000.00', '180000.00'),
('58', '35', '167', '1', '85000.00', '85000.00'),
('59', '36', '168', '1', '120000.00', '120000.00'),
('60', '36', '194', '2', '50000.00', '100000.00');

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `shipping_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `payment_method` varchar(50) DEFAULT 'cod',
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`order_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` VALUES
('1', '1', '2025-05-27 23:35:30', 'Thanh Hóa', '105000.00', 'Completed', 'cod', '0.00', '0.00', 'Gọi điện khi tới nơi', '2025-05-27 23:35:30', '2025-05-30 04:53:09'),
('2', '1', '2025-05-28 17:08:18', 'Thanh Hóa', '35000.00', 'Completed', 'cod', '0.00', '0.00', '', '2025-05-28 17:08:18', '2025-05-29 00:24:41'),
('3', '2', '2025-05-28 17:10:20', 'Hà Nội', '180000.00', 'Completed', 'cod', '0.00', '0.00', '', '2025-05-28 17:10:20', '2025-05-31 08:20:43'),
('4', '1', '2025-05-28 17:45:38', 'Thanh Hóa', '25000.00', 'Completed', 'cod', '0.00', '0.00', '', '2025-05-28 17:45:38', '2025-05-29 15:15:12'),
('5', '2', '2025-05-28 22:05:06', 'Hà Nội', '397800.00', 'Completed', 'cod', '0.00', '44200.00', '', '2025-05-28 22:05:06', '2025-05-31 08:21:12'),
('6', '1', '2025-05-29 08:13:35', 'Sầm Sơn - Thanh Hóa', '685000.00', 'Processing', 'cod', '0.00', '200000.00', '', '2025-05-29 08:13:35', '2025-05-29 08:14:17'),
('7', '1', '2025-05-31 10:49:24', 'Sầm Sơn - Thanh Hóa', '80000.00', 'Pending', 'cod', '0.00', '0.00', NULL, '2025-05-31 10:49:24', '2025-05-31 10:49:24'),
('8', '1', '2025-05-31 10:52:40', 'Sầm Sơn - Thanh Hóa', '52000.00', 'Pending', 'cod', '0.00', '0.00', NULL, '2025-05-31 10:52:40', '2025-05-31 10:52:40'),
('9', '1', '2025-05-31 10:57:07', 'Sầm Sơn - Thanh Hóa', '216000.00', 'Shipping', 'cod', '0.00', '24000.00', '', '2025-05-31 10:57:07', '2025-05-31 19:34:50'),
('10', '3', '2025-05-31 13:37:40', 'TP.HCM', '695000.00', 'Cancelled', 'cod', '0.00', '0.00', '', '2025-05-31 13:37:40', '2025-06-01 04:15:04'),
('11', '3', '2025-05-31 13:43:51', 'TP.HCM', '900000.00', 'Completed', 'cod', '0.00', '100000.00', '', '2025-05-31 13:43:51', '2025-06-01 04:15:21'),
('12', '1', '2025-05-31 14:37:50', 'Sầm Sơn - Thanh Hóa', '25000.00', 'Shipping', 'cod', '0.00', '0.00', '', '2025-05-31 14:37:50', '2025-06-01 04:10:40'),
('13', '1', '2025-05-31 14:40:01', 'Sầm Sơn - Thanh Hóa', '25000.00', 'Pending', 'cod', '0.00', '0.00', NULL, '2025-05-31 14:40:01', '2025-05-31 14:40:01'),
('14', '3', '2025-06-01 04:09:56', 'TP.HCM', '300000.00', 'Processing', 'cod', '0.00', '0.00', '', '2025-06-01 04:09:56', '2025-06-01 04:10:36'),
('15', '1', '2025-06-03 05:59:28', 'Sầm Sơn - Thanh Hóa', '405000.00', 'Shipping', 'cod', '0.00', '45000.00', '', '2025-06-03 05:59:28', '2025-06-05 06:23:16'),
('16', '1', '2025-06-05 05:26:07', '215 Lý tự trọng, phường Trường Sơn, Thanh Hóa', '400000.00', 'Completed', '0', '0.00', '0.00', '', '2025-06-05 05:26:07', '2025-06-07 09:10:23'),
('18', '1', '2025-06-05 05:54:16', '215 lÝ TỰ trọng - P.Trường Sơn, Thanh Hóa, vietnam', '385000.00', 'Completed', '0', '0.00', '96250.00', '', '2025-06-05 05:54:16', '2025-06-05 06:24:11'),
('19', '1', '2025-06-05 06:11:49', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '75000.00', 'Processing', '0', '0.00', '0.00', '', '2025-06-05 06:11:49', '2025-06-05 06:21:43'),
('20', '1', '2025-06-07 17:20:47', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '25000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-07 17:20:47', '2025-06-07 17:20:47'),
('21', '1', '2025-06-07 17:23:23', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '305000.00', 'Pending', '0', '0.00', '152500.00', NULL, '2025-06-07 17:23:23', '2025-06-07 17:23:23'),
('22', '1', '2025-06-07 17:53:42', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '8000.00', 'Completed', '0', '0.00', '0.00', '', '2025-06-07 17:53:42', '2025-06-07 17:54:02'),
('23', '1', '2025-06-07 17:59:37', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '75000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-07 17:59:37', '2025-06-07 17:59:37'),
('24', '2', '2025-06-07 18:03:53', 'Hai Bà Trưng - Hà Nội - Việt Nam, Hà Nội, Việt Nam', '40000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-07 18:03:53', '2025-06-07 18:03:53'),
('25', '2', '2025-06-07 18:05:54', 'Hai Bà Trưng - Hà Nội - Việt Nam, Hà Nội, Việt Nam', '30000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-07 18:05:54', '2025-06-07 18:05:54'),
('26', '2', '2025-06-07 18:06:06', 'Hai Bà Trưng - Hà Nội - Việt Nam, Hà Nội, Việt Nam', '0.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-07 18:06:06', '2025-06-07 18:06:06'),
('27', '2', '2025-06-07 18:06:16', 'Hai Bà Trưng - Hà Nội - Việt Nam, Hà Nội, Việt Nam', '20000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-07 18:06:16', '2025-06-07 18:06:16'),
('28', '2', '2025-06-07 18:14:45', 'Hai Bà Trưng - Hà Nội - Việt Nam, Hà Nội, Việt Nam', '48000.00', 'Completed', '0', '0.00', '0.00', '', '2025-06-07 18:14:45', '2025-06-07 18:15:16'),
('29', '2', '2025-06-07 18:32:44', 'Hai Bà Trưng - Hà Nội - Việt Nam, Hà Nội, Việt Nam', '450000.00', 'Completed', '0', '0.00', '0.00', '', '2025-06-07 18:32:44', '2025-06-07 18:33:50'),
('30', '17', '2025-06-09 14:54:31', '245 Lý Tự Trọng - Sàm sơn, Thanh Hóa, Việt Nam', '585000.00', 'Completed', '0', '0.00', '146250.00', '', '2025-06-09 14:54:31', '2025-06-09 14:55:45'),
('31', '17', '2025-06-09 14:59:08', 'â, Bình Định, Việt Nam', '2000000.00', 'Completed', '0', '0.00', '0.00', '', '2025-06-09 14:59:08', '2025-07-04 00:55:20'),
('32', '17', '2025-06-09 15:04:58', 'ssss, Bắc Ninh, Việt Nam', '25000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-09 15:04:58', '2025-06-09 15:04:58'),
('33', '17', '2025-06-09 15:18:29', 'df, Bình Thuận, Việt Nam', '15000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-06-09 15:18:29', '2025-06-09 15:18:29'),
('34', '1', '2025-07-04 16:30:33', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '15000.00', 'Completed', '0', '0.00', '0.00', '', '2025-07-04 16:30:33', '2025-07-04 16:31:24'),
('35', '1', '2025-07-20 22:06:47', 'Sầm Sơn - Thanh Hóa, Thanh Hóa, Việt Nam', '495000.00', 'Pending', '0', '0.00', '0.00', NULL, '2025-07-20 22:06:47', '2025-07-20 22:06:47'),
('36', '17', '2025-07-20 22:51:29', '245 Ly Tu Trong, Phuong Truong Son, Thanh Hóa, Việt Nam', '220000.00', 'Processing', '0', '0.00', '10000.00', '', '2025-07-20 22:51:29', '2025-07-20 22:53:21');

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 1 hour),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` VALUES
('3', '1', '5c82c846a9ba9282dd02a272ef07b2019abf8a50d9a2b5b07f194eeb10dcb86a', '2025-05-31 03:32:09', '2025-05-31 04:32:09');

--
-- Table structure for table `product_discounts`
--

CREATE TABLE `product_discounts` (
  `discount_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`discount_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `product_discounts_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_discounts`
--

INSERT INTO `product_discounts` VALUES
('1', '153', 'percentage', '20.00', '2025-05-01 00:00:00', '2025-12-31 23:59:59', '1', '2025-05-28 16:49:40', '2025-05-28 16:49:40'),
('2', '154', 'fixed', '5000.00', '2025-05-01 00:00:00', '2025-12-31 23:59:59', '1', '2025-05-28 16:49:40', '2025-05-28 16:49:40'),
('3', '155', 'percentage', '15.00', '2025-05-01 00:00:00', '2025-12-31 23:59:59', '1', '2025-05-28 16:49:40', '2025-05-28 16:49:40');

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_customer_product_review` (`customer_id`,`product_id`),
  KEY `product_id` (`product_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` VALUES
('2', '154', '2', '4', 'Chất lượng tốt, đóng gói cẩn thận', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('4', '156', '1', '4', 'Sản phẩm tươi ngon, sẽ mua lại', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('5', '157', '2', '3', 'Sản phẩm tạm được', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('6', '158', '3', '5', 'Củ dền tươi ngon, màu sắc đẹp', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('7', '159', '1', '4', 'Nấm đùi gà thơm ngon, chất lượng tốt', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('8', '160', '2', '5', 'Nấm kim châm tươi giòn, rất ngon', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('9', '161', '3', '4', 'Thịt ba chỉ tươi ngon, giao hàng đúng hẹn', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('10', '162', '1', '5', 'Sườn non mềm, ngon, đóng gói cẩn thận', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('11', '163', '2', '4', 'Thịt bò phi lê chất lượng cao', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('12', '164', '3', '5', 'Bò cuộn nấm ngon tuyệt vời', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('13', '165', '1', '4', 'Tôm sú tươi ngon, size lớn đều', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('14', '166', '2', '5', 'Cá hồi tươi ngon, thịt đẹp', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('15', '167', '3', '4', 'Chả giò rế thịt giòn ngon', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('16', '168', '1', '5', 'Viên tôm dai ngon, nhiều thịt tôm', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('17', '169', '2', '4', 'Kem vani Wall\'s ngon, đóng gói cẩn thận', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('18', '170', '3', '5', 'Kem ốc quế Merino ngon, vỏ bánh giòn', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('19', '171', '1', '5', 'Gạo ST25 thơm ngon, dẻo', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('20', '172', '2', '4', 'Đậu xanh sạch, nấu chín nhanh', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('21', '173', '3', '5', 'Coca Cola ngon lạnh, đúng chuẩn', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('22', '174', '1', '4', 'Pepsi sảng khoái, giá tốt', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('23', '175', '2', '5', 'Nước ép cam tươi ngon, vị tự nhiên', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('24', '176', '3', '4', 'Nước ép táo ngọt vừa, rất fresh', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('25', '177', '1', '5', 'Bột ngọt Ajinomoto chất lượng tốt', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('26', '178', '2', '4', 'Tiêu đen xay thơm nồng, đậm vị', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('27', '179', '3', '5', 'Bột ớt cay vừa phải, màu đẹp', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('29', '181', '2', '4', 'Bánh quy socola Goody Chips ngon, không ngấy', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('30', '182', '3', '5', 'Bánh quy socola yến mạch healthy và ngon', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('31', '183', '1', '4', 'Kẹo dẻo Alpenliebe ngon, nhiều vị', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('32', '184', '2', '5', 'Kẹo Migita cay nồng, sảng khoái', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('33', '185', '3', '4', 'Chocolate Dairy Milk béo ngậy, ngon', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('34', '186', '1', '3', 'Muối Vifon tinh khiết, mặn vừa', '2025-05-25 12:11:01', '2025-06-07 17:54:13'),
('35', '187', '2', '4', 'Đường trắng tinh luyện sạch, tan nhanh', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('36', '188', '3', '5', 'Chuối tiêu ngọt, thơm, chín vừa', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('37', '189', '1', '4', 'Thanh long ruột trắng ngọt, giòn', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('38', '191', '2', '5', 'Cam sành ngọt thanh, nhiều nước', '2025-05-25 12:11:01', '2025-05-25 12:11:01'),
('39', '192', '1', '4', 'Sản phẩm tốt', '2025-06-07 17:20:02', '2025-06-07 17:20:02'),
('40', '192', '2', '5', 'Ngoan', '2025-06-07 18:05:24', '2025-06-07 18:05:24'),
('41', '174', '2', '4', 'hơi kém', '2025-06-07 18:11:45', '2025-06-07 18:11:45'),
('42', '168', '17', '4', 'sản phẩm tốt', '2025-06-09 14:56:27', '2025-06-09 14:56:27'),
('43', '153', '1', '5', 'quá tuyệt vời khá logic!!', '2025-07-04 16:31:55', '2025-07-04 16:32:04');

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `origin` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `sold_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`product_id`),
  KEY `subcategory_id` (`subcategory_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`subcategory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` VALUES
('153', '1', 'Rau muống', 'Rau muống tươi được trồng tại Đà Lạt, có màu xanh đậm, thân giòn và lá mướt. Giàu vitamin A, C và sắt, rất tốt cho sức khỏe. Phù hợp để xào, luộc hoặc nấu canh. Được thu hoạch và đóng gói trong ngày để đảm bảo độ tươi ngon nhất.', 'Việt Nam', '15000.00', '../images/products/rau-muong.jpg', '95', '5', '2025-05-25 18:15:34', '2025-07-04 16:30:33'),
('154', '1', 'Cải thìa', 'Cải thìa tươi sạch, lá to dày, cuống trắng giòn ngọt. Được trồng theo phương pháp hữu cơ, không sử dụng thuốc trừ sâu độc hại. Giàu chất xơ, vitamin C và các khoáng chất. Thích hợp để xào, nấu canh hoặc làm salad. Được đóng gói cẩn thận để giữ độ tươi.', 'Việt Nam', '20000.00', '../images/products/cai-thia.jpg', '86', '14', '2025-05-25 18:15:34', '2025-06-07 18:06:16'),
('155', '1', 'Rau cải ngọt', 'Rau cải ngọt tươi được trồng tại vùng rau sạch, có vị ngọt đặc trưng, thân mềm và lá xanh mướt. Chứa nhiều vitamin và khoáng chất thiết yếu. Thích hợp để xào nhanh, nấu canh hoặc làm nguyên liệu trong các món soup. Được kiểm soát chất lượng nghiêm ngặt từ khâu giống đến thu hoạch.', 'Việt Nam', '18000.00', '../images/products/cai-ngot.jpg', '89', '6', '2025-05-25 18:15:34', '2025-06-05 06:11:49'),
('156', '2', 'Cà rốt', 'Cà rốt Đà Lạt có màu cam đẹp, thịt chắc, vị ngọt đậm đà. Được trồng trong điều kiện khí hậu mát mẻ, đất đai màu mỡ. Giàu beta-carotene, vitamin A và chất chống oxy hóa. Có thể ăn sống, nấu chín, ép nước hoặc làm bánh. Được thu hoạch đúng độ để đảm bảo hương vị tốt nhất.', 'Việt Nam', '25000.00', '../images/products/ca-rot.jpg', '148', '10', '2025-05-25 18:15:34', '2025-05-31 07:40:01'),
('157', '2', 'Khoai tây', 'Khoai tây Đà Lạt có vỏ mỏng, thịt vàng, độ bột cao. Được trồng trong điều kiện thổ nhưỡng và khí hậu lý tưởng của Đà Lạt. Giàu tinh bột, vitamin C và kali. Thích hợp để chiên, nướng, hấp hoặc làm salad. Được bảo quản trong điều kiện tối ưu để giữ độ tươi ngon.', 'Việt Nam', '30000.00', '../images/products/khoai-tay.jpg', '118', '9', '2025-05-25 18:15:34', '2025-06-07 18:05:54'),
('158', '2', 'Củ dền', 'Củ dền tươi có màu đỏ tím đặc trưng, thịt chắc mịn. Được trồng theo phương pháp hữu cơ, đảm bảo an toàn. Giàu chất chống oxy hóa, sắt và folate. Có thể luộc, nướng hoặc ép nước. Tốt cho người thiếu máu và người muốn thanh lọc cơ thể. Được đóng gói kỹ để tránh dập nát.', 'Việt Nam', '22000.00', '../images/products/cu-den.jpg', '79', '5', '2025-05-25 18:15:34', '2025-05-30 20:52:40'),
('159', '3', 'Nấm đùi gà', 'Nấm đùi gà tươi có thịt dày, màu trắng ngà, mùi thơm đặc trưng. Được nuôi trồng trong môi trường sạch, kiểm soát nghiêm ngặt. Giàu protein, ít calo và chất xơ. Thích hợp để nấu súp, xào hoặc nướng. Có thể thay thế thịt trong các món chay. Được đóng gói trong hộp đặc biệt để giữ độ tươi.', 'Việt Nam', '45000.00', '../images/products/nam-dui-ga.jpg', '47', '3', '2025-05-25 18:15:34', '2025-05-28 18:13:35'),
('160', '3', 'Nấm kim châm', 'Nấm kim châm tươi có sợi mảnh, trắng muốt, giòn ngọt. Được nuôi trồng trong nhà kính hiện đại với công nghệ Nhật Bản. Giàu protein, vitamin D và khoáng chất. Lý tưởng cho các món lẩu, súp hoặc xào. Được đóng gói trong khay nhựa đặc biệt để bảo quản tốt nhất.', 'Việt Nam', '30000.00', '../images/products/nam-kim-cham.jpg', '60', '0', '2025-05-25 18:15:34', '2025-05-25 18:15:34'),
('161', '4', 'Thịt ba chỉ', 'Thịt ba chỉ tươi được lựa chọn từ những con heo khỏe mạnh, được nuôi theo tiêu chuẩn VietGAP. Có tỷ lệ nạc mỡ cân đối, thớ thịt mịn, màu hồng tươi. Thích hợp để nướng, quay, kho hoặc làm thịt cuốn. Được đóng gói và bảo quản trong điều kiện vệ sinh an toàn thực phẩm.', 'Việt Nam', '180000.00', '../images/products/thit-ba-chi.jpg', '31', '9', '2025-05-25 18:15:34', '2025-07-20 22:06:47'),
('162', '4', 'Sườn non', 'Sườn non heo được tuyển chọn kỹ từ những phần sườn ngon nhất, thịt mềm, không có mùi lạ. Được cắt đều và đẹp mắt. Giàu protein và canxi, thích hợp để nướng, kho tộ hoặc làm sườn ram. Đảm bảo nguồn gốc rõ ràng và được kiểm dịch nghiêm ngặt.', 'Việt Nam', '200000.00', '../images/products/suon-non.jpg', '23', '15', '2025-05-25 18:15:34', '2025-05-31 06:43:51'),
('163', '5', 'Thịt bò phi lê', 'Thịt bò phi lê tươi được nhập từ những trang trại bò chất lượng cao. Thớ thịt mịn, màu đỏ tươi, không có gân, được cắt chuẩn theo kỹ thuật chuyên nghiệp. Giàu protein, sắt và vitamin B12. Lý tưởng cho các món bít tết, bò lúc lắc hoặc bò cuốn. Được bảo quản ở nhiệt độ thích hợp để giữ độ tươi ngon.', 'Việt Nam', '350000.00', '../images/products/bo-phi-le.jpg', '24', '6', '2025-05-25 18:15:34', '2025-05-28 08:05:06'),
('164', '5', 'Bò cuộn nấm', 'Bò cuộn nấm đặc biệt được chế biến từ thịt bò tươi ngon và nấm tươi chất lượng cao. Thịt bò được cắt mỏng đều, cuộn với nấm kim châm tươi ngon. Được tẩm ướp với công thức đặc biệt. Thích hợp để nướng hoặc áp chảo. Đóng gói tiện lợi, sẵn sàng để chế biến.', 'Việt Nam', '400000.00', '../images/products/bo-cuon-nam.jpg', '0', '11', '2025-05-25 18:15:34', '2025-06-09 14:59:08'),
('165', '6', 'Tôm sú', 'Tôm sú tươi size lớn được đánh bắt từ vùng biển sạch hoặc nuôi trong môi trường tự nhiên. Tôm có vỏ cứng, thịt chắc, màu sắc tươi sáng. Giàu protein, omega-3 và khoáng chất. Thích hợp để nướng, hấp hoặc làm gỏi. Được sơ chế sạch sẽ và đóng gói lạnh để đảm bảo độ tươi.', 'Việt Nam', '450000.00', '../images/products/tom-su.jpg', '33', '2', '2025-05-25 18:15:34', '2025-05-28 10:30:17'),
('166', '6', 'Cá hồi', 'Cá hồi tươi phi lê được nhập khẩu từ những trang trại nuôi cá hồi chất lượng cao. Thịt có màu hồng cam đẹp mắt, vân mỡ đều, không tanh. Giàu omega-3, protein và vitamin D. Thích hợp để làm sashimi, nướng hoặc hấp. Được đóng gói trong điều kiện lạnh đặc biệt để giữ độ tươi.', 'Việt Nam', '500000.00', '../images/products/ca-hoi.jpg', '30', '0', '2025-05-25 18:15:34', '2025-05-25 18:15:34'),
('167', '7', 'Chả giò rế thịt', 'Chả giò rế thịt được làm từ thịt heo tươi ngon, tôm biển và nấm mèo, bọc trong lớp bánh tráng rế giòn rụm. Được tẩm ướp với công thức gia truyền, tạo nên hương vị đặc trưng. Đóng gói tiện lợi 500g, phù hợp cho gia đình. Có thể chiên trực tiếp, không cần rã đông.', 'Việt Nam', '85000.00', '../images/products/cha-gio-re-thit.jpg', '99', '1', '2025-05-25 18:15:34', '2025-07-20 22:06:47'),
('168', '7', 'Viên tôm', 'Viên tôm được chế biến từ tôm tươi nguyên chất, giữ nguyên độ dai và ngọt tự nhiên. Được làm theo công thức truyền thống, không chất bảo quản. Đóng gói 300g, tiện lợi sử dụng. Thích hợp để chiên, nấu lẩu hoặc soup. Bảo quản đông lạnh để giữ hương vị tốt nhất.', 'Việt Nam', '120000.00', '../images/products/tom-vien.jpg', '78', '2', '2025-05-25 18:15:34', '2025-07-20 22:51:29'),
('169', '8', 'Kem ly Wall\'s vị vani', 'Kem vani Wall\'s với hương vị ngọt ngào, béo mịn từ sữa tươi cao cấp. Được sản xuất theo công nghệ hiện đại, đảm bảo vệ sinh an toàn thực phẩm. Hộp 500ml tiện lợi, phù hợp cho gia đình. Kết cấu mềm mịn, dễ múc và thưởng thức. Được bảo quản ở nhiệt độ âm để giữ độ ngon.', 'Việt Nam', '15000.00', '../images/products/walls-kem-ly-vani.jpg', '58', '2', '2025-05-25 18:15:34', '2025-07-20 22:06:47'),
('170', '8', 'Kem ốc quế Merino vị socola', 'Kem ốc quế Merino vị socola với lớp kem mềm mịn, đậm đà hương vị cacao nguyên chất. Vỏ bánh ốc quế giòn tan, được nướng thủ công. Được sản xuất từ nguyên liệu tự nhiên, không chất bảo quản. Đóng gói 500ml, tiện lợi sử dụng. Thích hợp cho mọi lứa tuổi.', 'Việt Nam', '15000.00', '../images/products/merino-kem-oc-que-socola.jpg', '60', '0', '2025-05-25 18:15:34', '2025-05-26 12:21:24'),
('171', '9', 'Gạo ST25', 'Gạo ST25 - giống lúa đạt giải nhất gạo ngon nhất thế giới. Hạt gạo dài, trắng trong, có hương thơm tự nhiên đặc trưng. Khi nấu cơm mềm dẻo, không bị nát, giữ được độ thơm lâu. Được đóng gói kỹ trong túi 5kg, bảo quản tốt. Nguồn gốc rõ ràng từ vùng đồng bằng sông Cửu Long.', 'Việt Nam', '150000.00', '../images/products/gao-st25.jpg', '200', '0', '2025-05-25 18:15:34', '2025-05-25 18:15:34'),
('172', '10', 'Đậu xanh', 'Đậu xanh được tuyển chọn từ những hạt đều, không sâu mọt. Được trồng theo phương pháp hữu cơ, đảm bảo an toàn. Giàu protein thực vật, chất xơ và khoáng chất. Thích hợp để nấu chè, làm bánh hoặc nảy mầm. Đóng gói 500g, bảo quản kỹ để giữ độ tươi ngon.', 'Việt Nam', '45000.00', '../images/products/dau-xanh.jpg', '146', '4', '2025-05-25 18:15:34', '2025-07-20 22:06:47'),
('173', '11', 'Coca Cola', 'Nước ngọt Coca Cola với hương vị đặc trưng, sảng khoái. Được sản xuất theo công nghệ hiện đại, đảm bảo vệ sinh an toàn thực phẩm. Lon 330ml nhỏ gọn, tiện lợi mang theo. Thích hợp để uống trực tiếp hoặc pha chế. Được đóng gói cẩn thận để giữ ga và hương vị.', 'Việt Nam', '12000.00', '../images/products/coca-cola-lon.jpg', '200', '0', '2025-05-25 18:15:34', '2025-05-26 05:23:54'),
('174', '11', 'Pepsi', 'Nước ngọt Pepsi với hương vị cola đặc trưng, giải khát tức thì. Được sản xuất từ công thức độc quyền, đảm bảo chất lượng quốc tế. Lon 330ml tiện dụng, dễ bảo quản. Thích hợp cho mọi hoạt động giải trí và thể thao. Được kiểm soát chất lượng nghiêm ngặt.', 'Việt Nam', '12000.00', '../images/products/pepsi-lon.jpg', '179', '1', '2025-05-25 18:15:34', '2025-05-28 08:05:06'),
('175', '12', 'Nước ép cam', 'Nước ép cam tươi 100% từ cam tươi nguyên chất, không thêm đường. Giữ nguyên vitamin C và các dưỡng chất tự nhiên. Chai 1L tiện lợi cho gia đình sử dụng. Được sản xuất và đóng chai trong ngày để đảm bảo độ tươi ngon. Thích hợp cho mọi lứa tuổi.', 'Việt Nam', '45000.00', '../images/products/chai-nuoc-ep-cam.jpg', '50', '0', '2025-05-25 18:15:34', '2025-05-26 05:27:02'),
('176', '12', 'Nước ép táo', 'Nước ép táo tươi 100% từ táo tươi nguyên chất, không chất bảo quản. Giữ nguyên vị ngọt tự nhiên và các vitamin thiết yếu. Chai 1L phù hợp sử dụng trong gia đình. Được chế biến từ táo tươi chọn lọc, đảm bảo vệ sinh. Thích hợp cho người ăn kiêng.', 'Việt Nam', '48000.00', '../images/products/chai-nuoc-ep-tao.jpg', '2', '1', '2025-05-25 18:15:34', '2025-06-09 14:58:15'),
('177', '42', 'Bột ngọt Ajinomoto', 'Bột ngọt Ajinomoto tinh khiết, được sản xuất theo công nghệ lên men tự nhiên từ mía đường và tinh bột sắn. Tạo vị umami đặc trưng cho món ăn. Gói 500g tiện lợi sử dụng. Được đóng gói kỹ, chống ẩm tốt. Đạt tiêu chuẩn vệ sinh an toàn thực phẩm quốc tế.', 'Việt Nam', '25000.00', '../images/products/bot-ngot-ajinomoto.jpg', '100', '0', '2025-05-25 18:15:34', '2025-05-26 08:46:33'),
('178', '13', 'Gói tiêu đen xay', 'Tiêu đen xay từ hạt tiêu Phú Quốc chất lượng cao, giữ nguyên hương vị cay nồng đặc trưng. Được rang xay theo công nghệ hiện đại, giữ trọn hương thơm. Gói 120g tiện lợi sử dụng. Đóng gói kỹ để bảo quản hương vị. Không chất bảo quản, an toàn cho sức khỏe.', 'Việt Nam', '35000.00', '../images/products/tieu-den-xay.jpg', '69', '11', '2025-05-25 18:15:34', '2025-06-05 05:54:16'),
('179', '13', 'Bột ớt', 'Bột ớt Hàn Quốc được chế biến từ ớt tươi chất lượng cao, có độ cay vừa phải và màu đỏ tươi đẹp. Thích hợp làm gia vị cho các món Hàn Quốc. Gói 100g tiện lợi sử dụng. Được đóng gói kỹ, bảo quản tốt. Không chứa phẩm màu nhân tạo.', 'Việt Nam', '30000.00', '../images/products/bot-ot-goi.jpg', '69', '1', '2025-05-25 18:15:34', '2025-05-28 08:05:06'),
('180', '14', 'Bánh quy bơ Danisa', 'Bánh quy bơ Danisa được làm từ bơ Đan Mạch thượng hạng, tạo nên hương vị béo ngậy đặc trưng. Vỏ bánh giòn tan, không vụn. Hộp 900g sang trọng, thích hợp làm quà tặng. Được sản xuất theo công nghệ Đan Mạch. Bảo quản tốt để giữ độ giòn.', 'Việt Nam', '75000.00', '../images/products/banh-quy-bo-danisa.jpg', '58', '2', '2025-05-25 18:15:34', '2025-06-07 17:59:37'),
('181', '14', 'Bánh quy socola Goody Chips', 'Bánh quy socola Goody Chips với lớp socola đen đậm đà, kết hợp với bánh quy giòn tan. Được làm từ bột mì cao cấp và socola nguyên chất. Thích hợp để ăn vặt hoặc tráng miệng. Đóng gói tiện lợi, dễ bảo quản. Hương vị phù hợp với mọi lứa tuổi.', 'Việt Nam', '25000.00', '../images/products/banh-quy-socola-goody.jpg', '99', '1', '2025-05-25 18:15:34', '2025-05-28 08:05:06'),
('182', '14', 'Bánh quy socola', 'Bánh quy socola yến mạch kết hợp giữa vị béo của socola và vị ngọt tự nhiên của yến mạch. Giàu chất xơ và năng lượng. Được làm từ nguyên liệu tự nhiên, không chất bảo quản. Thích hợp cho người ăn kiêng và tập thể thao. Đóng gói tiện lợi để mang theo.', 'Việt Nam', '22000.00', '../images/products/banh-quy-socola-cosy.jpg', '119', '1', '2025-05-25 18:15:34', '2025-06-05 06:11:49'),
('183', '15', 'Kẹo dẻo Alpenliebe', 'Kẹo dẻo Alpenliebe với nhiều hương vị trái cây tự nhiên, kết cấu mềm dẻo độc đáo. Được sản xuất từ nguyên liệu tự nhiên, màu sắc bắt mắt. Gói 80g tiện lợi mang theo. Thích hợp cho mọi lứa tuổi. Được đóng gói kỹ để giữ độ dẻo tốt nhất.', 'Việt Nam', '15000.00', '../images/products/keo-deo-alpenliebe.jpg', '149', '1', '2025-05-25 18:15:34', '2025-06-09 14:54:31'),
('184', '15', 'Kẹo Migita', 'Kẹo Migita với hương vị gừng cay nồng, giúp sảng khoái và ấm người. Được làm từ gừng tự nhiên, không hương liệu nhân tạo. Gói 100g tiện lợi sử dụng. Thích hợp cho ngày lạnh hoặc khi đau họng. Được đóng gói kỹ để tránh ẩm.', 'Việt Nam', '12000.00', '../images/products/keo-migita.jpg', '160', '0', '2025-05-25 18:15:34', '2025-05-26 05:44:03'),
('185', '15', 'Thanh chocolate Dairy Milk', 'Thanh chocolate Dairy Milk với hương vị sữa béo ngậy, tan chảy trong miệng. Được làm từ sữa tươi và ca cao chất lượng cao. Thanh 90g tiện lợi thưởng thức. Bao bì đẹp mắt, thích hợp làm quà tặng. Được bảo quản trong điều kiện mát để giữ nguyên hình dáng.', 'Việt Nam', '35000.00', '../images/products/dairy-milk-chocolate.jpg', '29', '5', '2025-05-25 18:15:34', '2025-05-31 06:37:40'),
('186', '42', 'Muối Vifon', 'Muối iốt Vifon được sản xuất theo công nghệ hiện đại, đảm bảo hàm lượng iốt cần thiết cho cơ thể. Tinh khiết, mịn, dễ tan. Gói 500g tiện lợi sử dụng trong gia đình. Được kiểm nghiệm chất lượng nghiêm ngặt. Bao bì kín để tránh ẩm và giữ độ mặn.', 'Việt Nam', '8000.00', '../images/products/vifon-muoi.jpg', '15', '1', '2025-05-25 18:15:34', '2025-06-07 17:53:42'),
('187', '42', 'Đường trắng tinh luyện', 'Đường trắng tinh luyện từ mía tươi chất lượng cao, có độ tinh khiết cao, hạt đều và trắng sáng. Dễ tan trong nước nóng và lạnh. Gói 1kg tiện lợi sử dụng. Được sản xuất theo quy trình hiện đại. Đóng gói kỹ để tránh ẩm và vón cục.', 'Việt Nam', '25000.00', '../images/products/duong-tinh-luyen.jpg', '150', '0', '2025-05-25 18:15:34', '2025-05-27 08:43:56'),
('188', '16', 'Chuối Tiêu', 'Chuối tiêu được trồng tại vùng đất phù sa màu mỡ, cho quả nhỏ đều, vỏ mỏng vàng óng khi chín. Thịt chuối mềm, ngọt vừa phải với hương thơm đặc trưng. Giàu kali và vitamin B6. Được thu hoạch đúng độ chín, đóng gói cẩn thận theo nải. Thích hợp ăn trực tiếp hoặc chế biến.', 'Việt Nam', '35000.00', '../images/products/6831ea2a7d319.jpg', '58', '2', '2025-05-26 16:47:54', '2025-07-20 22:06:47'),
('189', '16', 'Thanh long ruột trắng', 'Thanh long ruột trắng với vỏ hồng đỏ bắt mắt, nhiều tai xanh tươi. Ruột trắng tinh khiết với các hạt đen nhỏ bổ dưỡng. Vị ngọt thanh mát, giàu vitamin C và chất xơ. Được trồng theo tiêu chuẩn VietGAP, không thuốc trừ sâu. Đóng gói cẩn thận để bảo vệ trái.', 'Việt Nam', '45000.00', '../images/products/6832c8ac5d2f9.jpg', '50', '3', '2025-05-27 08:37:16', '2025-05-28 18:13:35'),
('191', '16', 'Cam sành', 'Cam sành tươi ngon với vỏ xanh đậm khi còn non và chuyển vàng cam khi chín. Múi cam mọng nước, vị chua ngọt cân bằng. Giàu vitamin C và các chất chống oxy hóa. Được trồng tại vùng chuyên canh, không sử dụng thuốc bảo vệ thực vật độc hại. Bảo quản tốt để giữ độ tươi.', 'Việt Nam', '20000.00', '../images/products/6832c92432dd5.jpg', '55', '10', '2025-05-27 08:39:16', '2025-05-31 06:37:40'),
('192', '16', 'Dưa hấu không hạt', 'Dưa hấu không hạt được lai tạo đặc biệt, cho quả tròn đều hoặc dài. Vỏ xanh sọc đen bóng đẹp, ruột đỏ tươi không hạt hoặc rất ít hạt. Thịt dưa giòn ngọt, nhiều nước. Được trồng trong điều kiện tự nhiên, đảm bảo an toàn. Thích hợp cho mọi lứa tuổi.', 'Việt Nam', '25000.00', '../images/products/6832c99579d3c.jpg', '4', '35', '2025-05-27 08:41:09', '2025-06-09 15:04:58'),
('193', '17', 'Xoài Cát Chu', 'Xoài Cát Chu với hình dáng thon dài đặc trưng, vỏ vàng óng khi chín. Thịt xoài vàng đậm, mềm mịn, vị ngọt đậm đà và thơm nồng. Giàu vitamin A và C. Được trồng tại vùng đất chuyên canh, thu hoạch đúng độ chín. Đóng gói cẩn thận để tránh dập nát.', 'Việt Nam', '40000.00', '../images/products/6832cacd78573.jpg', '76', '1', '2025-05-27 08:46:21', '2025-06-07 18:03:53'),
('194', '33', 'Gói Bánh bao nhân thịt', 'Bánh bao nhân thịt được làm từ bột mì cao cấp, nhân thịt heo tươi ngon kết hợp với trứng cút, xá xíu và nấm hương. Vỏ bánh mềm mịn, trắng muốt. Được chế biến trong ngày, đảm bảo vệ sinh. Đóng gói tiện lợi, hấp chín là có thể thưởng thức ngay.', 'Việt Nam', '50000.00', '../images/products/6832ed68d9e83.jpg', '26', '4', '2025-05-27 11:14:00', '2025-07-20 22:51:29'),
('196', '51', 'Bột chiên xù', 'Bột dùng để chiên xù', NULL, '9000.00', '../images/products/683d260c9a890.jpg', '45', '0', '2025-06-03 08:16:46', '2025-07-10 15:29:32');

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `subcategory_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`subcategory_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` VALUES
('1', '2', 'Rau xanh', '', '2025-05-25 18:12:21', '2025-05-26 16:03:44'),
('2', '2', 'Củ quả', '', '2025-05-25 18:12:21', '2025-05-26 16:03:30'),
('3', '2', 'Nấm các loại', '', '2025-05-25 18:12:21', '2025-05-26 16:03:37'),
('4', '3', 'Thịt heo', 'Các loại thịt heo tươi', '2025-05-25 18:12:21', '2025-05-26 05:53:25'),
('5', '3', 'Thịt bò', 'Các loại thịt bò tươi', '2025-05-25 18:12:21', '2025-05-26 05:53:28'),
('6', '3', 'Thủy - Hải sản', 'Hải sản tươi sống', '2025-05-25 18:12:21', '2025-05-26 05:53:51'),
('7', '14', 'Đồ chế biến sẵn', '', '2025-05-25 18:12:21', '2025-05-26 16:32:46'),
('8', '7', 'Kem các loại', '', '2025-05-25 18:12:21', '2025-05-26 16:02:23'),
('9', '12', 'Gạo', '', '2025-05-25 18:12:21', '2025-05-26 16:03:53'),
('10', '10', 'Hạt', NULL, '2025-05-25 18:12:21', '2025-05-26 08:58:35'),
('11', '5', 'Nước ngọt', '', '2025-05-25 18:12:21', '2025-05-26 16:02:42'),
('12', '5', 'Nước trái cây', '', '2025-05-25 18:12:21', '2025-05-26 16:02:51'),
('13', '9', 'Gia vị bột', '', '2025-05-25 18:12:21', '2025-05-26 16:02:06'),
('14', '7', 'Bánh ngọt', NULL, '2025-05-25 18:12:21', '2025-05-26 08:25:15'),
('15', '7', 'Kẹo các loại', '', '2025-05-25 18:12:21', '2025-05-26 16:02:29'),
('16', '1', 'Hoa quả Việt', 'Các loại hoa quả được sản xuất trong nước', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('17', '1', 'Hoa quả Nước ngoài', 'Các loại hoa quả được sản xuất ở nước ngoài', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('18', '3', 'Thịt gia cầm', 'Các loại thịt: Gà , vịt, ngỗng, dê,...', '2025-05-25 11:12:21', '2025-05-26 08:07:58'),
('19', '3', 'Các loại trứng', 'Các loại trứng nhập khẩu', '2025-05-25 11:12:21', '2025-05-26 08:08:04'),
('20', '4', 'Sữa động vật', NULL, '2025-05-25 11:12:21', '2025-05-26 08:11:31'),
('21', '4', 'Sữa thực vật', NULL, '2025-05-25 11:12:21', '2025-05-26 08:11:32'),
('22', '4', 'Sữa Chua', NULL, '2025-05-25 11:12:21', '2025-05-26 08:13:08'),
('23', '4', 'Sữa đặc', NULL, '2025-05-25 11:12:21', '2025-05-26 08:13:10'),
('24', '4', 'Sữa cacao', NULL, '2025-05-25 11:12:21', '2025-05-26 08:14:38'),
('25', '4', 'Bơ các loại', NULL, '2025-05-25 11:12:21', '2025-05-26 08:14:41'),
('26', '4', 'Phô mai', NULL, '2025-05-25 11:12:21', '2025-05-26 08:16:05'),
('27', '5', 'Nước Suối - Nước Khoáng', NULL, '2025-05-25 11:12:21', '2025-05-26 08:18:21'),
('28', '5', 'Trà lên men (Kombucha)', NULL, '2025-05-25 11:12:21', '2025-05-26 08:20:26'),
('29', '5', 'Nước tăng lực', NULL, '2025-05-25 11:12:21', '2025-05-26 08:20:33'),
('30', '5', 'Cà phê - Trà', NULL, '2025-05-25 11:12:21', '2025-05-26 08:20:36'),
('31', '6', 'Bia', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('32', '6', 'Rượu vang', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('33', '7', 'Bánh tươi', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('34', '7', 'Chocolate', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('35', '7', 'Snack', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('36', '7', 'Đồ ăn sấy khô/tẩm ướt', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('37', '9', 'Dầu ăn', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('38', '9', 'Mắm các loại', '', '2025-05-25 11:12:21', '2025-05-26 11:13:59'),
('39', '9', 'Nước tương - Dầu hào', '', '2025-05-25 11:12:21', '2025-05-26 11:14:02'),
('40', '9', 'Sốt - Giấm', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('41', '9', 'Tương ớt - Tương cà - Mayo', '', '2025-05-25 11:12:21', '2025-05-26 11:13:59'),
('42', '9', 'Muối - Bột ngọt - Đường', '', '2025-05-25 11:12:21', '2025-05-26 11:14:02'),
('43', '9', 'Gia vị hoàn chỉnh', '', '2025-05-25 11:12:21', '2025-05-26 11:14:02'),
('44', '10', 'Ngũ cốc', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('45', '11', 'Đồ ngâm', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('46', '11', 'Pate', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('47', '11', 'Cá hộp', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('48', '11', 'Thịt hộp', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('49', '12', 'Bánh Tráng', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('50', '12', 'Bún - Phở - Miến - Nui', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('51', '13', 'Các loại bột', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('52', '13', 'Đồ ăn liền(Mì,Miến,Cháo,...)', '', '2025-05-25 11:12:21', '2025-05-26 09:12:17'),
('53', '13', 'Pasta', '', '2025-05-25 11:12:21', '2025-05-26 11:13:56'),
('54', '14', 'Đồ ướp sẵn', '', '2025-05-25 11:12:21', '2025-05-26 09:14:57'),
('57', '15', 'Đồ chay tươi', 'Sử dụng các loại rau, củ, quả tươi, nấm, các loại đậu và ngũ cốc nguyên hạt.', '2025-05-26 16:00:35', '2025-05-26 16:00:35'),
('58', '15', 'Đồ chay khô', 'Các loại thực phẩm chay đã qua chế biến và làm khô', '2025-05-26 16:01:12', '2025-05-26 16:01:12'),
('59', '15', 'Đồ chay chế biến sẵn', 'Các món chay đã được chế biến và đóng gói sẵn', '2025-05-26 16:01:44', '2025-05-26 16:01:44'),
('60', '8', 'Mứt', '', '2025-05-28 01:49:25', '2025-05-28 01:49:25'),
('61', '8', 'Mật ong', '', '2025-05-28 01:49:35', '2025-05-28 01:49:35');

--
-- Table structure for table `support`
--

CREATE TABLE `support` (
  `support_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`support_id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `support_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `support`
--

INSERT INTO `support` VALUES
('1', '1', 'Cao Văn Đức', 'cvd@gmail.com', '0358964218', 'Sửa web đê!!!\r\n', '2025-05-30 17:52:23');

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `wishlist_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_wishlist_item` (`customer_id`,`product_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` VALUES
('44', '1', '189', '2025-05-29 14:51:29'),
('49', '1', '154', '2025-05-30 14:47:09'),
('50', '1', '158', '2025-05-30 20:38:35'),
('54', '1', '178', '2025-07-20 20:30:07'),
('55', '1', '196', '2025-07-20 21:16:26');
