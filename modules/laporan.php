<?php
require_once '../includes/auth.php';

$db = Database::getInstance();
$pageTitle = 'Laporan Penilaian SNP';

// Filter variables
$sekolah_filter = get('sekolah_id', '');
$tahun_filter = get('tahun_ajaran', '');
$status_filter = get('status', '');
$bulan_filter = get('bulan', '');

// Build WHERE clause
$where = [];
if ($sekolah_filter) {
    $where[] = "t.sekolah_id = " . (int)$sekolah_filter;
}
if ($tahun_filter) {
    $where[] = "t.tahun_ajaran = '" . $db->escape($tahun_filter) . "'";
}
if ($status_filter) {
    $where[] = "t.status = '" . $db->escape($status_filter) . "'";
}
if ($bulan_filter) {
    $where[] = "DATE_FORMAT(t.tanggal_penilaian, '%Y-%m') = '" . $db->escape($bulan_filter) . "'";
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get laporan data
$laporan = $db->query("
    SELECT 
        t.id,
        t.kode_penilaian,
        t.tahun_ajaran,
        t.tanggal_penilaian,
        t.status,
        s.nama_sekolah,
        s.jenis_sekolah,
        p.nama_lengkap as nama_penilik,
        (SELECT SUM(total_skor_perolehan) FROM rekapitulasi_snp WHERE transaksi_id = t.id) as total_perolehan,
        (SELECT SUM(total_skor_maksimal) FROM rekapitulasi_snp WHERE transaksi_id = t.id) as total_maksimal
    FROM transaksi_penilaian t
    LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
    LEFT JOIN master_penilik p ON t.penilik_id = p.id
    $whereClause
    ORDER BY t.tanggal_penilaian DESC
")->fetch_all(MYSQLI_ASSOC);

// Get filter options
$sekolahList = $db->query("SELECT id, nama_sekolah FROM master_sekolah WHERE is_active = 1 ORDER BY nama_sekolah")->fetch_all(MYSQLI_ASSOC);
$tahunList = $db->query("SELECT DISTINCT tahun_ajaran FROM transaksi_penilaian ORDER BY tahun_ajaran DESC")->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-file-earmark-bar-graph"></i> <?php echo $pageTitle; ?></h2>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Laporan</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Sekolah</label>
                <select class="form-select" name="sekolah_id">
                    <option value="">-- Semua Sekolah --</option>
                    <?php foreach ($sekolahList as $sekolah): ?>
                    <option value="<?php echo $sekolah['id']; ?>" <?php echo $sekolah_filter == $sekolah['id'] ? 'selected' : ''; ?>>
                        <?php echo $sekolah['nama_sekolah']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Tahun Ajaran</label>
                <select class="form-select" name="tahun_ajaran">
                    <option value="">-- Semua --</option>
                    <?php foreach ($tahunList as $tahun): ?>
                    <option value="<?php echo $tahun['tahun_ajaran']; ?>" <?php echo $tahun_filter == $tahun['tahun_ajaran'] ? 'selected' : ''; ?>>
                        <?php echo $tahun['tahun_ajaran']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">-- Semua --</option>
                    <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                    <option value="submitted" <?php echo $status_filter == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="approved" <?php echo $status_filter == 'approved' ? 'selected' : ''; ?>>Approved</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Bulan</label>
                <input type="month" class="form-control" name="bulan" value="<?php echo $bulan_filter; ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Tampilkan
                    </button>
                    <a href="laporan.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-clockwise"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Statistik Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card border-start border-primary border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Laporan</h6>
                <h3 class="mb-0"><?php echo count($laporan); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-success border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Approved</h6>
                <h3 class="mb-0"><?php echo count(array_filter($laporan, fn($l) => $l['status'] == 'approved')); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-warning border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Submitted</h6>
                <h3 class="mb-0"><?php echo count(array_filter($laporan, fn($l) => $l['status'] == 'submitted')); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-start border-secondary border-4">
            <div class="card-body">
                <h6 class="text-muted mb-2">Draft</h6>
                <h3 class="mb-0"><?php echo count(array_filter($laporan, fn($l) => $l['status'] == 'draft')); ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-table"></i> Data Laporan</h5>
        <a href="laporan-pdf.php?export=all<?php echo $sekolah_filter ? '&sekolah_id='.$sekolah_filter : ''; ?><?php echo $tahun_filter ? '&tahun_ajaran='.$tahun_filter : ''; ?>" class="btn btn-success btn-sm" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> Export PDF
        </a>
    </div>
    <div class="card-body">
        <?php if (count($laporan) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-primary">
                    <tr>
                        <th width="5%">No</th>
                        <th width="12%">Kode Penilaian</th>
                        <th width="20%">Sekolah</th>
                        <th width="10%">Jenis</th>
                        <th width="12%">Penilik</th>
                        <th width="10%">Tahun Ajaran</th>
                        <th width="10%">Tanggal</th>
                        <th width="8%">Nilai</th>
                        <th width="8%">Status</th>
                        <th width="5%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($laporan as $row): 
                        $nilai = 0;
                        if ($row['total_maksimal'] > 0) {
                            $nilai = round(($row['total_perolehan'] / $row['total_maksimal']) * 100, 2);
                        }
                        $kategori = getKategoriNilai($nilai);
                        
                        // Status badge
                        $statusBadge = '';
                        switch($row['status']) {
                            case 'approved':
                                $statusBadge = '<span class="badge bg-success">Approved</span>';
                                break;
                            case 'submitted':
                                $statusBadge = '<span class="badge bg-warning">Submitted</span>';
                                break;
                            default:
                                $statusBadge = '<span class="badge bg-secondary">Draft</span>';
                        }
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><strong><?php echo $row['kode_penilaian']; ?></strong></td>
                        <td><?php echo $row['nama_sekolah']; ?></td>
                        <td><?php echo $row['jenis_sekolah']; ?></td>
                        <td><?php echo $row['nama_penilik']; ?></td>
                        <td><?php echo $row['tahun_ajaran']; ?></td>
                        <td><?php echo formatTanggal($row['tanggal_penilaian']); ?></td>
                        <td>
                            <strong class="<?php echo $nilai >= 86 ? 'text-success' : ($nilai >= 71 ? 'text-warning' : 'text-danger'); ?>">
                                <?php echo number_format($nilai, 2); ?>
                            </strong>
                            <br>
                            <small class="text-muted"><?php echo $kategori; ?></small>
                        </td>
                        <td><?php echo $statusBadge; ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="penilaian-detail.php?kode=<?php echo $row['kode_penilaian']; ?>" 
                                   class="btn btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="laporan-pdf.php?kode=<?php echo $row['kode_penilaian']; ?>" 
                                   class="btn btn-danger" title="PDF" target="_blank">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Tidak ada data laporan yang sesuai dengan filter.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
