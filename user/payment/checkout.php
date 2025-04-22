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
$query_customer = "SELECT * FROM tb_customer WHERE id_login = '$id_login'";
$result_customer = mysqli_query($conn, $query_customer);

if (!$result_customer || mysqli_num_rows($result_customer) == 0) {
    echo "Customer data not found.";
    exit;
}

$customer = mysqli_fetch_assoc($result_customer);
$id_customer = $customer['id_customer'];

// Handle direct purchase (from "Beli Sekarang" button)
$direct_purchase = false;
$direct_product = null;
$direct_quantity = 1;

if (isset($_GET['direct']) && isset($_GET['id']) && isset($_GET['qty'])) {
    $direct_purchase = true;
    $direct_product_id = mysqli_real_escape_string($conn, $_GET['id']);
    $direct_quantity = (int)$_GET['qty'];
    
    // Get product details
    $product_query = "SELECT * FROM tb_produk WHERE id_produk = '$direct_product_id'";
    $product_result = mysqli_query($conn, $product_query);
    
    if ($product_result && mysqli_num_rows($product_result) > 0) {
        $direct_product = mysqli_fetch_assoc($product_result);
    } else {
        header("Location: ../index.php");
        exit;
    }
}

// Get cart items if not direct purchase
if (!$direct_purchase) {
    $cart_query = "SELECT p.*, c.jumlah, c.catatan_opsional 
                  FROM tb_produk p 
                  JOIN tb_keranjang c ON p.id_produk = c.id_produk 
                  WHERE c.id_customer = '$id_customer' AND c.status = 'dalam_keranjang'";
    $cart_result = mysqli_query($conn, $cart_query);

    // Check if cart is empty
    if (mysqli_num_rows($cart_result) == 0) {
        header("Location: ../keranjang.php?status=error&message=Keranjang kosong");
        exit;
    }

    // Calculate total
    $total = 0;
    $cart_items = [];
    while ($row = mysqli_fetch_assoc($cart_result)) {
        $cart_items[] = $row;
        $total += ($row['harga_produk'] * $row['jumlah']);
    }
} else {
    // Calculate total for direct purchase
    $total = $direct_product['harga_produk'] * $direct_quantity;
}

// Process the order
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    if (empty($_POST['alamat_penerima']) || empty($_POST['metode_pembayaran'])) {
        $error_message = "Alamat dan metode pembayaran harus diisi.";
    } else {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Save shipping and payment information
            $alamat_penerima = mysqli_real_escape_string($conn, $_POST['alamat_penerima']);
            $metode_pembayaran = mysqli_real_escape_string($conn, $_POST['metode_pembayaran']);
            
            // Process direct purchase
            if ($direct_purchase) {
                // Calculate total price
                $total_harga = $direct_product['harga_produk'] * $direct_quantity;
                
                // Check stock availability
                if ($direct_quantity > $direct_product['stok_produk']) {
                    throw new Exception("Stok produk {$direct_product['nama_produk']} tidak mencukupi.");
                }
                
                // Insert purchase record
                $insert_query = "INSERT INTO tb_pembelian (
                    tanggal_pembelian, 
                    id_produk, 
                    jumlah_produk, 
                    id_customer, 
                    metode_pembayaran, 
                    jumlah_pembayaran,
                    alamat_penerima
                ) VALUES (
                    NOW(), 
                    '{$direct_product['id_produk']}', 
                    '$direct_quantity', 
                    '$id_customer', 
                    '$metode_pembayaran', 
                    '$total_harga',
                    '$alamat_penerima'
                )";
                
                $insert_result = mysqli_query($conn, $insert_query);
                
                if (!$insert_result) {
                    throw new Exception("Gagal mencatat pembelian: " . mysqli_error($conn));
                }
                
                $order_id = mysqli_insert_id($conn);
                
                // Reduce product stock
                $update_stock = "UPDATE tb_produk SET stok_produk = stok_produk - $direct_quantity 
                                 WHERE id_produk = '{$direct_product['id_produk']}' AND stok_produk >= $direct_quantity";
                $update_result = mysqli_query($conn, $update_stock);
                
                if (!$update_result || mysqli_affected_rows($conn) == 0) {
                    throw new Exception("Gagal mengupdate stok produk: " . mysqli_error($conn));
                }
            } else {
                // Process cart items
                $order_ids = [];
                
                foreach ($cart_items as $item) {
                    $id_produk = $item['id_produk'];
                    $harga_produk = $item['harga_produk'];
                    $jumlah = $item['jumlah'];
                    $total_harga = $harga_produk * $jumlah;
                    
                    // Check stock availability
                    if ($jumlah > $item['stok_produk']) {
                        throw new Exception("Stok produk {$item['nama_produk']} tidak mencukupi.");
                    }
                    
                    // Insert purchase record
                    $insert_query = "INSERT INTO tb_pembelian (
                        tanggal_pembelian, 
                        id_produk, 
                        jumlah_produk, 
                        id_customer, 
                        metode_pembayaran, 
                        jumlah_pembayaran,
                        alamat_penerima
                    ) VALUES (
                        NOW(), 
                        '$id_produk', 
                        '$jumlah', 
                        '$id_customer', 
                        '$metode_pembayaran', 
                        '$total_harga',
                        '$alamat_penerima'
                    )";
                    
                    $insert_result = mysqli_query($conn, $insert_query);
                    
                    if (!$insert_result) {
                        throw new Exception("Gagal mencatat pembelian: " . mysqli_error($conn));
                    }
                    
                    $order_ids[] = mysqli_insert_id($conn);
                    
                    // Reduce product stock
                    $update_stock = "UPDATE tb_produk SET stok_produk = stok_produk - $jumlah 
                                     WHERE id_produk = '$id_produk' AND stok_produk >= $jumlah";
                    $update_result = mysqli_query($conn, $update_stock);
                    
                    if (!$update_result || mysqli_affected_rows($conn) == 0) {
                        throw new Exception("Gagal mengupdate stok produk: " . mysqli_error($conn));
                    }
                    
                    // Update cart status
                    $update_cart = "UPDATE tb_keranjang SET status = 'checkout' 
                                    WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
                    $update_cart_result = mysqli_query($conn, $update_cart);
                    
                    if (!$update_cart_result) {
                        throw new Exception("Gagal mengupdate status keranjang: " . mysqli_error($conn));
                    }
                }
                
                $order_id = $order_ids[0] ?? 0; // Use the first order ID for redirection
            }
            
            // Commit transaction
            mysqli_commit($conn);
            
            // Redirect to success page
            header("Location: berhasil.php?id=" . $order_id);
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MarketPlace</title>
    
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
    <div class="row">
        <div class="col-12">
            <h2 class="mb-4 fw-bold">
                <i class="bi bi-credit-card me-2"></i> Checkout
            </h2>
            
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger animate__animated animate__fadeIn" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Form Shipping & Payment Information -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">Informasi Pengiriman & Pembayaran</h5>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="">
                        <div class="mb-3">
                            <label for="alamat_penerima" class="form-label">Alamat Pengiriman <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat_penerima" name="alamat_penerima" rows="3" required><?= htmlspecialchars($customer['alamat_customer']) ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                            <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                                <option value="">Pilih metode pembayaran</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="QRIS">QRIS</option>
                                <option value="E-Wallet">E-Wallet</option>
                                <option value="COD">COD (Bayar di Tempat)</option>
                            </select>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-2"></i> Konfirmasi Pesanan
                            </button>
                            <!-- <a href="<?= $direct_purchase ? '../index.php' : '../keranjang.php' ?>" class="btn btn-outline-secondary ms-2">
                                <i class="bi bi-arrow-left me-2"></i> Kembali
                            </a> -->
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Summary -->
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-light py-3">
                    <h5 class="card-title mb-0">Ringkasan Pesanan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($customer['nama_customer']) ?></p>
                        <p class="mb-0"><strong>WhatsApp:</strong> <?= htmlspecialchars($customer['no_wa_cutomer']) ?></p>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Item</span>
                        <span class="fw-bold"><?= $direct_purchase ? '1' : count($cart_items) ?></span>
                    </div>
                    
                    <?php if (!$direct_purchase): ?>
                        <!-- Cart items -->
                        <div class="border-top border-bottom py-3 my-3">
                            <?php foreach ($cart_items as $item): ?>
                                <?php 
                                    $gambarArray = json_decode($item['gambar'], true);
                                    $gambarUtama = isset($gambarArray[0]) ? $gambarArray[0] : 'default.jpg';
                                    $subtotal = $item['harga_produk'] * $item['jumlah'];
                                ?>
                                <div class="d-flex mb-3">
                                    <img src="../../uploads/<?= htmlspecialchars($gambarUtama) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" 
                                         class="img-thumbnail me-2" style="width: 60px; height: 60px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                                        <small class="text-muted"><?= $item['jumlah'] ?> x Rp <?= number_format($item['harga_produk'], 0, ',', '.') ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Direct purchase item -->
                        <div class="border-top border-bottom py-3 my-3">
                            <?php 
                                $gambarArray = json_decode($direct_product['gambar'], true);
                                $gambarUtama = isset($gambarArray[0]) ? $gambarArray[0] : 'default.jpg';
                                $subtotal = $direct_product['harga_produk'] * $direct_quantity;
                            ?>
                            <div class="d-flex mb-3">
                                <img src="../../uploads/<?= htmlspecialchars($gambarUtama) ?>" alt="<?= htmlspecialchars($direct_product['nama_produk']) ?>" 
                                     class="img-thumbnail me-2" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0"><?= htmlspecialchars($direct_product['nama_produk']) ?></h6>
                                    <small class="text-muted"><?= $direct_quantity ?> x Rp <?= number_format($direct_product['harga_produk'], 0, ',', '.') ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>Rp <?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Biaya Pengiriman</span>
                        <span>Gratis</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold fs-5 text-primary">Rp <?= number_format($total, 0, ',', '.') ?></span>
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