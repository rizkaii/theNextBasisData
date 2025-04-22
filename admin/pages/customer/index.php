<?php
// Include database connection
require_once '../../../config/database.php';

// Process any search queries
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($search)) {
    $searchCondition = " WHERE id_customer LIKE '%$search%' OR nama_customer LIKE '%$search%' OR email_customer LIKE '%$search%'";
}

// Get all customers with optional search filter
$query = "SELECT * FROM tb_customer" . $searchCondition . " ORDER BY id_customer";
$result = mysqli_query($conn, $query);

// Process any messages
$message = '';
$messageType = '';
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message'];
    $messageType = $_GET['type'];
}

// Include header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Manage Customers</h1>
    <a href="add.php" class="btn btn-primary">Add New Customer</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by ID, name or email..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
                <?php if(!empty($search)): ?>
                <a href="index.php" class="btn btn-outline-danger">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Address</th>
                        <th>WhatsApp</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>{$row['id_customer']}</td>";
                            echo "<td>{$row['nama_customer']}</td>";
                            echo "<td>{$row['alamat_customer']}</td>";
                            echo "<td>{$row['no_wa_cutomer']}</td>";
                            echo "<td>{$row['email_customer']}</td>";
                            echo "<td>
                                    <a href='edit.php?id={$row['id_customer']}' class='btn btn-sm btn-primary btn-action' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i> Edit</a>
                                    <a href='delete.php?id={$row['id_customer']}' class='btn btn-sm btn-danger btn-action delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i> Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No customers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>