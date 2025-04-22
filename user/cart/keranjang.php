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

// Process actions (add, remove, update quantity)
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id_produk = isset($_GET['id']) ? $_GET['id'] : 0;
    
    // Add to cart
    if ($action == 'add' && $id_produk > 0) {
        // Check if the product is already in cart
        $check_query = "SELECT * FROM tb_keranjang WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            // Product already in cart, redirect to cart page
            header("Location: keranjang.php?status=info&message=Produk sudah ada di keranjang");
        } else {
            // Check product availability
            $product_query = "SELECT stok_produk FROM tb_produk WHERE id_produk = '$id_produk'";
            $product_result = mysqli_query($conn, $product_query);
            $product = mysqli_fetch_assoc($product_result);
            
            if ($product['stok_produk'] > 0) {
                // Add initial quantity to cart
                $insert_cart = "INSERT INTO tb_keranjang (id_customer, id_produk, jumlah, tanggal_ditambahkan, status) 
                                VALUES ('$id_customer', '$id_produk', 1, NOW(), 'dalam_keranjang')";
                $result_insert = mysqli_query($conn, $insert_cart);
                
                if (mysqli_affected_rows($conn) > 0) {
                    header("Location: keranjang.php?status=success&message=Produk ditambahkan ke keranjang");
                } else {
                    header("Location: keranjang.php?status=error&message=Gagal menambahkan produk");
                }
            } else {
                header("Location: keranjang.php?status=error&message=Stok produk tidak tersedia");
            }
        }
        exit;
    }
    
    // Remove from cart
    if ($action == 'remove' && $id_produk > 0) {
        // Remove from cart table
        $delete_query = "DELETE FROM tb_keranjang WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
        $result_delete = mysqli_query($conn, $delete_query);
        
        if (mysqli_affected_rows($conn) > 0) {
            header("Location: keranjang.php?status=success&message=Produk dihapus dari keranjang");
        } else {
            header("Location: keranjang.php?status=error&message=Gagal menghapus produk");
        }
        exit;
    }
    
    // Update quantity
    if ($action == 'update_qty' && $id_produk > 0 && isset($_POST['quantity'])) {
        $quantity = (int)$_POST['quantity'];
        
        if ($quantity <= 0) {
            header("Location: keranjang.php?status=error&message=Jumlah tidak valid");
            exit;
        }
        
        // Check if product has enough stock
        $stock_query = "SELECT stok_produk FROM tb_produk WHERE id_produk = '$id_produk'";
        $stock_result = mysqli_query($conn, $stock_query);
        $product = mysqli_fetch_assoc($stock_result);
        
        if ($quantity > $product['stok_produk']) {
            header("Location: keranjang.php?status=error&message=Jumlah melebihi stok yang tersedia");
            exit;
        }
        
        // Update quantity in cart
        $update_query = "UPDATE tb_keranjang SET jumlah = '$quantity' WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
        $result_update = mysqli_query($conn, $update_query);
        
        if (mysqli_affected_rows($conn) > 0) {
            header("Location: keranjang.php?status=success&message=Jumlah produk diperbarui");
        } else {
            header("Location: keranjang.php?status=error&message=Gagal memperbarui jumlah");
        }
        exit;
    }
    
    // Update product note
    if ($action == 'update_note' && $id_produk > 0 && isset($_POST['note'])) {
        $note = mysqli_real_escape_string($conn, $_POST['note']);
        
        // Update note in cart
        $update_query = "UPDATE tb_keranjang SET catatan_opsional = '$note' WHERE id_produk = '$id_produk' AND id_customer = '$id_customer' AND status = 'dalam_keranjang'";
        $result_update = mysqli_query($conn, $update_query);
        
        if (mysqli_affected_rows($conn) > 0) {
            header("Location: keranjang.php?status=success&message=Catatan produk diperbarui");
        } else {
            header("Location: keranjang.php?status=error&message=Gagal memperbarui catatan");
        }
        exit;
    }
    
    // Checkout process - redirect to checkout.php
    if ($action == 'checkout') {
        // Check if cart is empty
        $cart_count_query = "SELECT COUNT(*) as count FROM tb_keranjang WHERE id_customer = '$id_customer' AND status = 'dalam_keranjang'";
        $cart_count_result = mysqli_query($conn, $cart_count_query);
        $cart_count = mysqli_fetch_assoc($cart_count_result)['count'];
        
        if ($cart_count > 0) {
            // Redirect to checkout page
            header("Location: ../payment/checkout.php");
            exit;
        } else {
            header("Location: keranjang.php?status=error&message=Keranjang kosong");
            exit;
        }
    }
}

// Get cart items with quantities
$cart_query = "SELECT p.*, c.jumlah, c.catatan_opsional 
              FROM tb_produk p 
              JOIN tb_keranjang c ON p.id_produk = c.id_produk 
              WHERE c.id_customer = '$id_customer' AND c.status = 'dalam_keranjang'";
$cart_result = mysqli_query($conn, $cart_query);

// Calculate total
$total = 0;
$cart_items = [];
while ($row = mysqli_fetch_assoc($cart_result)) {
    $cart_items[] = $row;
    $total += ($row['harga_produk'] * $row['jumlah']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - MarketPlace</title>
    
    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../asset/style.css">
    
    <style>
        .cart-item {
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            background-color: #f8f9fa;
        }
        
        .cart-item img {
            object-fit: cover;
            height: 100px;
            width: 100px;
            border-radius: 8px;
        }
        
        .empty-cart {
            padding: 50px 0;
            text-align: center;
        }
        
        .empty-cart i {
            font-size: 50px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .quantity-control {
            max-width: 120px;
            margin: 0 auto;
        }
        
        .quantity-input {
            text-align: center;
            border-left: 0;
            border-right: 0;
        }
        
        .btn-update-qty {
            opacity: 0;
            transition: opacity 0.3s;
        }
        
        .quantity-control:hover .btn-update-qty {
            opacity: 1;
        }
        
    </style>
</head>
<body>

<?php include '../includes_P/navbar.php'; ?>

<div class="container py-5">
    <h2 class="mb-4 fw-bold">
        <i class="bi bi-cart3 me-2"></i> Keranjang Belanja
    </h2>
    
    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] == 'success'): ?>
            <div class="alert alert-success animate__animated animate__fadeIn" role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php elseif ($_GET['status'] == 'error'): ?>
            <div class="alert alert-danger animate__animated animate__fadeIn" role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php elseif ($_GET['status'] == 'info'): ?>
            <div class="alert alert-info animate__animated animate__fadeIn" role="alert">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <?php if (count($cart_items) > 0): ?>
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Produk</th>
                                <th>Kategori</th>
                                <th class="text-end">Harga</th>
                                <th class="text-center">Jumlah</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cart_items as $item): ?>
                                <?php 
                                    $gambarArray = json_decode($item['gambar'], true);
                                    $gambarUtama = isset($gambarArray[0]) ? $gambarArray[0] : 'default.jpg';
                                    $subtotal = $item['harga_produk'] * $item['jumlah'];
                                ?>
                                <tr class="cart-item">
                                    <td class="ps-4" data-label="Produk">
                                        <div class="d-flex align-items-center">
                                            <img src="../../uploads/<?= htmlspecialchars($gambarUtama) ?>" alt="<?= htmlspecialchars($item['nama_produk']) ?>" class="me-3">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($item['nama_produk']) ?></h6>
                                                <small class="text-muted">ID: <?= $item['id_produk'] ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle" data-label="Kategori"><?= htmlspecialchars($item['jenis_produk']) ?></td>
                                    <td class="align-middle text-end" data-label="Harga">Rp <?= number_format($item['harga_produk'], 0, ',', '.') ?></td>
                                    <td class="align-middle text-center" data-label="Jumlah">
                                        <form id="form-qty-<?= $item['id_produk'] ?>" action="keranjang.php?action=update_qty&id=<?= $item['id_produk'] ?>" method="post" class="quantity-control">
                                            <div class="input-group">
                                                <button type="button" class="btn btn-outline-secondary btn-sm quantity-minus" data-id="<?= $item['id_produk'] ?>">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" name="quantity" min="1" max="<?= $item['stok_produk'] ?>" value="<?= $item['jumlah'] ?>" 
                                                       class="form-control form-control-sm quantity-input" id="quantity-<?= $item['id_produk'] ?>">
                                                <button type="button" class="btn btn-outline-secondary btn-sm quantity-plus" data-id="<?= $item['id_produk'] ?>">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                            <div class="mt-1">
                                                <button type="submit" class="btn btn-sm btn-primary btn-update-qty w-100">
                                                    <i class="bi bi-check2"></i> Update
                                                </button>
                                            </div>
                                            <div class="mt-1">
                                                <a href="#" class="btn btn-sm btn-outline-secondary btn-add-note w-100" data-bs-toggle="modal" data-bs-target="#noteModal-<?= $item['id_produk'] ?>">
                                                    <i class="bi bi-pencil"></i> Catatan
                                                </a>
                                            </div>
                                            <div class="mt-1">
                                                <small class="text-muted">Stok: <?= $item['stok_produk'] ?></small>
                                            </div>
                                            <?php if ($item['catatan_opsional']): ?>
                                            <div class="mt-2">
                                                <small class="text-muted">Catatan: <?= htmlspecialchars($item['catatan_opsional']) ?></small>
                                            </div>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                    <td class="align-middle text-end fw-bold" data-label="Subtotal">
                                        Rp <?= number_format($subtotal, 0, ',', '.') ?>
                                    </td>
                                    <td class="align-middle text-center" data-label="Aksi">
                                        <a href="../produk/detail_produk.php?id=<?= $item['id_produk'] ?>" class="btn btn-sm btn-outline-primary me-2">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="keranjang.php?action=remove&id=<?= $item['id_produk'] ?>" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 offset-md-6">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Ringkasan Belanja</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Barang</span>
                            <span><?= count($cart_items) ?> item</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Total Harga</span>
                            <span class="fw-bold">Rp <?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        <a href="keranjang.php?action=checkout" class="btn btn-success w-100">
                            <i class="bi bi-credit-card me-2"></i> Lanjutkan ke Pembayaran
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body">
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h4 class="mb-3">Keranjang Anda Kosong</h4>
                    <p class="text-muted mb-4">Belum ada produk yang ditambahkan ke keranjang belanja.</p>
                    <a href="../index.php" class="btn btn-primary">
                        <i class="bi bi-shop me-2"></i> Lanjutkan Belanja
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes_P/footer.php'; ?>

<!-- Note Modal -->
<?php foreach ($cart_items as $item): ?>
<div class="modal fade" id="noteModal-<?= $item['id_produk'] ?>" tabindex="-1" aria-labelledby="noteModalLabel-<?= $item['id_produk'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="noteModalLabel-<?= $item['id_produk'] ?>">Tambah Catatan untuk <?= htmlspecialchars($item['nama_produk']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="keranjang.php?action=update_note&id=<?= $item['id_produk'] ?>" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note-<?= $item['id_produk'] ?>" class="form-label">Catatan Opsional</label>
                        <textarea class="form-control" id="note-<?= $item['id_produk'] ?>" name="note" rows="3" placeholder="Contoh: Warna, ukuran, atau permintaan khusus lainnya"><?= htmlspecialchars($item['catatan_opsional'] ?? '') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../asset/script.js"></script>

<script>
    // Quantity control functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Plus button functionality
        const plusButtons = document.querySelectorAll('.quantity-plus');
        plusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const inputElement = document.getElementById('quantity-' + productId);
                let currentValue = parseInt(inputElement.value);
                const maxValue = parseInt(inputElement.getAttribute('max'));
                
                if (currentValue < maxValue) {
                    inputElement.value = currentValue + 1;
                }
            });
        });
        
        // Minus button functionality
        const minusButtons = document.querySelectorAll('.quantity-minus');
        minusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.getAttribute('data-id');
                const inputElement = document.getElementById('quantity-' + productId);
                let currentValue = parseInt(inputElement.value);
                
                if (currentValue > 1) {
                    inputElement.value = currentValue - 1;
                }
            });
        });
        
        // Input validation
        const quantityInputs = document.querySelectorAll('.quantity-input');
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                const max = parseInt(this.getAttribute('max'));
                
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                } else if (value > max) {
                    this.value = max;
                }
            });
        });
    });
</script>

</body>
</html>