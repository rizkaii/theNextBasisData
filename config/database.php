<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "db_sbdl63";

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Temporarily disable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=0");

// Check if login table has any records
$checkLoginQuery = "SELECT COUNT(*) as count FROM tb_login";
$checkLoginResult = mysqli_query($conn, $checkLoginQuery);

if ($checkLoginResult) {
    $loginCount = mysqli_fetch_assoc($checkLoginResult)['count'];
    
    // Create a default admin user if login table is empty
    if ($loginCount == 0) {
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $createAdminQuery = "INSERT INTO tb_login (username, password, role) VALUES ('admin', '$defaultPassword', 'admin')";
        mysqli_query($conn, $createAdminQuery);
    }
}

// Check tb_pembelian constraint
try {
    $checkConstraintQuery = "SHOW CREATE TABLE tb_pembelian";
    $result = mysqli_query($conn, $checkConstraintQuery);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $createTableSql = $row['Create Table'];
        
        // If foreign key constraint is pointing to tb_login.id instead of tb_customer.id_customer
        if (strpos($createTableSql, 'FOREIGN KEY (`id_customer`) REFERENCES `tb_login`') !== false) {
            // Try to fix constraint
            mysqli_query($conn, "ALTER TABLE tb_pembelian DROP FOREIGN KEY tb_pembelian_ibfk_1");
        }
    }
} catch (Exception $e) {
    // Silently continue if there's an issue checking constraints
}

// Re-enable foreign key checks
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS=1");
?>