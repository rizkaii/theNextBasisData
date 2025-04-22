<?php
session_start();
include '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id_login'])) {
    header("Location: ../home/login.php");
    exit;
}

$id_login = $_SESSION['id_login'];

// Get customer ID from login ID
$query_customer = "SELECT id_customer FROM tb_customer WHERE id_login = '$id_login'";
$result_customer = mysqli_query($conn, $query_customer);

if (!$result_customer || mysqli_num_rows($result_customer) == 0) {
    echo "Customer data not found.";
    exit;
}

$customer = mysqli_fetch_assoc($result_customer);
$id_customer = $customer['id_customer'];

// Get all orders for this customer
$query_orders = "SELECT p.*, pr.nama_produk, pr.harga_produk, pr.gambar, pr.jenis_produk 
                FROM tb_pembelian p 
                JOIN tb_produk pr ON p.id_produk = pr.id_produk 
                WHERE p.id_customer = '$id_customer' 
                ORDER BY p.tanggal_pembelian DESC";
$result_orders = mysqli_query($conn, $query_orders);

// Get total spent
$query_total = "SELECT SUM(jumlah_pembayaran) as total_spent 
               FROM tb_pembelian 
               WHERE id_customer = '$id_customer'";
$result_total = mysqli_query($conn, $query_total);
$total_spent = 0;

if ($result_total && mysqli_num_rows($result_total) > 0) {
    $row_total = mysqli_fetch_assoc($result_total);
    $total_spent = $row_total['total_spent'];
}

// Get order count
$order_count = mysqli_num_rows($result_orders);

// Filter by date range if specified
$filter_from = isset($_GET['from']) ? $_GET['from'] : '';
$filter_to = isset($_GET['to']) ? $_GET['to'] : '';
$filter_active = !empty($filter_from) && !empty($filter_to);

if ($filter_active) {
    $from_date = mysqli_real_escape_string($conn, $filter_from);
    $to_date = mysqli_real_escape_string($conn, $filter_to);
    
    $query_orders = "SELECT p.*, pr.nama_produk, pr.harga_produk, pr.gambar, pr.jenis_produk 
                    FROM tb_pembelian p 
                    JOIN tb_produk pr ON p.id_produk = pr.id_produk 
                    WHERE p.id_customer = '$id_customer' 
                    AND p.tanggal_pembelian BETWEEN '$from_date' AND '$to_date'
                    ORDER BY p.tanggal_pembelian DESC";
    $result_orders = mysqli_query($conn, $query_orders);
}

// Filter by payment method if specified
$filter_payment = isset($_GET['payment']) ? $_GET['payment'] : '';
if (!empty($filter_payment)) {
    $payment_method = mysqli_real_escape_string($conn, $filter_payment);
    
    $where_clause = "p.id_customer = '$id_customer' AND p.metode_pembayaran = '$payment_method'";
    
    if ($filter_active) {
        $where_clause .= " AND p.tanggal_pembelian BETWEEN '$from_date' AND '$to_date'";
    }
    
    $query_orders = "SELECT p.*, pr.nama_produk, pr.harga_produk, pr.gambar, pr.jenis_produk 
                    FROM tb_pembelian p 
                    JOIN tb_produk pr ON p.id_produk = pr.id_produk 
                    WHERE $where_clause
                    ORDER BY p.tanggal_pembelian DESC";
    $result_orders = mysqli_query($conn, $query_orders);
}

// Get all payment methods for filter dropdown
$query_payment_methods = "SELECT DISTINCT metode_pembayaran 
                         FROM tb_pembelian 
                         WHERE id_customer = '$id_customer'";
$result_payment_methods = mysqli_query($conn, $query_payment_methods);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - MarketPlace</title>
    
    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../asset/style.css">
    
    <style>
        .order-card {
            transition: transform 0.2s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
        }
        
        .order-item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        
        .date-badge {
            font-size: 0.8rem;
        }
        
        .empty-orders {
            padding: 60px 0;
        }
        
        .empty-orders i {
            font-size: 60px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        /* Enhanced Responsive Styles */
        @media (max-width: 991px) {
            .stats-card {
                margin-bottom: 15px;
            }
        }
        
        @media (max-width: 767px) {
            .order-card .d-flex {
                flex-direction: column;
            }
            
            .order-item-img {
                width: 100%;
                height: 150px;
                margin-bottom: 15px;
            }
            
            .order-card .d-flex.justify-content-between {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start !important;
            }
            
            .filter-section .col-md-4 {
                margin-bottom: 15px;
            }
            
            h2.mb-0.fw-bold {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 576px) {
            .container.py-5 {
                padding-left: 10px !important;
                padding-right: 10px !important;
            }
            
            .d-flex.justify-content-between.align-items-center {
                flex-direction: column;
                align-items: flex-start !important;
                gap: 15px;
            }
            
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<?php include '../includes_P/navbar.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h4 class="mb-0 fw-bold">
            <i class="bi bi-bag-check me-2"></i> Pesanan Saya
        </h4>
        <a href="../index.php" class="btn btn-outline-primary mt-2 mt-md-0">
            <i class="bi bi-shop me-1"></i> Lanjutkan Belanja
        </a>
    </div>
    
    <!-- Order Statistics -->
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 mb-lg-0">
            <div class="card border-0 shadow-sm stats-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3">
                            <i class="bi bi-receipt text-primary fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Pesanan</h6>
                            <h5 class="mb-0 fw-bold"><?= $order_count ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6 col-sm-12 mb-3 mb-lg-0">
            <div class="card border-0 shadow-sm stats-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 me-3">
                            <i class="bi bi-wallet2 text-success fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Pengeluaran</h6>
                            <h5 class="mb-0 fw-bold text-nowrap">Rp <?= number_format($total_spent, 0, ',', '.') ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-12 col-sm-12">
            <div class="card border-0 shadow-sm stats-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 me-3">
                            <i class="bi bi-calendar-check text-info fs-4"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Pesanan Terakhir</h6>
                            <?php 
                            mysqli_data_seek($result_orders, 0);
                            $latest_order = mysqli_fetch_assoc($result_orders);
                            mysqli_data_seek($result_orders, 0);
                            ?>
                            <?php if ($latest_order): ?>
                                <h5 class="mb-0 fw-bold"><?= date('d M Y', strtotime($latest_order['tanggal_pembelian'])) ?></h5>
                            <?php else: ?>
                                <h4 class="mb-0 fw-bold">-</h4>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Filter Pesanan</h5>
            
            <form action="" method="GET" class="row g-3 filter-section">
                <div class="col-md-4 col-sm-6">
                    <label for="from" class="form-label">Dari Tanggal</label>
                    <input type="date" class="form-control" id="from" name="from" value="<?= $filter_from ?>">
                </div>
                
                <div class="col-md-4 col-sm-6">
                    <label for="to" class="form-label">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="to" name="to" value="<?= $filter_to ?>">
                </div>
                
                <div class="col-md-4 col-sm-12">
                    <label for="payment" class="form-label">Metode Pembayaran</label>
                    <select class="form-select" id="payment" name="payment">
                        <option value="">Semua Metode</option>
                        <?php while ($payment = mysqli_fetch_assoc($result_payment_methods)): ?>
                            <option value="<?= htmlspecialchars($payment['metode_pembayaran']) ?>" <?= $filter_payment == $payment['metode_pembayaran'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($payment['metode_pembayaran']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        
                        <?php if ($filter_active || !empty($filter_payment)): ?>
                            <a href="pesanan.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Reset Filter
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Order List -->
    <?php if (mysqli_num_rows($result_orders) > 0): ?>
        <div class="row">
            <?php while ($order = mysqli_fetch_assoc($result_orders)): ?>
                <?php 
                    // Extract image data
                    $gambarArray = [];
                    if (!empty($order['gambar'])) {
                        $decodedArray = json_decode($order['gambar'], true);
                        if (is_array($decodedArray)) {
                            $gambarArray = $decodedArray;
                        } else if ($order['gambar']) {
                            $gambarArray = [$order['gambar']];
                        }
                    }
                    
                    // Set default image if array is empty
                    if (empty($gambarArray)) {
                        $gambarArray = ['default.jpg'];
                    }
                    
                    $gambarUtama = $gambarArray[0];
                ?>
                
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card border-0 shadow-sm order-card position-relative">
                        <div class="card-body">
                            <div class="d-flex mb-3">
                                <div class="me-3 order-img-container">
                                    <img src="../../uploads/<?= htmlspecialchars($gambarUtama) ?>" 
                                         alt="<?= htmlspecialchars($order['nama_produk']) ?>" 
                                         class="order-item-img">
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-1"><?= htmlspecialchars($order['nama_produk']) ?></h5>
                                    <p class="card-text text-muted mb-2">
                                        <small>
                                            <i class="bi bi-tag me-1"></i> <?= htmlspecialchars($order['jenis_produk']) ?>
                                        </small>
                                    </p>
                                    <p class="mb-1">
                                        <span class="badge bg-primary me-2"><?= $order['jumlah_produk'] ?> item</span>
                                        <span class="fw-bold text-primary">Rp <?= number_format($order['jumlah_pembayaran'], 0, ',', '.') ?></span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div class="order-meta">
                                    <span class="badge bg-info me-2 mb-2 d-inline-block">
                                        <i class="bi bi-credit-card me-1"></i> <?= htmlspecialchars($order['metode_pembayaran']) ?>
                                    </span>
                                    <span class="date-badge text-muted d-inline-block mb-2">
                                        <i class="bi bi-calendar me-1"></i> <?= date('d M Y', strtotime($order['tanggal_pembelian'])) ?>
                                    </span>
                                </div>
                                
                                <a href="../order/detail_pesanan.php?id=<?= $order['id_pembelian'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> Detail
                                </a>
                            </div>
                            
                            <div class="status-badge">
                                <span class="badge bg-success">Selesai</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="empty-orders text-center">
                    <i class="bi bi-bag-x"></i>
                    <h4 class="mb-3">Belum Ada Pesanan</h4>
                    <p class="text-muted mb-4">Anda belum melakukan pemesanan apapun.</p>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="bi bi-shop me-2"></i> Mulai Belanja
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes_P/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>