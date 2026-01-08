<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

// Handle Delete BEFORE any output
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM master_sekolah WHERE id = $id");
    setFlash('success', 'Data sekolah berhasil dihapus!');
    redirect('modules/master-sekolah.php');
}

// Handle Add/Edit BEFORE any output
if (isPost()) {
    $id = post('id');
    $npsn = post('npsn');
    $nama_sekolah = post('nama_sekolah');
    $jenis_sekolah = post('jenis_sekolah');
    $alamat = post('alamat');
    $kecamatan = post('kecamatan');
    $kabupaten = post('kabupaten');
    $provinsi = post('provinsi');
    $kode_pos = post('kode_pos');
    $telepon = post('telepon');
    $email = post('email');
    $nama_kepala_sekolah = post('nama_kepala_sekolah');
    $nip_kepala_sekolah = post('nip_kepala_sekolah');
    
    if ($id) {
        // Update
        $stmt = $db->prepare("UPDATE master_sekolah SET npsn=?, nama_sekolah=?, jenis_sekolah=?, alamat=?, kecamatan=?, kabupaten=?, provinsi=?, kode_pos=?, telepon=?, email=?, nama_kepala_sekolah=?, nip_kepala_sekolah=? WHERE id=?");
        $stmt->bind_param("ssssssssssssi", $npsn, $nama_sekolah, $jenis_sekolah, $alamat, $kecamatan, $kabupaten, $provinsi, $kode_pos, $telepon, $email, $nama_kepala_sekolah, $nip_kepala_sekolah, $id);
        $stmt->execute();
        setFlash('success', 'Data sekolah berhasil diupdate!');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO master_sekolah (npsn, nama_sekolah, jenis_sekolah, alamat, kecamatan, kabupaten, provinsi, kode_pos, telepon, email, nama_kepala_sekolah, nip_kepala_sekolah) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssss", $npsn, $nama_sekolah, $jenis_sekolah, $alamat, $kecamatan, $kabupaten, $provinsi, $kode_pos, $telepon, $email, $nama_kepala_sekolah, $nip_kepala_sekolah);
        $stmt->execute();
        setFlash('success', 'Data sekolah berhasil ditambahkan!');
    }
    redirect('modules/master-sekolah.php');
}

$pageTitle = 'Master Sekolah';
require_once '../includes/header.php';

// Get data for edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = $db->query("SELECT * FROM master_sekolah WHERE id = $id")->fetch_assoc();
}

// Get all data
$dataSekolah = $db->query("SELECT * FROM master_sekolah ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="bi bi-building"></i> <?php echo $pageTitle; ?></h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $editData ? 'pencil' : 'plus'; ?>-circle"></i>
                    <?php echo $editData ? 'Edit' : 'Tambah'; ?> Sekolah
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">NPSN</label>
                        <input type="text" class="form-control" name="npsn" value="<?php echo $editData['npsn'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Sekolah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_sekolah" value="<?php echo $editData['nama_sekolah'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jenis Sekolah</label>
                        <select class="form-select" name="jenis_sekolah" required>
                            <option value="TK" <?php echo (isset($editData) && $editData['jenis_sekolah'] == 'TK') ? 'selected' : ''; ?>>TK</option>
                            <option value="SD" <?php echo (isset($editData) && $editData['jenis_sekolah'] == 'SD') ? 'selected' : ''; ?>>SD</option>
                            <option value="SMP" <?php echo (isset($editData) && $editData['jenis_sekolah'] == 'SMP') ? 'selected' : ''; ?>>SMP</option>
                            <option value="SMA" <?php echo (isset($editData) && $editData['jenis_sekolah'] == 'SMA') ? 'selected' : ''; ?>>SMA</option>
                            <option value="SMK" <?php echo (isset($editData) && $editData['jenis_sekolah'] == 'SMK') ? 'selected' : ''; ?>>SMK</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" rows="2"><?php echo $editData['alamat'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" class="form-control" name="kecamatan" value="<?php echo $editData['kecamatan'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kabupaten/Kota</label>
                        <input type="text" class="form-control" name="kabupaten" value="<?php echo $editData['kabupaten'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Provinsi</label>
                        <input type="text" class="form-control" name="provinsi" value="<?php echo $editData['provinsi'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kode Pos</label>
                        <input type="text" class="form-control" name="kode_pos" value="<?php echo $editData['kode_pos'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Telepon</label>
                        <input type="text" class="form-control" name="telepon" value="<?php echo $editData['telepon'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo $editData['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Kepala Sekolah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kepala_sekolah" value="<?php echo $editData['nama_kepala_sekolah'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">NIP Kepala Sekolah <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nip_kepala_sekolah" value="<?php echo $editData['nip_kepala_sekolah'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <?php if ($editData): ?>
                        <a href="<?php echo BASE_URL; ?>modules/master-sekolah.php" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Batal
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Sekolah</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NPSN</th>
                                <th>Nama Sekolah</th>
                                <th>Jenis</th>
                                <th>Kepala Sekolah</th>
                                <th>NIP</th>
                                <th>Kecamatan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($dataSekolah as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $item['npsn']; ?></td>
                                <td><strong><?php echo $item['nama_sekolah']; ?></strong></td>
                                <td><span class="badge bg-info"><?php echo $item['jenis_sekolah']; ?></span></td>
                                <td><?php echo $item['nama_kepala_sekolah']; ?></td>
                                <td><?php echo $item['nip_kepala_sekolah']; ?></td>
                                <td><?php echo $item['kecamatan']; ?></td>
                                <td>
                                    <a href="?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="javascript:void(0)" onclick="confirmDelete('?delete=<?php echo $item['id']; ?>')" class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
