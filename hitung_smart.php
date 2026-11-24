<?php
// hitung_smart.php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login_admin") {
    header("Location: login.php");
    exit;
}

$notif = "";

// ==========================================
// PROSES ENGINE UTAMA KOMPUTASI METODE SMART
// ==========================================
if (isset($_POST['proses_smart'])) {
    $bobot = [];
    $q_kriteria = mysqli_query($koneksi, "SELECT id_kriteria, bobot_normalisasi, sifat_kriteria FROM tabel_kriteria");
    while ($k = mysqli_fetch_assoc($q_kriteria)) {
        $bobot[$k['id_kriteria']] = [
            'w' => (float)$k['bobot_normalisasi'],
            'sifat' => $k['sifat_kriteria']
        ];
    }

    $q_nilai = mysqli_query($koneksi, "SELECT * FROM tabel_nilai");
    
    if (mysqli_num_rows($q_nilai) == 0) {
        $notif = "<div class='alert alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Gagal! Belum ada parameter nilai siswa di database.</div>";
    } else {
        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");
        mysqli_query($koneksi, "TRUNCATE TABLE tabel_hasil");
        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");
        $list_skor = [];

        while ($n = mysqli_fetch_assoc($q_nilai)) {
            $nis = $n['nis'];
            
            $u_c1 = (float)$n['c1_akademik'] / 100;
            $u_c2 = (float)$n['c2_kehadiran'] / 100;
            $u_c3 = (float)$n['c3_prestasi'] / 100;
            $u_c4 = (float)$n['c4_ekskul'] / 100;
            
            $mentah_c5 = (float)$n['c5_keterlambatan'];
            $u_c5 = (100 - $mentah_c5) / 100;
            if ($u_c5 < 0) $u_c5 = 0;

            $skor_akhir = ($u_c1 * $bobot['C1']['w']) + 
                          ($u_c2 * $bobot['C2']['w']) + 
                          ($u_c3 * $bobot['C3']['w']) + 
                          ($u_c4 * $bobot['C4']['w']) + 
                          ($u_c5 * $bobot['C5']['w']);

            $list_skor[] = [
                'nis' => $nis,
                'skor' => $skor_akhir
            ];
        }

        // Urutkan nilai skor akhir tertinggi ke terendah
        usort($list_skor, function($a, $b) {
            return $b['skor'] <=> $a['skor'];
        });

        $rank_umum = 1;
        foreach ($list_skor as $item) {
            $nis_item = $item['nis'];
            $skor_item = $item['skor'];
            
            mysqli_query($koneksi, "INSERT INTO tabel_hasil (nis, skor_akhir, ranking_umum, ranking_jurusan) VALUES ('$nis_item', '$skor_item', '$rank_umum', 0)");
            $rank_umum++;
        }

        // MEMASUKKAN ANTREAN JURUSAN DINAMIS TERMASUK AKL KEDALAM ENGINE SINKRONISASI
        $list_jurusan = ['RPL', 'BDP', 'OTKP', 'PH', 'AKL'];
        foreach ($list_jurusan as $jur) {
            $q_jur = mysqli_query($koneksi, "SELECT tabel_hasil.id_hasil FROM tabel_hasil INNER JOIN tabel_siswa ON tabel_hasil.nis = tabel_siswa.nis WHERE tabel_siswa.jurusan = '$jur' ORDER BY tabel_hasil.skor_akhir DESC");
            $rank_jur = 1;
            while ($row_jur = mysqli_fetch_assoc($q_jur)) {
                $id_h = $row_jur['id_hasil'];
                mysqli_query($koneksi, "UPDATE tabel_hasil SET ranking_jurusan = '$rank_jur' WHERE id_hasil = '$id_h'");
                $rank_jur++;
            }
        }

        $notif = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Komputasi Sukses! Ranking Lulusan per Jurusan Berhasil Diperbarui.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Analytics Computation Center — SMK Puspita Bangsa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=JetBrains+Mono:wght@600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f1f5f9; margin: 0; padding: 0; color: #0f172a; }
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
        .navbar a { color: white; text-decoration: none; font-size: 12px; font-weight: 700; background: #3b82f6; padding: 8px 16px; border-radius: 8px; transition: 0.2s; }
        .navbar a:hover { background: #2563eb; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        
        .container { padding: 30px; max-width: 1300px; margin: 0 auto; }
        .grid-layout { display: grid; grid-template-columns: 300px 1fr; gap: 30px; align-items: start; }
        .box { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; color: #1e3a8a; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .jurusan-container { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 25px; }
        .jurusan-title { font-size: 16px; font-weight: 700; color: #1e3a8a; margin-bottom: 15px; border-left: 4px solid #3b82f6; padding-left: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        button { background: #4f46e5; color: white; border: none; padding: 14px; border-radius: 10px; cursor: pointer; font-weight: 700; width: 100%; font-size: 14px; transition: 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
        button:hover { background: #4338ca; transform: translateY(-1px); }
        
        table { width: 100%; border-collapse: collapse; }
        table th { background-color: #f8fafc; color: #475569; font-weight: 600; padding: 12px; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        table td { border-bottom: 1px solid #edf2f7; padding: 12px; text-align: center; font-size: 14px; color: #334155; }
        
        .mono { font-family: 'JetBrains Mono', monospace; }
        .alert { padding: 14px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; text-align: center; font-size: 14px; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        
        .rank-medal { padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 11px; display: inline-flex; align-items: center; gap: 4px; }
        .m1 { background-color: #fef08a; color: #854d0e; border: 1px solid #fde047; }
        .m2 { background-color: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; }
        .m3 { background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>SMK PUSPITA BANGSA — DATA ANALYTICS & COMPUTATION CENTER</h2>
    <a href="dashboard_guru.php"><i class="fa-solid fa-arrow-left"></i> Dashboard Beranda</a>
</div>

<div class="container">
    <?php echo $notif; ?>

    <div class="grid-layout">
        <div class="box">
            <h3><i class="fa-solid fa-gears" style="color:#4f46e5;"></i> Kontrol Matriks</h3>
            <p style="font-size: 13px; color: #64748b; line-height: 1.5; margin-bottom: 20px;">Klik tombol di bawah untuk memproses perankingan lulusan terbaik otomatis per masing-masing jurusan.</p>
            <form action="" method="POST">
                <button type="submit" name="proses_smart"><i class="fa-solid fa-bolt"></i> Eksekusi SMART</button>
            </form>
        </div>

        <div>
            <?php 
            $ar_jurusan = ['RPL', 'BDP', 'OTKP', 'PH', 'AKL'];
            foreach ($ar_jurusan as $j_key): 
                // Ambil data ranking khusus untuk jurusan yang sedang di-looping
                $q_tabel_jur = mysqli_query($koneksi, "SELECT tabel_hasil.*, tabel_siswa.nama_siswa FROM tabel_hasil INNER JOIN tabel_siswa ON tabel_hasil.nis = tabel_siswa.nis WHERE tabel_siswa.jurusan = '$j_key' ORDER BY tabel_hasil.ranking_jurusan ASC");
                $jumlah_anak = mysqli_num_rows($q_tabel_jur);
            ?>
            <div class="jurusan-container">
                <div class="jurusan-title">
                    <span><i class="fa-solid fa-graduation-cap"></i> Kompetensi Keahlian: <?php echo $j_key; ?></span>
                    <span style="font-size:12px; background:#e2e8f0; color:#475569; padding:4px 10px; border-radius:20px;">Total: <?php echo $jumlah_anak; ?> Siswa</span>
                </div>
                
                <?php if ($jumlah_anak > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 140px;">Rank Jurusan</th>
                            <th style="width: 150px;">NIS</th>
                            <th>Nama Lengkap Siswa</th>
                            <th style="width: 160px;">Skor Akhir ($V_i$)</th>
                            <th style="width: 130px;">Rank Umum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($q_tabel_jur)): ?>
                        <tr>
                            <td>
                                <?php 
                                if($row['ranking_jurusan'] == 1) echo "<span class='rank-medal m1'><i class='fa-solid fa-trophy'></i> Juara 1</span>";
                                elseif($row['ranking_jurusan'] == 2) echo "<span class='rank-medal m2'><i class='fa-solid fa-award'></i> Juara 2</span>";
                                elseif($row['ranking_jurusan'] == 3) echo "<span class='rank-medal m3'><i class='fa-solid fa-award'></i> Juara 3</span>";
                                else echo "<span class='mono' style='color:#64748b; font-weight:600;'>Peringkat " . $row['ranking_jurusan'] . "</span>";
                                ?>
                            </td>
                            <td class="mono"><code><?php echo $row['nis']; ?></code></td>
                            <td style="text-align: left; font-weight: 600; color:#334155;"><?php echo $row['nama_siswa']; ?></td>
                            <td><span class="mono" style="background:#dcfce7; padding:4px 10px; border-radius:6px; color:#15803d; font-weight:700; border: 1px solid #bbf7d0;"><?php echo number_format($row['skor_akhir'], 6); ?></span></td>
                            <td><span class="mono" style="color:#64748b; font-weight:600;">Ke-<?php echo $row['ranking_umum']; ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p style="margin: 10px 0 0 0; font-size:13px; color:#94a3b8; font-style:italic;"><i class="fa-solid fa-folder-open"></i> Tidak ada antrean data atau belum ada siswa yang terdaftar pada kompetensi keahlian ini.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

    </div>
</div>

</body>
</html>