<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    echo "Attempting database connection...\n";
    $conn = new mysqli('localhost', 'root', '', 'ql_thucpham');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "Connected successfully\n";
    
    $conn->set_charset("utf8");
    echo "Charset set to utf8\n";
    
    // Test query
    $result = $conn->query("SELECT * FROM customers WHERE username='admin01'");
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        echo "Found user:\n";
        print_r($row);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) $conn->close();
} 