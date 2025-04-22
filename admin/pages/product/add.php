<?php

require_once '../../../config/database.php';

$message = '';
$messageType = '';
$errors = [];

$formData = [
    'nama_produk'   => '',
    'harga_produk'  => '',
    'stok_produk'   => '',
    'jenis_produk'  => '',
    'exp_produk'    => ''
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $formData = [
        'nama_produk'      => trim($_POST['nama_produk']),
        'harga_produk'     => trim($_POST['harga_produk']),
        'stok_produk'      => trim($_POST['stok_produk']),
        'jenis_produk'     => trim($_POST['jenis_produk']),
        'exp_produk'       => trim($_POST['exp_produk']),
        'deskripsi' => trim($_POST['deskripsi']) // ← Tambahkan baris ini di sini
    ];

    $uploadedFiles = [];

    // Cek apakah gambar dikirim dan tidak kosong
    if (!empty($_FILES['gambar']['name'][0])) {
        $allowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        $fileCount = count($_FILES['gambar']['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $imageName = $_FILES['gambar']['name'][$i];
            $imageTmp  = $_FILES['gambar']['tmp_name'][$i];
            $imageExt  = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if (in_array($imageExt, $allowedExt)) {
                $uniqueName = uniqid('produk_') . '.' . $imageExt;
                $uploadPath = '../../../uploads/' . $uniqueName;

                if (move_uploaded_file($imageTmp, $uploadPath)) {
                    $uploadedFiles[] = $uniqueName;
                } else {
                    $errors[] = "Gagal mengupload gambar: $imageName";
                }
            } else {
                $errors[] = "Format tidak valid untuk gambar: $imageName";
            }
        }
    } else {
        $errors[] = "Minimal satu gambar harus diunggah.";
    }

    if (empty($errors)) {
        $gambarJSON = json_encode($uploadedFiles);
        $status_produk = 'belum_dibeli';

        $query = "INSERT INTO tb_produk 
            (nama_produk, harga_produk, stok_produk, jenis_produk, exp_produk, deskripsi, gambar, status_produk) 
        VALUES (
            '{$formData['nama_produk']}',
            '{$formData['harga_produk']}',
            '{$formData['stok_produk']}',
            '{$formData['jenis_produk']}',
            '{$formData['exp_produk']}',
            '{$formData['deskripsi']}',
            '$gambarJSON',
            '$status_produk'
        )";


        if (mysqli_query($conn, $query)) {
            $message = 'Produk berhasil ditambahkan!';
            $messageType = 'success';

            // Reset data
            $formData = [
                'nama_produk'   => '',
                'harga_produk'  => '',
                'stok_produk'   => '',
                'jenis_produk'  => '',
                'exp_produk'    => '',
                'deskripsi' => ''
            ];
            
        } else {
            $message = 'Gagal menyimpan produk: ' . mysqli_error($conn);
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
    <h1>Add New Product</h1>
    <a href="index.php" class="btn btn-secondary">Back to Products</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo $message; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row mb-3">
                <!-- <div class="col-md-6">
                    <label for="id_produk" class="form-label">Product ID</label>
                    <input type="text" class="form-control" id="id_produk" name="id_produk" value="<?php echo htmlspecialchars($formData['id_produk']); ?>" required>
                </div> -->
                <div class="col-md-6">
                    <label for="nama_produk" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($formData['nama_produk']); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="harga_produk" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" class="form-control" id="harga_produk" name="harga_produk" value="<?php echo htmlspecialchars($formData['harga_produk']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="stok_produk" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="stok_produk" name="stok_produk" value="<?php echo htmlspecialchars($formData['stok_produk']); ?>" required>
                </div>
            </div>
            
            <div class="row mb-3">
            <div class="col-md-6">
                <label for="jenis_produk" class="form-label">Product Type</label>
                <select class="form-select" id="jenis_produk" name="jenis_produk" required>
                    <option value="elektronik" <?php echo $formData['jenis_produk'] == 'elektronik' ? 'selected' : ''; ?>>Elektronik</option>
                    <option value="fashion" <?php echo $formData['jenis_produk'] == 'fashion' ? 'selected' : ''; ?>>Fashion</option>
                    <option value="kecantikan" <?php echo $formData['jenis_produk'] == 'kecantikan' ? 'selected' : ''; ?>>Kecantikan & Perawatan</option>
                    <option value="olahraga" <?php echo $formData['jenis_produk'] == 'olahraga' ? 'selected' : ''; ?>>Olahraga & Kebugaran</option>
                    <option value="makanan" <?php echo $formData['jenis_produk'] == 'makanan' ? 'selected' : ''; ?>>Makanan & Minuman</option>
                    <option value="perabotan" <?php echo $formData['jenis_produk'] == 'perabotan' ? 'selected' : ''; ?>>Perabotan Rumah Tangga</option>
                    <option value="mainan" <?php echo $formData['jenis_produk'] == 'mainan' ? 'selected' : ''; ?>>Mainan & Hobi</option>
                    <option value="kesehatan" <?php echo $formData['jenis_produk'] == 'kesehatan' ? 'selected' : ''; ?>>Kesehatan & Kebugaran</option>
                    <option value="automotif" <?php echo $formData['jenis_produk'] == 'automotif' ? 'selected' : ''; ?>>Automotif</option>
                    <option value="gadget" <?php echo $formData['jenis_produk'] == 'gadget' ? 'selected' : ''; ?>>Gadget & Aksesori</option>
                    <option value="buku" <?php echo $formData['jenis_produk'] == 'buku' ? 'selected' : ''; ?>>Buku & Perlengkapan Pendidikan</option>
                    <option value="rumah_kebun" <?php echo $formData['jenis_produk'] == 'rumah_kebun' ? 'selected' : ''; ?>>Rumah & Kebun</option>
                    <option value="travel" <?php echo $formData['jenis_produk'] == 'travel' ? 'selected' : ''; ?>>Travel & Liburan</option>
                    <option value="kehidupan_sehari" <?php echo $formData['jenis_produk'] == 'kehidupan_sehari' ? 'selected' : ''; ?>>Kehidupan Sehari-hari</option>
                    <option value="seni_kerajinan" <?php echo $formData['jenis_produk'] == 'seni_kerajinan' ? 'selected' : ''; ?>>Karya Seni & Kerajinan</option>
                </select>
            </div>


                <div class="col-md-6">
                    <label for="exp_produk" class="form-label">Expiry Date</label>
                    <input type="date" class="form-control" id="exp_produk" name="exp_produk" value="<?php echo htmlspecialchars($formData['exp_produk']); ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="gambar" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="gambar" name="gambar[]" accept="image/*" multiple required>
                </div>
            </div>
            <div class="col-md-12 mt-3">
                <label for="deskripsi_produk" class="form-label">Product Description</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($formData['deskripsi'] ?? ''); ?></textarea>
            </div>


            
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<?php
// Include footer
include '../includes/footer.php';
?>
