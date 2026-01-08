<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

$transaksi_id = get('id');
$snp_id = get('snp');

if (!$transaksi_id || !$snp_id) {
    setFlash('danger', 'Parameter tidak lengkap!');
    redirect('modules/penilaian-list.php');
}

// Get transaksi data
$transaksi = $db->query("
    SELECT t.*, s.nama_sekolah, s.nama_kepala_sekolah, s.nip_kepala_sekolah, s.alamat,
           p.nama_lengkap as nama_pengawas,
           snp.kode_snp, snp.nama_snp
    FROM transaksi_penilaian t
    LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
    LEFT JOIN master_pengawas p ON t.pengawas_id = p.id
    LEFT JOIN master_snp snp ON snp.id = $snp_id
    WHERE t.id = $transaksi_id
")->fetch_assoc();

if (!$transaksi) {
    setFlash('danger', 'Data penilaian tidak ditemukan!');
    redirect('modules/penilaian-list.php');
}

// Get complete tree structure (Aspek -> Indikator -> Sub Indikator)
$dataAspek = $db->query("
    SELECT a.*
    FROM aspek_snp a
    WHERE a.snp_id = $snp_id AND a.is_active = 1
    ORDER BY a.urutan
")->fetch_all(MYSQLI_ASSOC);

// Build tree structure
$treeData = [];
foreach ($dataAspek as $aspek) {
    $aspek['indikators'] = $db->query("
        SELECT p.*
        FROM pertanyaan_snp p
        WHERE p.aspek_id = {$aspek['id']} AND p.is_active = 1
        ORDER BY p.urutan
    ")->fetch_all(MYSQLI_ASSOC);
    
    // Get sub-indikator for each indikator
    foreach ($aspek['indikators'] as &$indikator) {
        $indikator['sub_indikators'] = $db->query("
            SELECT sp.*, 
                   IFNULL(sp.skor_maksimal, 4) as skor_max
            FROM sub_pertanyaan sp
            WHERE sp.pertanyaan_id = {$indikator['id']} AND sp.is_active = 1
            ORDER BY sp.urutan
        ")->fetch_all(MYSQLI_ASSOC);
    }
    unset($indikator);
    
    $treeData[] = $aspek;
}

// Get existing scores if edit mode
$existingScores = [];
$resultScores = $db->query("
    SELECT pertanyaan_id, sub_pertanyaan_id, skor 
    FROM detail_penilaian 
    WHERE transaksi_id = $transaksi_id AND snp_id = $snp_id
");
while ($row = $resultScores->fetch_assoc()) {
    if ($row['sub_pertanyaan_id']) {
        $existingScores[$row['pertanyaan_id']][$row['sub_pertanyaan_id']] = $row['skor'];
    } else {
        $existingScores[$row['pertanyaan_id']] = $row['skor'];
    }
}

// Handle form submission BEFORE any output
if (isPost()) {
    $skor_data = $_POST['skor'] ?? [];
    $total_skor_perolehan = 0;
    $total_skor_maksimal = 0;
    
    foreach ($skor_data as $pertanyaan_id => $skorItem) {
        if (is_array($skorItem)) {
            // Untuk pertanyaan dengan sub-item
            foreach ($skorItem as $sub_id => $skor) {
                $skor = (int)$skor;
                $total_skor_perolehan += $skor;
                $total_skor_maksimal += 4; // Skor maksimal 4
                
                $stmt = $db->prepare("INSERT INTO detail_penilaian (transaksi_id, snp_id, pertanyaan_id, sub_pertanyaan_id, skor) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE skor = ?");
                $stmt->bind_param("iiiiii", $transaksi_id, $snp_id, $pertanyaan_id, $sub_id, $skor, $skor);
                $stmt->execute();
            }
        } else {
            // Untuk pertanyaan tunggal
            $skor = (int)$skorItem;
            $total_skor_perolehan += $skor;
            $total_skor_maksimal += 4;
            
            $sub_null = null;
            $stmt = $db->prepare("INSERT INTO detail_penilaian (transaksi_id, snp_id, pertanyaan_id, sub_pertanyaan_id, skor) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE skor = ?");
            $stmt->bind_param("iiiiii", $transaksi_id, $snp_id, $pertanyaan_id, $sub_null, $skor, $skor);
            $stmt->execute();
        }
    }
    
    // Hitung nilai (0-100)
    $nilai = $total_skor_maksimal > 0 ? ($total_skor_perolehan / $total_skor_maksimal) * 100 : 0;
    $kategori = getKategoriNilai($nilai);
    
    // Insert atau update rekapitulasi
    $stmt = $db->prepare("INSERT INTO rekapitulasi_snp (transaksi_id, snp_id, total_skor_perolehan, total_skor_maksimal, nilai, kategori) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE total_skor_perolehan = ?, total_skor_maksimal = ?, nilai = ?, kategori = ?");
    $stmt->bind_param("iiiidsiids", $transaksi_id, $snp_id, $total_skor_perolehan, $total_skor_maksimal, $nilai, $kategori, $total_skor_perolehan, $total_skor_maksimal, $nilai, $kategori);
    $stmt->execute();
    
    // Update status transaksi
    $db->query("UPDATE transaksi_penilaian SET status = 'selesai' WHERE id = $transaksi_id");
    
    setFlash('success', 'Penilaian berhasil disimpan! Total Skor: ' . $total_skor_perolehan . '/' . $total_skor_maksimal . ' | Nilai: ' . number_format($nilai, 2) . ' | Kategori: ' . $kategori);
    redirect('modules/penilaian-detail.php?kode=' . $transaksi['kode_penilaian']);
}

$pageTitle = 'Form Penilaian Detail';
require_once '../includes/header.php';
?>

<style>
/* Tree View untuk Form Penilaian */
.penilaian-tree {
    margin-top: 20px;
}

.aspek-container {
    margin-bottom: 20px;
}

.aspek-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    font-weight: bold;
    margin-bottom: 15px;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
    display: flex;
    align-items: center;
    user-select: none;
}

.aspek-header:hover {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.5);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.aspek-header .badge {
    background: rgba(255, 255, 255, 0.3);
    color: white;
    padding: 5px 12px;
    margin-left: 10px;
}

.aspek-content {
    transition: max-height 0.4s ease-out;
    overflow: hidden;
}

.aspek-toggle {
    transition: transform 0.3s ease;
    font-size: 1.2rem;
    display: inline-block;
}

.collapse-controls {
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 4px solid #667eea;
}

.indikator-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 10px;
    margin-left: 20px;
    box-shadow: 0 2px 6px rgba(245, 87, 108, 0.3);
}

.indikator-card .badge {
    background: rgba(255, 255, 255, 0.3);
    padding: 4px 10px;
}

.sub-indikator-list {
    margin-left: 40px;
    margin-top: 10px;
}

.sub-indikator-item {
    background: white;
    border-left: 4px solid #4facfe;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.sub-indikator-item .kode {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
    padding: 3px 10px;
    border-radius: 4px;
    font-weight: bold;
    display: inline-block;
    margin-right: 10px;
}

.indikator-tunggal {
    background: white;
    border-left: 4px solid #f093fb;
    padding: 15px;
    margin-left: 20px;
    margin-bottom: 10px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.skor-selector {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 10px;
}

.skor-selector input[type="radio"] {
    display: none;
}

.skor-selector label {
    width: 45px;
    height: 45px;
    border: 2px solid #ddd;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s;
    font-size: 1.1rem;
    background: white;
}

.skor-selector input[type="radio"]:checked + label {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
    transform: scale(1.15);
    box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}

.skor-selector label:hover {
    border-color: #667eea;
    background: #f8f9fa;
    transform: scale(1.05);
}

.data-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 25px;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.data-header h5 {
    font-weight: bold;
    margin-bottom: 15px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    display: block;
    opacity: 0.5;
}

.progress-info {
    position: sticky;
    top: 20px;
    background: white;
    border: 2px solid #667eea;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.progress-info h6 {
    color: #667eea;
    font-weight: bold;
}

.progress-info.incomplete {
    border-color: #dc3545;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
    50% { box-shadow: 0 0 0 10px rgba(220, 53, 69, 0); }
}

.item-belum-diisi {
    border-left-color: #dc3545 !important;
    animation: shake 0.5s;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.required-indicator {
    color: #dc3545;
    font-weight: bold;
    margin-left: 5px;
}
</style>

<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2><i class="bi bi-clipboard-check"></i> Form Penilaian SNP</h2>
            <p class="text-muted mb-0"><?php echo $transaksi['kode_snp']; ?> - <?php echo $transaksi['nama_snp']; ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>modules/penilaian-list.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<!-- Data Utama -->
<div class="data-header">
    <div class="row">
        <div class="col-md-6">
            <h5 class="mb-3">DATA UTAMA</h5>
            <table class="table table-borderless text-white">
                <tr>
                    <td width="150"><strong>Nama Sekolah</strong></td>
                    <td>: <?php echo $transaksi['nama_sekolah']; ?></td>
                </tr>
                <tr>
                    <td><strong>Alamat</strong></td>
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
        <div class="col-md-6">
            <h5 class="mb-3">INFORMASI PENILAIAN</h5>
            <table class="table table-borderless text-white">
                <tr>
                    <td width="150"><strong>Kode Penilaian</strong></td>
                    <td>: <?php echo $transaksi['kode_penilaian']; ?></td>
                </tr>
                <tr>
                    <td><strong>Tahun Ajaran</strong></td>
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
            </table>
        </div>
    </div>
</div>

<!-- Form Penilaian -->
<form method="POST" id="formPenilaian">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="bi bi-diagram-3-fill"></i> 
                <?php echo $transaksi['kode_snp']; ?> - <?php echo $transaksi['nama_snp']; ?>
            </h5>
            <small class="text-muted">Isi skor untuk setiap indikator dan sub-indikator (Skala 0-4)</small>
        </div>
        <div class="card-body">
            <?php if (empty($treeData)): ?>
            <div class="empty-state">
                <i class="bi bi-folder-x"></i>
                <h5>Belum Ada Data</h5>
                <p>Belum ada pertanyaan untuk SNP ini.<br>Silakan tambahkan pertanyaan di menu <strong>Master Pertanyaan</strong>.</p>
                <a href="<?php echo BASE_URL; ?>modules/master-pertanyaan.php?snp=<?php echo $snp_id; ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Pertanyaan
                </a>
            </div>
            <?php else: ?>
            
            <!-- Collapse Controls -->
            <div class="collapse-controls mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Klik header aspek untuk collapse/expand, atau gunakan tombol di samping
                        </small>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="expandAllAspek()">
                            <i class="bi bi-arrows-expand"></i> Expand All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="collapseAllAspek()">
                            <i class="bi bi-arrows-collapse"></i> Collapse All
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="penilaian-tree">
                <?php foreach ($treeData as $aspekIndex => $aspek): ?>
                <div class="aspek-container">
                    <!-- Aspek Header -->
                    <div class="aspek-header" onclick="toggleAspek(this)" style="cursor: pointer;">
                        <i class="bi bi-chevron-down aspek-toggle me-2" style="transition: transform 0.3s;"></i>
                        <i class="bi bi-bookmark-fill"></i>
                        <strong><?php echo $aspek['kode_aspek']; ?>. <?php echo $aspek['nama_aspek']; ?></strong>
                        <span class="badge">
                            <?php echo count($aspek['indikators']); ?> Indikator
                        </span>
                        <small class="ms-auto text-white-50" style="font-size: 0.85rem;">
                            <i class="bi bi-info-circle"></i> Klik untuk collapse/expand
                        </small>
                    </div>
                    
                    <!-- Aspek Content (Collapsible) -->
                    <div class="aspek-content" style="max-height: none; overflow: hidden; transition: max-height 0.4s ease-out;">
                    <?php foreach ($aspek['indikators'] as $indikator): ?>
                        <?php if (!empty($indikator['sub_indikators'])): ?>
                        <!-- Indikator dengan Sub-Indikator -->
                        <div class="indikator-card">
                            <i class="bi bi-list-check"></i>
                            <strong><?php echo $indikator['nomor_pertanyaan']; ?>.</strong>
                            <?php echo $indikator['pertanyaan']; ?>
                            <span class="badge">
                                <?php echo count($indikator['sub_indikators']); ?> Sub
                            </span>
                        </div>
                        
                        <div class="sub-indikator-list">
                            <?php foreach ($indikator['sub_indikators'] as $sub): ?>
                            <div class="sub-indikator-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <span class="kode"><?php echo $sub['kode_sub']; ?></span>
                                        <span><?php echo $sub['sub_pertanyaan']; ?></span>
                                        <?php if (isset($sub['skor_max'])): ?>
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-info-circle"></i> Skor Maksimal: <?php echo $sub['skor_max']; ?>
                                        </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="skor-selector">
                                        <?php 
                                        $currentScore = isset($existingScores[$indikator['id']][$sub['id']]) ? $existingScores[$indikator['id']][$sub['id']] : null;
                                        for ($i = 0; $i <= 4; $i++): 
                                        ?>
                                        <input type="radio" 
                                               id="skor_<?php echo $indikator['id']; ?>_<?php echo $sub['id']; ?>_<?php echo $i; ?>" 
                                               name="skor[<?php echo $indikator['id']; ?>][<?php echo $sub['id']; ?>]" 
                                               value="<?php echo $i; ?>" 
                                               <?php echo ($currentScore !== null && $i == $currentScore) ? 'checked' : ''; ?>
                                               class="skor-radio" required>
                                        <label for="skor_<?php echo $indikator['id']; ?>_<?php echo $sub['id']; ?>_<?php echo $i; ?>"
                                               title="Skor <?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php else: ?>
                        <!-- Indikator Tunggal (tanpa sub) -->
                        <div class="indikator-tunggal">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <strong style="color: #f5576c;">
                                        <i class="bi bi-check-circle"></i>
                                        <?php echo $indikator['nomor_pertanyaan']; ?>. <?php echo $indikator['pertanyaan']; ?>
                                    </strong>
                                    <small class="text-muted d-block mt-1">
                                        <i class="bi bi-info-circle"></i> Jenis: <?php echo ucfirst($indikator['jenis_jawaban']); ?>
                                    </small>
                                </div>
                                <div class="skor-selector">
                                    <?php 
                                    $currentScore = isset($existingScores[$indikator['id']]) && !is_array($existingScores[$indikator['id']]) ? $existingScores[$indikator['id']] : null;
                                    for ($i = 0; $i <= 4; $i++): 
                                    ?>
                                    <input type="radio" 
                                           id="skor_<?php echo $indikator['id']; ?>_<?php echo $i; ?>" 
                                           name="skor[<?php echo $indikator['id']; ?>]" 
                                           value="<?php echo $i; ?>" 
                                           <?php echo ($currentScore !== null && $i == $currentScore) ? 'checked' : ''; ?>
                                           class="skor-radio" required>
                                    <label for="skor_<?php echo $indikator['id']; ?>_<?php echo $i; ?>"
                                           title="Skor <?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </div><!-- End aspek-content -->
                </div>
                <?php endforeach; ?>
            </div>
            
            <hr>
            
            <div class="progress-info" id="progressInfo">
                <h6>
                    <i class="bi bi-bar-chart-fill"></i> Progress Penilaian 
                    <span class="required-indicator" id="requiredIndicator" style="display:none;">* Wajib 100%</span>
                </h6>
                <div id="progressBar" class="progress mb-2" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                         role="progressbar" 
                         style="width: 0%"
                         id="progressBarInner">0%</div>
                </div>
                <small class="text-muted">
                    <span id="progressText">Belum ada item yang diberi skor</span>
                </small>
                
                <!-- Detail Skor -->
                <div class="row mt-3 pt-3 border-top">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-square text-primary me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Item Dikerjakan</small>
                                <strong class="text-dark" id="itemsDone" style="font-size: 1.2rem;">0 / 0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-calculator text-success me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Total Skor</small>
                                <strong class="text-dark" id="totalScore" style="font-size: 1.2rem;">0 / 0</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-trophy text-warning me-2" style="font-size: 1.5rem;"></i>
                            <div>
                                <small class="text-muted d-block">Nilai</small>
                                <strong class="text-primary" id="nilaiScore" style="font-size: 1.2rem;">0.00</strong>
                                <span class="badge bg-secondary ms-2" id="kategoriScore">-</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="<?php echo BASE_URL; ?>modules/penilaian-list.php" class="btn btn-secondary btn-lg">
                    <i class="bi bi-x-circle"></i> Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Simpan Penilaian
                </button>
            </div>
            
            <?php endif; ?>
        </div>
    </div>
</form>

<script>
// Toggle collapse aspek
function toggleAspek(headerElement) {
    const aspekContent = headerElement.nextElementSibling;
    const toggleIcon = headerElement.querySelector('.aspek-toggle');
    
    if (aspekContent && aspekContent.classList.contains('aspek-content')) {
        const isCollapsed = aspekContent.style.maxHeight === '0px';
        
        if (isCollapsed) {
            // Expand
            aspekContent.style.maxHeight = aspekContent.scrollHeight + 'px';
            toggleIcon.style.transform = 'rotate(0deg)';
        } else {
            // Collapse
            aspekContent.style.maxHeight = '0px';
            toggleIcon.style.transform = 'rotate(-90deg)';
        }
    }
}

// Expand all aspek
function expandAllAspek() {
    const aspekContents = document.querySelectorAll('.aspek-content');
    const toggleIcons = document.querySelectorAll('.aspek-toggle');
    
    aspekContents.forEach(content => {
        content.style.maxHeight = content.scrollHeight + 'px';
    });
    
    toggleIcons.forEach(icon => {
        icon.style.transform = 'rotate(0deg)';
    });
}

// Collapse all aspek
function collapseAllAspek() {
    const aspekContents = document.querySelectorAll('.aspek-content');
    const toggleIcons = document.querySelectorAll('.aspek-toggle');
    
    aspekContents.forEach(content => {
        content.style.maxHeight = '0px';
    });
    
    toggleIcons.forEach(icon => {
        icon.style.transform = 'rotate(-90deg)';
    });
}

// Auto-calculate progress
function updateProgress() {
    const allRadios = document.querySelectorAll('.skor-radio');
    const totalItems = allRadios.length / 5; // Dibagi 5 karena ada 5 opsi (0-4)
    
    const checkedRadios = document.querySelectorAll('.skor-radio:checked');
    const filledItems = checkedRadios.length;
    
    // Hitung total skor
    let skorPerolehan = 0;
    let skorMaksimal = totalItems * 4; // Setiap item maksimal 4
    
    checkedRadios.forEach(radio => {
        skorPerolehan += parseInt(radio.value);
    });
    
    // Hitung nilai (0-100)
    const nilai = skorMaksimal > 0 ? (skorPerolehan / skorMaksimal) * 100 : 0;
    
    // Tentukan kategori
    let kategori = '-';
    let badgeClass = 'bg-secondary';
    if (nilai >= 91) {
        kategori = 'A (Amat Baik)';
        badgeClass = 'bg-success';
    } else if (nilai >= 86) {
        kategori = 'B (Baik)';
        badgeClass = 'bg-info';
    } else if (nilai >= 71) {
        kategori = 'C (Cukup)';
        badgeClass = 'bg-primary';
    } else if (nilai >= 55) {
        kategori = 'D (Sedang)';
        badgeClass = 'bg-warning';
    } else if (filledItems > 0) {
        kategori = 'E (Kurang)';
        badgeClass = 'bg-danger';
    }
    
    const percentage = totalItems > 0 ? Math.round((filledItems / totalItems) * 100) : 0;
    
    const progressBar = document.getElementById('progressBarInner');
    const progressText = document.getElementById('progressText');
    const progressInfo = document.getElementById('progressInfo');
    const requiredIndicator = document.getElementById('requiredIndicator');
    
    // Update detail skor
    const itemsDone = document.getElementById('itemsDone');
    const totalScore = document.getElementById('totalScore');
    const nilaiScore = document.getElementById('nilaiScore');
    const kategoriScore = document.getElementById('kategoriScore');
    
    if (itemsDone) itemsDone.textContent = filledItems + ' / ' + totalItems;
    if (totalScore) totalScore.textContent = skorPerolehan + ' / ' + skorMaksimal;
    if (nilaiScore) nilaiScore.textContent = nilai.toFixed(2);
    if (kategoriScore) {
        kategoriScore.textContent = kategori;
        kategoriScore.className = 'badge ms-2 ' + badgeClass;
    }
    
    if (progressBar && progressText) {
        progressBar.style.width = percentage + '%';
        progressBar.textContent = percentage + '%';
        
        if (percentage === 0) {
            progressText.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Belum ada item yang diberi skor';
            progressBar.classList.remove('bg-success', 'bg-info', 'bg-warning');
            progressBar.classList.add('bg-danger');
            progressInfo?.classList.add('incomplete');
            if (requiredIndicator) requiredIndicator.style.display = 'inline';
        } else if (percentage < 50) {
            progressText.innerHTML = '<i class="bi bi-hourglass-split"></i> ' + filledItems + ' dari ' + totalItems + ' item telah diberi skor';
            progressBar.classList.remove('bg-success', 'bg-info');
            progressBar.classList.add('bg-warning');
            progressInfo?.classList.add('incomplete');
            if (requiredIndicator) requiredIndicator.style.display = 'inline';
        } else if (percentage < 100) {
            progressText.innerHTML = '<i class="bi bi-hourglass-bottom"></i> ' + filledItems + ' dari ' + totalItems + ' item telah diberi skor (Tinggal ' + (totalItems - filledItems) + ' lagi)';
            progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-success');
            progressBar.classList.add('bg-info');
            progressInfo?.classList.add('incomplete');
            if (requiredIndicator) requiredIndicator.style.display = 'inline';
        } else {
            progressText.innerHTML = '<i class="bi bi-check-circle-fill"></i> Semua item telah diberi skor! Siap untuk disimpan (' + totalItems + ' item)';
            progressBar.classList.remove('bg-danger', 'bg-warning', 'bg-info');
            progressBar.classList.add('bg-success');
            progressInfo?.classList.remove('incomplete');
            if (requiredIndicator) requiredIndicator.style.display = 'none';
        }
    }
}

// Update progress on page load and radio change
document.addEventListener('DOMContentLoaded', function() {
    updateProgress();
    
    const radios = document.querySelectorAll('.skor-radio');
    radios.forEach(radio => {
        radio.addEventListener('change', updateProgress);
    });
});

// Validasi form - harus 100% sebelum submit
document.getElementById('formPenilaian')?.addEventListener('submit', function(e) {
    const totalRadios = document.querySelectorAll('.skor-radio').length / 5;
    const filledRadios = document.querySelectorAll('.skor-radio:checked').length;
    const percentage = totalRadios > 0 ? Math.round((filledRadios / totalRadios) * 100) : 0;
    
    if (percentage < 100) {
        e.preventDefault();
        
        // Tampilkan notifikasi
        const sisaItem = totalRadios - filledRadios;
        alert('❌ Progress Penilaian Belum 100%!\n\n' +
              'Progress saat ini: ' + percentage + '%\n' +
              'Item yang belum diisi: ' + sisaItem + ' dari ' + totalRadios + '\n\n' +
              'Silakan lengkapi semua skor terlebih dahulu sebelum menyimpan.');
        
        // Scroll ke progress bar
        document.getElementById('progressBar')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        return false;
    }
    
    // Konfirmasi jika sudah 100%
    return confirm('✅ Progress Penilaian: 100%\n\nSemua item telah diberi skor.\nLanjutkan menyimpan penilaian?');
});
</script>

<?php require_once '../includes/footer.php'; ?>
