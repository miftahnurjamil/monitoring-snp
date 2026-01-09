<?php
require_once '../config/config.php';

$db = Database::getInstance();

$kode = get('kode');
if (!$kode) {
    die('Kode penilaian tidak ditemukan!');
}

// Get transaksi data
$transaksi = $db->query("
    SELECT t.*, 
           s.nama_sekolah, s.nama_kepala_sekolah, s.nip_kepala_sekolah, s.alamat, s.kecamatan,
           p.nama_lengkap as nama_penilik, p.pangkat_golongan, p.jabatan
    FROM transaksi_penilaian t
    LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
    LEFT JOIN master_penilik p ON t.penilik_id = p.id
    WHERE t.kode_penilaian = '$kode'
")->fetch_assoc();

if (!$transaksi) {
    die('Data penilaian tidak ditemukan!');
}

// Get rekapitulasi per SNP
$rekapitulasi = $db->query("
    SELECT r.*, snp.kode_snp, snp.nama_snp
    FROM rekapitulasi_snp r
    LEFT JOIN master_snp snp ON r.snp_id = snp.id
    WHERE r.transaksi_id = {$transaksi['id']}
    ORDER BY snp.urutan
")->fetch_all(MYSQLI_ASSOC);

// Calculate total
$total_perolehan = 0;
$total_maksimal = 0;
foreach ($rekapitulasi as $r) {
    $total_perolehan += $r['total_skor_perolehan'];
    $total_maksimal += $r['total_skor_maksimal'];
}
$nilai_akhir = $total_maksimal > 0 ? ($total_perolehan / $total_maksimal) * 100 : 0;
$kategori_akhir = getKategoriNilai($nilai_akhir);
?>
$nilai_akhir = $total_maksimal > 0 ? ($total_perolehan / $total_maksimal) * 100 : 0;
$kategori_akhir = getKategoriNilai($nilai_akhir);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan SNP - <?php echo $kode; ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            line-height: 1.6;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .header h2 {
            margin: 5px 0;
            color: #333;
        }
        
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        
        .info-table td {
            padding: 5px;
            vertical-align: top;
        }
        
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        table.data th,
        table.data td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        table.data th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .footer {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        
        .signature {
            margin-top: 60px;
        }
        
        .kategori {
            background: #f0f0f0;
            padding: 10px;
            margin: 20px 0;
        }
        
        .btn-print {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .btn-print:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="btn-print no-print">
        🖨️ Cetak / Download PDF
    </button>
    
    <!-- Header -->
    <div class="header">
        <h2>LAPORAN HASIL PENILAIAN</h2>
        <h2>STANDAR NASIONAL PENDIDIKAN (SNP)</h2>
        <p>Kode Penilaian: <strong><?php echo $transaksi['kode_penilaian']; ?></strong></p>
    </div>
    
    <!-- Data Utama -->
    <table class="info-table">
        <tr>
            <td width="150"><strong>Nama Sekolah</strong></td>
            <td width="10">:</td>
            <td><strong><?php echo $transaksi['nama_sekolah']; ?></strong></td>
        </tr>
        <tr>
            <td><strong>Alamat Sekolah</strong></td>
            <td>:</td>
            <td><?php echo $transaksi['alamat']; ?></td>
        </tr>
        <tr>
            <td><strong>Kepala Sekolah</strong></td>
            <td>:</td>
            <td><?php echo $transaksi['nama_kepala_sekolah']; ?></td>
        </tr>
        <tr>
            <td><strong>NIP</strong></td>
            <td>:</td>
            <td><?php echo $transaksi['nip_kepala_sekolah']; ?></td>
        </tr>
        <tr>
            <td><strong>Tahun Ajaran</strong></td>
            <td>:</td>
            <td><?php echo $transaksi['tahun_ajaran']; ?></td>
        </tr>
        <tr>
            <td><strong>Semester</strong></td>
            <td>:</td>
            <td><?php echo $transaksi['semester']; ?></td>
        </tr>
        <tr>
            <td><strong>Tanggal Penilaian</strong></td>
            <td>:</td>
            <td><?php echo formatTanggal($transaksi['tanggal_penilaian']); ?></td>
        </tr>
    </table>
    
    <!-- Rekapitulasi -->
    <h3 style="margin-top: 30px;">REKAPITULASI PENILAIAN PER SNP</h3>
    <table class="data">
        <thead>
            <tr>
                <th width="30" class="text-center">No</th>
                <th>Standar Nasional Pendidikan</th>
                <th width="80" class="text-center">Skor Perolehan</th>
                <th width="80" class="text-center">Skor Maksimal</th>
                <th width="70" class="text-center">Nilai</th>
                <th width="120">Kategori</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($rekapitulasi as $r): 
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td>
                    <strong><?php echo $r['kode_snp']; ?></strong><br>
                    <small><?php echo $r['nama_snp']; ?></small>
                </td>
                <td class="text-center"><strong><?php echo $r['total_skor_perolehan']; ?></strong></td>
                <td class="text-center"><?php echo $r['total_skor_maksimal']; ?></td>
                <td class="text-center"><strong><?php echo number_format($r['nilai'], 2); ?></strong></td>
                <td><?php echo $r['kategori']; ?></td>
            </tr>
            <?php endforeach; ?>
            
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="2" class="text-right"><strong>TOTAL</strong></td>
                <td class="text-center"><?php echo $total_perolehan; ?></td>
                <td class="text-center"><?php echo $total_maksimal; ?></td>
                <td class="text-center"><?php echo number_format($nilai_akhir, 2); ?></td>
                <td><?php echo $kategori_akhir; ?></td>
            </tr>
        </tbody>
    </table>
    
    <!-- Keterangan -->
    <div class="kategori">
        <h4>Keterangan Kategori Nilai:</h4>
        <table style="width: 100%;">
            <tr>
                <td width="50%">91 - 100 : A (Amat Baik)</td>
                <td>55 - 70 : D (Sedang)</td>
            </tr>
            <tr>
                <td>86 - 90 : B (Baik)</td>
                <td>< 55 : E (Kurang)</td>
            </tr>
            <tr>
                <td>71 - 85 : C (Cukup)</td>
                <td></td>
            </tr>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <table style="width: 100%; margin-top: 40px;">
            <tr>
                <td width="50%">
                    <div class="signature">
                        <p>Kepala Sekolah,</p>
                        <br><br><br>
                        <p><strong><?php echo $transaksi['nama_kepala_sekolah']; ?></strong><br>
                        NIP. <?php echo $transaksi['nip_kepala_sekolah']; ?></p>
                    </div>
                </td>
                <td width="50%" class="text-center">
                    <div class="signature">
                        <p><?php echo $transaksi['kecamatan']; ?>, <?php echo formatTanggal(date('Y-m-d')); ?></p>
                        <p>Penilik,</p>
                        <br><br><br>
                        <p><strong><?php echo $transaksi['nama_penilik'] ?? '............................'; ?></strong></p>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div style="text-align: center; margin-top: 30px; font-size: 10px; color: #666;">
        Dicetak pada: <?php echo date('d-m-Y H:i:s'); ?> | <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?>
    </div>
    
    <script>
        // Auto print dialog on load (optional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
