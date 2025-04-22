<?php
session_start();
include '../config/database.php';

// Ambil input dari query string
$search = trim($_GET['search'] ?? ''); // hapus spasi ekstra
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" type="image/png" href="img/logo2.png">
  <title>InfinityStore</title>

  <!-- Animate.css CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-yKmzBtGCzNOBt9R58aKhZ0Rrm3rBhTogPNUgRyF1D9AxVAkUOLC3OFi0VgfqhlZzSvUz9v6cHJlD0kQ0VtImVw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <link rel="stylesheet" href="asset/style.css">
  
  <style>
    /* Custom responsive styles */
    
    /* Banner carousel - full width on mobile, 90% on larger screens */
    #bannerCarousel {
      width: 100%;
      margin: 10px auto;
    }
    
    @media (min-width: 768px) {
      #bannerCarousel {
        width: 90%;
      }
    }
    
    /* Produk Unggulan items */
    .produk-unggulan-item {
      flex-shrink: 0;
      transition: all 0.2s ease;
    }
    
    .produk-unggulan-scroll {
      display: flex;
      flex-wrap: nowrap;
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: thin;
      gap: 0.8rem;
      padding: 0.5rem 0;
      width: 100%;
    }
    
    .produk-unggulan-scroll::-webkit-scrollbar {
      height: 10px;
    }
    
    .produk-unggulan-scroll::-webkit-scrollbar-thumb {
      background-color: rgba(0,0,0,0.2);
      border-radius: 10px;
    }
    
    /* Produk Unggulan - Alat Kecantikan */
    .beauty-product-item {
      display: flex;
      flex-direction: column;
      margin-bottom: 1rem;
      width: 100%;
    }
    
    @media (min-width: 576px) {
      .beauty-product-item {
        flex-direction: row;
        align-items: center;
      }
    }
    
    .beauty-product-image {
      width: 100%;
      max-width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 8px;
      margin-bottom: 0.5rem;
    }
    
    @media (min-width: 576px) {
      .beauty-product-image {
        margin-bottom: 0;
        margin-right: 1rem;
      }
    }
    
    .beauty-product-info {
      flex: 1;
    }
    
    /* Card products */
    .product-card {
      height: 100%;
      transition: transform 0.2s;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    
    .product-image {
      height: 180px;
      object-fit: cover;
    }
    
    @media (max-width: 576px) {
      .product-image {
        height: 150px;
      }
    }
    
    /* Section spacing */
    .section-title {
      position: relative;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 50px;
      height: 3px;
      background-color: #0d6efd;
    }
    
    /* Loading indicator */
    #loading-indicator {
      display: none;
      text-align: center;
      padding: 10px;
    }
    
    /* Load more button */
    .load-more-container {
      text-align: center;
      margin: 30px 0;
    }
    
    .btn-load-more {
      padding: 10px 25px;
      background-color: #fff;
      border: 1px solid #0d6efd;
      color: #0d6efd;
      border-radius: 50px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    
    .btn-load-more:hover {
      background-color: #0d6efd;
      color: #fff;
    }
    
    .btn-load-more .spinner-border {
      width: 1rem;
      height: 1rem;
      margin-right: 0.5rem;
      display: none;
    }
    
    /* Fade in animation for products */
    .fade-in {
      animation: fadeIn 0.5s ease-in forwards;
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>

<!-- Navbar -->
<?php include 'includes/navbar.php'; ?>

<!-- Banner Carousel - Responsive -->
<div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2500">
  <div class="carousel-inner rounded shadow">
    <!-- Banner 1 -->
    <div class="carousel-item active">
      <img src="img/banner/2.jpg" class="d-block w-100" alt="Promo Diskon Spesial">
    </div>
    <!-- Banner 2 -->
    <div class="carousel-item">
      <img src="img/banner/4.jpg" class="d-block w-100" alt="Belanja Online Hemat">
    </div>
    <!-- Banner 3 -->
    <div class="carousel-item">
      <img src="img/banner/8.jpg" class="d-block w-100" alt="Diskon Besar-Besaran">
    </div>
    <!-- Banner 4 -->
    <div class="carousel-item">
      <img src="img/banner/7.jpg" class="d-block w-100" alt="Penawaran Terbaik Minggu Ini">
    </div>
  </div>

  <!-- Navigasi Kiri Kanan -->
  <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Sebelumnya</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Berikutnya</span>
  </button>

  <!-- Indicator -->
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
  </div>
</div>

<!-- Section Produk Unggulan - Improved responsive scrolling -->
<section class="py-4">
  <div class="container">
    <h5 class="mb-3 fw-bold section-title">
      <i class="bi bi-award me-2" style="color:blue;"></i>Produk Unggulan
    </h5>

    <div class="produk-unggulan-scroll pb-2">
  <?php
  // Ambil 9 produk yang paling sering dibeli
  $queryUnggulan = "
    SELECT p.*, COUNT(b.id_produk) as total_terjual
    FROM tb_pembelian b
    JOIN tb_produk p ON b.id_produk = p.id_produk
    GROUP BY b.id_produk
    ORDER BY total_terjual DESC
    LIMIT 9
  ";
  $resultUnggulan = mysqli_query($conn, $queryUnggulan);

  while ($produk = mysqli_fetch_assoc($resultUnggulan)) :
    $gambarArray = json_decode($produk['gambar'], true);
    $gambarUtama = isset($gambarArray[0]) ? $gambarArray[0] : 'default.jpg';
  ?>
    <div class="produk-unggulan-item text-center">
      <a href="produk/detail_produk.php?id=<?= urlencode($produk['id_produk']) ?>" class="text-decoration-none">
        <div class="bg-white rounded shadow-sm p-2" style="width: 110px;">
          <img src="../uploads/<?= htmlspecialchars($gambarUtama) ?>" 
              alt="<?= htmlspecialchars($produk['nama_produk']) ?>" 
              class="img-fluid mb-2"
              style="width: 90px; height: 90px; object-fit: cover; border-radius: 8px;">
          <p class="small text-dark mb-0 text-truncate" style="max-width: 90px;">
            <?= htmlspecialchars($produk['nama_produk']) ?>
          </p>
        </div>
      </a>
    </div>
  <?php endwhile; ?>
</div>

  </div>
</section>

<!-- Section Produk Unggulan Alat Kecantikan - Responsive grid -->
<section class="py-4">
  <div class="container">
    <h5 class="mb-3 fw-bold section-title">
      <i class="bi bi-brush-fill me-2" style="color:#e83e8c;"></i>Produk Unggulan - Alat Kecantikan
    </h5>

    <div class="row gy-3">
      <?php
      // Ambil 3 produk alat kecantikan
      $queryUnggulan = "SELECT * FROM tb_produk WHERE stok_produk > 0 AND jenis_produk = 'kecantikan' ORDER BY RAND() LIMIT 3";
      $resultUnggulan = mysqli_query($conn, $queryUnggulan);

      $index = 0;
      while ($produk = mysqli_fetch_assoc($resultUnggulan)) :
        $gambarArray = json_decode($produk['gambar'], true);
        $gambarUtama = isset($gambarArray[0]) ? $gambarArray[0] : 'default.jpg';
        $deskripsi = htmlspecialchars($produk['deskripsi']);
        $deskripsiPendek = mb_strimwidth($deskripsi, 0, 40, "...");
        
        // Hanya tampilkan produk pertama di mobile
        $extraClass = ($index > 0) ? 'd-none d-md-block' : '';
      ?>
        <div class="col-12 col-md-4 <?= $extraClass ?>">
          <div class="beauty-product-item bg-white rounded shadow-sm p-3">
            <img src="../uploads/<?= htmlspecialchars($gambarUtama) ?>" 
                alt="<?= htmlspecialchars($produk['nama_produk']) ?>" 
                class="beauty-product-image">
            
            <div class="beauty-product-info">
              <h6 class="mb-1">
                <?= mb_strimwidth(htmlspecialchars($produk['nama_produk']), 0, 35, "...") ?>
              </h6>
              <p class="small text-secondary mb-2"><?= $deskripsiPendek ?></p>
              <a href="produk/detail_produk.php?id=<?= urlencode($produk['id_produk']) ?>" 
                class="btn btn-sm btn-outline-primary">
                Lihat Detail
              </a>
            </div>
          </div>
        </div>
      <?php 
        $index++;
      endwhile; 
      ?>
    </div>
  </div>
</section>


<!-- Section Produk - With Load More Button -->
<section class="py-5 bg-light">
  <div class="container">
    <h5 class="mb-4 fw-bold section-title">
      <i class="bi bi-star-fill me-2 text-danger"></i> Rekomendasi Produk
    </h5>

    <!-- Initial products - Just load a small batch first -->
    <div class="row g-3" id="product-container">
      <?php
      // Query dasar untuk awal
      $initialLimit = 6; // Show 6 products initially
      $query = "SELECT * FROM tb_produk WHERE stok_produk > 0";

      // Jika ada keyword pencarian, tambahkan ke query
      if (!empty($search)) {
          $query .= " AND nama_produk LIKE ?";
          $stmt = $conn->prepare($query . " LIMIT ?");
          $search_param = "%" . $search . "%";
          $stmt->bind_param("si", $search_param, $initialLimit);
      } else {
          // Random ordering if not searching
          $query .= " ORDER BY RAND() LIMIT ?";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $initialLimit);
      }

      $stmt->execute();
      $initialResult = $stmt->get_result();
      
      if ($initialResult->num_rows > 0) :
        while ($produk = $initialResult->fetch_assoc()) :
          $gambarArray = json_decode($produk['gambar'], true);
          $gambarUtama = isset($gambarArray[0]) ? $gambarArray[0] : 'default.jpg';
      ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2">
          <div class="card product-card shadow-sm border-0 h-100">
            <img src="../uploads/<?= htmlspecialchars($gambarUtama) ?>" 
                class="card-img-top product-image" 
                alt="<?= htmlspecialchars($produk['nama_produk']) ?>">

            <div class="card-body d-flex flex-column">
              <h6 class="card-title mb-1 text-truncate"><?= htmlspecialchars($produk['nama_produk']) ?></h6>
              <p class="card-text text-danger fw-bold mb-2">
                Rp <?= number_format($produk['harga_produk'], 0, ',', '.') ?>
              </p>
              <a href="produk/detail_produk.php?id=<?= urlencode($produk['id_produk']) ?>" 
                class="btn btn-sm btn-outline-primary w-100 mt-auto">
                <i class="bi bi-eye"></i> Lihat Produk
              </a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      <?php else : ?>
        <div class="col-12 text-center py-5">
          <div class="py-4">
            <i class="bi bi-search" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">Tidak ada produk yang ditemukan.</p>
            <a href="?" class="btn btn-outline-secondary mt-2">Lihat Semua Produk</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
    
    <!-- Loading indicator -->
    <div id="loading-indicator" class="my-3">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>
    
    <!-- Load More Button -->
    <?php if ($initialResult->num_rows > 0) : ?>
    <div class="load-more-container">
      <button id="load-more-btn" class="btn-load-more">
        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Lihat Produk Lainnya
      </button>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="asset/script.js"></script>

<!-- Load More Products Script - FIXED VERSION -->
<script>
// Variables to control pagination
let page = 2; // Start from page 2 since page 1 is loaded initially
const limit = 6; // Number of products per load
let loading = false;
let allLoaded = false;
const search = "<?= htmlspecialchars($search) ?>"; // Get search query from PHP

// DOM elements
const loadMoreBtn = document.getElementById('load-more-btn');
const loadingIndicator = document.getElementById('loading-indicator');
const productContainer = document.getElementById('product-container');
const btnSpinner = loadMoreBtn ? loadMoreBtn.querySelector('.spinner-border') : null;

// Function to load more products
function loadMoreProducts() {
  if (loading || allLoaded) return;
  
  loading = true;
  
  // Show loading spinner inside button
  if (btnSpinner) btnSpinner.style.display = 'inline-block';
  
  // Create AJAX request
  const xhr = new XMLHttpRequest();
  xhr.open('GET', `asset/load_product.php?page=${page}&limit=${limit}&search=${encodeURIComponent(search)}`, true);
  
  // Set timeout
  xhr.timeout = 10000; // 10 seconds
  
  xhr.onload = function() {
    try {
      if (this.status === 200) {
        const response = JSON.parse(this.responseText);
        
        // Check if response has products property
        if (!response.hasOwnProperty('products')) {
          console.error('Invalid response format:', response);
          throw new Error('Invalid response format');
        }
        
        const products = response.products;
        
        // Check if any products were returned
        if (products.length === 0) {
          // No more products to load
          allLoaded = true;
          
          // Hide the button or show a "no more products" message
          if (loadMoreBtn) {
            loadMoreBtn.innerHTML = 'Tidak ada produk lagi';
            loadMoreBtn.disabled = true;
            loadMoreBtn.classList.add('disabled');
          }
        } else {
          // Add products to container
          products.forEach(product => {
            // Create new product card
            const productEl = document.createElement('div');
            productEl.className = 'col-6 col-sm-6 col-md-4 col-lg-3 col-xl-2 fade-in';
            
            // Parse the JSON string for the image
            let gambarArray = [];
            try {
              gambarArray = JSON.parse(product.gambar);
            } catch (e) {
              console.error('Error parsing image JSON', e);
            }
            
            const gambarUtama = gambarArray[0] || 'default.jpg';
            
            // Create product card HTML
            productEl.innerHTML = `
              <div class="card product-card shadow-sm border-0 h-100">
                <img src="../uploads/${gambarUtama}" 
                     class="card-img-top product-image" 
                     alt="${product.nama_produk}">
                <div class="card-body d-flex flex-column">
                  <h6 class="card-title mb-1 text-truncate">${product.nama_produk}</h6>
                  <p class="card-text text-danger fw-bold mb-2">
                    Rp ${new Intl.NumberFormat('id-ID').format(product.harga_produk)}
                  </p>
                  <a href="produk/detail_produk.php?id=${product.id_produk}" 
                     class="btn btn-sm btn-outline-primary w-100 mt-auto">
                    <i class="bi bi-eye"></i> Lihat Produk
                  </a>
                </div>
              </div>
            `;
            
            // Add to container
            productContainer.appendChild(productEl);
          });
          
          // Increment page for next load
          page++;
        }
      } else {
        console.error('Error status:', this.status);
        throw new Error(`Server returned status ${this.status}`);
      }
    } catch (error) {
      console.error('Error processing response:', error);
      alert('Terjadi kesalahan saat memuat produk. Silakan coba lagi.');
    } finally {
      // Always hide loading spinner and reset loading state
      if (btnSpinner) btnSpinner.style.display = 'none';
      loading = false;
    }
  };
  
  // Error handling for request failures
  xhr.onerror = function() {
    console.error('Request failed');
    alert('Koneksi gagal. Silakan periksa koneksi internet Anda dan coba lagi.');
    if (btnSpinner) btnSpinner.style.display = 'none';
    loading = false;
  };
  
  // Timeout handling
  xhr.ontimeout = function() {
    console.error('Request timed out');
    alert('Permintaan timeout. Silakan coba lagi.');
    if (btnSpinner) btnSpinner.style.display = 'none';
    loading = false;
  };
  
  xhr.send();
}

// Add click event listener to Load More button
if (loadMoreBtn) {
  loadMoreBtn.addEventListener('click', loadMoreProducts);
}
</script>

</body>
</html>