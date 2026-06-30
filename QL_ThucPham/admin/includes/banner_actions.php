<?php
// Prevent any output before header
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

try {
    require_once '../../config/connect.php';

    // Get action from either POST or GET
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    
    if (empty($action)) {
        throw new Exception('Hành động không hợp lệ');
    }

    function uploadImage($file) {
        $target_dir = "../../images/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types)) {
            throw new Exception("Chỉ chấp nhận file ảnh JPG, JPEG, PNG & GIF");
        }
        
        // Check file size (5MB max)
        if ($file['size'] > 5000000) {
            throw new Exception("File không được vượt quá 5MB");
        }
        
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return '../images/' . $new_filename;
        } else {
            throw new Exception("Lỗi khi tải file lên");
        }
    }

    $response = ['success' => false, 'message' => 'Hành động không hợp lệ'];

    switch ($action) {
        case 'list':
            $status_filter = isset($_GET['status']) ? 'WHERE status = ' . (int)$_GET['status'] : '';
            $sql = "SELECT * FROM banners $status_filter ORDER BY position";
            $result = mysqli_query($conn, $sql);
            
            if (!$result) {
                throw new Exception("Lỗi truy vấn: " . mysqli_error($conn));
            }
            
            $banners = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $banners[] = $row;
            }
            
            $response = ['success' => true, 'data' => $banners];
            break;

        case 'add':
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                throw new Exception("Vui lòng chọn hình ảnh");
            }
            
            if (empty($_POST['name'])) {
                throw new Exception("Vui lòng nhập tên banner");
            }
            
            $image_url = uploadImage($_FILES['image']);
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $position = (int)$_POST['position'];
            $status = (int)$_POST['status'];
            
            $sql = "INSERT INTO banners (name, image_url, position, status) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "ssii", $name, $image_url, $position, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $response = ['success' => true, 'message' => 'Banner đã được thêm thành công'];
            } else {
                throw new Exception("Lỗi khi thêm banner: " . mysqli_stmt_error($stmt));
            }
            break;

        case 'edit':
            if (empty($_POST['id'])) {
                throw new Exception("ID banner không hợp lệ");
            }
            
            if (empty($_POST['name'])) {
                throw new Exception("Vui lòng nhập tên banner");
            }
            
            $id = (int)$_POST['id'];
            $name = mysqli_real_escape_string($conn, $_POST['name']);
            $position = (int)$_POST['position'];
            $status = (int)$_POST['status'];
            
            if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                // Get old image to delete
                $sql = "SELECT image_url FROM banners WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if (!$stmt) {
                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, "i", $id);
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Lỗi truy vấn: " . mysqli_stmt_error($stmt));
                }
                
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                $old_image = $row ? $row['image_url'] : '';
                
                // Upload new image
                $image_url = uploadImage($_FILES['image']);
                
                // Update with new image
                $sql = "UPDATE banners SET name = ?, image_url = ?, position = ?, status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if (!$stmt) {
                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, "ssiii", $name, $image_url, $position, $status, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // Delete old image if it exists and is different from new image
                    if (!empty($old_image) && $old_image !== $image_url) {
                        $old_image_path = str_replace('../', '../../', $old_image);
                        if (file_exists($old_image_path)) {
                            unlink($old_image_path);
                        }
                    }
                    $response = ['success' => true, 'message' => 'Banner đã được cập nhật thành công'];
                } else {
                    throw new Exception("Lỗi khi cập nhật banner: " . mysqli_stmt_error($stmt));
                }
            } else {
                // Update without changing image
                $sql = "UPDATE banners SET name = ?, position = ?, status = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                if (!$stmt) {
                    throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
                }
                
                mysqli_stmt_bind_param($stmt, "siii", $name, $position, $status, $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $response = ['success' => true, 'message' => 'Banner đã được cập nhật thành công'];
                } else {
                    throw new Exception("Lỗi khi cập nhật banner: " . mysqli_stmt_error($stmt));
                }
            }
            break;

        case 'delete':
            if (empty($_POST['id'])) {
                throw new Exception("ID banner không hợp lệ");
            }
            
            $id = (int)$_POST['id'];
            
            // Get image URL before deleting
            $sql = "SELECT image_url FROM banners WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Lỗi truy vấn: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $image_url = $row ? $row['image_url'] : '';
            
            // Delete record
            $sql = "DELETE FROM banners WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Delete image file if it exists
                if (!empty($image_url)) {
                    $image_path = str_replace('../', '../../', $image_url);
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                $response = ['success' => true, 'message' => 'Banner đã được xóa thành công'];
            } else {
                throw new Exception("Lỗi khi xóa banner: " . mysqli_stmt_error($stmt));
            }
            break;

        case 'get':
            if (empty($_POST['id'])) {
                throw new Exception("ID banner không hợp lệ");
            }
            
            $id = (int)$_POST['id'];
            
            $sql = "SELECT * FROM banners WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if (!$stmt) {
                throw new Exception("Lỗi chuẩn bị câu lệnh: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Lỗi truy vấn: " . mysqli_stmt_error($stmt));
            }
            
            $result = mysqli_stmt_get_result($stmt);
            if ($banner = mysqli_fetch_assoc($result)) {
                $response = ['success' => true, 'data' => $banner];
            } else {
                throw new Exception("Không tìm thấy banner");
            }
            break;
    }
} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
} finally {
    if (isset($conn)) {
        mysqli_close($conn);
    }
    // Ensure no other output has been sent
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
?> 