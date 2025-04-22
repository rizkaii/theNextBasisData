<?php
// Include database connection
require_once '../../../config/database.php';

// Check if ID is set
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get customer data
$query = "SELECT * FROM tb_customer WHERE id_customer = '$id'";
$result = mysqli_query($conn, $query);

// Check if customer exists
if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

$customer = mysqli_fetch_assoc($result);

// Process form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $nama_customer = trim($_POST['nama_customer']);
    $alamat_customer = trim($_POST['alamat_customer']);
    $no_wa_cutomer = trim($_POST['no_wa_cutomer']);
    $email_customer = trim($_POST['email_customer']);
    
    // Validate form data
    $errors = [];
    
    if (empty($nama_customer)) {
        $errors[] = 'Customer name is required';
    }
    
    if (empty($alamat_customer)) {
        $errors[] = 'Customer address is required';
    }
    
    if (empty($no_wa_cutomer)) {
        $errors[] = 'WhatsApp number is required';
    } elseif (!preg_match('/^[0-9]+$/', $no_wa_cutomer)) {
        $errors[] = 'WhatsApp number must contain only digits';
    }
    
    if (empty($email_customer)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email_customer, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // If no errors, update data
    if (empty($errors)) {
        $updateQuery = "UPDATE tb_customer SET 
                        nama_customer = '$nama_customer', 
                        alamat_customer = '$alamat_customer', 
                        no_wa_cutomer = '$no_wa_cutomer', 
                        email_customer = '$email_customer' 
                        WHERE id_customer = '$id'";
        
        if (mysqli_query($conn, $updateQuery)) {
            $message = 'Customer updated successfully';
            $messageType = 'success';
            
            // Update customer data
            $customer['nama_customer'] = $nama_customer;
            $customer['alamat_customer'] = $alamat_customer;
            $customer['no_wa_cutomer'] = $no_wa_cutomer;
            $customer['email_customer'] = $email_customer;
        } else {
            $message = 'Error updating customer: ' . mysqli_error($conn);
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
    <h1>Edit Customer</h1>
    <a href="index.php" class="btn btn-secondary">Back to Customers</a>
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
            <div class="mb-3">
                <label for="id_customer" class="form-label">Customer ID</label>
                <input type="text" class="form-control" id="id_customer" value="<?php echo htmlspecialchars($customer['id_customer']); ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label for="nama_customer" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="nama_customer" name="nama_customer" value="<?php echo htmlspecialchars($customer['nama_customer']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="alamat_customer" class="form-label">Address</label>
                <textarea class="form-control" id="alamat_customer" name="alamat_customer" rows="3" required><?php echo htmlspecialchars($customer['alamat_customer']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="no_wa_cutomer" class="form-label">WhatsApp Number</label>
                <div class="input-group">
                    <span class="input-group-text">+62</span>
                    <input type="text" class="form-control" id="no_wa_cutomer" name="no_wa_cutomer" value="<?php echo htmlspecialchars($customer['no_wa_cutomer']); ?>" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email_customer" class="form-label">Email</label>
                <input type="email" class="form-control" id="email_customer" name="email_customer" value="<?php echo htmlspecialchars($customer['email_customer']); ?>" required>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="index.php" class="btn btn-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Customer</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>