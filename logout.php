<?php
// logout.php
session_start();

// 1. Hancurkan seluruh session login guru secara total
session_unset();
session_destroy();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sesi Berakhir — SMK Puspita Bangsa</title>
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
        .logout-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(30, 58, 138, 0.05);
            border: 1px solid #e2e8f0;
            text-align: center;
            max-width: 400px;
            width: 100%;
            box-sizing: border-box;
        }
        .icon-box {
            width: 70px;
            height: 70px;
            background-color: #fee2e2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 24px auto;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }
        h2 {
            margin: 0 0 10px 0;
            font-size: 22px;
            font-weight: 700;
            color: #1e3a8a;
        }
        p {
            margin: 0 0 30px 0;
            color: #475569;
            font-size: 14px;
            line-height: 1.5;
        }
        .btn-login-back {
            background-color: #1e3a8a;
            color: white;
            padding: 14px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            display: block;
            transition: 0.2s;
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.2);
        }
        .btn-login-back:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(29, 78, 216, 0.3);
        }
        .home-link {
            display: inline-block;
            margin-top: 20px;
            color: #64748b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: 0.2s;
        }
        .home-link:hover {
            color: #1e3a8a;
        }
    </style>
</head>
<body>

<div class="logout-container">
    <div class="icon-box">
        <i class="fa-solid fa-right-from-bracket"></i>
    </div>
    <h2>Sesi Anda Telah Berakhir</h2>
    <p>Anda telah berhasil keluar dengan aman dari sistem kendali otoritas kelulusan SMART SMK Puspita Bangsa.</p>
    
    <a href="login.php" class="btn-login-back"><i class="fa-solid fa-shield-halved"></i> Kembali ke Halaman Login</a>
    
    <a href="index.php" class="home-link"><i class="fa-solid fa-house"></i> Menuju Beranda Siswa</a>
</div>

</body>
</html>