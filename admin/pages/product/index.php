<?php

require_once '../../../config/database.php';

// Process any search queries
$search = isset($_GET['search']) ? $_GET['search'] : '';
$searchCondition = '';
if (!empty($search)) {
    $searchCondition = " WHERE id_produk LIKE '%$search%' OR nama_produk LIKE '%$search%' OR jenis_produk LIKE '%$search%'";
}

// Get all products with optional search filter
$query = "SELECT * FROM tb_produk" . $searchCondition . " ORDER BY id_produk";
$result = mysqli_query($conn, $query);

// Include header
include '../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Manage Products</h1>
    <a href="add.php" class="btn btn-primary">Add New Product</a>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by ID, name or type..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Type</th>
                        <th>Expiry Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Format date
                            $expDate = date('d-m-Y', strtotime($row['exp_produk']));
                            
                            // Check if product is close to expiry (within 30 days)
                            $expiryClass = '';
                            $today = new DateTime();
                            $expiry = new DateTime($row['exp_produk']);
                            $diff = $today->diff($expiry);
                            
                            if ($expiry < $today) {
                                $expiryClass = 'table-danger';
                            } elseif ($diff->days <= 30) {
                                $expiryClass = 'table-warning';
                            }
                            
                            echo "<tr class='$expiryClass'>";
                            echo "<td>{$row['id_produk']}</td>";
                            echo "<td>{$row['nama_produk']}</td>";
                            echo "<td>Rp " . number_format($row['harga_produk'], 0, ',', '.') . "</td>";
                            echo "<td>{$row['stok_produk']}</td>";
                            echo "<td>{$row['jenis_produk']}</td>";
                            echo "<td>{$expDate}</td>";
                            echo "<td>
                                    <a href='edit.php?id={$row['id_produk']}' class='btn btn-sm btn-primary btn-action' data-bs-toggle='tooltip' title='Edit'><i class='bi bi-pencil'></i> Edit</a>
                                    <a href='delete.php?id={$row['id_produk']}' class='btn btn-sm btn-danger btn-action delete-btn' data-bs-toggle='tooltip' title='Delete'><i class='bi bi-trash'></i> Delete</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No products found</td></tr>";
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