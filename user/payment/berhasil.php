<?php
session_start();
include '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id_login'])) {
    header("Location: ../home/login.php");
    exit;
}

// Check if purchase ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$purchase_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get purchase details
// Menyesuaikan query dengan nama kolom yang benar dalam database
$query = "SELECT p.*, pr.nama_produk, pr.harga_produk, pr.gambar, c.nama_customer, c.email_customer, c.no_wa_cutomer 
          FROM tb_pembelian p
          JOIN tb_produk pr ON p.id_produk = pr.id_produk
          JOIN tb_customer c ON p.id_customer = c.id_customer
          WHERE p.id_pembelian = '$purchase_id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    header("Location: ../index.php");
    exit;
}

$purchase = mysqli_fetch_assoc($result);

// Get product image
$gambarArray = [];
if (!empty($purchase['gambar'])) {
    $decodedArray = json_decode($purchase['gambar'], true);
    if (is_array($decodedArray)) {
        $gambarArray = $decodedArray;
    } else if ($purchase['gambar']) {
        // Jika bukan array tapi ada nilai, mungkin hanya satu gambar
        $gambarArray = [$purchase['gambar']];
    }
}

// Jika masih kosong, tambahkan gambar default
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
    <title>Pembelian Berhasil - MarketPlace</title>
    
    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../asset/style.css">
</head>
<body>

<?php include '../includes_P/navbar.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-success text-white py-3">
                    <h4 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i> Pembelian Berhasil!</h4>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <img src="../../uploads/<?= htmlspecialchars($gambarUtama) ?>" alt="<?= htmlspecialchars($purchase['nama_produk']) ?>" 
                             class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                    </div>
                    
                    <h5 class="card-title"><?= htmlspecialchars($purchase['nama_produk']) ?></h5>
                    
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold mb-3">Detail Pembelian</h6>
                                <p class="mb-2">
                                    <span class="text-muted">ID Pembelian:</span><br>
                                    <strong>#<?= $purchase['id_pembelian'] ?></strong>
                                </p>
                                <p class="mb-2">
                                    <span class="text-muted">Tanggal:</span><br>
                                    <strong><?= date('d F Y', strtotime($purchase['tanggal_pembelian'])) ?></strong>
                                </p>
                                <p class="mb-2">
                                    <span class="text-muted">Jumlah:</span><br>
                                    <strong><?= $purchase['jumlah_produk'] ?> item</strong>
                                </p>
                                <p class="mb-2">
                                    <span class="text-muted">Metode Pembayaran:</span><br>
                                    <strong><?= htmlspecialchars($purchase['metode_pembayaran']) ?></strong>
                                </p>
                                <p class="mb-0">
                                    <span class="text-muted">Total Pembayaran:</span><br>
                                    <strong class="text-success">Rp <?= number_format($purchase['jumlah_pembayaran'], 0, ',', '.') ?></strong>
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold mb-3">Informasi Pembeli</h6>
                                <p class="mb-2">
                                    <span class="text-muted">Nama:</span><br>
                                    <strong><?= htmlspecialchars($purchase['nama_customer']) ?></strong>
                                </p>
                                <p class="mb-2">
                                    <span class="text-muted">Email:</span><br>
                                    <strong><?= htmlspecialchars($purchase['email_customer']) ?></strong>
                                </p>
                                <?php if (!empty($purchase['no_wa_cutomer'])): ?>
                                <p class="mb-0">
                                    <span class="text-muted">WhatsApp:</span><br>
                                    <strong><?= htmlspecialchars($purchase['no_wa_cutomer']) ?></strong>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        Silakan lakukan pembayaran sesuai dengan total yang tertera. Konfirmasi pembayaran akan diproses dalam 1x24 jam.
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="../index.php" class="btn btn-primary">
                            <i class="bi bi-shop me-2"></i> Kembali Belanja
                        </a>
                        <a href="#" class="btn btn-outline-success ms-2" onclick="window.print()">
                            <i class="bi bi-printer me-2"></i> Cetak Bukti Pembelian
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes_P/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../asset/script.js"></script>

</body>
</html>