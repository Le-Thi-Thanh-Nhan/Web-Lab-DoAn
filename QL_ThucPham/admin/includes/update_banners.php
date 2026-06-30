<?php
require_once('../../config/connect.php');

// Cập nhật đường dẫn ảnh banner
$sql = "UPDATE banners SET image_url = REPLACE(image_url, '../images/', 'images/')";
if(mysqli_query($conn, $sql)) {
    echo "Đã cập nhật đường dẫn ảnh banner thành công!";
} else {
    echo "Lỗi khi cập nhật: " . mysqli_error($conn);
}
?> 