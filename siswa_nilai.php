<?php
// siswa_nilai.php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] !== "login_admin") {
    header("Location: login.php");
    exit;
}

$pesan = "";

// ==========================================
// 1. PROSES IMPOR MASSAL EXCEL (.CSV)
// ==========================================
if (isset($_POST['import_csv'])) {
    $file = $_FILES['file_csv']['tmp_name'];
    
    if (empty($file)) {
        $pesan = "<div class='alert alert-danger'><i class='fa-solid fa-circle-exclamation'></i> Gagal! Silakan pilih file CSV terlebih dahulu.</div>";
    } else {
        $file_test = fopen($file, "r");
        $first_line = fgets($file_test);
        fclose($file_test);
        
        $jumlah_koma = substr_count($first_line, ',');
        $jumlah_titik_koma = substr_count($first_line, ';');
        
        $delimiter = ",";
        if ($jumlah_titik_koma > $jumlah_koma) {
            $delimiter = ";";
        }

        $handle = fopen($file, "r");
        $baris = 0;
        $sukses_import = 0;

        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");
        mysqli_query($koneksi, "TRUNCATE TABLE tabel_hasil");
        mysqli_query($koneksi, "TRUNCATE TABLE tabel_nilai");
        mysqli_query($koneksi, "TRUNCATE TABLE tabel_siswa");
        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");

        while (($data = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
            $baris++;
            if ($baris == 1) continue; 

            if (isset($data[0]) && !empty(trim($data[0]))) {
                $nis        = mysqli_real_escape_string($koneksi, trim($data[0]));
                $nama_siswa = mysqli_real_escape_string($koneksi, trim($data[1]));
                $jurusan    = mysqli_real_escape_string($koneksi, trim($data[2]));
                $kelas      = mysqli_real_escape_string($koneksi, trim($data[3]));
                
                $fix_c1 = isset($data[4]) && trim($data[4]) !== '' ? str_replace(',', '.', trim($data[4])) : 0;
                $fix_c2 = isset($data[5]) && trim($data[5]) !== '' ? str_replace(',', '.', trim($data[5])) : 0;
                $fix_c3 = isset($data[6]) && trim($data[6]) !== '' ? str_replace(',', '.', trim($data[6])) : 0;
                $fix_c4 = isset($data[7]) && trim($data[7]) !== '' ? str_replace(',', '.', trim($data[7])) : 0;
                $fix_c5 = isset($data[8]) && trim($data[8]) !== '' ? str_replace(',', '.', trim($data[8])) : 0;

                $c1_akademik     = mysqli_real_escape_string($koneksi, $fix_c1); 
                $c2_kehadiran    = mysqli_real_escape_string($koneksi, $fix_c2); 
                $c3_prestasi     = mysqli_real_escape_string($koneksi, $fix_c3); 
                $c4_ekskul       = mysqli_real_escape_string($koneksi, $fix_c4); 
                $c5_keterlambatan = mysqli_real_escape_string($koneksi, $fix_c5); 

                mysqli_query($koneksi, "INSERT IGNORE INTO tabel_siswa (nis, nama_siswa, jurusan, kelas) VALUES ('$nis', '$nama_siswa', '$jurusan', '$kelas')");

                $query_nilai = "INSERT INTO tabel_nilai (nis, c1_akademik, c2_kehadiran, c3_prestasi, c4_ekskul, c5_keterlambatan) 
                                VALUES ('$nis', '$c1_akademik', '$c2_kehadiran', '$c3_prestasi', '$c4_ekskul', '$c5_keterlambatan')";
                mysqli_query($koneksi, $query_nilai);
                $sukses_import++;
            }
        }
        fclose($handle);
        $pesan = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Impor sukses! Berhasil memproses $sukses_import data via Excel.</div>";
    }
}

// ==========================================
// 2. PROSES INPUT TERPADU
// ==========================================
if (isset($_POST['simpan_terpadu'])) {
    $nis        = mysqli_real_escape_string($koneksi, $_POST['nis']);
    $nama_siswa = mysqli_real_escape_string($koneksi, $_POST['nama_siswa']);
    $jurusan    = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $kelas      = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    
    $c1 = (isset($_POST['c1']) && trim($_POST['c1']) !== '') ? mysqli_real_escape_string($koneksi, str_replace(',', '.', $_POST['c1'])) : 0;
    $c2 = (isset($_POST['c2']) && trim($_POST['c2']) !== '') ? mysqli_real_escape_string($koneksi, str_replace(',', '.', $_POST['c2'])) : 0;
    $c3 = (isset($_POST['c3']) && trim($_POST['c3']) !== '') ? mysqli_real_escape_string($koneksi, str_replace(',', '.', $_POST['c3'])) : 0;
    $c4 = (isset($_POST['c4']) && trim($_POST['c4']) !== '') ? mysqli_real_escape_string($koneksi, str_replace(',', '.', $_POST['c4'])) : 0;
    $c5 = (isset($_POST['c5']) && trim($_POST['c5']) !== '') ? mysqli_real_escape_string($koneksi, str_replace(',', '.', $_POST['c5'])) : 0;

    mysqli_query($koneksi, "INSERT INTO tabel_siswa (nis, nama_siswa, jurusan, kelas) VALUES ('$nis', '$nama_siswa', '$jurusan', '$kelas') ON DUPLICATE KEY UPDATE nama_siswa='$nama_siswa', jurusan='$jurusan', kelas='$kelas'");
    mysqli_query($koneksi, "INSERT INTO tabel_nilai (nis, c1_akademik, c2_kehadiran, c3_prestasi, c4_ekskul, c5_keterlambatan) VALUES ('$nis', '$c1', '$c2', '$c3', '$c4', '$c5') ON DUPLICATE KEY UPDATE c1_akademik='$c1', c2_kehadiran='$c2', c3_prestasi='$c3', c4_ekskul='$c4', c5_keterlambatan='$c5'");
    
    $pesan = "<div class='alert alert-success'><i class='fa-solid fa-circle-check'></i> Data Terpadu Berhasil Disimpan! Nilai kosong otomatis diset 0.</div>";
}

// ==========================================
// 3. PROSES HAPUS SISWA
// ==========================================
if (isset($_GET['hapus'])) {
    $nis_hapus = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    if (mysqli_query($koneksi, "DELETE FROM tabel_siswa WHERE nis = '$nis_hapus'")) {
        $pesan = "<div class='alert alert-success'><i class='fa-solid fa-trash-can'></i> Sukses menghapus data siswa dengan NIS $nis_hapus.</div>";
    }
}

// ==========================================
// 4. PROSES KOSONGKAN DATABASE SISWA
// ==========================================
if (isset($_POST['kosongkan_semua'])) {
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 0");
    mysqli_query($koneksi, "TRUNCATE TABLE tabel_hasil");
    mysqli_query($koneksi, "TRUNCATE TABLE tabel_nilai");
    mysqli_query($koneksi, "TRUNCATE TABLE tabel_siswa");
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS = 1");
    $pesan = "<div class='alert alert-success'><i class='fa-solid fa-broom'></i> Database berhasil dibersihkan secara total.</div>";
}

$query_tampil = "SELECT tabel_siswa.*, tabel_nilai.c1_akademik, tabel_nilai.c2_kehadiran, tabel_nilai.c3_prestasi, tabel_nilai.c4_ekskul, tabel_nilai.c5_keterlambatan 
                 FROM tabel_siswa 
                 LEFT JOIN tabel_nilai ON tabel_siswa.nis = tabel_nilai.nis
                 ORDER BY tabel_siswa.nama_siswa ASC";
$result_tampil = mysqli_query($koneksi, $query_tampil);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hub Kendali Data Terpadu — SMK Puspita Bangsa</title>
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
        
        .container { padding: 30px; max-width: 1400px; margin: 0 auto; }
        .grid-layout { display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start; }
        .box { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; }
        h3 { margin-top: 0; border-bottom: 2px solid #f1f5f9; padding-bottom: 12px; color: #1e3a8a; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; color: #475569; }
        input[type="text"], select, input[type="number"], input[type="file"] { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 14px; outline: none; color: #334155; }
        input:focus, select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15); }
        
        .row-kriteria { display: grid; grid-template-columns: repeat(5, 1fr); gap: 6px; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; }
        .row-kriteria label { font-size: 10px; text-align: center; font-weight: 700; color: #64748b; }
        .row-kriteria input { padding: 6px; text-align: center; font-size: 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 700; }
        
        button { background: #10b981; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; font-weight: 600; width: 100%; font-size: 14px; transition: 0.2s; }
        button:hover { background: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2); }
        .btn-blue { background: #3b82f6; }
        .btn-blue:hover { background: #2563eb; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2); }
        .btn-red { background: #ef4444; }
        .btn-red:hover { background: #dc2626; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); }
        
        table { width: 100%; border-collapse: collapse; }
        table th { background-color: #f8fafc; color: #475569; font-weight: 600; padding: 14px; font-size: 12px; border-bottom: 2px solid #e2e8f0; text-transform: uppercase; letter-spacing: 0.5px; }
        table td { border-bottom: 1px solid #edf2f7; padding: 14px; text-align: center; font-size: 14px; color: #334155; }
        table tr:hover td { background-color: #f8fafc; }
        .mono { font-family: 'JetBrains Mono', monospace; }
        
        .alert { padding: 14px; border-radius: 10px; margin-bottom: 25px; font-weight: 600; text-align: center; font-size: 14px; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-danger { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        
        .btn-ui { padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; border: 1px solid #bfdbfe; background: #eff6ff; color: #2563eb; cursor: pointer; }
        .btn-ui:hover { background: #2563eb; color: white; }
        .btn-ui.b-del { background: #fef2f2; color: #dc2626; border-color: #fca5a5; margin-left: 4px; }
        .btn-ui.b-del:hover { background: #dc2626; color: white; }
    </style>
</head>
<body>

<div class="navbar">
    <h2>SMK PUSPITA BANGSA — HUB DATA & NILAI TERPADU</h2>
    <a href="dashboard_guru.php"><i class="fa-solid fa-arrow-left"></i> Dashboard Beranda</a>
</div>

<div class="container">
    <?php echo $pesan; ?>

    <div class="grid-layout">
        <div>
            <div class="box" style="margin-bottom: 25px; background: #fffdf5; border-color: #fef3c7;">
                <h3><i class="fa-solid fa-file-excel" style="color:#10b981;"></i> Impor Massal Excel</h3>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="file_csv" accept=".csv" required>
                    </div>
                    <button type="submit" name="import_csv" class="btn-blue"><i class="fa-solid fa-bolt"></i> Generate Data</button>
                </form>
            </div>

            <div class="box" style="margin-bottom: 25px;">
                <h3><i class="fa-solid fa-user-pen" style="color:#3b82f6;"></i> Input Terpadu</h3>
                <form action="" method="POST" id="form_terpadu">
                    <div class="form-group"><label>NIS Siswa</label><input type="text" name="nis" id="form_nis" required class="mono"></div>
                    <div class="form-group"><label>Nama Lengkap</label><input type="text" name="nama_siswa" id="form_nama" required></div>
                    <div class="form-group">
                        <label>Jurusan</label>
                        <select name="jurusan" id="form_jurusan" required>
                            <option value="RPL">RPL</option>
                            <option value="BDP">BDP</option>
                            <option value="OTKP">OTKP</option>
                            <option value="PH">PH</option>
                            <option value="AKL">AKL</option>
                        </select>
                    </div>
                    <input type="hidden" name="kelas" value="XII">
                    
                    <label style="color: #1e3a8a; margin-top:20px; display:block; font-weight:700;">Parameter Kriteria (Opsional):</label>
                    <div class="row-kriteria">
                        <div><label>Akademik</label><input type="number" step="0.01" name="c1" id="form_c1" min="0" max="100"></div>
                        <div><label>Kehadiran</label><input type="number" step="0.01" name="c2" id="form_c2" min="0" max="100"></div>
                        <div><label>Prestasi</label><input type="number" step="0.01" name="c3" id="form_c3" min="0" max="100"></div>
                        <div><label>Ekskul</label><input type="number" step="0.01" name="c4" id="form_c4" min="0" max="100"></div>
                        <div><label style="color:#dc2626;">Telat (x)</label><input type="number" name="c5" id="form_c5" min="0"></div>
                    </div>
                    <button type="submit" name="simpan_terpadu" style="margin-top: 20px;"><i class="fa-solid fa-floppy-disk"></i> Simpan Data</button>
                </form>
            </div>

            <div class="box" style="background: #fef2f2; border-color: #fca5a5;">
                <h3><i class="fa-solid fa-triangle-exclamation" style="color:#ef4444;"></i> Area Bahaya</h3>
                <form action="" method="POST" onsubmit="return confirm('PERINGATAN! Ini akan menghapus seluruh data siswa di database. Anda yakin?')">
                    <button type="submit" name="kosongkan_semua" class="btn-red"><i class="fa-solid fa-trash-arrow-up"></i> Kosongkan Database</button>
                </form>
            </div>
        </div>

        <div class="box">
            <h3><i class="fa-solid fa-table-list"></i> Rekapitulasi Profil & Kriteria Nilai Mentah Siswa</h3>
            <table>
                <thead>
                    <tr>
                        <th>NIS</th><th>Nama Siswa</th><th>Jurusan</th><th>C1</th><th>C2</th><th>C3</th><th>C4</th><th>C5</th><th style="width:140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_tampil) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result_tampil)): ?>
                        <tr>
                            <td class="mono"><code><?php echo $row['nis']; ?></code></td>
                            <td style="text-align: left; font-weight: 600; color:#334155;"><?php echo $row['nama_siswa']; ?></td>
                            <td><strong style="color:#2563eb;"><?php echo $row['jurusan']; ?></strong></td>
                            <td class="mono"><?php echo $row['c1_akademik'] ?? '0'; ?></td>
                            <td class="mono"><?php echo $row['c2_kehadiran'] ? $row['c2_kehadiran'].'%' : '0%'; ?></td>
                            <td class="mono"><?php echo $row['c3_prestasi'] ?? '0'; ?></td>
                            <td class="mono"><?php echo $row['c4_ekskul'] ?? '0'; ?></td>
                            <td class="mono" style="color: <?php echo ($row['c5_keterlambatan'] ?? 0) > 0 ? '#ef4444' : '#10b981'; ?>; font-weight:bold;">
                                <?php echo $row['c5_keterlambatan'] ? $row['c5_keterlambatan'].'x' : '0x'; ?>
                            </td>
                            <td>
                                <a href="#" class="btn-ui" onclick="tarikKeForm('<?php echo $row['nis']; ?>', '<?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>', '<?php echo $row['jurusan']; ?>', '<?php echo $row['c1_akademik']; ?>', '<?php echo $row['c2_kehadiran']; ?>', '<?php echo $row['c3_prestasi']; ?>', '<?php echo $row['c4_ekskul']; ?>', '<?php echo $row['c5_keterlambatan']; ?>')"><i class="fa-solid fa-user-pen"></i> Tarik</a>
                                <a href="siswa_nilai.php?hapus=<?php echo $row['nis']; ?>" class="btn-ui b-del" onclick="return confirm('Hapus siswa <?php echo htmlspecialchars($row['nama_siswa'], ENT_QUOTES); ?>?')"><i class="fa-solid fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" style="color: #64748b; padding: 30px;">Belum ada data siswa. Silakan pakai panel impor data.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function tarikKeForm(nis, nama, jurusan, c1, c2, c3, c4, c5) {
    document.getElementById('form_nis').value = nis;
    document.getElementById('form_nama').value = nama;
    document.getElementById('form_jurusan').value = jurusan;
    document.getElementById('form_c1').value = c1 ? c1 : '';
    document.getElementById('form_c2').value = c2 ? c2 : '';
    document.getElementById('form_c3').value = c3 ? c3 : '';
    document.getElementById('form_c4').value = c4 ? c4 : '';
    document.getElementById('form_c5').value = c5 ? c5 : '';
    document.getElementById('form_nama').focus();
}
</script>
</body>
</html>