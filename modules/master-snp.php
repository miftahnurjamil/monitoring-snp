<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

// Handle Delete BEFORE any output
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM master_snp WHERE id = $id");
    setFlash('success', 'Data SNP berhasil dihapus!');
    redirect('modules/master-snp.php');
}

// Handle Add/Edit BEFORE any output
if (isPost()) {
    $id = post('id');
    $kode_snp = post('kode_snp');
    $nama_snp = post('nama_snp');
    $deskripsi = post('deskripsi');
    $urutan = post('urutan');
    
    if ($id) {
        // Update
        $stmt = $db->prepare("UPDATE master_snp SET kode_snp=?, nama_snp=?, deskripsi=?, urutan=? WHERE id=?");
        $stmt->bind_param("sssii", $kode_snp, $nama_snp, $deskripsi, $urutan, $id);
        $stmt->execute();
        setFlash('success', 'Data SNP berhasil diupdate!');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO master_snp (kode_snp, nama_snp, deskripsi, urutan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $kode_snp, $nama_snp, $deskripsi, $urutan);
        $stmt->execute();
        setFlash('success', 'Data SNP berhasil ditambahkan!');
    }
    redirect('modules/master-snp.php');
}

$pageTitle = 'Master SNP';
require_once '../includes/header.php';

// Get data for edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = $db->query("SELECT * FROM master_snp WHERE id = $id")->fetch_assoc();
}

// Get all data
$dataSNP = $db->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM pertanyaan_snp WHERE snp_id = s.id) as jumlah_pertanyaan
    FROM master_snp s 
    ORDER BY s.urutan
")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="bi bi-list-check"></i> <?php echo $pageTitle; ?></h2>
    <p class="text-muted">8 Standar Nasional Pendidikan</p>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $editData ? 'pencil' : 'plus'; ?>-circle"></i>
                    <?php echo $editData ? 'Edit' : 'Tambah'; ?> SNP
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode SNP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_snp" 
                               value="<?php echo $editData['kode_snp'] ?? ''; ?>" 
                               placeholder="SNP-01" required>
                        <small class="text-muted">Contoh: SNP-01, SNP-02, dst.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama SNP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_snp" 
                               value="<?php echo $editData['nama_snp'] ?? ''; ?>" 
                               placeholder="Standar Kompetensi Lulusan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="deskripsi" rows="3" 
                                  placeholder="Deskripsi singkat tentang SNP ini..."><?php echo $editData['deskripsi'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Urutan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="urutan" 
                               value="<?php echo $editData['urutan'] ?? '1'; ?>" 
                               min="1" required>
                        <small class="text-muted">Urutan tampilan SNP (1-8)</small>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <?php if ($editData): ?>
                        <a href="<?php echo BASE_URL; ?>modules/master-snp.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Info Card -->
        <div class="card mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Informasi</h6>
            </div>
            <div class="card-body">
                <small>
                    <strong>8 Standar Nasional Pendidikan:</strong><br>
                    1. Standar Kompetensi Lulusan<br>
                    2. Standar Isi<br>
                    3. Standar Proses<br>
                    4. Standar Penilaian Pendidikan<br>
                    5. Standar Pendidik dan Tenaga Kependidikan<br>
                    6. Standar Sarana dan Prasarana<br>
                    7. Standar Pengelolaan<br>
                    8. Standar Pembiayaan
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Daftar 8 SNP</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Urutan</th>
                                <th width="100">Kode</th>
                                <th>Nama SNP</th>
                                <th width="100" class="text-center">Pertanyaan</th>
                                <th width="100" class="text-center">Status</th>
                                <th width="120">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dataSNP as $item): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-secondary"><?php echo $item['urutan']; ?></span>
                                </td>
                                <td><strong><?php echo $item['kode_snp']; ?></strong></td>
                                <td>
                                    <strong><?php echo $item['nama_snp']; ?></strong><br>
                                    <small class="text-muted"><?php echo $item['deskripsi']; ?></small>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['jumlah_pertanyaan'] > 0): ?>
                                    <span class="badge bg-success"><?php echo $item['jumlah_pertanyaan']; ?> item</span>
                                    <?php else: ?>
                                    <span class="badge bg-warning">0 item</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($item['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>modules/master-pertanyaan.php?snp=<?php echo $item['id']; ?>" 
                                           class="btn btn-sm btn-primary" title="Kelola Pertanyaan">
                                            <i class="bi bi-question-circle"></i>
                                        </a>
                                        <?php if ($item['jumlah_pertanyaan'] == 0): ?>
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
                            
                            <?php if (empty($dataSNP)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p>Belum ada data SNP</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
