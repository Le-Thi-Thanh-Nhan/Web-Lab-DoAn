<?php
function getDBConnection() {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'ql_thucpham';

    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    return $conn;
}

function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?> 