<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

// Get all SNP
$dataSNP = $db->query("SELECT * FROM master_snp WHERE is_active = 1 ORDER BY urutan")->fetch_all(MYSQLI_ASSOC);

// Get selected SNP
$selected_snp = get('snp', $dataSNP[0]['id'] ?? 1);

// Handle Delete Aspek BEFORE any output
if (isset($_GET['delete_aspek'])) {
    $id = (int)$_GET['delete_aspek'];
    $db->query("DELETE FROM aspek_snp WHERE id = $id");
    setFlash('success', 'Aspek berhasil dihapus!');
    redirect('modules/master-pertanyaan.php?snp=' . $selected_snp);
}

// Handle Delete Pertanyaan BEFORE any output
if (isset($_GET['delete_pertanyaan'])) {
    $id = (int)$_GET['delete_pertanyaan'];
    $db->query("DELETE FROM pertanyaan_snp WHERE id = $id");
    setFlash('success', 'Pertanyaan berhasil dihapus!');
    redirect('modules/master-pertanyaan.php?snp=' . $selected_snp);
}

// Handle Aspek Add/Edit BEFORE any output
if (isPost() && post('action') == 'aspek') {
    $aspek_id = post('aspek_id');
    $kode_aspek = post('kode_aspek');
    $nama_aspek = post('nama_aspek');
    $urutan = post('urutan');
    
    if ($aspek_id) {
        $stmt = $db->prepare("UPDATE aspek_snp SET kode_aspek=?, nama_aspek=?, urutan=? WHERE id=?");
        $stmt->bind_param("ssii", $kode_aspek, $nama_aspek, $urutan, $aspek_id);
        setFlash('success', 'Aspek berhasil diupdate!');
    } else {
        $stmt = $db->prepare("INSERT INTO aspek_snp (snp_id, kode_aspek, nama_aspek, urutan) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $selected_snp, $kode_aspek, $nama_aspek, $urutan);
        setFlash('success', 'Aspek berhasil ditambahkan!');
    }
    $stmt->execute();
    redirect('modules/master-pertanyaan.php?snp=' . $selected_snp);
}

// Handle Pertanyaan Add/Edit BEFORE any output
if (isPost() && post('action') == 'pertanyaan') {
    $pertanyaan_id = post('pertanyaan_id');
    $aspek_id = post('aspek_id');
    $nomor_pertanyaan = post('nomor_pertanyaan');
    $pertanyaan = post('pertanyaan');
    $jenis_jawaban = post('jenis_jawaban');
    $urutan = post('urutan');
    
    if ($pertanyaan_id) {
        $stmt = $db->prepare("UPDATE pertanyaan_snp SET aspek_id=?, nomor_pertanyaan=?, pertanyaan=?, jenis_jawaban=?, urutan=? WHERE id=?");
        $stmt->bind_param("isssii", $aspek_id, $nomor_pertanyaan, $pertanyaan, $jenis_jawaban, $urutan, $pertanyaan_id);
        setFlash('success', 'Pertanyaan berhasil diupdate!');
    } else {
        $stmt = $db->prepare("INSERT INTO pertanyaan_snp (snp_id, aspek_id, nomor_pertanyaan, pertanyaan, jenis_jawaban, urutan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssi", $selected_snp, $aspek_id, $nomor_pertanyaan, $pertanyaan, $jenis_jawaban, $urutan);
        setFlash('success', 'Pertanyaan berhasil ditambahkan!');
    }
    $stmt->execute();
    redirect('modules/master-pertanyaan.php?snp=' . $selected_snp);
}

// Get complete tree structure
$dataAspek = $db->query("
    SELECT a.*
    FROM aspek_snp a
    WHERE a.snp_id = $selected_snp
    ORDER BY a.urutan
")->fetch_all(MYSQLI_ASSOC);

// Build tree structure
$treeData = [];
foreach ($dataAspek as $aspek) {
    $aspek['indikators'] = $db->query("
        SELECT p.*
        FROM pertanyaan_snp p
        WHERE p.aspek_id = {$aspek['id']}
        ORDER BY p.urutan
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Get sub-indikator for each indikator
    foreach ($aspek['indikators'] as &$indikator) {
        $indikator['sub_indikators'] = $db->query("
            SELECT sp.*, 
                   IFNULL(sp.skor_maksimal, 4) as skor
            FROM sub_pertanyaan sp
            WHERE sp.pertanyaan_id = {$indikator['id']}
            ORDER BY sp.urutan
        ")->fetch_all(MYSQLI_ASSOC);
    }
    unset($indikator); // Clear reference
    
    $treeData[] = $aspek;
}

$pageTitle = 'Master Pertanyaan SNP';
require_once '../includes/header.php';
?>

<style>
/* Tree View Styles */
.tree-view {
    list-style: none;
    padding-left: 0;
}

.tree-view ul {
    list-style: none;
    padding-left: 0;
}

.tree-node {
    position: relative;
    padding: 10px 15px;
    margin: 5px 0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: normal;
    word-wrap: break-word;
    overflow-wrap: break-word;
    line-height: 1.5;
}

.tree-node-aspek {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.tree-node-aspek:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
}

.tree-node-indikator {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    margin-left: 30px;
    box-shadow: 0 2px 6px rgba(245, 87, 108, 0.3);
}

.tree-node-indikator:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 10px rgba(245, 87, 108, 0.5);
}

.tree-node-sub {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    margin-left: 60px;
    font-size: 0.9rem;
    box-shadow: 0 2px 4px rgba(79, 172, 254, 0.3);
}

.tree-node-sub:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 8px rgba(79, 172, 254, 0.5);
}

.tree-toggle {
    display: inline-block;
    margin-right: 8px;
    transition: transform 0.3s ease;
}

.tree-toggle.collapsed {
    transform: rotate(-90deg);
}

.tree-children {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease-out;
}

.tree-children.show {
    max-height: 5000px;
    transition: max-height 0.6s ease-in;
}

.tree-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-left: 8px;
}

.tree-actions {
    float: right;
}

.tree-actions .btn {
    padding: 2px 8px;
    font-size: 0.8rem;
    margin-left: 5px;
}

.tree-empty {
    text-align: center;
    padding: 40px;
    color: #999;
}

.tree-empty i {
    font-size: 3rem;
    margin-bottom: 15px;
    display: block;
}
</style>

<div class="page-header">
    <h2><i class="bi bi-diagram-3"></i> <?php echo $pageTitle; ?></h2>
    <p class="text-muted">Kelola Aspek, Indikator, dan Sub-Indikator untuk setiap SNP</p>
    <small class="text-info">
        <strong>Struktur:</strong> Aspek → Indikator → Sub-Indikator (dengan skor)
    </small>
</div>

<!-- SNP Selector -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <label class="form-label"><strong>Pilih SNP:</strong></label>
            </div>
            <div class="col-md-9">
                <select class="form-select" onchange="window.location.href='?snp='+this.value">
                    <?php foreach ($dataSNP as $snp): ?>
                    <option value="<?php echo $snp['id']; ?>" <?php echo $selected_snp == $snp['id'] ? 'selected' : ''; ?>>
                        <?php echo $snp['kode_snp']; ?> - <?php echo $snp['nama_snp']; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Tree View Section -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h5 class="mb-0 text-white">
                    <i class="bi bi-diagram-3-fill"></i> Struktur Hierarki SNP
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($treeData)): ?>
                    <div class="mb-3">
                        <button class="btn btn-sm btn-outline-primary" onclick="expandAll()">
                            <i class="bi bi-arrows-expand"></i> Expand All
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="collapseAll()">
                            <i class="bi bi-arrows-collapse"></i> Collapse All
                        </button>
                    </div>
                    
                    <ul class="tree-view">
                        <?php foreach ($treeData as $aspek): ?>
                            <li>
                                <!-- Aspek Node -->
                                <div class="tree-node tree-node-aspek" onclick="toggleNode(this)">
                                    <i class="bi bi-chevron-down tree-toggle"></i>
                                    <strong><?php echo $aspek['kode_aspek']; ?>. <?php echo $aspek['nama_aspek']; ?></strong>
                                    <span class="tree-badge bg-white text-primary">
                                        <?php echo count($aspek['indikators']); ?> Indikator
                                    </span>
                                    <span class="tree-actions">
                                        <a href="?snp=<?php echo $selected_snp; ?>&edit_aspek=<?php echo $aspek['id']; ?>" 
                                           onclick="event.stopPropagation()" 
                                           class="btn btn-sm btn-warning">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="javascript:void(0)" 
                                           onclick="event.stopPropagation(); confirmDelete('?delete_aspek=<?php echo $aspek['id']; ?>&snp=<?php echo $selected_snp; ?>')" 
                                           class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </span>
                                </div>
                                
                                <!-- Indikator Children -->
                                <?php if (!empty($aspek['indikators'])): ?>
                                <ul class="tree-children">
                                    <?php foreach ($aspek['indikators'] as $indikator): ?>
                                        <li>
                                            <!-- Indikator Node -->
                                            <div class="tree-node tree-node-indikator" onclick="toggleNode(this)">
                                                <i class="bi bi-chevron-down tree-toggle"></i>
                                                <strong><?php echo $indikator['nomor_pertanyaan']; ?>.</strong>
                                                <?php echo $indikator['pertanyaan']; ?>
                                                <span class="tree-badge bg-white text-danger">
                                                    <?php echo count($indikator['sub_indikators']); ?> Sub
                                                </span>
                                                <span class="tree-actions">
                                                    <a href="sub-indikator.php?pertanyaan_id=<?php echo $indikator['id']; ?>" 
                                                       onclick="event.stopPropagation()"
                                                       class="btn btn-sm btn-success">
                                                        <i class="bi bi-plus"></i> Sub
                                                    </a>
                                                    <a href="?snp=<?php echo $selected_snp; ?>&edit_pertanyaan=<?php echo $indikator['id']; ?>" 
                                                       onclick="event.stopPropagation()" 
                                                       class="btn btn-sm btn-warning">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="javascript:void(0)" 
                                                       onclick="event.stopPropagation(); confirmDelete('?delete_pertanyaan=<?php echo $indikator['id']; ?>&snp=<?php echo $selected_snp; ?>')" 
                                                       class="btn btn-sm btn-danger">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </span>
                                            </div>
                                            
                                            <!-- Sub Indikator Children -->
                                            <?php if (!empty($indikator['sub_indikators'])): ?>
                                            <ul class="tree-children">
                                                <?php foreach ($indikator['sub_indikators'] as $sub): ?>
                                                    <li>
                                                        <!-- Sub Indikator Node -->
                                                        <div class="tree-node tree-node-sub">
                                                            <i class="bi bi-check2-circle"></i>
                                                            <strong><?php echo $sub['kode_sub']; ?>.</strong>
                                                            <?php echo $sub['sub_pertanyaan']; ?>
                                                            <?php if (isset($sub['skor']) && $sub['skor'] > 0): ?>
                                                                <span class="tree-badge bg-warning text-dark">
                                                                    Skor: <?php echo $sub['skor']; ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div class="tree-empty">
                        <i class="bi bi-folder-x"></i>
                        <p>Belum ada data. Silakan tambahkan Aspek terlebih dahulu.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Quick Add Forms Section -->
    <div class="col-md-4">
        <!-- Form Add/Edit Aspek -->
        <?php 
        $editAspek = null;
        if (isset($_GET['edit_aspek'])) {
            $id = (int)$_GET['edit_aspek'];
            $editAspek = $db->query("SELECT * FROM aspek_snp WHERE id = $id")->fetch_assoc();
        }
        ?>
        <div class="card mb-3">
            <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h6 class="mb-0 text-white">
                    <i class="bi bi-<?php echo $editAspek ? 'pencil-square' : 'plus-circle'; ?>"></i> 
                    <?php echo $editAspek ? 'Edit' : 'Tambah'; ?> Aspek
                </h6>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="aspek">
                    <?php if ($editAspek): ?>
                    <input type="hidden" name="aspek_id" value="<?php echo $editAspek['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-2">
                        <label class="form-label">Kode Aspek</label>
                        <input type="text" class="form-control form-control-sm" name="kode_aspek" 
                               value="<?php echo $editAspek['kode_aspek'] ?? ''; ?>" 
                               placeholder="1, 2, 3..." required>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Nama Aspek</label>
                        <textarea class="form-control form-control-sm" name="nama_aspek" rows="2" 
                                  placeholder="Nama aspek..." required><?php echo $editAspek['nama_aspek'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Urutan</label>
                        <input type="number" class="form-control form-control-sm" name="urutan" 
                               value="<?php echo $editAspek['urutan'] ?? 1; ?>" required>
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-<?php echo $editAspek ? 'success' : 'primary'; ?> w-100">
                        <i class="bi bi-save"></i> <?php echo $editAspek ? 'Update' : 'Simpan'; ?> Aspek
                    </button>
                    <?php if ($editAspek): ?>
                    <a href="?snp=<?php echo $selected_snp; ?>" class="btn btn-sm btn-secondary w-100 mt-2">
                        <i class="bi bi-x-circle"></i> Batal Edit
                    </a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <!-- Form Add/Edit Indikator -->
        <?php 
        $editPertanyaan = null;
        if (isset($_GET['edit_pertanyaan'])) {
            $id = (int)$_GET['edit_pertanyaan'];
            $editPertanyaan = $db->query("SELECT * FROM pertanyaan_snp WHERE id = $id")->fetch_assoc();
        }
        ?>
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h6 class="mb-0 text-white">
                    <i class="bi bi-<?php echo $editPertanyaan ? 'pencil-square' : 'plus-circle'; ?>"></i> 
                    <?php echo $editPertanyaan ? 'Edit' : 'Tambah'; ?> Indikator
                </h6>
            </div>
            <div class="card-body">
                <?php if (!empty($dataAspek)): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="pertanyaan">
                    <?php if ($editPertanyaan): ?>
                    <input type="hidden" name="pertanyaan_id" value="<?php echo $editPertanyaan['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-2">
                        <label class="form-label">Aspek</label>
                        <select class="form-select form-select-sm" name="aspek_id" required>
                            <option value="">-- Pilih Aspek --</option>
                            <?php foreach ($dataAspek as $aspek): ?>
                            <option value="<?php echo $aspek['id']; ?>" 
                                    <?php echo ($editPertanyaan && $editPertanyaan['aspek_id'] == $aspek['id']) ? 'selected' : ''; ?>>
                                <?php echo $aspek['kode_aspek']; ?>. <?php echo $aspek['nama_aspek']; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label">No.</label>
                            <input type="text" class="form-control form-control-sm" name="nomor_pertanyaan" 
                                   value="<?php echo $editPertanyaan['nomor_pertanyaan'] ?? ''; ?>" 
                                   placeholder="1.1" required>
                        </div>
                        
                        <div class="col-6 mb-2">
                            <label class="form-label">Urutan</label>
                            <input type="number" class="form-control form-control-sm" name="urutan" 
                                   value="<?php echo $editPertanyaan['urutan'] ?? 1; ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label">Pertanyaan/Indikator</label>
                        <textarea class="form-control form-control-sm" name="pertanyaan" rows="3" required><?php echo $editPertanyaan['pertanyaan'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Jawaban</label>
                        <select class="form-select form-select-sm" name="jenis_jawaban" required>
                            <option value="skor" <?php echo ($editPertanyaan && $editPertanyaan['jenis_jawaban'] == 'skor') ? 'selected' : ''; ?>>Skor (0-4)</option>
                            <option value="checkbox" <?php echo ($editPertanyaan && $editPertanyaan['jenis_jawaban'] == 'checkbox') ? 'selected' : ''; ?>>Checkbox</option>
                            <option value="ya_tidak" <?php echo ($editPertanyaan && $editPertanyaan['jenis_jawaban'] == 'ya_tidak') ? 'selected' : ''; ?>>Ya/Tidak</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-sm btn-<?php echo $editPertanyaan ? 'success' : 'danger'; ?> w-100">
                        <i class="bi bi-save"></i> <?php echo $editPertanyaan ? 'Update' : 'Simpan'; ?> Indikator
                    </button>
                    <?php if ($editPertanyaan): ?>
                    <a href="?snp=<?php echo $selected_snp; ?>" class="btn btn-sm btn-secondary w-100 mt-2">
                        <i class="bi bi-x-circle"></i> Batal Edit
                    </a>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                <div class="alert alert-warning mb-0">
                    <small><i class="bi bi-exclamation-triangle"></i> Tambahkan <strong>Aspek</strong> terlebih dahulu</small>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle tree node
function toggleNode(element) {
    const toggle = element.querySelector('.tree-toggle');
    const children = element.nextElementSibling;
    
    if (children && children.classList.contains('tree-children')) {
        toggle.classList.toggle('collapsed');
        children.classList.toggle('show');
    }
}

// Expand all nodes
function expandAll() {
    document.querySelectorAll('.tree-toggle').forEach(toggle => {
        toggle.classList.remove('collapsed');
    });
    document.querySelectorAll('.tree-children').forEach(children => {
        children.classList.add('show');
    });
}

// Collapse all nodes
function collapseAll() {
    document.querySelectorAll('.tree-toggle').forEach(toggle => {
        toggle.classList.add('collapsed');
    });
    document.querySelectorAll('.tree-children').forEach(children => {
        children.classList.remove('show');
    });
}

// Confirm delete with sweet confirmation
function confirmDelete(url) {
    if (confirm('Apakah Anda yakin ingin menghapus data ini? Data yang terhubung juga akan terhapus.')) {
        window.location.href = url;
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
