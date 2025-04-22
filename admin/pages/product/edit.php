<?php
// Include database connection
require_once '../../../config/database.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get product data
$query = "SELECT * FROM tb_produk WHERE id_produk = '$id'";
$result = mysqli_query($conn, $query);

// Check if product exists
if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

$product = mysqli_fetch_assoc($result);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $nama_produk = trim($_POST['nama_produk']);
    $harga_produk = trim($_POST['harga_produk']);
    $stok_produk = trim($_POST['stok_produk']);
    $jenis_produk = trim($_POST['jenis_produk']);
    $exp_produk = trim($_POST['exp_produk']);
    
    // Validate form data
    $errors = [];
    
    if (empty($nama_produk)) {
        $errors[] = 'Product name is required';
    }
    
    if (empty($harga_produk)) {
        $errors[] = 'Product price is required';
    } elseif (!is_numeric($harga_produk)) {
        $errors[] = 'Product price must be a number';
    }
    
    if (empty($stok_produk)) {
        $errors[] = 'Product stock is required';
    } elseif (!is_numeric($stok_produk)) {
        $errors[] = 'Product stock must be a number';
    }
    
    if (empty($jenis_produk)) {
        $errors[] = 'Product type is required';
    }
    
    if (empty($exp_produk)) {
        $errors[] = 'Product expiry date is required';
    }
    
    // If no errors, update data
    if (empty($errors)) {
        $updateQuery = "UPDATE tb_produk SET 
                        nama_produk = '$nama_produk', 
                        harga_produk = '$harga_produk', 
                        stok_produk = '$stok_produk', 
                        jenis_produk = '$jenis_produk', 
                        exp_produk = '$exp_produk' 
                        WHERE id_produk = '$id'";
        
        if (mysqli_query($conn, $updateQuery)) {
            $message = 'Product updated successfully';
            $messageType = 'success';
            
            // Update product data
            $product['nama_produk'] = $nama_produk;
            $product['harga_produk'] = $harga_produk;
            $product['stok_produk'] = $stok_produk;
            $product['jenis_produk'] = $jenis_produk;
            $product['exp_produk'] = $exp_produk;
        } else {
            $message = 'Error updating product: ' . mysqli_error($conn);
            $messageType = 'danger';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'danger';
    }
}

// Include header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Product</h1>
    <a href="index.php" class="btn btn-secondary">Back to Products</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="id_produk" class="form-label">Product ID</label>
                    <input type="text" class="form-control" id="id_produk" value="<?php echo htmlspecialchars($product['id_produk']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="nama_produk" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="harga_produk" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="harga_produk" name="harga_produk" value="<?php echo htmlspecialchars($product['harga_produk']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="stok_produk" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stok_produk" name="stok_produk" value="<?php echo htmlspecialchars($product['stok_produk']); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
            <div class="col-md-6">
    <label for="jenis_produk" class="form-label">Product Type</label>
    <select class="form-select" id="jenis_produk" name="jenis_produk" required>
        <option value="elektronik" <?php echo $product['jenis_produk'] == 'elektronik' ? 'selected' : ''; ?>>Elektronik</option>
        <option value="fashion" <?php echo $product['jenis_produk'] == 'fashion' ? 'selected' : ''; ?>>Fashion</option>
        <option value="kecantikan" <?php echo $product['jenis_produk'] == 'kecantikan' ? 'selected' : ''; ?>>Kecantikan & Perawatan</option>
        <option value="olahraga" <?php echo $product['jenis_produk'] == 'olahraga' ? 'selected' : ''; ?>>Olahraga & Kebugaran</option>
        <option value="makanan" <?php echo $product['jenis_produk'] == 'makanan' ? 'selected' : ''; ?>>Makanan & Minuman</option>
        <option value="perabotan" <?php echo $product['jenis_produk'] == 'perabotan' ? 'selected' : ''; ?>>Perabotan Rumah Tangga</option>
        <option value="mainan" <?php echo $product['jenis_produk'] == 'mainan' ? 'selected' : ''; ?>>Mainan & Hobi</option>
        <option value="kesehatan" <?php echo $product['jenis_produk'] == 'kesehatan' ? 'selected' : ''; ?>>Kesehatan & Kebugaran</option>
        <option value="automotif" <?php echo $product['jenis_produk'] == 'automotif' ? 'selected' : ''; ?>>Automotif</option>
        <option value="gadget" <?php echo $product['jenis_produk'] == 'gadget' ? 'selected' : ''; ?>>Gadget & Aksesori</option>
        <option value="buku" <?php echo $product['jenis_produk'] == 'buku' ? 'selected' : ''; ?>>Buku & Perlengkapan Pendidikan</option>
        <option value="rumah_kebun" <?php echo $product['jenis_produk'] == 'rumah_kebun' ? 'selected' : ''; ?>>Rumah & Kebun</option>
        <option value="travel" <?php echo $product['jenis_produk'] == 'travel' ? 'selected' : ''; ?>>Travel & Liburan</option>
        <option value="kehidupan_sehari" <?php echo $product['jenis_produk'] == 'kehidupan_sehari' ? 'selected' : ''; ?>>Kehidupan Sehari-hari</option>
        <option value="seni_kerajinan" <?php echo $product['jenis_produk'] == 'seni_kerajinan' ? 'selected' : ''; ?>>Karya Seni & Kerajinan</option>
    </select>
</div>

                
                <div class="col-md-6">
                    <label for="exp_produk" class="form-label">Expiry Date</label>
                    <input type="date" class="form-control" id="exp_produk" name="exp_produk" value="<?php echo htmlspecialchars($product['exp_produk']); ?>" required>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>