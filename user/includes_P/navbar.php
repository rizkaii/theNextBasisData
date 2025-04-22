<?php

$nama_customer = 'Akun'; // default

if (isset($_SESSION['id_login'])) {
    $id_login = mysqli_real_escape_string($conn, $_SESSION['id_login']);
    $query = "SELECT nama_customer FROM tb_customer WHERE id_login = '$id_login'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_assoc($result);
        $nama_customer = $data['nama_customer'];
    }
}

// Get all categories for dropdown
$kategori_query = "SELECT DISTINCT jenis_produk FROM tb_produk ORDER BY jenis_produk ASC";
$kategori_result = mysqli_query($conn, $kategori_query);

// Get cart count
// Get cart count
$cart_count = 0;
if (isset($_SESSION['id_login'])) {
    $id_login = mysqli_real_escape_string($conn, $_SESSION['id_login']);
    
    // Dapatkan id_customer dari id_login
    $customer_query = "SELECT id_customer FROM tb_customer WHERE id_login = '$id_login'";
    $customer_result = mysqli_query($conn, $customer_query);
    
    if ($customer_result && mysqli_num_rows($customer_result) > 0) {
        $customer_data = mysqli_fetch_assoc($customer_result);
        $id_customer = $customer_data['id_customer'];
        
        // Hitung produk dalam keranjang yang terkait dengan customer
        $cart_query = "SELECT COUNT(*) as count FROM tb_keranjang k
                       JOIN tb_produk p ON k.id_produk = p.id_produk
                       WHERE k.id_customer = '$id_customer' 
                       AND k.status = 'dalam_keranjang'";
        $cart_result = mysqli_query($conn, $cart_query);
        
        if ($cart_result && mysqli_num_rows($cart_result) > 0) {
            $cart_data = mysqli_fetch_assoc($cart_result);
            $cart_count = $cart_data['count'];
        }
    }
}

?>

<!-- Navbar -->
<nav id="mainNavbar" class="navbar navbar-expand-lg custom-navbar shadow-sm sticky-top py-3 transition">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../index.php">
      <img src="../img/logo1.png" alt="logo" class="logo-img"> InfinityStore
    </a>

    <!-- Hamburger button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" 
      aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar links and search -->
    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
            <a class="nav-link active" href="../index.php">
            <i class="bi bi-house-door"></i> Beranda
        </a>

        </li>
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="kategoriDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-tags"></i> Kategori
        </a>
        <ul class="dropdown-menu animate__animated" aria-labelledby="kategoriDropdown">
            <li><hr class="dropdown-divider"></li>
            <?php while ($row = mysqli_fetch_assoc($kategori_result)): ?>
                <li><a class="dropdown-item" href="../kategori/kategori.php?kategori=<?= urlencode($row['jenis_produk']) ?>"><?= htmlspecialchars($row['jenis_produk']) ?></a></li>
            <?php endwhile; ?>
        </ul>
        </li>


        <li class="nav-item">
          <a class="nav-link" href="../cart/keranjang.php">
            <i class="bi bi-cart"></i> Keranjang
            <?php if ($cart_count > 0): ?>
                <span class="badge rounded-pill bg-danger"><?= $cart_count ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item dropdown">
  <a class="nav-link dropdown-toggle" href="#" id="akunDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
  <i class="bi bi-person-circle"></i> <?= htmlspecialchars($nama_customer) ?>
  </a>
  <ul class="dropdown-menu" aria-labelledby="akunDropdown">
    <li><a class="dropdown-item" href="../profile/profile.php">Profil Saya</a></li>
    <li><a class="dropdown-item" href="../order/pesanan.php">Pesanan</a></li>
    <li><a class="dropdown-item" href="../setting/pengaturan.php">Pengaturan</a></li>
    <li><hr class="dropdown-divider"></li>
    <li><a class="dropdown-item" href="../../home/logout.php">Keluar</a></li>
  </ul>
</li>

      </ul>

      <!-- Search bar (tanpa container lagi di dalam) -->
      <form class="d-flex search-bar" role="search" method="GET" action="">
      <input 
        class="form-control me-2 search-responsive" 
        type="search" 
        name="search" 
        placeholder="Cari produk..." 
        aria-label="Search"
        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
      >
        <button class="btn btn-outline-danger" type="submit">
          <i class="bi bi-search text-red"></i>
        </button>
      </form>


    </div>
  </div>
</nav>