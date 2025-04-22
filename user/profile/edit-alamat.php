<?php
session_start();
include '../../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['id_login'])) {
    header("Location: ../login.php");
    exit();
}

$id_login = $_SESSION['id_login'];

// Fetch current user data
$query = mysqli_query($conn, "
    SELECT 
        l.username, 
        c.id_customer,
        c.nama_customer, 
        c.email_customer, 
        c.no_wa_cutomer, 
        c.alamat_customer,
        c.foto_profil
    FROM tb_login l 
    JOIN tb_customer c ON l.id = c.id_login 
    WHERE l.id = '$id_login'
");

if ($data = mysqli_fetch_assoc($query)) {
    $id_customer = $data['id_customer'];
    $username = $data['username'];
    $nama = $data['nama_customer'];
    $alamat = $data['alamat_customer'];
    $foto = $data['foto_profil'];
} else {
    header("Location: ../index.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    
    // Update address in database
    $update_query = "UPDATE tb_customer SET alamat_customer = '$new_alamat' WHERE id_customer = '$id_customer'";
    
    if (mysqli_query($conn, $update_query)) {
        // Redirect with success message
        header("Location: profile.php?message=Alamat berhasil diperbarui&type=success");
        exit();
    } else {
        $error = "Gagal memperbarui alamat: " . mysqli_error($conn);
    }
}

include '../includes_P/navbar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Alamat</title>
    
    <!-- Animate.css CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../asset/style.css">
    
    <style>
        /* Responsive CSS */
        .profil-page {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .sidebar {
            flex: 1;
            min-width: 250px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
            height: fit-content;
        }
        
        .content {
            flex: 3;
            min-width: 300px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        
        .profil-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .profil-header img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 0;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .menu a:hover, .menu a.active {
            color: #007bff;
        }
        
        .menu-title {
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            color: #555;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        textarea {
            height: 150px;
            resize: vertical;
        }
        
        .btn-simpan {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: 15px;
        }
        
        .btn-simpan:hover {
            background-color: #0056b3;
        }
        
        /* Mobile menu toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #333;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profil-page {
                flex-direction: column;
            }
            
            .sidebar, .content {
                width: 100%;
            }
            
            .menu-toggle {
                display: block;
                margin-bottom: 10px;
            }
            
            .menu {
                display: none;
            }
            
            .menu.show {
                display: block;
            }
        }
        
        @media (max-width: 480px) {
            .profil-header {
                flex-direction: column;
                text-align: center;
            }
            
            h2, .subtext {
                text-align: center;
            }
        }
    </style>
</head>
<body>

<div class="profil-page">
    <div class="sidebar">
        <button class="menu-toggle" id="menuToggle">
            <i class="bi bi-list"></i> Menu
        </button>
        
        <div class="profil-header">
            <?php if (!empty($foto)) : ?>
            <img src="../../uploads/profile/<?php echo htmlspecialchars($foto); ?>" alt="Foto Profil">
            <?php else : ?>
            <img src="../../uploads/profile/default-avatar.png" alt="Default Profile">
            <?php endif; ?>
            <div>
                <p class="username"><?php echo $username; ?></p>
                <a href="edit-profile.php" class="edit-profile">Ubah Profil</a>
            </div>
        </div>
        
        <div class="menu" id="sidebarMenu">
            <p class="menu-title">Akun Saya</p>
            <a href="profile.php"><i data-lucide="user"></i> Profil</a>
            <a href="edit-profile.php"><i data-lucide="edit"></i> Edit Profil</a>
            <a href="edit-alamat.php" class="active"><i data-lucide="map-pin"></i> Alamat</a>
            <a href="ubah-password.php"><i data-lucide="key-round"></i> Ubah Password</a>
            <a href="../setting/pengaturan.php"><i data-lucide="credit-card"></i> Bank & Kartu</a>
            <a href="../setting/pengaturan.php"><i data-lucide="bell"></i> Pengaturan Notifikasi</a>
            <a href="../setting/pengaturan.php"><i data-lucide="shield"></i> Pengaturan Privasi</a>

            <p class="menu-title">Lainnya</p>
            <a href="../order/pesanan.php"><i data-lucide="shopping-bag"></i> Pesanan Saya</a>
            <a href="../setting/pengaturan.php"><i data-lucide="message-circle"></i> Notifikasi</a>
            <a href="../setting/pengaturan.php"><i data-lucide="ticket"></i> Voucher Saya</a>
            <a href="../setting/pengaturan.php"><i data-lucide="coins"></i> Koin Saya</a>
        </div>
    </div>

    <div class="content">
        <h2>Edit Alamat</h2>
        <p class="subtext">Perbarui alamat pengiriman Anda.</p>

        <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['message'])): ?>
        <div class="alert alert-<?php echo $_GET['type'] ?? 'info'; ?>">
            <?php echo htmlspecialchars($_GET['message']); ?>
        </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label for="alamat">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" class="form-control" required><?php echo $alamat; ?></textarea>
                <small class="text-muted">Masukkan alamat lengkap termasuk kode pos.</small>
            </div>

            <button type="submit" class="btn-simpan">Simpan Alamat</button>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();
    
    // Toggle sidebar menu on mobile
    document.getElementById('menuToggle').addEventListener('click', function() {
        document.getElementById('sidebarMenu').classList.toggle('show');
    });
    
    // Check screen size on resize and adjust menu visibility
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            document.getElementById('sidebarMenu').classList.remove('show');
        }
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>