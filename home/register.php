<?php
session_start();
require_once '../config/database.php';

$message = '';
$messageType = '';

// Redirect jika user sudah login
if (isset($_SESSION['user_id'])) {
    // Cek apakah user sudah mengisi data customer
    $user_id = $_SESSION['user_id'];
    $check_query = "SELECT * FROM tb_customer WHERE id_login = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($check_stmt, "i", $user_id);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($check_result) > 0) {
        // Jika sudah ada data customer, redirect ke halaman utama
        header("Location: index.php");
    } else {
        // Jika belum ada data customer, redirect ke halaman lengkapi profil
        header("Location: customer.php");
    }
    exit;
}

// Cek jika form register dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek apakah semua field diisi
    if (empty($username) || empty($password)) {
        $message = 'Username dan password wajib diisi.';
        $messageType = 'danger';
    } else {
        // Cek jika username sudah terdaftar
        $query = "SELECT * FROM tb_login WHERE username = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $message = 'Username sudah terdaftar.';
            $messageType = 'danger';
        } else {
            // Simpan data user ke tb_login
            $query = "INSERT INTO tb_login (username, password, role) VALUES (?, ?, 'customer')";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "ss", $username, $password);  // Langsung menyimpan password tanpa hash
            
            if (mysqli_stmt_execute($stmt)) {
                // Ambil id yang baru dibuat
                $user_id = mysqli_insert_id($conn);
                
                // Set session untuk login otomatis
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'customer';
                
                $message = 'Pendaftaran berhasil! Silakan lengkapi profil Anda.';
                $messageType = 'success';
                
                // Redirect ke halaman lengkapi profil setelah 2 detik
                header("Refresh: 2; URL=customer.php");
            } else {
                $message = 'Gagal mendaftar: ' . mysqli_error($conn);
                $messageType = 'danger';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gruppe Vier - Registration</title>
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
                <h2>Selamat datang di InfinityStore</h2>
                <p>Buat akunmu dan mulai belanja</p>
            </div>
            
            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Choose a username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <span class="password-toggle">👁️</span>
                    </div>
                </div>
                <button type="submit">Sign up</button>
                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in</a>
                </div>
                <div class="or-divider"></div>
            </form>
        </div>
    </div>

    <script>
        // Toggle password visibility
        document.querySelector('.password-toggle').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.textContent = '👁️‍🗨️';
            } else {
                passwordInput.type = 'password';
                this.textContent = '👁️';
            }
        });
    </script>
</body>
</html>