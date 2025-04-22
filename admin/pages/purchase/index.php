<?php
// Include database connection
require_once '../../../config/database.php';

// Process any search queries
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($search)) {
    $searchCondition = " WHERE pembelian.id_pembelian LIKE '%$search%' OR pembelian.id_customer LIKE '%$search%' OR customer.nama_customer LIKE '%$search%' OR produk.nama_produk LIKE '%$search%'";
}

// Get all purchases with optional search filter
$query = "SELECT pembelian.*, customer.nama_customer, produk.nama_produk 
          FROM tb_pembelian pembelian
          LEFT JOIN tb_customer customer ON pembelian.id_customer = customer.id_customer
          LEFT JOIN tb_produk produk ON pembelian.id_produk = produk.id_produk" . $searchCondition . "
          ORDER BY pembelian.tanggal_pembelian DESC, pembelian.id_pembelian";
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
    <h1>Manage Purchases</h1>
    <a href="add.php" class="btn btn-primary">Add New Purchase</a>
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
                <input type="text" class="form-control" name="search" placeholder="Search by ID, customer name or product..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Payment Method</th>
                        <th>Total Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Format date
                            $purchaseDate = date('d-m-Y', strtotime($row['tanggal_pembelian']));
                            
                            echo "<tr>";
                            echo "<td>{$row['id_pembelian']}</td>";
                            echo "<td>{$purchaseDate}</td>";
                            echo "<td>" . (isset($row['nama_customer']) ? htmlspecialchars($row['nama_customer']) : "Customer ID: {$row['id_customer']}") . "</td>";
                            echo "<td>" . (isset($row['nama_produk']) ? htmlspecialchars($row['nama_produk']) : "Product ID: {$row['id_produk']}") . "</td>";
                            echo "<td>{$row['jumlah_produk']}</td>";
                            echo "<td>{$row['metode_pembayaran']}</td>";
                            echo "<td>Rp " . number_format($row['jumlah_pembayaran'], 0, ',', '.') . "</td>";
                            echo "<td>
                                    <a href='edit.php?id={$row['id_pembelian']}' class='btn btn-sm btn-primary btn-action' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i> Edit</a>
                                    <a href='delete.php?id={$row['id_pembelian']}' class='btn btn-sm btn-danger btn-action delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i> Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No purchases found</td></tr>";
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