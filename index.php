<?php
// index.php
include 'koneksi.php';

$siswa = null;
$hasil = null;
$nilai = null;
$pesan = "";

// PROSES PENCARIAN TRANSPARANSI PERSONAL SISWA VIA NIS
if (isset($_POST['cek_ranking'])) {
    $nis = mysqli_real_escape_string($koneksi, $_POST['nis']);

    $query_siswa = "SELECT * FROM tabel_siswa WHERE nis = '$nis'";
    $res_siswa   = mysqli_query($koneksi, $query_siswa);

    if (mysqli_num_rows($res_siswa) === 1) {
        $siswa = mysqli_fetch_assoc($res_siswa);

        $query_hasil = "SELECT * FROM tabel_hasil WHERE nis = '$nis'";
        $res_hasil   = mysqli_query($koneksi, $query_hasil);
        $hasil       = mysqli_fetch_assoc($res_hasil);

        $query_nilai = "SELECT * FROM tabel_nilai WHERE nis = '$nis'";
        $res_nilai   = mysqli_query($koneksi, $query_nilai);
        $nilai       = mysqli_fetch_assoc($res_nilai);

        if (!$hasil) {
            $pesan = "<div class='alert alert-warning'><i class='fa-solid fa-circle-exclamation'></i> Profil ditemukan, tetapi Admin Guru belum mengeksekusi perhitungan SMART untuk Anda.</div>";
        }
    } else {
        $pesan = "<div class='alert alert-danger'><i class='fa-solid fa-triangle-exclamation'></i> Maaf, data NIS <strong>" . htmlspecialchars($nis) . "</strong> tidak ditemukan.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Sistem Kelulusan Terbaik SMART — SMK Puspita Bangsa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <style>
        /* TEMA AKADEMIK KONSISTEN: Soft Slate & Corporate Blue */
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; color: #0f172a; }
        .navbar { background-color: #1e3a8a; padding: 20px 40px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(30, 58, 138, 0.15); }
        .navbar h2 { margin: 0; font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }
        .btn-portal { background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; text-decoration: none; transition: 0.2s; }
        .btn-portal:hover { background-color: #2563eb; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        
        .container { padding: 40px; max-width: 1000px; margin: 0 auto; }
        .welcome-section { text-align: center; margin-bottom: 40px; }
        .welcome-section h1 { margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #1e3a8a; }
        .welcome-section p { margin: 0; color: #475569; font-size: 14px; font-weight: 500; }
        
        /* Layout Box Klaster Jurusan */
        .jurusan-box { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e2e8f0; margin-bottom: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); }
        .jurusan-title { font-size: 15px; font-weight: 700; color: #1e3a8a; margin-bottom: 15px; border-left: 4px solid #3b82f6; padding-left: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        table { width: 100%; border-collapse: collapse; }
        table th { background-color: #f8fafc; color: #475569; font-weight: 600; padding: 12px; font-size: 12px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; }
        table td { border-bottom: 1px solid #edf2f7; padding: 12px; text-align: center; font-size: 14px; color: #334155; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .medal { padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; display: inline-flex; align-items: center; gap: 4px; }
        .m1 { background-color: #fef08a; color: #854d0e; border: 1px solid #fde047; }
        .m2 { background-color: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; }
        .m3 { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
        
        /* Card Pencarian NIS & Hasil Validasi */
        .search-box { background: white; padding: 30px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); margin-top: 40px; text-align: center; }
        .search-box h3 { margin: 0 0 8px 0; font-size: 16px; color: #1e3a8a; text-transform: uppercase; }
        .search-box p { margin: 0 0 20px 0; color: #64748b; font-size: 13px; }
        .form-inline { display: flex; max-width: 500px; margin: 0 auto; gap: 10px; }
        .form-inline input { flex: 1; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; outline: none; text-align: center; font-weight: 600; }
        .form-inline input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        .form-inline button { background: #3b82f6; color: white; border: none; padding: 0 24px; border-radius: 8px; cursor: pointer; font-weight: 700; font-size: 14px; transition: 0.2s; }
        .form-inline button:hover { background: #2563eb; }
        
        .alert { padding: 14px; border-radius: 10px; margin-top: 20px; font-weight: 600; font-size: 14px; text-align: center; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .alert-warning { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        
        .result-card { background: white; padding: 30px; border-radius: 16px; border: 2px solid #3b82f6; box-shadow: 0 10px 25px rgba(59, 130, 246, 0.06); margin-top: 30px; text-align: left; }
        .result-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
        .result-item { background: #f8fafc; padding: 12px 16px; border-radius: 10px; border: 1px solid #edf2f7; }
        .result-item label { display: block; font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 700; margin-bottom: 4px; }
        .result-item span { font-size: 14px; font-weight: 700; color: #0f172a; }
        .table-nilai { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .table-nilai th { background-color: #f8fafc; font-size: 11px; padding: 10px; }
        .table-nilai td { padding: 12px; font-size: 13px; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>SMK PUSPITA BANGSA — PORTAL INFORMASI AKADEMIK</h2>
    <a href="login.php" class="btn-portal"><i class="fa-solid fa-lock-open"></i> Portal Guru</a>
</div>

<div class="container">
    <div class="welcome-section">
        <h1>Sistem Informasi Transparansi Kelulusan</h1>
        <p>Pengumuman Rekomendasi Siswa Terbaik Berdasarkan Kompetensi Keahlian Menggunakan Algoritma SMART</p>
    </div>

    <h3 style="text-align: center; color: #1e3a8a; font-size: 16px; font-weight: 700; margin-bottom: 25px; text-transform: uppercase; letter-spacing: 0.5px;">
        <i class="fa-solid fa-trophy" style="color:#eab308;"></i> Rekomendasi Lulusan Terbaik Per Jurusan
    </h3>

    <?php 
    // Daftar antrean 5 jurusan resmi sekolah secara legal (Termasuk AKL)
    $list_papan_jurusan = ['RPL', 'BDP', 'OTKP', 'PH', 'AKL'];
    
    foreach ($list_papan_jurusan as $j_papan): 
        // Mengambil TOP 3 (LIMIT 3) siswa dari database berdasarkan urutan ranking_jurusan
        $q_papan = mysqli_query($koneksi, "SELECT tabel_hasil.*, tabel_siswa.nama_siswa 
                                           FROM tabel_hasil 
                                           INNER JOIN tabel_siswa ON tabel_hasil.nis = tabel_siswa.nis 
                                           WHERE tabel_siswa.jurusan = '$j_papan' 
                                           ORDER BY tabel_hasil.ranking_jurusan ASC 
                                           LIMIT 3");
        $jumlah_papan = mysqli_num_rows($q_papan);
    ?>
    <div class="jurusan-box">
        <div class="jurusan-title">
            <span><i class="fa-solid fa-graduation-cap"></i> Jurusan: <?php echo $j_papan; ?></span>
            <span style="font-size: 11px; background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 12px; font-weight: bold;">Top 3 Terklaster</span>
        </div>
        
        <?php if ($jumlah_papan > 0): ?>
        <table>
            <thead>
                <tr>
                    <th style="width: 130px;">Peringkat</th>
                    <th style="width: 150px;">NIS</th>
                    <th style="text-align: left;">Nama Lengkap Siswa</th>
                    <th style="width: 180px;">Skor Akhir SMART ($V_i$)</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row_papan = mysqli_fetch_assoc($q_papan)): ?>
                <tr>
                    <td>
                        <?php 
                        if ($row_papan['ranking_jurusan'] == 1) echo "<span class='medal m1'>🥇 Juara 1</span>";
                        elseif ($row_papan['ranking_jurusan'] == 2) echo "<span class='medal m2'>🥈 Juara 2</span>";
                        elseif ($row_papan['ranking_jurusan'] == 3) echo "<span class='medal m3'>🥉 Juara 3</span>";
                        ?>
                    </td>
                    <td class="mono"><code><?php echo $row_papan['nis']; ?></code></td>
                    <td style="text-align: left; font-weight: 600; color: #334155;"><?php echo $row_papan['nama_siswa']; ?></td>
                    <td><span class="mono" style="background:#dcfce7; padding:4px 10px; border-radius:6px; color:#15803d; font-weight:700; border: 1px solid #bbf7d0;"><?php echo number_format($row_papan['skor_akhir'], 6); ?></span></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="margin: 5px 0 0 0; font-size: 13px; color: #94a3b8; font-style: italic;"><i class="fa-solid fa-folder-open"></i> Belum ada data kelulusan terhitung atau siswa terdaftar pada kompetensi keahlian ini.</p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>


    <div class="search-box">
        <h3>Cek Detail Perankingan & Nilai Anda</h3>
        <p>Masukkan Nomor Induk Siswa (NIS) Anda untuk memvalidasi transparansi skor akhir</p>
        
        <form action="" method="POST" class="form-inline">
            <input type="text" name="nis" placeholder="Masukkan NIS Anda..." required autocomplete="off" class="mono" value="<?php echo isset($_POST['nis']) ? htmlspecialchars($_POST['nis']) : ''; ?>">
            <button type="submit" name="cek_ranking"><i class="fa-solid fa-magnifying-glass"></i> Cari Data</button>
        </form>

        <?php if ($pesan != "") echo $pesan; ?>
    </div>

    <?php if ($siswa && $hasil): ?>
    <div class="result-card">
        <h3 style="margin-top:0; color:#1e3a8a; border-bottom:2px solid #f1f5f9; padding-bottom:10px; font-size:14px;"><i class="fa-solid fa-id-card"></i> Lembar Transparansi Hasil Komputasi Siswa</h3>
        
        <div class="result-grid">
            <div class="result-item"><label>Nama Lengkap</label><span><?php echo $siswa['nama_siswa']; ?></span></div>
            <div class="result-item"><label>NIS / Kelas / Jurusan</label><span><?php echo $siswa['nis']; ?> — <?php echo $siswa['kelas']; ?> <?php echo $siswa['jurusan']; ?></span></div>
            
            <div class="result-item" style="background:#eff6ff; border-color:#bfdbfe;">
                <label style="color:#1e40af;"><i class="fa-solid fa-award"></i> Peringkat Jurusan (<?php echo $siswa['jurusan']; ?>)</label>
                <span style="color:#1e3a8a; font-size:18px;">Ke-<?php echo $hasil['ranking_jurusan'] ?? '-'; ?></span>
            </div>
            <div class="result-item" style="background:#f0fdf4; border-color:#bbf7d0;">
                <label style="color:#166534;"><i class="fa-solid fa-trophy"></i> Peringkat Umum Sekolah</label>
                <span style="color:#15803d; font-size:18px;">Ke-<?php echo $hasil['ranking_umum'] ?? '-'; ?></span>
            </div>
        </div>
        
        <p style="text-align: center; font-size: 13px; margin: 20px 0 0 0; color: #475569;">Skor Akumulasi SMART: <strong class="mono" style="color:#15803d; background:#dcfce7; padding:4px 10px; border-radius:6px; border:1px solid #bbf7d0; font-size:15px;"><?php echo number_format($hasil['skor_akhir'], 6); ?></strong></p>

        <?php if ($nilai): ?>
            <table class="table-nilai">
                <thead>
                    <tr>
                        <th>C1 (Akademik)</th>
                        <th>C2 (Hadir)</th>
                        <th>C3 (Prestasi)</th>
                        <th>C4 (Ekskul)</th>
                        <th style="color:#ef4444;">C5 (Telat)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="mono">
                        <td><?php echo $nilai['c1_akademik']; ?></td>
                        <td><?php echo $nilai['c2_kehadiran']; ?>%</td>
                        <td><?php echo $nilai['c3_prestasi']; ?></td>
                        <td><?php echo $nilai['c4_ekskul']; ?></td>
                        <td style="color:#ef4444; font-weight:bold;"><?php echo $nilai['c5_keterlambatan']; ?>x</td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>
        
        <p style="font-size:11px; color:#94a3b8; margin:20px 0 0 0; text-align:center; font-style:italic;">* Data peringkat di atas dihitung secara sah dan transparan menggunakan pembobotan resmi matriks sekolah.</p>
    </div>
    <?php endif; ?>

</div>

</body>
</html>