<?php
// Include database connection
require_once '../../../config/database.php';

// Get all customers for dropdown
$customersQuery = "SELECT id_customer, nama_customer FROM tb_customer ORDER BY nama_customer";
$customersResult = mysqli_query($conn, $customersQuery);

// Get all products for dropdown
$productsQuery = "SELECT id_produk, nama_produk, harga_produk, stok_produk FROM tb_produk WHERE stok_produk > 0 ORDER BY nama_produk";
$productsResult = mysqli_query($conn, $productsQuery);

// Process form submission
$message = '';
$messageType = '';
$formData = [
    'tanggal_pembelian' => date('Y-m-d'),
    'id_customer' => '',
    'id_produk' => '',
    'jumlah_produk' => '',
    'metode_pembayaran' => '',
    'jumlah_pembayaran' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $formData = [
        'tanggal_pembelian' => trim($_POST['tanggal_pembelian']),
        'id_customer' => trim($_POST['id_customer']),
        'id_produk' => trim($_POST['id_produk']),
        'jumlah_produk' => trim($_POST['jumlah_produk']),
        'metode_pembayaran' => trim($_POST['metode_pembayaran']),
        'jumlah_pembayaran' => trim($_POST['jumlah_pembayaran'])
    ];
    
    // Validate form data
    $errors = [];
    
    if (empty($formData['tanggal_pembelian'])) {
        $errors[] = 'Purchase date is required';
    }
    
    if (empty($formData['id_customer'])) {
        $errors[] = 'Customer is required';
    }
    
    if (empty($formData['id_produk'])) {
        $errors[] = 'Product is required';
    }
    
    if (empty($formData['jumlah_produk'])) {
        $errors[] = 'Quantity is required';
    } elseif (!is_numeric($formData['jumlah_produk']) || $formData['jumlah_produk'] <= 0) {
        $errors[] = 'Quantity must be a positive number';
    } else {
        // Check product stock
        $stockQuery = "SELECT stok_produk FROM tb_produk WHERE id_produk = '{$formData['id_produk']}'";
        $stockResult = mysqli_query($conn, $stockQuery);
        $stock = mysqli_fetch_assoc($stockResult)['stok_produk'];
        
        if ($formData['jumlah_produk'] > $stock) {
            $errors[] = "Not enough stock. Available: $stock";
        }
    }
    
    if (empty($formData['metode_pembayaran'])) {
        $errors[] = 'Payment method is required';
    }
    
    if (empty($formData['jumlah_pembayaran'])) {
        $errors[] = 'Payment amount is required';
    } elseif (!is_numeric($formData['jumlah_pembayaran']) || $formData['jumlah_pembayaran'] <= 0) {
        $errors[] = 'Payment amount must be a positive number';
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        // Begin transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Auto-increment ID
            $getMaxIdQuery = "SELECT MAX(id_pembelian) as max_id FROM tb_pembelian";
            $maxIdResult = mysqli_query($conn, $getMaxIdQuery);
            $maxId = mysqli_fetch_assoc($maxIdResult)['max_id'];
            $newId = ($maxId > 0) ? $maxId + 1 : 1;
            
            // Insert purchase
            $insertQuery = "INSERT INTO tb_pembelian (id_pembelian, tanggal_pembelian, id_produk, jumlah_produk, id_customer, metode_pembayaran, jumlah_pembayaran) 
                          VALUES ('$newId', '{$formData['tanggal_pembelian']}', '{$formData['id_produk']}', 
                                 '{$formData['jumlah_produk']}', '{$formData['id_customer']}', '{$formData['metode_pembayaran']}', '{$formData['jumlah_pembayaran']}')";
            
            mysqli_query($conn, $insertQuery);
            
            // Update product stock
            $updateStockQuery = "UPDATE tb_produk SET stok_produk = stok_produk - {$formData['jumlah_produk']} WHERE id_produk = '{$formData['id_produk']}'";
            mysqli_query($conn, $updateStockQuery);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $message = 'Purchase recorded successfully';
            $messageType = 'success';
            
            // Reset form data after successful submission
            $formData = [
                'tanggal_pembelian' => date('Y-m-d'),
                'id_customer' => '',
                'id_produk' => '',
                'jumlah_produk' => '',
                'metode_pembayaran' => '',
                'jumlah_pembayaran' => ''
            ];
            
            // Refresh products result for dropdown (to update stock)
            $productsResult = mysqli_query($conn, $productsQuery);
        } catch (Exception $e) {
            // Rollback transaction
            mysqli_rollback($conn);
            
            $message = 'Error recording purchase: ' . $e->getMessage();
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
    <h1>Add New Purchase</h1>
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
                    <label for="tanggal_pembelian" class="form-label">Purchase Date</label>
                    <input type="date" class="form-control" id="tanggal_pembelian" name="tanggal_pembelian" value="<?php echo htmlspecialchars($formData['tanggal_pembelian']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="id_customer" class="form-label">Customer</label>
                    <select class="form-select" id="id_customer" name="id_customer" required>
                        <option value="">Select Customer</option>
                        <?php while ($customer = mysqli_fetch_assoc($customersResult)): ?>
                            <option value="<?php echo $customer['id_customer']; ?>" <?php echo ($formData['id_customer'] == $customer['id_customer']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($customer['nama_customer']); ?> (ID: <?php echo $customer['id_customer']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="id_produk" class="form-label">Product</label>
                    <select class="form-select" id="id_produk" name="id_produk" required>
                        <option value="">Select Product</option>
                        <?php while ($product = mysqli_fetch_assoc($productsResult)): ?>
                            <option value="<?php echo $product['id_produk']; ?>" 
                                    data-price="<?php echo $product['harga_produk']; ?>"
                                    data-stock="<?php echo $product['stok_produk']; ?>"
                                    <?php echo ($formData['id_produk'] == $product['id_produk']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($product['nama_produk']); ?> - Rp <?php echo number_format($product['harga_produk'], 0, ',', '.'); ?> (Stock: <?php echo $product['stok_produk']; ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="jumlah_produk" class="form-label">Quantity</label>
                    <input type="number" class="form-control" id="jumlah_produk" name="jumlah_produk" min="1" value="<?php echo htmlspecialchars($formData['jumlah_produk']); ?>" required>
                    <div id="stock-info" class="form-text"></div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="metode_pembayaran" class="form-label">Payment Method</label>
                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                        <option value="">Select Payment Method</option>
                        <option value="Cash" <?php echo ($formData['metode_pembayaran'] == 'Cash') ? 'selected' : ''; ?>>Cash</option>
                        <option value="Credit Card" <?php echo ($formData['metode_pembayaran'] == 'Credit Card') ? 'selected' : ''; ?>>Credit Card</option>
                        <option value="Debit Card" <?php echo ($formData['metode_pembayaran'] == 'Debit Card') ? 'selected' : ''; ?>>Debit Card</option>
                        <option value="Bank Transfer" <?php echo ($formData['metode_pembayaran'] == 'Bank Transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                        <option value="Digital Wallet" <?php echo ($formData['metode_pembayaran'] == 'Digital Wallet') ? 'selected' : ''; ?>>Digital Wallet</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="jumlah_pembayaran" class="form-label">Payment Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="jumlah_pembayaran" name="jumlah_pembayaran" value="<?php echo htmlspecialchars($formData['jumlah_pembayaran']); ?>" required>
                    </div>
                </div>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                <button type="submit" class="btn btn-primary">Save Purchase</button>
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