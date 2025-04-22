<?php
session_start();
require_once '../config/database.php';

$message = '';
$messageType = '';

// Cek jika form login dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = 'Username dan password wajib diisi.';
        $messageType = 'danger';
    } else {
        $query = "SELECT * FROM tb_login WHERE username = ? AND password = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['id_login'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header('Location: ../admin/pages/home/index.php');
            } elseif ($user['role'] == 'customer') {
                header('Location: checking.php');
            } else {
                $message = 'Role tidak dikenali.';
                $messageType = 'danger';
            }
            exit();
        } else {
            $message = 'Username atau password salah.';
            $messageType = 'danger';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gruppe Vier - Login</title>
    <style>
        <?php // Gaya CSS sama dengan halaman register ?>
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
        }

        .image-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .shopping-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }

        .right-panel {
            flex: 1;
            background-color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
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

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                height: auto;
                width: 95%;
            }

            .left-panel {
                height: 200px;
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
                <img src="banner.jpg" alt="Login Banner" class="shopping-image">
            </div>
        </div>
        <div class="right-panel">
            <div class="header">
                
            </div>
            <div class="welcome-text">
            <img src="../user/img/logo1.png" alt="Logo" class="img-fluid" style="max-height: 100px;">
                <h2>Selamat datang Kembali</h2>
                <p>Masukan lagi akunmu dan mulai belanja</p>
            </div>

            <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo $message; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <span class="password-toggle">👁️</span>
                    </div>
                </div>
                <button type="submit">Sign in</button>
                <div class="login-link">
                    Belum punya akun? <a href="register.php">Daftar di sini</a>
                </div>
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
