<?php
session_start();
include '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id_login'])) {
    header("Location: ../home/login.php");
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../profile/pesanan.php");
    exit;
}

$id_login = $_SESSION['id_login'];
$id_pembelian = mysqli_real_escape_string($conn, $_GET['id']);

// Get customer ID from login ID
$query_customer = "SELECT id_customer FROM tb_customer WHERE id_login = '$id_login'";
$result_customer = mysqli_query($conn, $query_customer);

if (!$result_customer || mysqli_num_rows($result_customer) == 0) {
    echo "Customer data not found.";
    exit;
}

$customer = mysqli_fetch_assoc($result_customer);
$id_customer = $customer['id_customer'];

// Get order details with product info
$query_order = "SELECT p.*, pr.nama_produk, pr.harga_produk, pr.deskripsi, pr.jenis_produk, pr.gambar, 
                       c.nama_customer, c.email_customer, c.no_wa_cutomer, c.alamat_customer 
                FROM tb_pembelian p
                JOIN tb_produk pr ON p.id_produk = pr.id_produk
                JOIN tb_customer c ON p.id_customer = c.id_customer
                WHERE p.id_pembelian = '$id_pembelian' AND p.id_customer = '$id_customer'";
$result_order = mysqli_query($conn, $query_order);

if (!$result_order || mysqli_num_rows($result_order) == 0) {
    // Order not found or does not belong to this customer
    header("Location: ../profile/pesanan.php");
    exit;
}

$order = mysqli_fetch_assoc($result_order);

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?= $order['id_pembelian'] ?> - MarketPlace</title>
    
    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../asset/style.css">
    
    <style>
        .order-status-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .order-status-timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            height: 100%;
            width: 2px;
            background-color: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }
        
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            height: 14px;
            width: 14px;
            border-radius: 50%;
            background-color: #ffffff;
            border: 2px solid #6c757d;
            z-index: 1;
        }
        
        .timeline-item.active::before {
            background-color: #198754;
            border-color: #198754;
        }
        
        .product-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .container {
                width: 100% !important;
                max-width: 100% !important;
            }
            
            .card {
                border: 1px solid #ddd !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <?php include '../includes_P/navbar.php'; ?>
</div>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <!-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="../index.php">Beranda</a></li>
                <li class="breadcrumb-item"><a href="../profile/pesanan.php">Pesanan Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail Pesanan #<?= $order['id_pembelian'] ?></li>
            </ol> -->
        </nav>
        
        <div>
            <button type="button" class="btn btn-outline-secondary me-2" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
            <!-- <a href="../profile/pesanan.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a> -->
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Pesanan #<?= $order['id_pembelian'] ?></h5>
                <span class="badge bg-success">Selesai</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <img src="../../uploads/<?= htmlspecialchars($gambarUtama) ?>" alt="<?= htmlspecialchars($order['nama_produk']) ?>" class="product-image mb-3">
                    
                    <h4 class="mb-2"><?= htmlspecialchars($order['nama_produk']) ?></h4>
                    <p class="text-muted mb-3">
                        <i class="bi bi-tag me-1"></i> <?= htmlspecialchars($order['jenis_produk']) ?>
                    </p>
                    
                    <a href="../produk/detail_produk.php?id=<?= urlencode($order['id_produk']) ?>" 
                        class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-eye"></i> Lihat Produk
                    </a>

                    <!-- <?php if (!empty($order['deskripsi'])): ?>
                        <h6 class="fw-bold">Deskripsi Produk:</h6>
                        
                        <p class="text-muted mb-3">
                            <?= nl2br(htmlspecialchars(substr($order['deskripsi'], 0, 100))) ?>
                            <?= strlen($order['deskripsi']) > 100 ? '...' : '' ?>
                        </p>
                    <?php endif; ?> -->
                </div>
                
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Informasi Pesanan</h5>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">ID Pesanan</div>
                                <div class="col-7 fw-bold">#<?= $order['id_pembelian'] ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Tanggal Pembelian</div>
                                <div class="col-7"><?= date('d F Y', strtotime($order['tanggal_pembelian'])) ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Kuantitas</div>
                                <div class="col-7"><?= $order['jumlah_produk'] ?> item</div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Harga Satuan</div>
                                <div class="col-7">Rp <?= number_format($order['harga_produk'], 0, ',', '.') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Total Pembayaran</div>
                                <div class="col-7 fw-bold text-primary">Rp <?= number_format($order['jumlah_pembayaran'], 0, ',', '.') ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Metode Pembayaran</div>
                                <div class="col-7"><?= htmlspecialchars($order['metode_pembayaran']) ?></div>
                            </div>
                            
                            <hr class="my-3">
                            
                            <h5 class="card-title mb-3">Informasi Pengiriman</h5>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Penerima</div>
                                <div class="col-7"><?= htmlspecialchars($order['nama_customer']) ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">No. Telepon</div>
                                <div class="col-7"><?= htmlspecialchars($order['no_wa_cutomer']) ?></div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-5 text-muted">Email</div>
                                <div class="col-7"><?= htmlspecialchars($order['email_customer']) ?></div>
                            </div>
                            
                            <div class="row mb-0">
                                <div class="col-5 text-muted">Alamat Pengiriman</div>
                                <div class="col-7">
                                    <?= empty($order['alamat_penerima']) ? htmlspecialchars($order['alamat_customer']) : htmlspecialchars($order['alamat_penerima']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 pt-4 border-top">
                <h5 class="mb-3">Status Pesanan</h5>
                
                <div class="order-status-timeline">
                    <div class="timeline-item active">
                        <h6 class="fw-bold mb-1">Pesanan Selesai</h6>
                        <p class="text-muted mb-0">
                            Pesanan telah selesai dan produk telah diterima.
                        </p>
                    </div>
                    
                    <div class="timeline-item active">
                        <h6 class="fw-bold mb-1">Pembayaran Dikonfirmasi</h6>
                        <p class="text-muted mb-0">
                            Pembayaran untuk pesanan ini telah dikonfirmasi.
                        </p>
                    </div>
                    
                    <div class="timeline-item active">
                        <h6 class="fw-bold mb-1">Pesanan Dibuat</h6>
                        <p class="text-muted mb-0">
                            <?= date('d F Y, H:i', strtotime($order['tanggal_pembelian'])) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="no-print">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Butuh Bantuan?</h5>
                <p class="text-muted mb-3">
                    Jika Anda memiliki pertanyaan atau membutuhkan bantuan terkait pesanan ini, 
                    silakan hubungi customer service kami.
                </p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="#" class="btn btn-outline-success">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </a>
                    <a href="#" class="btn btn-outline-primary">
                        <i class="bi bi-envelope me-1"></i> Email
                    </a>
                    <a href="#" class="btn btn-outline-secondary">
                        <i class="bi bi-telephone me-1"></i> Telepon
                    </a>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between align-items-center">
            <a href="../profile/pesanan.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Pesanan
            </a>
            <a href="../index.php" class="btn btn-primary">
                <i class="bi bi-shop me-1"></i> Lanjutkan Belanja
            </a>
        </div>
    </div>
</div>

<?php include '../includes_P/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>