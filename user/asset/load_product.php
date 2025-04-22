<?php
// load-products.php - Handles AJAX requests for loading more products

// Start session and include database connection
session_start();
include '../../config/database.php';

// Set header to return JSON
header('Content-Type: application/json');

// Get pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Calculate offset
$offset = ($page - 1) * $limit;

// Prepare query
$query = "SELECT * FROM tb_produk WHERE stok_produk > 0";

// Add search filter if provided
if (!empty($search)) {
    $query .= " AND nama_produk LIKE ?";
    $stmt = $conn->prepare($query . " LIMIT ? OFFSET ?");
    $search_param = "%" . $search . "%";
    $stmt->bind_param("sii", $search_param, $limit, $offset);
} else {
    // Random ordering if not searching
    $query .= " ORDER BY RAND() LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $limit, $offset);
}

// Execute query
$result = false;
$products = [];

try {
    // Execute query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        // Fetch and prepare data
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        throw new Exception("Query execution failed: " . $stmt->error);
    }
} catch (Exception $e) {
    // Log error
    error_log("Error in load-products.php: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'error' => true,
        'message' => 'Database error occurred',
        'products' => []
    ]);
    exit;
}

// Return JSON response
echo json_encode(['products' => $products]);