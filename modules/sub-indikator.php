<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

$pertanyaan_id = get('pertanyaan_id');
if (!$pertanyaan_id) {
    setFlash('danger', 'ID Indikator tidak ditemukan!');
    redirect('modules/master-pertanyaan.php');
}

// Get pertanyaan data
$pertanyaan = $db->query("
    SELECT p.*, s.nama_snp, s.kode_snp, a.nama_aspek
    FROM pertanyaan_snp p
    LEFT JOIN master_snp s ON p.snp_id = s.id
    LEFT JOIN aspek_snp a ON p.aspek_id = a.id
    WHERE p.id = $pertanyaan_id
")->fetch_assoc();

if (!$pertanyaan) {
    setFlash('danger', 'Indikator tidak ditemukan!');
    redirect('modules/master-pertanyaan.php');
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM sub_pertanyaan WHERE id = $id");
    setFlash('success', 'Sub-indikator berhasil dihapus!');
    redirect('modules/sub-indikator.php?pertanyaan_id=' . $pertanyaan_id);
}

// Handle Add/Edit
if (isPost()) {
    $sub_id = post('sub_id');
    $kode_sub = post('kode_sub');
    $sub_pertanyaan = post('sub_pertanyaan');
    $skor_maksimal = post('skor_maksimal', 4);
    $urutan = post('urutan');
    
    if ($sub_id) {
        $stmt = $db->prepare("UPDATE sub_pertanyaan SET kode_sub=?, sub_pertanyaan=?, skor_maksimal=?, urutan=? WHERE id=?");
        $stmt->bind_param("ssiii", $kode_sub, $sub_pertanyaan, $skor_maksimal, $urutan, $sub_id);
        setFlash('success', 'Sub-indikator berhasil diupdate!');
    } else {
        $stmt = $db->prepare("INSERT INTO sub_pertanyaan (pertanyaan_id, kode_sub, sub_pertanyaan, skor_maksimal, urutan) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issii", $pertanyaan_id, $kode_sub, $sub_pertanyaan, $skor_maksimal, $urutan);
        setFlash('success', 'Sub-indikator berhasil ditambahkan!');
    }
    $stmt->execute();
    redirect('modules/sub-indikator.php?pertanyaan_id=' . $pertanyaan_id);
}

// Get edit data
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = $db->query("SELECT * FROM sub_pertanyaan WHERE id = $id")->fetch_assoc();
}

// Get all sub-indikator
$dataSub = $db->query("
    SELECT * FROM sub_pertanyaan 
    WHERE pertanyaan_id = $pertanyaan_id 
    ORDER BY urutan
")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Kelola Sub-Indikator';
require_once '../includes/header.php';
?>

<div class="page-header">
    <h2><i class="bi bi-list-nested"></i> <?php echo $pageTitle; ?></h2>
</div>

<!-- Info Indikator -->
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Indikator</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>SNP</strong></td>
                        <td>: <?php echo $pertanyaan['kode_snp']; ?> - <?php echo $pertanyaan['nama_snp']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Aspek</strong></td>
                        <td>: <?php echo $pertanyaan['nama_aspek']; ?></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-sm table-borderless">
                    <tr>
                        <td width="150"><strong>No. Indikator</strong></td>
                        <td>: <?php echo $pertanyaan['nomor_pertanyaan']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Indikator</strong></td>
                        <td>: <?php echo $pertanyaan['pertanyaan']; ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <div class="mt-2">
            <a href="master-pertanyaan.php?snp=<?php echo $pertanyaan['snp_id']; ?>" class="btn btn-secondary btn-sm">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Form Section -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $editData ? 'pencil' : 'plus'; ?>-circle"></i>
                    <?php echo $editData ? 'Edit' : 'Tambah'; ?> Sub-Indikator
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                    <input type="hidden" name="sub_id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Sub <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="kode_sub" 
                               value="<?php echo $editData['kode_sub'] ?? ''; ?>" 
                               placeholder="a, b, c..." required>
                        <small class="text-muted">Contoh: a, b, c, atau 1, 2, 3</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Sub-Indikator <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="sub_pertanyaan" rows="4" required><?php echo $editData['sub_pertanyaan'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Skor Maksimal <span class="text-danger">*</span></label>
                        <select class="form-select" name="skor_maksimal" required>
                            <option value="1" <?php echo (isset($editData) && $editData['skor_maksimal'] == 1) ? 'selected' : ''; ?>>1</option>
                            <option value="2" <?php echo (isset($editData) && $editData['skor_maksimal'] == 2) ? 'selected' : ''; ?>>2</option>
                            <option value="3" <?php echo (isset($editData) && $editData['skor_maksimal'] == 3) ? 'selected' : ''; ?>>3</option>
                            <option value="4" <?php echo (!isset($editData) || $editData['skor_maksimal'] == 4) ? 'selected' : ''; ?>>4</option>
                            <option value="5" <?php echo (isset($editData) && $editData['skor_maksimal'] == 5) ? 'selected' : ''; ?>>5</option>
                        </select>
                        <small class="text-muted">Skor yang bisa diperoleh: 0 sampai nilai maksimal</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Urutan <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="urutan" 
                               value="<?php echo $editData['urutan'] ?? (count($dataSub) + 1); ?>" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <?php if ($editData): ?>
                        <a href="sub-indikator.php?pertanyaan_id=<?php echo $pertanyaan_id; ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- List Section -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list"></i> Daftar Sub-Indikator</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($dataSub)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">Kode</th>
                                <th width="55%">Sub-Indikator</th>
                                <th width="12%" class="text-center">Skor Max</th>
                                <th width="10%" class="text-center">Urutan</th>
                                <th width="15%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $totalSkor = 0;
                            foreach ($dataSub as $sub): 
                                $totalSkor += $sub['skor_maksimal'];
                            ?>
                            <tr>
                                <td><strong><?php echo $sub['kode_sub']; ?></strong></td>
                                <td><?php echo $sub['sub_pertanyaan']; ?></td>
                                <td class="text-center">
                                    <span class="badge bg-primary"><?php echo $sub['skor_maksimal']; ?></span>
                                </td>
                                <td class="text-center"><?php echo $sub['urutan']; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <a href="?pertanyaan_id=<?php echo $pertanyaan_id; ?>&edit=<?php echo $sub['id']; ?>" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?pertanyaan_id=<?php echo $pertanyaan_id; ?>&delete=<?php echo $sub['id']; ?>" 
                                           class="btn btn-danger" title="Hapus"
                                           onclick="return confirm('Yakin hapus sub-indikator ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end">Total Skor Maksimal:</th>
                                <th class="text-center">
                                    <strong class="text-primary"><?php echo $totalSkor; ?></strong>
                                </th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Belum ada sub-indikator. Silakan tambahkan menggunakan form di samping.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
