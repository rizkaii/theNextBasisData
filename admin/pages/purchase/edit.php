<?php
// Include database connection
require_once '../../../config/database.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get purchase data
$query = "SELECT * FROM tb_pembelian WHERE id_pembelian = '$id'";
$result = mysqli_query($conn, $query);

// Check if purchase exists
if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

$purchase = mysqli_fetch_assoc($result);

// Get original product quantity for stock calculation
$originalQuantity = $purchase['jumlah_produk'];
$originalProductId = $purchase['id_produk'];

// Get all customers for dropdown
$customersQuery = "SELECT id_customer, nama_customer FROM tb_customer ORDER BY nama_customer";
$customersResult = mysqli_query($conn, $customersQuery);

// Get all products for dropdown (including 0 stock to allow original product to be selected)
$productsQuery = "SELECT id_produk, nama_produk, harga_produk, stok_produk FROM tb_produk ORDER BY nama_produk";
$productsResult = mysqli_query($conn, $productsQuery);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $tanggal_pembelian = trim($_POST['tanggal_pembelian']);
    $id_customer = trim($_POST['id_customer']);
    $id_produk = trim($_POST['id_produk']);
    $jumlah_produk = trim($_POST['jumlah_produk']);
    $metode_pembayaran = trim($_POST['metode_pembayaran']);
    $jumlah_pembayaran = trim($_POST['jumlah_pembayaran']);
    
    // Validate form data
    $errors = [];
    
    if (empty($tanggal_pembelian)) {
        $errors[] = 'Purchase date is required';
    }
    
    if (empty($id_customer)) {
        $errors[] = 'Customer is required';
    }
    
    if (empty($id_produk)) {
        $errors[] = 'Product is required';
    }
    
    if (empty($jumlah_produk)) {
        $errors[] = 'Quantity is required';
    } elseif (!is_numeric($jumlah_produk) || $jumlah_produk <= 0) {
        $errors[] = 'Quantity must be a positive number';
    } else {
        // Check product stock if product changed or quantity increased
        if ($id_produk != $originalProductId || $jumlah_produk > $originalQuantity) {
            // If product changed, check new product stock
            if ($id_produk != $originalProductId) {
                $stockQuery = "SELECT stok_produk FROM tb_produk WHERE id_produk = '$id_produk'";
                $stockResult = mysqli_query($conn, $stockQuery);
                $availableStock = mysqli_fetch_assoc($stockResult)['stok_produk'];
                
                if ($jumlah_produk > $availableStock) {
                    $errors[] = "Not enough stock. Available: $availableStock";
                }
            } else {
                // If same product but quantity increased, check additional stock
                $stockQuery = "SELECT stok_produk FROM tb_produk WHERE id_produk = '$id_produk'";
                $stockResult = mysqli_query($conn, $stockQuery);
                $currentStock = mysqli_fetch_assoc($stockResult)['stok_produk'];
                $additionalQuantity = $jumlah_produk - $originalQuantity;
                
                if ($additionalQuantity > $currentStock) {
                    $errors[] = "Not enough stock for additional quantity. Available: $currentStock";
                }
            }
        }
    }
    
    if (empty($metode_pembayaran)) {
        $errors[] = 'Payment method is required';
    }
    
    if (empty($jumlah_pembayaran)) {
        $errors[] = 'Payment amount is required';
    } elseif (!is_numeric($jumlah_pembayaran) || $jumlah_pembayaran <= 0) {
        $errors[] = 'Payment amount must be a positive number';
    }
    
    // If no errors, update data
    if (empty($errors)) {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Update purchase
            $updateQuery = "UPDATE tb_pembelian SET 
                            tanggal_pembelian = '$tanggal_pembelian', 
                            id_customer = '$id_customer', 
                            id_produk = '$id_produk', 
                            jumlah_produk = '$jumlah_produk', 
                            metode_pembayaran = '$metode_pembayaran', 
                            jumlah_pembayaran = '$jumlah_pembayaran' 
                            WHERE id_pembelian = '$id'";
            
            mysqli_query($conn, $updateQuery);
            
            // Adjust stock for original product (add back the original quantity)
            $updateOriginalStockQuery = "UPDATE tb_produk SET stok_produk = stok_produk + $originalQuantity WHERE id_produk = '$originalProductId'";
            mysqli_query($conn, $updateOriginalStockQuery);
            
            // Adjust stock for new or same product (subtract the new quantity)
            $updateNewStockQuery = "UPDATE tb_produk SET stok_produk = stok_produk - $jumlah_produk WHERE id_produk = '$id_produk'";
            mysqli_query($conn, $updateNewStockQuery);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $message = 'Purchase updated successfully';
            $messageType = 'success';
            
            // Update purchase data
            $purchase['tanggal_pembelian'] = $tanggal_pembelian;
            $purchase['id_customer'] = $id_customer;
            $purchase['id_produk'] = $id_produk;
            $purchase['jumlah_produk'] = $jumlah_produk;
            $purchase['metode_pembayaran'] = $metode_pembayaran;
            $purchase['jumlah_pembayaran'] = $jumlah_pembayaran;
            
            // Update original values for next edit
            $originalQuantity = $jumlah_produk;
            $originalProductId = $id_produk;
            
            // Refresh products result for dropdown (to update stock)
            $productsResult = mysqli_query($conn, $productsQuery);
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            
            $message = 'Error updating purchase: ' . $e->getMessage();
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
    <h1>Edit Purchase</h1>
    <a href="index.php" class="btn btn-secondary">Back to Purchases</a>
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
                    <label for="id_pembelian" class="form-label">Purchase ID</label>
                    <input type="text" class="form-control" id="id_pembelian" value="<?php echo htmlspecialchars($purchase['id_pembelian']); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="tanggal_pembelian" class="form-label">Purchase Date</label>
                    <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" value="<?php echo htmlspecialchars($purchase['tanggal_pembelian']); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="id_customer" class="form-label">Customer</label>
                    <select class="form-select" id="id_customer" name="id_customer" required>
                        <option value="">Select Customer</option>
                        <?php 
                        mysqli_data_seek($customersResult, 0);
                        while ($customer = mysqli_fetch_assoc($customersResult)): 
                        ?>
                            <option value="<?php echo $customer['id_customer']; ?>" <?php echo ($purchase['id_customer'] == $customer['id_customer']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['nama_customer']); ?> (ID: <?php echo $customer['id_customer']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="jumlah_pembayaran" class="form-label">Payment Amount</label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="number" class="form-control" id="jumlah_pembayaran" name="jumlah_pembayaran" value="<?php echo htmlspecialchars($purchase['jumlah_pembayaran']); ?>" required>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Purchase</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Auto-calculate payment amount based on product price and quantity
    document.addEventListener('DOMContentLoaded', function() {
        const productSelect = document.getElementById('id_produk');
        const quantityInput = document.getElementById('jumlah_produk');
        const paymentAmountInput = document.getElementById('jumlah_pembayaran');
        const stockInfo = document.getElementById('stock-info');
        
        function updatePaymentAmount() {
            if (productSelect.value && quantityInput.value) {
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                const stock = selectedOption.getAttribute('data-stock');
                
                // Update stock info
                stockInfo.textContent = `Available stock: ${stock}`;
                
                // Calculate total
                const total = price * quantityInput.value;
                paymentAmountInput.value = total;
            }
        }
        
        productSelect.addEventListener('change', updatePaymentAmount);
        quantityInput.addEventListener('change', updatePaymentAmount);
        quantityInput.addEventListener('keyup', updatePaymentAmount);
        
        // Initial calculation
        if (productSelect.value) {
            updatePaymentAmount();
        }
    });
</script>

<?php
// Include footer
include '../includes/footer.php';
?>
                </div>
                <div class="col-md-6">
                    <label for="id_produk" class="form-label">Product</label>
                    <select class="form-select" id="id_produk" name="id_produk" required>
                        <option value="">Select Product</option>
                        <?php 
                        mysqli_data_seek($productsResult, 0);
                        while ($product = mysqli_fetch_assoc($productsResult)): 
                            // Calculate available stock for this product
                            $availableStock = $product['stok_produk'];
                            if ($product['id_produk'] == $originalProductId) {
                                $availableStock += $originalQuantity;
                            }
                        ?>
                            <option value="<?php echo $product['id_produk']; ?>" 
                                    data-price="<?php echo $product['harga_produk']; ?>"
                                    data-stock="<?php echo $availableStock; ?>"
                                    <?php echo ($purchase['id_produk'] == $product['id_produk']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['nama_produk']); ?> - Rp <?php echo number_format($product['harga_produk'], 0, ',', '.'); ?> 
                                (Stock: <?php echo $availableStock; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="jumlah_produk" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="jumlah_produk" name="jumlah_produk" min="1" value="<?php echo htmlspecialchars($purchase['jumlah_produk']); ?>" required>
                    <div id="stock-info" class="form-text"></div>
                </div>
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Payment Method</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash" <?php echo ($purchase['metode_pembayaran'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                        <option value="Credit Card" <?php echo ($purchase['metode_pembayaran'] == 'Credit Card') ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="Debit Card" <?php echo ($purchase['metode_pembayaran'] == 'Debit Card') ? 'selected' : ''; ?>>Debit Card</option>
                        <option value="Bank Transfer" <?php echo ($purchase['metode_pembayaran'] == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                        <option value="Digital Wallet" <?php echo ($purchase['metode_pembayaran'] == 'Digital Wallet') ? 'selected' : ''; ?>>Digital Wallet</option>
                    </select>