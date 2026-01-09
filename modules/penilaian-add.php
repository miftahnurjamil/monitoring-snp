<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

// Handle form submission BEFORE any output
if (isPost()) {
    $kode_penilaian = generateKode('PNL');
    $sekolah_id = post('sekolah_id');
    $penilik_id = post('penilik_id');
    $tahun_ajaran = post('tahun_ajaran');
    $semester = post('semester');
    $tanggal_penilaian = post('tanggal_penilaian');
    $selected_snp = post('snp_id');
    
    // Insert transaksi
    $stmt = $db->prepare("INSERT INTO transaksi_penilaian (kode_penilaian, sekolah_id, penilik_id, tahun_ajaran, semester, tanggal_penilaian, status, user_id) VALUES (?, ?, ?, ?, ?, ?, 'draft', ?)");
    $stmt->bind_param("siiissi", $kode_penilaian, $sekolah_id, $penilik_id, $tahun_ajaran, $semester, $tanggal_penilaian, $_SESSION['user_id']);
    $stmt->execute();
    $transaksi_id = $db->lastInsertId();
    
    setFlash('success', 'Penilaian berhasil dibuat! Silakan isi detail penilaian.');
    redirect('modules/penilaian-form.php?id=' . $transaksi_id . '&snp=' . $selected_snp);
}

// Get data for form
$dataSekolah = $db->query("SELECT id, nama_sekolah FROM master_sekolah WHERE is_active = 1 ORDER BY nama_sekolah")->fetch_all(MYSQLI_ASSOC);
$dataPenilik = $db->query("SELECT id, nama_lengkap FROM master_penilik WHERE is_active = 1 ORDER BY nama_lengkap")->fetch_all(MYSQLI_ASSOC);
$dataSNP = $db->query("SELECT id, kode_snp, nama_snp FROM master_snp WHERE is_active = 1 ORDER BY urutan")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Input Penilaian SNP';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-plus-circle"></i> <?php echo $pageTitle; ?></h2>
    <p class="text-muted">Buat data penilaian SNP baru</p>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-file-earmark-plus"></i> Form Penilaian SNP</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sekolah <span class="text-danger">*</span></label>
                            <select class="form-select" name="sekolah_id" required>
                                <option value="">-- Pilih Sekolah --</option>
                                <?php foreach ($dataSekolah as $sekolah): ?>
                                <option value="<?php echo $sekolah['id']; ?>"><?php echo $sekolah['nama_sekolah']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Penilik</label>
                            <select class="form-select" name="penilik_id">
                                <option value="">-- Pilih Penilik --</option>
                                <?php foreach ($dataPenilik as $penilik): ?>
                                <option value="<?php echo $penilik['id']; ?>"><?php echo $penilik['nama_lengkap']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="tahun_ajaran" placeholder="2025/2026" required>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Semester <span class="text-danger">*</span></label>
                            <select class="form-select" name="semester" required>
                                <option value="1">Semester 1</option>
                                <option value="2">Semester 2</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tanggal Penilaian <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal_penilaian" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pilih SNP yang akan dinilai <span class="text-danger">*</span></label>
                        <select class="form-select" name="snp_id" required>
                            <option value="">-- Pilih SNP --</option>
                            <?php foreach ($dataSNP as $snp): ?>
                            <option value="<?php echo $snp['id']; ?>"><?php echo $snp['kode_snp']; ?> - <?php echo $snp['nama_snp']; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Setelah membuat penilaian, Anda akan diarahkan ke form pengisian detail penilaian</small>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-arrow-right-circle"></i> Lanjut ke Form Penilaian
                        </button>
                        <a href="<?php echo BASE_URL; ?>modules/penilaian-list.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
