<?php
// Prevent caching - HARUS DI PALING ATAS
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

require_once '../includes/auth.php';

$db = Database::getInstance();

// Handle Delete BEFORE any output
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM transaksi_penilaian WHERE id = $id");
    setFlash('success', 'Data penilaian berhasil dihapus!');
    redirect('modules/penilaian-list.php');
}

$pageTitle = 'Data Penilaian SNP';
require_once '../includes/header.php';

// Get all penilaian
$dataPenilaian = $db->query("
    SELECT 
        t.id,
        t.kode_penilaian,
        t.tahun_ajaran,
        t.semester,
        t.tanggal_penilaian,
        t.status,
        s.nama_sekolah,
        p.nama_lengkap as nama_pengawas
    FROM transaksi_penilaian t
    LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
    LEFT JOIN master_pengawas p ON t.pengawas_id = p.id
    ORDER BY t.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="bi bi-clipboard-data"></i> <?php echo $pageTitle; ?></h2>
            <p class="text-muted mb-0">Daftar semua penilaian SNP yang telah dilakukan</p>
        </div>
        <a href="<?php echo BASE_URL; ?>modules/penilaian-add.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Tambah Penilaian
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover datatable" id="penilaianTable">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kode Penilaian</th>
                        <th>Sekolah</th>
                        <th>Pengawas</th>
                        <th>Tahun Ajaran</th>
                        <th>Semester</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    foreach ($dataPenilaian as $item): 
                    $badgeClass = [
                        'draft' => 'bg-warning',
                        'selesai' => 'bg-success',
                        'terverifikasi' => 'bg-info'
                    ];
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <span class="badge bg-secondary"><?php echo $item['kode_penilaian']; ?></span>
                        </td>
                        <td><strong><?php echo $item['nama_sekolah']; ?></strong></td>
                        <td><?php echo $item['nama_pengawas'] ?? '-'; ?></td>
                        <td><?php echo $item['tahun_ajaran']; ?></td>
                        <td><?php echo $item['semester']; ?></td>
                        <td><?php echo formatTanggal($item['tanggal_penilaian']); ?></td>
                        <td>
                            <span class="badge <?php echo $badgeClass[$item['status']]; ?>">
                                <?php echo strtoupper($item['status']); ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="<?php echo BASE_URL; ?>modules/penilaian-detail.php?kode=<?php echo $item['kode_penilaian']; ?>" 
                                   class="btn btn-sm btn-info" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($item['status'] == 'draft'): ?>
                                <a href="javascript:void(0)" 
                                   onclick="confirmDelete('?delete=<?php echo $item['id']; ?>')" 
                                   class="btn btn-sm btn-danger" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Destroy existing DataTable if any, then reinitialize
    if ($.fn.DataTable.isDataTable('#penilaianTable')) {
        $('#penilaianTable').DataTable().destroy();
    }
    
    $('#penilaianTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
        },
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
