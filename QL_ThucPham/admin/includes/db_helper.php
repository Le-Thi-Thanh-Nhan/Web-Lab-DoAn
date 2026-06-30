<?php
// Database connection configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ql_thucpham');

// Function to get database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
    return $conn;
}

// Function to update SQL file
function updateSQLFile() {
    try {
        $conn = getDBConnection();
        $output = "";
        
        // Add SQL file header
        $output .= "-- phpMyAdmin SQL Dump\n";
        $output .= "-- version 5.2.1\n";
        $output .= "-- https://www.phpmyadmin.net/\n";
        $output .= "--\n";
        $output .= "-- Host: " . DB_HOST . "\n";
        $output .= "-- Generation Time: " . date('M d, Y \a\t h:i A') . "\n";
        $output .= "-- Server version: 10.4.32-MariaDB\n";
        $output .= "-- PHP Version: 8.0.30\n\n";

        $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $output .= "START TRANSACTION;\n";
        $output .= "SET time_zone = \"+00:00\";\n\n";
        
        // Get all tables
        $tables = $conn->query("SHOW TABLES");
        while($table = $tables->fetch_array()) {
            $tableName = $table[0];
            
            // Get create table syntax
            $createTable = $conn->query("SHOW CREATE TABLE `$tableName`");
            $tableCreate = $createTable->fetch_array();
            $output .= "\n--\n-- Table structure for table `$tableName`\n--\n\n";
            $output .= $tableCreate[1] . ";\n\n";
            
            // Get table data
            $result = $conn->query("SELECT * FROM `$tableName`");
            if ($result->num_rows > 0) {
                $output .= "--\n-- Dumping data for table `$tableName`\n--\n\n";
                $output .= "INSERT INTO `$tableName` VALUES\n";
                $first = true;
                
                while($row = $result->fetch_array(MYSQLI_NUM)) {
                    if (!$first) {
                        $output .= ",\n";
                    }
                    $first = false;
                    
                    $output .= "(";
                    for($j=0; $j<count($row); $j++) {
                        if ($j > 0) {
                            $output .= ", ";
                        }
                        if ($row[$j] === null) {
                            $output .= "NULL";
                        } else {
                            $output .= "'" . $conn->real_escape_string($row[$j]) . "'";
                        }
                    }
                    $output .= ")";
                }
                $output .= ";\n";
            }
        }
        
        // Write to file
        $sqlFile = fopen("../includes/ql_thucpham.sql", "w");
        fwrite($sqlFile, $output);
        fclose($sqlFile);
        
        return true;
    } catch (Exception $e) {
        error_log("Error updating SQL file: " . $e->getMessage());
        return false;
    }
}

// Function to handle CRUD operations and update SQL file
function handleCRUD($operation, $table, $data, $where = null) {
    try {
        $conn = getDBConnection();
        $result = false;
        
        switch($operation) {
            case 'CREATE':
                $columns = implode("`, `", array_keys($data));
                $values = "'" . implode("', '", array_values($data)) . "'";
                $sql = "INSERT INTO `$table` (`$columns`) VALUES ($values)";
                $result = $conn->query($sql);
                break;
                
            case 'UPDATE':
                $set = [];
                foreach($data as $key => $value) {
                    $set[] = "`$key` = '$value'";
                }
                $sql = "UPDATE `$table` SET " . implode(", ", $set);
                if ($where) {
                    $sql .= " WHERE $where";
                }
                $result = $conn->query($sql);
                break;
                
            case 'DELETE':
                $sql = "DELETE FROM `$table`";
                if ($where) {
                    $sql .= " WHERE $where";
                }
                $result = $conn->query($sql);
                break;
        }
        
        if ($result) {
            // Update SQL file after successful operation
            updateSQLFile();
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in CRUD operation: " . $e->getMessage());
        return false;
    }
}
?> 