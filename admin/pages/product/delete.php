<?php
// Include database connection
require_once '../../../config/database.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if the product exists
$checkQuery = "SELECT id_produk FROM tb_produk WHERE id_produk = '$id'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    header('Location: index.php');
    exit();
}

// Process deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    // Delete product
    $deleteQuery = "DELETE FROM tb_produk WHERE id_produk = '$id'";
    
    if (mysqli_query($conn, $deleteQuery)) {
        // Redirect to products page with success message
        header('Location: index.php?message=Product deleted successfully&type=success');
        exit();
    } else {
        // Redirect to products page with error message
        header('Location: index.php?message=Error deleting product: ' . mysqli_error($conn) . '&type=danger');
        exit();
    }
}

// Include header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Delete Product</h1>
    <a href="index.php" class="btn btn-secondary">Back to Products</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Warning!</h4>
            <p>Are you sure you want to delete this product? This action cannot be undone.</p>
        </div>
        
        <?php
        // Get product details for confirmation
        $productQuery = "SELECT * FROM tb_produk WHERE id_produk = '$id'";
        $productResult = mysqli_query($conn, $productQuery);
        $product = mysqli_fetch_assoc($productResult);
        ?>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Product Details</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> <?php echo htmlspecialchars($product['id_produk']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($product['nama_produk']); ?></p>
                <p><strong>Price:</strong> Rp <?php echo number_format($product['harga_produk'], 0, ',', '.'); ?></p>
                <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stok_produk']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($product['jenis_produk']); ?></p>
                <p><strong>Expiry Date:</strong> <?php echo date('d-m-Y', strtotime($product['exp_produk'])); ?></p>
            </div>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="confirm_delete" value="yes">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-danger">Delete Product</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>