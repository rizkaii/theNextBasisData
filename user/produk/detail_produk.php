<?php
require_once '../../config/database.php';
session_start();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID produk tidak ditemukan.";
    exit;
}

$id_produk = mysqli_real_escape_string($conn, $_GET['id']);
$query = "SELECT * FROM tb_produk WHERE id_produk = '$id_produk'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "Produk tidak ditemukan.";
    exit;
}

$produk = mysqli_fetch_assoc($result);

// Tangani array gambar dengan aman
$gambarArray = [];
if (!empty($produk['gambar'])) {
    $decodedArray = json_decode($produk['gambar'], true);
    if (is_array($decodedArray)) {
        $gambarArray = $decodedArray;
    } else if ($produk['gambar']) {
        // Jika bukan array tapi ada nilai, mungkin hanya satu gambar
        $gambarArray = [$produk['gambar']];
    }
}

// Jika masih kosong, tambahkan gambar default
if (empty($gambarArray)) {
    $gambarArray = ['default.jpg'];
}

// Cek apakah produk sudah ada di keranjang pengguna
$in_cart = false;
$cart_quantity = 1; // Default quantity
if (isset($_SESSION['id_login'])) {
    $id_login = $_SESSION['id_login'];
    
    // Get customer ID
    $query_customer = "SELECT id_customer FROM tb_customer WHERE id_login = '$id_login'";
    $result_customer = mysqli_query($conn, $query_customer);
    
    if ($result_customer && mysqli_num_rows($result_customer) > 0) {
        $customer = mysqli_fetch_assoc($result_customer);
        $id_customer = $customer['id_customer'];
        
        // Check if product is already in cart
        $check_query = "SELECT jumlah FROM tb_keranjang WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
        $check_result = mysqli_query($conn, $check_query);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $in_cart = true;
            $cart_data = mysqli_fetch_assoc($check_result);
            $cart_quantity = $cart_data['jumlah'];
        }
    }
}

// Process add to cart action
if (isset($_POST['action']) && $_POST['action'] == 'add_to_cart') {
    // Pastikan pengguna sudah login
    if (!isset($_SESSION['id_login'])) {
        header("Location: ../home/login.php");
        exit;
    }
    
    $id_login = $_SESSION['id_login'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validasi jumlah
    if ($quantity <= 0) {
        $error_message = "Jumlah produk harus lebih dari 0.";
        goto skip_processing;
    }
    
    if ($quantity > $produk['stok_produk']) {
        $error_message = "Jumlah melebihi stok yang tersedia.";
        goto skip_processing;
    }
    
    // Dapatkan ID customer
    $query_customer = "SELECT id_customer FROM tb_customer WHERE id_login = '$id_login'";
    $result_customer = mysqli_query($conn, $query_customer);
    
    if (!$result_customer || mysqli_num_rows($result_customer) == 0) {
        $error_message = "Data pelanggan tidak ditemukan.";
        goto skip_processing;
    }
    
    $customer = mysqli_fetch_assoc($result_customer);
    $id_customer = $customer['id_customer'];
    
    // Periksa apakah produk sudah ada di keranjang
    $check_query = "SELECT id_keranjang, jumlah FROM tb_keranjang WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
    $check_result = mysqli_query($conn, $check_query);
    
    if ($check_result && mysqli_num_rows($check_result) > 0) {
        // Update jumlah jika sudah ada di keranjang
        $cart_data = mysqli_fetch_assoc($check_result);
        $new_quantity = $quantity;
        
        $update_query = "UPDATE tb_keranjang SET jumlah = '$new_quantity' WHERE id_keranjang = '{$cart_data['id_keranjang']}'";
        $result_update = mysqli_query($conn, $update_query);
        
        if ($result_update) {
            $success_message = "Jumlah produk di keranjang diperbarui!";
            $in_cart = true;
            $cart_quantity = $new_quantity;
        } else {
            $error_message = "Gagal memperbarui jumlah produk: " . mysqli_error($conn);
        }
    } else {
        // Tambahkan ke tabel keranjang
        $insert_query = "INSERT INTO tb_keranjang (id_customer, id_produk, jumlah, tanggal_ditambahkan, status) 
                        VALUES ('$id_customer', '$id_produk', '$quantity', NOW(), 'dalam_keranjang')";
        $result_insert = mysqli_query($conn, $insert_query);
        
        if ($result_insert) {
            $success_message = "Produk berhasil ditambahkan ke keranjang!";
            $in_cart = true;
            $cart_quantity = $quantity;
        } else {
            $error_message = "Gagal menambahkan produk ke keranjang: " . mysqli_error($conn);
        }
    }
    
    skip_processing:
}

// Process buy now action
if (isset($_POST['action']) && $_POST['action'] == 'buy_now') {
    // Pastikan pengguna sudah login
    if (!isset($_SESSION['id_login'])) {
        header("Location: ../home/login.php");
        exit;
    }
    
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validasi jumlah
    if ($quantity <= 0) {
        $error_message = "Jumlah produk harus lebih dari 0.";
        goto skip_purchase;
    }
    
    if ($quantity > $produk['stok_produk']) {
        $error_message = "Jumlah melebihi stok yang tersedia.";
        goto skip_purchase;
    }
    
    // Redirect to payment process page
    header("Location: ../payment/checkout.php?direct=1&id=$id_produk&qty=$quantity");
    exit;
    
    skip_purchase:
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produk['nama_produk']) ?> - MarketPlace</title>
    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../asset/style.css">
    
    <style>
        .quantity-control {
            max-width: 140px;
        }
        
        .quantity-input {
            text-align: center;
            border-left: 0;
            border-right: 0;
        }
    </style>
</head>
<body>

<?php include '../includes_P/navbar.php'; ?>

<div class="container py-5">
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success animate__animated animate__fadeIn mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $success_message ?>
            <a href="../cart/keranjang.php" class="alert-link ms-2">Lihat Keranjang</a>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger animate__animated animate__fadeIn mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error_message ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Gambar Produk -->
        <div class="col-md-6">
            <div id="carouselGambar" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded shadow-sm">
                    <?php foreach ($gambarArray as $index => $img): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="../../uploads/<?= htmlspecialchars($img) ?>" class="d-block w-100" style="object-fit: cover; height: 400px;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($gambarArray) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselGambar" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselGambar" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Tambahkan Deskripsi di bawah gambar -->
            <div class="mt-3 p-3 bg-light rounded shadow-sm">
                <h5 class="fw-semibold">Deskripsi Produk</h5>
                <p class="text-secondary mb-0"><?= nl2br(htmlspecialchars($produk['deskripsi'])) ?: '<em>Tidak ada deskripsi.</em>' ?></p>
            </div>
        </div>

        <!-- Detail Produk -->
        <div class="col-md-6">
            <!-- <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="../kategori.php?kategori=<?= urlencode($produk['jenis_produk']) ?>"><?= htmlspecialchars($produk['jenis_produk']) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($produk['nama_produk']) ?></li>
                </ol>
            </nav> -->

            <h2 class="fw-bold"><?= htmlspecialchars($produk['nama_produk']) ?></h2>
            <p class="text-muted">Kategori: <a href="../kategori/kategori.php?kategori=<?= urlencode($produk['jenis_produk']) ?>" class="text-decoration-none"><?= htmlspecialchars($produk['jenis_produk']) ?></a></p>

            <h4 class="text-primary mb-3">Rp <?= number_format($produk['harga_produk'], 0, ',', '.') ?></h4>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Stok</span>
                        <span class="fw-bold"><?= $produk['stok_produk'] ?> item</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Kedaluwarsa</span>
                        <span class="fw-bold"><?= date('d M Y', strtotime($produk['exp_produk'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Status</span>
                        <?php if ($in_cart): ?>
                            <span class="badge bg-warning">Dalam Keranjang</span>
                        <?php else: ?>
                            <?php 
                                $status = $produk['status_produk'];
                                $statusLabel = match($status) {
                                    'belum_dibeli' => '<span class="badge bg-success">Tersedia</span>',
                                    'sudah_dibeli' => '<span class="badge bg-secondary">Sudah Dibeli</span>',
                                    default => '<span class="badge bg-secondary">Tidak Diketahui</span>'
                                };
                                echo $statusLabel;
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($produk['stok_produk'] > 0): ?>
                <form method="POST">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Jumlah</label>
                        <div class="input-group quantity-control mb-3">
                            <button type="button" class="btn btn-outline-secondary quantity-minus">
                                <i class="bi bi-dash"></i>
                            </button>
                            <input type="number" name="quantity" id="quantity" min="1" max="<?= $produk['stok_produk'] ?>" 
                                   value="<?= $in_cart ? $cart_quantity : 1 ?>" class="form-control quantity-input">
                            <button type="button" class="btn btn-outline-secondary quantity-plus">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i> Stok tersedia: <?= $produk['stok_produk'] ?> item
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" name="action" value="add_to_cart" class="btn btn-primary flex-grow-1">
                            <i class="bi bi-cart-plus me-1"></i> <?= $in_cart ? 'Perbarui Keranjang' : 'Tambah ke Keranjang' ?>
                        </button>
                        <button type="submit" name="action" value="buy_now" class="btn btn-success flex-grow-1">
                            <i class="bi bi-cash-stack me-1"></i> Beli Sekarang
                        </button>
                    </div>
                </form>
                
                <?php if ($in_cart): ?>
                <div class="alert alert-info mt-3">
                    <i class="bi bi-cart-check me-2"></i> Produk ini sudah ada di keranjang Anda dengan jumlah <?= $cart_quantity ?>.
                    
                </div>
                <?php endif; ?>
                
            <?php elseif ($produk['stok_produk'] <= 0): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i> Stok produk ini telah habis.
                </div>
                <a href="../index.php" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Kembali Belanja
                </a>
            <?php else: ?>
                <div class="alert alert-secondary">
                    <i class="bi bi-info-circle me-2"></i> Produk ini tidak tersedia untuk dibeli.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes_P/footer.php'; ?>
<script src="../asset/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Quantity control functionality
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const plusButton = document.querySelector('.quantity-plus');
        const minusButton = document.querySelector('.quantity-minus');
        
        if (quantityInput && plusButton && minusButton) {
            // Plus button functionality
            plusButton.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                const maxValue = parseInt(quantityInput.getAttribute('max'));
                
                if (currentValue < maxValue) {
                    quantityInput.value = currentValue + 1;
                }
            });
            
            // Minus button functionality
            minusButton.addEventListener('click', function() {
                let currentValue = parseInt(quantityInput.value);
                
                if (currentValue > 1) {
                    quantityInput.value = currentValue - 1;
                }
            });
            
            // Input validation
            quantityInput.addEventListener('change', function() {
                let value = parseInt(this.value);
                const max = parseInt(this.getAttribute('max'));
                
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                } else if (value > max) {
                    this.value = max;
                }
            });
        }
    });
</script>

</body>
</html>
