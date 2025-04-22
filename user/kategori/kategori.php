<?php
session_start();
include '../../config/database.php';

// Ambil parameter kategori dari URL
$selected_kategori = isset($_GET['kategori']) ? mysqli_real_escape_string($conn, $_GET['kategori']) : '';

// Query untuk mendapatkan semua kategori
$kategori_query = "SELECT DISTINCT jenis_produk FROM tb_produk ORDER BY jenis_produk ASC";
$kategori_result = mysqli_query($conn, $kategori_query);

// Query untuk produk
$produk_query = "SELECT * FROM tb_produk WHERE stok_produk > 0";
if (!empty($selected_kategori)) {
    $produk_query .= " AND jenis_produk = '$selected_kategori'";
}
$produk_query .= " ORDER BY nama_produk ASC";
$produk_result = mysqli_query($conn, $produk_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kategori Produk - MarketPlace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../asset/style.css">
</head>
<body>
    <?php include '../includes_P/navbar.php'; ?>

    <div class="container py-5">
        
        
        <!-- Kategori -->
        <div class="row mb-5">
            <?php 
            $icons = [
                'hiburan' => 'bi-controller',
                'makanan' => 'bi-egg-fried',
                'kecantikan' => 'bi-brush',
                'default' => 'bi-grid'
            ];
            
            while ($kat = mysqli_fetch_assoc($kategori_result)):
                $kat_name = $kat['jenis_produk'];
                $kat_lower = strtolower($kat_name);
                $icon = isset($icons[$kat_lower]) ? $icons[$kat_lower] : $icons['default'];
                
                // Hitung jumlah produk
                $count_query = "SELECT COUNT(*) as count FROM tb_produk WHERE jenis_produk = '$kat_name' AND stok_produk > 0";
                $count_result = mysqli_query($conn, $count_query);
                $count_data = mysqli_fetch_assoc($count_result);
                $count = $count_data['count'];
            ?>
            <div class="col-6 col-md-3 mb-4">
                <a href="?kategori=<?= urlencode($kat_name) ?>" class="text-decoration-none">
                    <div class="card text-center h-100 shadow-sm">
                        <div class="card-body">
                            <i class="bi <?= $icon ?> fs-1 mb-3"></i>
                            <h5><?= htmlspecialchars($kat_name) ?></h5>
                            <p class="text-muted"><?= $count ?> produk</p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Produk -->
        <h4 class="mb-4">
            <?= !empty($selected_kategori) ? 'Produk dalam kategori "' . htmlspecialchars($selected_kategori) . '"' : 'Semua Produk' ?>
        </h4>
        
        <div class="row">
            <?php 
            if (mysqli_num_rows($produk_result) > 0):
                while ($produk = mysqli_fetch_assoc($produk_result)):
                    // Pastikan kita memiliki data produk yang valid
                    if (!is_array($produk)) continue;
                    
                    // Dapatkan data produk yang aman
                    $id_produk = $produk['id_produk'] ?? 0;
                    $nama_produk = $produk['nama_produk'] ?? 'Produk';
                    $harga_produk = $produk['harga_produk'] ?? 0;
                    $jenis_produk = $produk['jenis_produk'] ?? '';
                    
                    // Dapatkan gambar
                    $gambar = 'default.jpg';
                    if (!empty($produk['gambar'])) {
                        $gambar_array = json_decode($produk['gambar'], true);
                        if (is_array($gambar_array) && !empty($gambar_array)) {
                            $gambar = $gambar_array[0];
                        }
                    }
            ?>
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <img src="../../uploads/<?= htmlspecialchars($gambar) ?>" class="card-img-top" alt="<?= htmlspecialchars($nama_produk) ?>" style="height: 180px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($nama_produk) ?></h5>
                        <p class="card-text text-muted"><?= htmlspecialchars($jenis_produk) ?></p>
                        <p class="card-text fw-bold">Rp <?= number_format((float)$harga_produk, 0, ',', '.') ?></p>
                        <a href="../produk/detail_produk.php?id=<?= $id_produk ?>" class="btn btn-primary">Lihat Detail</a>
                    </div>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div class="col-12 text-center py-5">
                <i class="bi bi-search display-1 text-muted"></i>
                <h3 class="mt-3">Tidak ada produk yang ditemukan</h3>
                <p class="text-muted">Coba pilih kategori lain atau kembali ke halaman utama</p>
                <a href="kategori.php" class="btn btn-primary mt-3">Lihat Semua Kategori</a>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include '../includes_P/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../asset/script.js"></script>
</body>
</html>