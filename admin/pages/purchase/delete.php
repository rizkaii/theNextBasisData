<?php
// Include database connection
require_once '../../../config/database.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if the purchase exists
$checkQuery = "SELECT * FROM tb_pembelian WHERE id_pembelian = '$id'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    header('Location: index.php');
    exit();
}

$purchase = mysqli_fetch_assoc($checkResult);

// Process deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    // Begin transaction
    mysqli_begin_transaction($conn);
    
    try {
        // Return product stock
        $returnStockQuery = "UPDATE tb_produk SET stok_produk = stok_produk + {$purchase['jumlah_produk']} WHERE id_produk = '{$purchase['id_produk']}'";
        mysqli_query($conn, $returnStockQuery);
        
        // Delete purchase
        $deleteQuery = "DELETE FROM tb_pembelian WHERE id_pembelian = '$id'";
        mysqli_query($conn, $deleteQuery);
        
        // Commit transaction
        mysqli_commit($conn);
        
        // Redirect to purchases page with success message
        header('Location: index.php?message=Purchase deleted successfully&type=success');
        exit();
    } catch (Exception $e) {
        // Rollback transaction
        mysqli_rollback($conn);
        
        // Redirect to purchases page with error message
        header('Location: index.php?message=Error deleting purchase: ' . $e->getMessage() . '&type=danger');
        exit();
    }
}

// Get product and customer details
$productQuery = "SELECT nama_produk FROM tb_produk WHERE id_produk = '{$purchase['id_produk']}'";
$productResult = mysqli_query($conn, $productQuery);
$productName = (mysqli_num_rows($productResult) > 0) ? mysqli_fetch_assoc($productResult)['nama_produk'] : 'Unknown Product';

$customerQuery = "SELECT nama_customer FROM tb_customer WHERE id_customer = '{$purchase['id_customer']}'";
$customerResult = mysqli_query($conn, $customerQuery);
$customerName = (mysqli_num_rows($customerResult) > 0) ? mysqli_fetch_assoc($customerResult)['nama_customer'] : 'Unknown Customer';

// Include header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Delete Purchase</h1>
    <a href="index.php" class="btn btn-secondary">Back to Purchases</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Warning!</h4>
            <p>Are you sure you want to delete this purchase? This action cannot be undone.</p>
            <p>The product quantity will be returned to inventory.</p>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Purchase Details</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> <?php echo htmlspecialchars($purchase['id_pembelian']); ?></p>
                <p><strong>Date:</strong> <?php echo date('d-m-Y', strtotime($purchase['tanggal_pembelian'])); ?></p>
                <p><strong>Customer:</strong> <?php echo htmlspecialchars($customerName); ?> (ID: <?php echo htmlspecialchars($purchase['id_customer']); ?>)</p>
                <p><strong>Product:</strong> <?php echo htmlspecialchars($productName); ?> (ID: <?php echo htmlspecialchars($purchase['id_produk']); ?>)</p>
                <p><strong>Quantity:</strong> <?php echo htmlspecialchars($purchase['jumlah_produk']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($purchase['metode_pembayaran']); ?></p>
                <p><strong>Payment Amount:</strong> Rp <?php echo number_format($purchase['jumlah_pembayaran'], 0, ',', '.'); ?></p>
            </div>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="confirm_delete" value="yes">
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-danger">Delete Purchase</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>