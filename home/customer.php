<?php
session_start();
require_once '../config/database.php';

$message = '';
$messageType = '';

$id_login = $_SESSION['id_login'] ?? null;

if (!$id_login) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_customer = $_POST['nama_customer'];
    $alamat_customer = $_POST['alamat_customer'];
    $no_wa_customer = $_POST['no_wa_customer'];
    $email_customer = $_POST['email_customer'];

    // Validasi input
    if (empty($nama_customer) || empty($alamat_customer) || empty($no_wa_customer) || empty($email_customer)) {
        $message = 'Semua field wajib diisi.';
        $messageType = 'danger';
    } elseif (!isset($_FILES['foto_profil']) || $_FILES['foto_profil']['error'] != 0) {
        $message = 'Foto profil wajib diupload.';
        $messageType = 'danger';
    } else {
        // Proses upload file
        $upload_dir = '../uploads/profile/';
        $foto_name = basename($_FILES['foto_profil']['name']);
        $tmp_name = $_FILES['foto_profil']['tmp_name'];
        $target_file = $upload_dir . $foto_name;

        // Buat folder jika belum ada
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Validasi ekstensi file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($foto_name, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            $message = 'Format foto harus JPG, JPEG, PNG, atau GIF.';
            $messageType = 'danger';
        } else {
            if (move_uploaded_file($tmp_name, $target_file)) {
                // Simpan data ke database
                $query = "INSERT INTO tb_customer (nama_customer, alamat_customer, no_wa_cutomer, email_customer, id_login, foto_profil)
                          VALUES ('$nama_customer', '$alamat_customer', '$no_wa_customer', '$email_customer', '$id_login', '$foto_name')";

                if (mysqli_query($conn, $query)) {
                    $message = 'Akun customer berhasil dibuat.';
                    $messageType = 'success';
                    header('Location: checking.php');
                    exit;
                } else {
                    $message = 'Gagal menyimpan data: ' . mysqli_error($conn);
                    $messageType = 'danger';
                }
            } else {
                $message = 'Gagal mengupload foto profil.';
                $messageType = 'danger';
            }
        }
    }
}
?>



<!-- HTML sama dengan style form login kamu -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Customer Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
            
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .container {
            display: flex;
            width: 90%;
            max-width: 1000px;
            height: 600px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .left-panel {
            flex: 1;
            background-color: #4a94d3;
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0; /* Remove padding to allow image to fill */
        }
        
        .image-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .shopping-image {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Change to cover to fill the space */
            object-position: center; /* Center the image */
            display: block;
        }
        
        .right-panel {
            flex: 1;
            background-color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            overflow-y: auto; /* Allow scrolling if content is too tall */
        }
        
        .header {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        
        .logo {
            width: 30px;
            height: 30px;
            background-color: #0066ff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .welcome-text {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .welcome-text h2 {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .welcome-text p {
            font-size: 16px;
            color: #666;
            margin-top: 5px;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        label {
            font-size: 12px;
            margin-bottom: 5px;
            color: #666;
        }
        
        input {
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        button {
            padding: 15px;
            background-color: orangered;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .login-link {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        
        .login-link a {
            color: #0066ff;
            text-decoration: none;
        }
        
        .social-login {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .social-btn {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .or-divider {
            text-align: center;
            margin: 15px 0;
            color: #666;
            font-size: 14px;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive design for smaller screens */
        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
                width: 95%;
            }
            
            .left-panel {
                height: 200px; /* Fixed height for mobile */
            }
            
            .right-panel {
                padding: 30px 20px;
            }
            
            .welcome-text h2 {
                font-size: 20px;
            }
        }
        .img-fluid {
        max-height: 100px;
        filter: brightness(0) saturate(100%) invert(32%) sepia(99%) saturate(5085%) hue-rotate(359deg) brightness(97%) contrast(92%);
    }
    
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <div class="image-container">
                <img src="banner.jpg" alt="Online Shopping" class="shopping-image">
            </div>
        </div>
        <div class="right-panel">
            <div class="header">
            </div>
            <div class="welcome-text">
            <img src="../user/img/logo1.png" alt="Logo" class="img-fluid" style="max-height: 100px;">
                <h2>Lengkapi Akun Anda</h2>
                <p>Isi data lengkapmu sebelum melanjutkan</p>
            </div>

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_customer">Nama Lengkap</label>
                    <input type="text" id="nama_customer" name="nama_customer" required placeholder="Masukkan nama lengkap">
                </div>
                <div class="form-group">
                    <label for="alamat_customer">Alamat</label>
                    <input type="text" id="alamat_customer" name="alamat_customer" required placeholder="Alamat lengkap">
                </div>
                <div class="form-group">
                    <label for="no_wa_customer">Nomor WhatsApp</label>
                    <input type="text" id="no_wa_customer" name="no_wa_customer" required placeholder="Contoh: 0812xxxxxxx">
                </div>
                <div class="form-group">
                    <label for="email_customer">Email</label>
                    <input type="email" id="email_customer" name="email_customer" required placeholder="contoh@email.com">
                </div>
                <div class="form-group">
                    <label for="foto_profil">Foto Profil</label>
                    <input type="file" id="foto_profil" name="foto_profil" accept="image/*" required>
                </div>
                <button type="submit">Simpan Akun</button>
            </form>
        </div>
    </div>
</body>
</html>
