<?php
// kelola_kriteria.php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login_admin") {
    header("Location: login.php");
    exit;
}

$pesan = "";

// ==========================================
// PROSES SIMPAN / UPDATE KRITERIA
// ==========================================
if (isset($_POST['simpan_kriteria'])) {
    $id_list   = $_POST['id_kriteria'];
    $nama_list = $_POST['nama_kriteria'];
    $bobot_list = $_POST['bobot_awal'];
    $sifat_list = $_POST['sifat_kriteria'];

    // Hitung total bobot awal untuk normalisasi
    $total_bobot = 0;
    foreach ($bobot_list as $b) {
        $total_bobot += (float)$b;
    }

    if ($total_bobot <= 0) {
        $pesan = "<div class='alert alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Total bobot tidak boleh 0.</div>";
    } else {
        $berhasil = 0;
        for ($i = 0; $i < count($id_list); $i++) {
            $id    = mysqli_real_escape_string($koneksi, $id_list[$i]);
            $nama  = mysqli_real_escape_string($koneksi, $nama_list[$i]);
            $bobot = (float)$bobot_list[$i];
            $sifat = mysqli_real_escape_string($koneksi, $sifat_list[$i]);

            // Normalisasi otomatis
            $bobot_norm = round($bobot / $total_bobot, 4);

            $query = "UPDATE tabel_kriteria SET 
                        nama_kriteria = '$nama',
                        bobot_awal = '$bobot',
                        bobot_normalisasi = '$bobot_norm',
                        sifat_kriteria = '$sifat'
                      WHERE id_kriteria = '$id'";
            
            if (mysqli_query($koneksi, $query)) {
                $berhasil++;
            }
        }

        if ($berhasil > 0) {
            $pesan = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Berhasil memperbarui $berhasil kriteria. Bobot normalisasi otomatis dihitung ulang.</div>";
        } else {
            $pesan = "<div class='alert alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Gagal menyimpan perubahan.</div>";
        }
    }
}

// Ambil data kriteria
$data_kriteria = [];
$q = mysqli_query($koneksi, "SELECT * FROM tabel_kriteria ORDER BY id_kriteria ASC");
if ($q && mysqli_num_rows($q) > 0) {
    while ($row = mysqli_fetch_assoc($q)) {
        $data_kriteria[] = $row;
    }
} else {
    // Fallback jika tabel kosong
    $data_kriteria = [
        ['id_kriteria' => 'C1', 'nama_kriteria' => 'Akademik', 'bobot_awal' => 35, 'bobot_normalisasi' => 0.35, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C2', 'nama_kriteria' => 'Kehadiran', 'bobot_awal' => 15, 'bobot_normalisasi' => 0.15, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C3', 'nama_kriteria' => 'Prestasi', 'bobot_awal' => 25, 'bobot_normalisasi' => 0.25, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C4', 'nama_kriteria' => 'Ekskul', 'bobot_awal' => 15, 'bobot_normalisasi' => 0.15, 'sifat_kriteria' => 'benefit'],
        ['id_kriteria' => 'C5', 'nama_kriteria' => 'Keterlambatan', 'bobot_awal' => 10, 'bobot_normalisasi' => 0.10, 'sifat_kriteria' => 'cost']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Bobot Kriteria — SMK Puspita Bangsa</title>
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
        .navbar a:hover { background: #2563eb; }

        .container { padding: 30px; max-width: 1100px; margin: 0 auto; }
        .box { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; color: #1e3a8a; font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background-color: #f8fafc; color: #475569; font-weight: 600; padding: 12px; font-size: 12px; text-transform: uppercase; border-bottom: 2px solid #e2e8f0; }
        table td { padding: 12px; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
        
        input[type="text"], input[type="number"], select {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            font-weight: 600;
            color: #334155;
        }
        input:focus, select:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        
        .mono { font-family: 'JetBrains Mono', monospace; }
        .btn-save { background: #10b981; color: white; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: 0.2s; }
        .btn-save:hover { background: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); }

        .alert { padding: 14px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; text-align: center; font-size: 14px; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .info-box { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 14px 18px; border-radius: 10px; font-size: 13px; margin-bottom: 25px; line-height: 1.6; }
        .badge-benefit { background: #d1fae5; color: #065f46; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
        .badge-cost { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>SMK PUSPITA BANGSA — KELOLA BOBOT KRITERIA</h2>
    <a href="dashboard_guru.php"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
</div>

<div class="container">
    <?php echo $pesan; ?>

    <div class="info-box">
        <i class="fa-solid fa-circle-info"></i> 
        <strong>Petunjuk:</strong> Ubah <strong>Bobot Awal</strong> (dalam persen). Sistem akan otomatis menghitung ulang <strong>Bobot Normalisasi</strong> agar totalnya selalu 1. 
        Setelah menyimpan, silakan jalankan ulang <strong>Eksekusi SMART</strong> agar ranking terbaru menggunakan bobot baru.
    </div>

    <div class="box">
        <h3><i class="fa-solid fa-sliders"></i> Konfigurasi Matriks Pembobotan Kriteria</h3>
        
        <form action="" method="POST">
            <table>
                <thead>
                    <tr>
                        <th style="width: 90px;">Kode</th>
                        <th>Nama Kriteria</th>
                        <th style="width: 140px;">Bobot Awal (%)</th>
                        <th style="width: 150px;">Bobot Normalisasi</th>
                        <th style="width: 160px;">Sifat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data_kriteria as $k): ?>
                    <tr>
                        <td class="mono" style="font-weight:700; color:#1e3a8a;">
                            <?php echo $k['id_kriteria']; ?>
                            <input type="hidden" name="id_kriteria[]" value="<?php echo $k['id_kriteria']; ?>">
                        </td>
                        <td>
                            <input type="text" name="nama_kriteria[]" value="<?php echo htmlspecialchars($k['nama_kriteria']); ?>" required>
                        </td>
                        <td>
                            <input type="number" name="bobot_awal[]" value="<?php echo $k['bobot_awal']; ?>" step="0.01" min="0" max="100" required>
                        </td>
                        <td>
                            <span class="mono" style="background:#fef9c3; padding:6px 12px; border-radius:6px; font-weight:700; color:#a16207; border:1px solid #fef08a;">
                                <?php echo number_format($k['bobot_normalisasi'], 4); ?>
                            </span>
                        </td>
                        <td>
                            <select name="sifat_kriteria[]" required>
                                <option value="benefit" <?php echo $k['sifat_kriteria'] === 'benefit' ? 'selected' : ''; ?>>Benefit</option>
                                <option value="cost" <?php echo $k['sifat_kriteria'] === 'cost' ? 'selected' : ''; ?>>Cost</option>
                            </select>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div style="margin-top: 25px; text-align: right;">
                <button type="submit" name="simpan_kriteria" class="btn-save">
                    <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Bobot
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>