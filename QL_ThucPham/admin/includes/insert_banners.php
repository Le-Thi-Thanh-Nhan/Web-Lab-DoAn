<?php
require_once('../../config/connect.php');

// Mảng dữ liệu banner
$banners = [
    [
        'name' => 'Banner 1',
        'image_url' => 'images/banner1.jpg',
        'position' => 1,
        'status' => 1
    ],
    [
        'name' => 'Banner 2',
        'image_url' => 'images/banner2.jpg',
        'position' => 2,
        'status' => 1
    ],
    [
        'name' => 'Banner 3',
        'image_url' => 'images/banner3.jpg',
        'position' => 3,
        'status' => 1
    ],
    [
        'name' => 'Banner 4',
        'image_url' => 'images/banner4.jpg',
        'position' => 4,
        'status' => 1
    ],
    [
        'name' => 'Banner 5',
        'image_url' => 'images/banner5.jpg',
        'position' => 5,
        'status' => 1
    ]
];

// Xóa dữ liệu cũ (nếu có)
mysqli_query($conn, "TRUNCATE TABLE banners");

// Thêm dữ liệu mới
$stmt = mysqli_prepare($conn, "INSERT INTO banners (name, image_url, position, status, created_at, updated_at) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");

foreach ($banners as $banner) {
    mysqli_stmt_bind_param($stmt, "ssii", 
        $banner['name'],
        $banner['image_url'],
        $banner['position'],
        $banner['status']
    );
    mysqli_stmt_execute($stmt);
}

mysqli_stmt_close($stmt);
echo "Đã thêm dữ liệu banner thành công!";
?> 