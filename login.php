<?php
// login.php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = isset($_POST['username']) ? trim($_POST['username']) : "";
    $password = isset($_POST['password']) ? trim($_POST['password']) : "";

    if (empty($username) && empty($password)) {
        $error = "Isi username dan password terlebih dahulu!";
    }
    elseif (empty($username)) {
        $error = "Input username terlebih dahulu!";
    }
    elseif (empty($password)) {
        $error = "Input password terlebih dahulu!";
    }
    else {
        $username_clean = mysqli_real_escape_string($koneksi, $username);
        
        $query  = "SELECT * FROM tabel_admin WHERE username = '$username_clean'";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            
            // Verifikasi password menggunakan fungsi hash PHP
            if (password_verify($password, $row['password'])) {
                $_SESSION['status']   = "login_admin";
                $_SESSION['id_admin'] = $row['id_admin'];
                $_SESSION['username'] = $row['username']; 
                
                header("Location: dashboard_guru.php");
                exit;
            } else {
                $error = "Kata sandi yang Anda masukkan salah!";
            }
        } else {
            $error = "Username tidak terdaftar dalam sistem!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Log In Otoritas Guru — SMK Puspita Bangsa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        /* TEMA SERASI: Academic Pure Clean (Soft Light Mode) */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: #0f172a;
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.05);
            border: 1px solid #e2e8f0;
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
        }
        
        /* Wadah Lingkaran Logo Sekolah */
        .logo-container-school {
            width: 100px;
            height: 100px;
            background-color: #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px auto;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }
        .logo-container-school img {
            width: 85%;
            height: 85%;
            object-fit: contain;
        }

        h2 { margin: 0 0 6px 0; font-size: 20px; font-weight: 700; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px; }
        .sub-title { margin: 0 0 30px 0; color: #64748b; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 6px; font-size: 12px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper i { position: absolute; left: 16px; color: #94a3b8; font-size: 16px; }
        .input-wrapper input { width: 100%; padding: 12px 16px 12px 46px; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 14px; outline: none; box-sizing: border-box; font-weight: 600; color: #334155; }
        .input-wrapper input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        
        button { background-color: #1e3a8a; color: white; border: none; padding: 14px; border-radius: 10px; cursor: pointer; font-weight: 700; width: 100%; font-size: 14px; margin-top: 10px; transition: 0.2s; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2); }
        button:hover { background-color: #2563eb; transform: translateY(-1px); box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3); }
        
        .error-box { background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; font-weight: 600; border: 1px solid #fca5a5; display: flex; align-items: center; gap: 8px; justify-content: center; }
        .back-link { display: inline-block; margin-top: 25px; color: #64748b; text-decoration: none; font-size: 13px; font-weight: 600; transition: 0.2s; }
        .back-link:hover { color: #1e3a8a; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="logo-container-school">
        <img src="https://puspitabangsablog.wordpress.com/wp-content/uploads/2017/08/cropped-logo-pupsita.png" alt="Logo Resmi SMK Puspita Bangsa">
    </div>
    
    <h2>Log In Guru</h2>
    <div class="sub-title">Sistem Kendali Perankingan SMART</div>
    
    <?php if ($error != ""): ?>
        <div class="error-box"><i class="fa-solid fa-circle-exclamation"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Username Otoritas</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Masukkan username admin..." autocomplete="off" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Kata Sandi</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="Masukkan password..." required>
            </div>
        </div>
        <button type="submit" name="login"><i class="fa-solid fa-right-to-bracket"></i> Masuk ke Panel</button>
    </form>

    <a href="index.php" class="back-link"><i class="fa-solid fa-house"></i> Kembali ke Beranda Siswa</a>
</div>

</body>
</html>