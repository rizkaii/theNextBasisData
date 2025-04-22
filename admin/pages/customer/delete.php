<?php
// Include database connection
require_once '../../../config/database.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Check if the customer exists and get login ID
$checkQuery = "SELECT c.id_customer, c.nama_customer, c.id_login 
               FROM tb_customer c 
               WHERE c.id_customer = '$id'";
$checkResult = mysqli_query($conn, $checkQuery);

if (mysqli_num_rows($checkResult) == 0) {
    header('Location: index.php?message=Customer not found&type=danger');
    exit();
}

$customerData = mysqli_fetch_assoc($checkResult);
$loginId = $customerData['id_login'];

// Process force deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    // Start transaction for atomic operation
    mysqli_begin_transaction($conn);
    
    try {
        $success = true;
        $errorMessages = [];
        
        // 1. Delete from tb_pembelian (purchases)
        $deletePurchasesQuery = "DELETE FROM tb_pembelian WHERE id_customer = '$id'";
        if (!mysqli_query($conn, $deletePurchasesQuery)) {
            $success = false;
            $errorMessages[] = "Failed to delete purchase records: " . mysqli_error($conn);
        }
        
        // 2. Delete from tb_keranjang (cart)
        $deleteCartQuery = "DELETE FROM tb_keranjang WHERE id_customer = '$id'";
        if (!mysqli_query($conn, $deleteCartQuery)) {
            $success = false;
            $errorMessages[] = "Failed to delete cart items: " . mysqli_error($conn);
        }
        
        // 3. Delete from tb_customer
        $deleteCustomerQuery = "DELETE FROM tb_customer WHERE id_customer = '$id'";
        if (!mysqli_query($conn, $deleteCustomerQuery)) {
            $success = false;
            $errorMessages[] = "Failed to delete customer: " . mysqli_error($conn);
        }
        
        // 4. Delete from tb_login if login ID exists
        if ($loginId) {
            $deleteLoginQuery = "DELETE FROM tb_login WHERE id = '$loginId'";
            if (!mysqli_query($conn, $deleteLoginQuery)) {
                $success = false;
                $errorMessages[] = "Failed to delete login account: " . mysqli_error($conn);
            }
        }
        
        // Commit or rollback based on success
        if ($success) {
            mysqli_commit($conn);
            header('Location: index.php?message=Customer and all related data deleted successfully&type=success');
            exit();
        } else {
            // Rollback if any step failed
            mysqli_rollback($conn);
            $errorMessage = implode("<br>", $errorMessages);
            header('Location: index.php?message=' . urlencode("Error deleting customer: " . $errorMessage) . '&type=danger');
            exit();
        }
    } catch (Exception $e) {
        // Rollback on any exception
        mysqli_rollback($conn);
        header('Location: index.php?message=' . urlencode("Error deleting customer: " . $e->getMessage()) . '&type=danger');
        exit();
    }
}

// Get related record counts for display purposes
// Check purchases
$purchasesQuery = "SELECT COUNT(*) as count FROM tb_pembelian WHERE id_customer = '$id'";
$purchasesResult = mysqli_query($conn, $purchasesQuery);
$purchasesCount = mysqli_fetch_assoc($purchasesResult)['count'];

// Check shopping cart items
$cartQuery = "SELECT COUNT(*) as count FROM tb_keranjang WHERE id_customer = '$id'";
$cartResult = mysqli_query($conn, $cartQuery);
$cartCount = mysqli_fetch_assoc($cartResult)['count'];

// Include header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Force Delete Customer</h1>
    <a href="index.php" class="btn btn-secondary">Back to Customers</a>
</div>

<div class="card">
    <div class="card-body">
        <div class="alert alert-danger">
            <h4 class="alert-heading">Warning!</h4>
            <p>You are about to permanently delete this customer and ALL related data. This action cannot be undone.</p>
            
            <hr>
            <p class="mb-0"><strong>The following data will be deleted:</strong></p>
            <ul>
                <li>Customer profile</li>
                <li>User account (login credentials)</li>
                <?php if ($purchasesCount > 0): ?>
                <li><?php echo $purchasesCount; ?> purchase record(s)</li>
                <?php endif; ?>
                <?php if ($cartCount > 0): ?>
                <li><?php echo $cartCount; ?> cart item(s)</li>
                <?php endif; ?>
            </ul>
        </div>
        
        <?php
        // Get customer details for confirmation
        $customerQuery = "SELECT c.*, l.username 
                         FROM tb_customer c 
                         LEFT JOIN tb_login l ON c.id_login = l.id 
                         WHERE c.id_customer = '$id'";
        $customerResult = mysqli_query($conn, $customerQuery);
        $customer = mysqli_fetch_assoc($customerResult);
        ?>
        
        <div class="card mb-4">
            <div class="card-header bg-danger text-white">
                <h5 class="card-title mb-0">Customer To Be Deleted</h5>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> <?php echo htmlspecialchars($customer['id_customer']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($customer['nama_customer']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($customer['alamat_customer']); ?></p>
                <p><strong>WhatsApp:</strong> <?php echo htmlspecialchars($customer['no_wa_cutomer']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($customer['email_customer']); ?></p>
                <?php if (!empty($customer['username'])): ?>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($customer['username']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <form method="POST" action="">
            <input type="hidden" name="confirm_delete" value="yes">
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="confirm_understanding" required>
                <label class="form-check-label" for="confirm_understanding">
                    I understand that this action will permanently delete all customer data and cannot be undone
                </label>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-danger">Permanently Delete Customer</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>