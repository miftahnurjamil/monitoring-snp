<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

$kode = get('kode');
if (!$kode) {
    setFlash('danger', 'Kode penilaian tidak ditemukan!');
    redirect('modules/penilaian-list.php');
}

// Get transaksi data
$transaksi = $db->query("
    SELECT t.*, 
           s.nama_sekolah, s.nama_kepala_sekolah, s.nip_kepala_sekolah, s.alamat, s.kecamatan,
           p.nama_lengkap as nama_pengawas, p.pangkat_golongan, p.jabatan
    FROM transaksi_penilaian t
    LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
    LEFT JOIN master_pengawas p ON t.pengawas_id = p.id
    WHERE t.kode_penilaian = '$kode'
")->fetch_assoc();

if (!$transaksi) {
    setFlash('danger', 'Data penilaian tidak ditemukan!');
    redirect('modules/penilaian-list.php');
}

$pageTitle = 'Detail Penilaian SNP';
require_once '../includes/header.php';

// Get rekapitulasi per SNP
$rekapitulasi = $db->query("
    SELECT r.*, snp.kode_snp, snp.nama_snp
    FROM rekapitulasi_snp r
    LEFT JOIN master_snp snp ON r.snp_id = snp.id
    WHERE r.transaksi_id = {$transaksi['id']}
    ORDER BY snp.urutan
")->fetch_all(MYSQLI_ASSOC);

// Get detail penilaian dengan tree structure (Aspek -> Indikator -> Sub Indikator)
$detailBySNP = [];
foreach ($rekapitulasi as $rekap) {
    $snp_id = $rekap['snp_id'];
    
    // Get aspek
    $dataAspek = $db->query("
        SELECT a.*
        FROM aspek_snp a
        WHERE a.snp_id = $snp_id AND a.is_active = 1
        ORDER BY a.urutan
    ")->fetch_all(MYSQLI_ASSOC);
    
    $treeData = [];
    foreach ($dataAspek as $aspek) {
        // Get indikators for this aspek
        $aspek['indikators'] = $db->query("
            SELECT p.*
            FROM pertanyaan_snp p
            WHERE p.aspek_id = {$aspek['id']} AND p.is_active = 1
            ORDER BY p.urutan
        ")->fetch_all(MYSQLI_ASSOC);
        
        // Get sub-indikators and scores for each indikator
        foreach ($aspek['indikators'] as &$indikator) {
            // Get sub indikators
            $indikator['sub_indikators'] = $db->query("
                SELECT sp.*, 
                       d.skor,
                       IFNULL(sp.skor_maksimal, 4) as skor_max
                FROM sub_pertanyaan sp
                LEFT JOIN detail_penilaian d ON sp.id = d.sub_pertanyaan_id 
                    AND d.transaksi_id = {$transaksi['id']} 
                    AND d.pertanyaan_id = {$indikator['id']}
                WHERE sp.pertanyaan_id = {$indikator['id']} AND sp.is_active = 1
                ORDER BY sp.urutan
            ")->fetch_all(MYSQLI_ASSOC);
            
            // Get score for indikator tanpa sub
            if (empty($indikator['sub_indikators'])) {
                $scoreResult = $db->query("
                    SELECT skor FROM detail_penilaian 
                    WHERE transaksi_id = {$transaksi['id']} 
                    AND pertanyaan_id = {$indikator['id']}
                    AND sub_pertanyaan_id IS NULL
                    LIMIT 1
                ");
                $indikator['skor'] = $scoreResult && $scoreResult->num_rows > 0 ? $scoreResult->fetch_assoc()['skor'] : 0;
            }
        }
        unset($indikator);
        
        $treeData[] = $aspek;
    }
    
    if (!empty($treeData)) {
        $detailBySNP[$snp_id] = [
            'kode_snp' => $rekap['kode_snp'],
            'nama_snp' => $rekap['nama_snp'],
            'tree_data' => $treeData
        ];
    }
}

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

<style>
    .detail-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    .score-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .score-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 8px solid;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: bold;
        margin: 0 auto;
    }
    
    /* Tree View Styles for Detail */
    .detail-aspek-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        margin-bottom: 15px;
        font-weight: bold;
    }
    
    .detail-indikator {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 10px 15px;
        border-radius: 6px;
        margin-left: 20px;
        margin-bottom: 10px;
        font-size: 0.95rem;
    }
    
    .detail-sub-list {
        margin-left: 40px;
        margin-bottom: 15px;
    }
    
    .detail-sub-item {
        background: white;
        border-left: 4px solid #4facfe;
        padding: 12px 15px;
        margin-bottom: 8px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .detail-sub-item .kode-sub {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 2px 10px;
        border-radius: 4px;
        font-weight: bold;
        margin-right: 8px;
    }
    
    .detail-indikator-tunggal {
        background: white;
        border-left: 4px solid #f093fb;
        padding: 12px 15px;
        margin-left: 20px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .skor-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background: white !important;
        }
    }
</style>

<div class="no-print">
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2><i class="bi bi-file-earmark-text"></i> <?php echo $pageTitle; ?></h2>
                <p class="text-muted mb-0"><?php echo $transaksi['kode_penilaian']; ?></p>
            </div>
            <div>
                <a href="<?php echo BASE_URL; ?>modules/laporan-pdf.php?kode=<?php echo $kode; ?>" class="btn btn-danger" target="_blank">
                    <i class="bi bi-file-pdf"></i> Export PDF
                </a>
                <button onclick="window.print()" class="btn btn-info">
                    <i class="bi bi-printer"></i> Print
                </button>
                <a href="<?php echo BASE_URL; ?>modules/penilaian-list.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
        
        <!-- Edit Penilaian per SNP -->
        <div class="alert alert-info mt-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-pencil-square"></i>
                <strong>Edit Penilaian:</strong> Pilih SNP yang ingin diedit dari tabel di bawah
            </div>
        </div>
        </div>
    </div>
</div>

<!-- Header Info -->
<div class="detail-header">
    <div class="row">
        <div class="col-md-8">
            <h4 class="mb-3">DATA UTAMA</h4>
            <table class="table table-borderless text-white">
                <tr>
                    <td width="180"><strong>Nama Sekolah</strong></td>
                    <td>: <strong><?php echo $transaksi['nama_sekolah']; ?></strong></td>
                </tr>
                <tr>
                    <td><strong>Alamat Sekolah</strong></td>
                    <td>: <?php echo $transaksi['alamat']; ?></td>
                </tr>
                <tr>
                    <td><strong>Kepala Sekolah</strong></td>
                    <td>: <?php echo $transaksi['nama_kepala_sekolah']; ?></td>
                </tr>
                <tr>
                    <td><strong>NIP</strong></td>
                    <td>: <?php echo $transaksi['nip_kepala_sekolah']; ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-4">
            <h4 class="mb-3">INFORMASI PENILAIAN</h4>
            <table class="table table-borderless text-white">
                <tr>
                    <td width="120"><strong>Tahun Ajaran</strong></td>
                    <td>: <?php echo $transaksi['tahun_ajaran']; ?></td>
                </tr>
                <tr>
                    <td><strong>Semester</strong></td>
                    <td>: <?php echo $transaksi['semester']; ?></td>
                </tr>
                <tr>
                    <td><strong>Tanggal</strong></td>
                    <td>: <?php echo formatTanggal($transaksi['tanggal_penilaian']); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>: <span class="badge bg-light text-dark"><?php echo strtoupper($transaksi['status']); ?></span></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Rekapitulasi Per SNP -->
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Rekapitulasi Per SNP</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Standar Nasional Pendidikan</th>
                        <th width="150" class="text-center">Skor Perolehan</th>
                        <th width="150" class="text-center">Skor Maksimal</th>
                        <th width="120" class="text-center">Nilai</th>
                        <th width="150">Kategori</th>
                        <th width="120" class="text-center no-print">Aksi</th>
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
                        <td class="text-center"><strong class="text-primary"><?php echo number_format($r['nilai'], 2); ?></strong></td>
                        <td>
                            <?php
                            $badgeColor = 'secondary';
                            if ($r['nilai'] >= 91) $badgeColor = 'success';
                            elseif ($r['nilai'] >= 86) $badgeColor = 'info';
                            elseif ($r['nilai'] >= 71) $badgeColor = 'primary';
                            elseif ($r['nilai'] >= 55) $badgeColor = 'warning';
                            else $badgeColor = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $badgeColor; ?>"><?php echo $r['kategori']; ?></span>
                        </td>
                        <td class="text-center no-print">
                            <a href="<?php echo BASE_URL; ?>modules/penilaian-form.php?id=<?php echo $transaksi['id']; ?>&snp=<?php echo $r['snp_id']; ?>" 
                               class="btn btn-sm btn-warning" 
                               title="Edit Penilaian SNP">
                                <i class="bi bi-pencil-square"></i> Edit
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($rekapitulasi)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p>Belum ada data rekapitulasi</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($rekapitulasi)): ?>
                <tfoot class="table-secondary">
                    <tr>
                        <th colspan="2" class="text-end">TOTAL</th>
                        <th class="text-center"><?php echo $total_perolehan; ?></th>
                        <th class="text-center"><?php echo $total_maksimal; ?></th>
                        <th class="text-center"><?php echo number_format($nilai_akhir, 2); ?></th>
                        <th>
                            <?php
                            $badgeColor = 'secondary';
                            if ($nilai_akhir >= 91) $badgeColor = 'success';
                            elseif ($nilai_akhir >= 86) $badgeColor = 'info';
                            elseif ($nilai_akhir >= 71) $badgeColor = 'primary';
                            elseif ($nilai_akhir >= 55) $badgeColor = 'warning';
                            else $badgeColor = 'danger';
                            ?>
                            <span class="badge bg-<?php echo $badgeColor; ?>"><?php echo $kategori_akhir; ?></span>
                        </th>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>

<!-- Detail Penilaian Per SNP (Tree View) -->
<?php if (!empty($detailBySNP)): ?>
<div class="card mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-diagram-3-fill"></i> Detail Skor Per Indikator & Sub-Indikator</h5>
    </div>
    <div class="card-body">
        <?php foreach ($detailBySNP as $snp_id => $snpData): ?>
        <div class="mb-5">
            <h4 class="text-white p-3 rounded mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="bi bi-bookmark-star-fill"></i> <?php echo $snpData['kode_snp']; ?> - <?php echo $snpData['nama_snp']; ?>
            </h4>
            
            <?php foreach ($snpData['tree_data'] as $aspek): ?>
            <div class="mb-4">
                <!-- Aspek Header -->
                <div class="detail-aspek-header">
                    <i class="bi bi-bookmark-fill"></i>
                    <strong><?php echo $aspek['kode_aspek']; ?>. <?php echo $aspek['nama_aspek']; ?></strong>
                    <span class="badge bg-light text-dark ms-2">
                        <?php echo count($aspek['indikators']); ?> Indikator
                    </span>
                </div>
                
                <?php foreach ($aspek['indikators'] as $indikator): ?>
                    <?php if (!empty($indikator['sub_indikators'])): ?>
                    <!-- Indikator dengan Sub -->
                    <div class="detail-indikator">
                        <i class="bi bi-list-check"></i>
                        <strong><?php echo $indikator['nomor_pertanyaan']; ?>.</strong>
                        <?php echo $indikator['pertanyaan']; ?>
                        <span class="badge bg-light text-dark ms-2">
                            <?php echo count($indikator['sub_indikators']); ?> Sub
                        </span>
                    </div>
                    
                    <div class="detail-sub-list">
                        <?php foreach ($indikator['sub_indikators'] as $sub): ?>
                        <div class="detail-sub-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="flex-grow-1">
                                    <span class="kode-sub"><?php echo $sub['kode_sub']; ?></span>
                                    <span><?php echo $sub['sub_pertanyaan']; ?></span>
                                </div>
                                <div>
                                    <span class="skor-badge"><?php echo isset($sub['skor']) ? $sub['skor'] : 0; ?></span>
                                    <small class="text-muted ms-2">/ <?php echo $sub['skor_max']; ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php else: ?>
                    <!-- Indikator Tunggal (tanpa sub) -->
                    <div class="detail-indikator-tunggal">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1">
                                <strong style="color: #f5576c;">
                                    <i class="bi bi-check-circle"></i>
                                    <?php echo $indikator['nomor_pertanyaan']; ?>. <?php echo $indikator['pertanyaan']; ?>
                                </strong>
                            </div>
                            <div>
                                <span class="skor-badge"><?php echo isset($indikator['skor']) ? $indikator['skor'] : 0; ?></span>
                                <small class="text-muted ms-2">/ 4</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
            
            <hr class="my-4">
        </div>
        <?php endforeach; ?>
        
        <!-- Keterangan -->
        <div class="alert alert-light border mt-4">
            <strong><i class="bi bi-info-circle"></i> Keterangan Skor:</strong><br>
            <div class="row mt-2">
                <div class="col-md-6">
                    0 = Tidak ada / Tidak dilaksanakan<br>
                    1 = Ada tapi tidak lengkap / Belum maksimal<br>
                    2 = Ada dan cukup lengkap / Sedang
                </div>
                <div class="col-md-6">
                    3 = Ada dan lengkap / Baik<br>
                    4 = Ada, lengkap dan berkualitas / Sangat Baik
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Keterangan Kategori -->
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Keterangan Kategori Nilai</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td width="150">91 - 100</td>
                        <td>:</td>
                        <td><span class="badge bg-success">A (Amat Baik)</span></td>
                    </tr>
                    <tr>
                        <td>86 - 90</td>
                        <td>:</td>
                        <td><span class="badge bg-info">B (Baik)</span></td>
                    </tr>
                    <tr>
                        <td>71 - 85</td>
                        <td>:</td>
                        <td><span class="badge bg-primary">C (Cukup)</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td width="150">55 - 70</td>
                        <td>:</td>
                        <td><span class="badge bg-warning">D (Sedang)</span></td>
                    </tr>
                    <tr>
                        <td>< 55</td>
                        <td>:</td>
                        <td><span class="badge bg-danger">E (Kurang)</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
