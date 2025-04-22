<?php
// Include database connection
require_once '../../../config/database.php';

// Process form submission
$message = '';
$messageType = '';
$formData = [
    'nama_customer' => '',
    'alamat_customer' => '',
    'no_wa_cutomer' => '',
    'email_customer' => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $formData = [
        'nama_customer' => trim($_POST['nama_customer']),
        'alamat_customer' => trim($_POST['alamat_customer']),
        'no_wa_cutomer' => trim($_POST['no_wa_cutomer']),
        'email_customer' => trim($_POST['email_customer'])
    ];
    
    // Validate form data
    $errors = [];
    
    if (empty($formData['nama_customer'])) {
        $errors[] = 'Customer name is required';
    }
    
    if (empty($formData['alamat_customer'])) {
        $errors[] = 'Customer address is required';
    }
    
    if (empty($formData['no_wa_cutomer'])) {
        $errors[] = 'WhatsApp number is required';
    } elseif (!preg_match('/^[0-9]+$/', $formData['no_wa_cutomer'])) {
        $errors[] = 'WhatsApp number must contain only digits';
    }
    
    if (empty($formData['email_customer'])) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($formData['email_customer'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    // If no errors, insert data
    if (empty($errors)) {
        // Auto-increment ID
        $getMaxIdQuery = "SELECT MAX(id_customer) as max_id FROM tb_customer";
        $maxIdResult = mysqli_query($conn, $getMaxIdQuery);
        $maxId = mysqli_fetch_assoc($maxIdResult)['max_id'];
        $newId = ($maxId > 0) ? $maxId + 1 : 1;
        
        $query = "INSERT INTO tb_customer (id_customer, nama_customer, alamat_customer, no_wa_cutomer, email_customer) 
                  VALUES ('$newId', '{$formData['nama_customer']}', '{$formData['alamat_customer']}', 
                          '{$formData['no_wa_cutomer']}', '{$formData['email_customer']}')";
        
        if (mysqli_query($conn, $query)) {
            $message = 'Customer added successfully';
            $messageType = 'success';
            
            // Reset form data after successful submission
            $formData = [
                'nama_customer' => '',
                'alamat_customer' => '',
                'no_wa_cutomer' => '',
                'email_customer' => ''
            ];
        } else {
            $message = 'Error adding customer: ' . mysqli_error($conn);
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
    <h1>Add New Customer</h1>
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
                <label for="nama_customer" class="form-label">Customer Name</label>
                <input type="text" class="form-control" id="nama_customer" name="nama_customer" value="<?php echo htmlspecialchars($formData['nama_customer']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="alamat_customer" class="form-label">Address</label>
                <textarea class="form-control" id="alamat_customer" name="alamat_customer" rows="3" required><?php echo htmlspecialchars($formData['alamat_customer']); ?></textarea>
            </div>
            
            <div class="mb-3">
                <label for="no_wa_cutomer" class="form-label">WhatsApp Number</label>
                <div class="input-group">
                    <span class="input-group-text">+62</span>
                    <input type="text" class="form-control" id="no_wa_cutomer" name="no_wa_cutomer" value="<?php echo htmlspecialchars($formData['no_wa_cutomer']); ?>" placeholder="Example: 8123456789" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="email_customer" class="form-label">Email</label>
                <input type="email" class="form-control" id="email_customer" name="email_customer" value="<?php echo htmlspecialchars($formData['email_customer']); ?>" required>
            </div>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>