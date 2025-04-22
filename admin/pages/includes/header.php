<?php
session_start();

// include '../../../config/database.php';

// Fungsi fallback kalau getRelativePath belum tersedia
if (!function_exists('getRelativePath')) {
    function getRelativePath() {
        return './';
    }
}

// Fungsi bantu untuk menghitung data
function getCount($table) {
    global $conn;
    $result = mysqli_query($conn, "SELECT COUNT(*) as total FROM $table");
    $data = mysqli_fetch_assoc($result);
    return $data['total'] ?? 0;
}

// Hitung jumlah data
$customerCount = getCount('tb_customer');
$productCount = getCount('tb_produk');
$purchaseCount = getCount('tb_pembelian');
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBDL63 Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo getRelativePath(); ?>assets/css/style.css">
    <style>
    .navbar {
        position: sticky;
        top: 0;
        z-index: 1000;
        padding: 20px 0;
        transition: all 0.3s ease;
    }

    .navbar.shrink {
        padding: 8px 0;
        background-color: #003d80 !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .navbar-brand {
        font-size: 24px;
        transition: font-size 0.3s ease;
    }

    .navbar.shrink .navbar-brand {
        font-size: 20px;
    }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo getRelativePath(); ?>../home/index.php">DASHBOARD</a>
        <!-- Toggler for smaller screens -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <!-- Collapsible navbar links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo getRelativePath(); ?>../../pages/customer/index.php">Customers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo getRelativePath(); ?>../../pages/product/index.php">Products</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo getRelativePath(); ?>../../pages/purchase/index.php">Purchases</a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?>
                        (<?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'User'; ?>)
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="<?php echo getRelativePath(); ?>../../../home/logout.php">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
