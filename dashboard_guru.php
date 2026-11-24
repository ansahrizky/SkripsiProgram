<?php
// dashboard_guru.php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login_admin") {
    header("Location: login.php");
    exit;
}

// AMBIL DATA USERNAME DARI SESSION LOGIN GURU (JIKA KOSONG, DEFAULT KE ADMINISTRATOR)
$username_login = isset($_SESSION['username']) ? $_SESSION['username'] : "Administrator";

$data_kriteria = [];
$query_kriteria = "SELECT * FROM tabel_kriteria";
$result_kriteria = mysqli_query($koneksi, $query_kriteria);

if ($result_kriteria && mysqli_num_rows($result_kriteria) > 0) {
    while ($row = mysqli_fetch_assoc($result_kriteria)) {
        $data_kriteria[] = $row;
    }
} else {
    $data_kriteria = [
        ['id_kriteria' => 'C1', 'nama_kriteria' => 'Akademik', 'bobot_awal' => 35, 'bobot_normalisasi' => 0.35, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C2', 'nama_kriteria' => 'Kehadiran', 'bobot_awal' => 15, 'bobot_normalisasi' => 0.15, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C3', 'nama_kriteria' => 'Prestasi', 'bobot_awal' => 25, 'bobot_normalisasi' => 0.25, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C4', 'nama_kriteria' => 'Ekskul', 'bobot_awal' => 15, 'bobot_normalisasi' => 0.15, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C5', 'nama_kriteria' => 'Keterlambatan', 'bobot_awal' => 10, 'bobot_normalisasi' => 0.10, 'sifat_kriteria' => 'cost']
    ];
}

$count_siswa = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_siswa"))['total'];
$count_nilai = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_nilai"))['total'];
$count_hasil = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM tabel_hasil"))['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Guru — SMK Puspita Bangsa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <style>
        /* TEMA AKADEMIK: Soft Slate & Corporate Blue */
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f1f5f9; 
            margin: 0; 
            padding: 0; 
            color: #0f172a; 
        }
        .navbar { 
            background-color: #1e3a8a; 
            padding: 20px 40px; 
            color: white; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15);
        }
        .navbar h2 { margin: 0; font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }
        .user-profile { display: flex; align-items: center; gap: 15px; }
        .user-info span { display: block; font-size: 13px; font-weight: 600; }
        .btn-logout { background-color: #ef4444; color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; transition: 0.2s; }
        .btn-logout:hover { background-color: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); }
        
        .container { padding: 40px; max-width: 1300px; margin: 0 auto; }
        .welcome-section { margin-bottom: 35px; }
        .welcome-section h1 { 
            margin: 0 0 8px 0; 
            font-size: 28px; 
            font-weight: 700; 
            color: #1e3a8a;
        }
        .welcome-section p { margin: 0; color: #475569; font-size: 14px; font-weight: 500; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: #ffffff; padding: 22px; border-radius: 16px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); }
        .stat-icon { width: 54px; height: 54px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-info h3 { margin: 0 0 4px 0; font-size: 24px; font-weight: 700; color: #0f172a; }
        .stat-info p { margin: 0; font-size: 11px; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .grid-menu { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 40px; }
        .menu-card { background: #ffffff; padding: 35px; border-radius: 20px; border: 1px solid #e2e8f0; text-decoration: none; color: #0f172a; transition: 0.3s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); }
        .menu-card:hover { transform: translateY(-4px); border-color: #2563eb; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1); }
        .menu-icon { font-size: 36px; margin-bottom: 20px; }
        .menu-card h4 { margin: 0 0 10px 0; font-size: 18px; font-weight: 700; color: #1e3a8a; }
        .menu-card p { margin: 0; color: #475569; font-size: 13px; line-height: 1.6; }
        
        .box-kriteria { background: #ffffff; padding: 30px; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); }
        .box-kriteria h3 { margin: 0 0 20px 0; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #475569; border-bottom: 1px solid #edf2f7; padding-bottom: 12px; }
        
        table { width: 100%; border-collapse: collapse; }
        table th { background-color: #f8fafc; color: #475569; font-weight: 600; padding: 14px; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; letter-spacing: 0.5px; }
        table td { padding: 16px; text-align: center; font-size: 14px; border-bottom: 1px solid #edf2f7; color: #334155; }
        table tr:hover td { background-color: #f8fafc; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        .badge { padding: 5px 12px; border-radius: 6px; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; }
        .badge-benefit { background-color: #d1fae5; color: #065f46; }
        .badge-cost { background-color: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>SMK PUSPITA BANGSA — PANEL KENDALI ADMINISTRATOR</h2>
    <div class="user-profile">
        <div class="user-info">
            <span>Guru: <?php echo htmlspecialchars($username_login); ?></span>
        </div>
        <a href="logout.php" class="btn-logout"><i class="fa-solid fa-power-off"></i> Log Out</a>
    </div>
</div>

<div class="container">
    <div class="welcome-section">
        <h1>Selamat Datang Kembali, <?php echo htmlspecialchars($username_login); ?></h1>
        <p>Sistem Informasi Keputusan Kelulusan Terbaik Menggunakan Komputasi Algoritma SMART.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #059669;"><i class="fa-solid fa-user-graduate"></i></div>
            <div class="stat-info"><h3><?php echo $count_siswa; ?></h3><p>Total Siswa</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #cffaf2; color: #0891b2;"><i class="fa-solid fa-chart-simple"></i></div>
            <div class="stat-info"><h3><?php echo $count_nilai; ?></h3><p>Data Ternilai</p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0e7ff; color: #4f46e5;"><i class="fa-solid fa-square-poll-vertical"></i></div>
            <div class="stat-info"><h3><?php echo $count_hasil; ?></h3><p>Sudah Diklaster</p></div>
        </div>
    </div>

    <div class="grid-menu">
        <a href="kelola_kriteria.php" class="menu-card">
            <div class="menu-icon" style="color: #d97706;"><i class="fa-solid fa-sliders"></i></div>
            <h4>Kelola Bobot Kriteria</h4>
            <p>Atur bobot awal, normalisasi, dan sifat (benefit/cost) dari setiap kriteria SMART (C1–C5).</p>
        </a>
        <a href="siswa_nilai.php" class="menu-card">
            <div class="menu-icon" style="color: #059669;"><i class="fa-solid fa-folder-combined"></i> <i class="fa-solid fa-users-gear"></i></div>
            <h4>Kelola Data & Nilai Terpadu</h4>
            <p>Pusat kendali data induk siswa beserta parameter kriteria SMART. Anda dapat mengimpor berkas Excel atau melakukan pengisian mandiri secara opsional.</p>
        </a>
        
        <a href="hitung_smart.php" class="menu-card">
            <div class="menu-icon" style="color: #4f46e5;"><i class="fa-solid fa-calculator"></i></div>
            <h4>Proses Hitung SMART</h4>
            <p>Eksekusi engine SPK untuk melakukan normalisasi kriteria desimal, pengalian matriks pembobotan dinas, dan pengunduhan hasil pemeringkatan lulusan terbaik.</p>
        </a>
    </div>

    <div class="box-kriteria">
        <h3><i class="fa-solid fa-sliders"></i> Konfigurasi Matriks Pembobotan Kriteria </h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 140px;">Kode</th>
                    <th>Nama Parameter Kriteria</th>
                    <th style="width: 160px;">Bobot Awal</th>
                    <th style="width: 220px;">Bobot Normalisasi</th>
                    <th style="width: 240px;">Sifat Kriteria</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data_kriteria as $kriteria): ?>
                <tr>
                    <td class="mono" style="color: #1e3a8a;"><strong><?php echo $kriteria['id_kriteria']; ?></strong></td>
                    <td style="text-align: left; padding-left: 20px; font-weight: 600; color: #334155;"><?php echo $kriteria['nama_kriteria']; ?></td>
                    <td style="font-weight: 600; color: #475569;"><?php echo $kriteria['bobot_awal']; ?>%</td>
                    <td><span style="background-color: #fef9c3; padding: 4px 12px; font-weight: 700; border-radius: 6px; color:#a16207; border: 1px solid #fef08a;" class="mono"><?php echo $kriteria['bobot_normalisasi']; ?></span></td>
                    <td>
                        <?php if ($kriteria['sifat_kriteria'] === 'benefit'): ?>
                            <span class="badge badge-benefit"><i class="fa-solid fa-arrow-trend-up"></i> Benefit (Maksimum)</span>
                        <?php else: ?>
                            <span class="badge badge-cost"><i class="fa-solid fa-arrow-trend-down"></i> Cost (Minimum)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>