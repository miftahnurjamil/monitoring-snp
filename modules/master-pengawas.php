<?php
require_once '../includes/auth.php';

$db = Database::getInstance();

// Handle Delete BEFORE any output
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("DELETE FROM master_pengawas WHERE id = $id");
    setFlash('success', 'Data pengawas berhasil dihapus!');
    redirect('modules/master-pengawas.php');
}

// Handle Add/Edit BEFORE any output
if (isPost()) {
    $id = post('id');
    $nip = post('nip');
    $nama_lengkap = post('nama_lengkap');
    $pangkat_golongan = post('pangkat_golongan');
    $jabatan = post('jabatan');
    $wilayah_binaan = post('wilayah_binaan');
    $telepon = post('telepon');
    $email = post('email');
    $alamat = post('alamat');
    
    if ($id) {
        // Update
        $stmt = $db->prepare("UPDATE master_pengawas SET nip=?, nama_lengkap=?, pangkat_golongan=?, jabatan=?, wilayah_binaan=?, telepon=?, email=?, alamat=? WHERE id=?");
        $stmt->bind_param("ssssssssi", $nip, $nama_lengkap, $pangkat_golongan, $jabatan, $wilayah_binaan, $telepon, $email, $alamat, $id);
        $stmt->execute();
        setFlash('success', 'Data pengawas berhasil diupdate!');
    } else {
        // Insert
        $stmt = $db->prepare("INSERT INTO master_pengawas (nip, nama_lengkap, pangkat_golongan, jabatan, wilayah_binaan, telepon, email, alamat) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $nip, $nama_lengkap, $pangkat_golongan, $jabatan, $wilayah_binaan, $telepon, $email, $alamat);
        $stmt->execute();
        setFlash('success', 'Data pengawas berhasil ditambahkan!');
    }
    redirect('modules/master-pengawas.php');
}

$pageTitle = 'Master Pengawas';
require_once '../includes/header.php';

// Get data for edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = $db->query("SELECT * FROM master_pengawas WHERE id = $id")->fetch_assoc();
}

// Get all data
$dataPengawas = $db->query("SELECT * FROM master_pengawas ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="bi bi-person-badge"></i> <?php echo $pageTitle; ?></h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $editData ? 'pencil' : 'plus'; ?>-circle"></i>
                    <?php echo $editData ? 'Edit' : 'Tambah'; ?> Pengawas
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">NIP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nip" value="<?php echo $editData['nip'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" value="<?php echo $editData['nama_lengkap'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Pangkat/Golongan</label>
                        <input type="text" class="form-control" name="pangkat_golongan" value="<?php echo $editData['pangkat_golongan'] ?? ''; ?>" placeholder="Contoh: Pembina Tk.I / IV-b">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Jabatan</label>
                        <input type="text" class="form-control" name="jabatan" value="<?php echo $editData['jabatan'] ?? ''; ?>" placeholder="Contoh: Pengawas TK/PAUD">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Wilayah Binaan</label>
                        <input type="text" class="form-control" name="wilayah_binaan" value="<?php echo $editData['wilayah_binaan'] ?? ''; ?>" placeholder="Contoh: Kecamatan Tasikmalaya">
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
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"><?php echo $editData['alamat'] ?? ''; ?></textarea>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <?php if ($editData): ?>
                        <a href="<?php echo BASE_URL; ?>modules/master-pengawas.php" class="btn btn-secondary">
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
                <h5 class="mb-0"><i class="bi bi-table"></i> Daftar Pengawas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover datatable">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>NIP</th>
                                <th>Nama Lengkap</th>
                                <th>Pangkat/Golongan</th>
                                <th>Jabatan</th>
                                <th>Wilayah Binaan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; foreach ($dataPengawas as $item): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $item['nip']; ?></td>
                                <td><strong><?php echo $item['nama_lengkap']; ?></strong></td>
                                <td><?php echo $item['pangkat_golongan']; ?></td>
                                <td><?php echo $item['jabatan']; ?></td>
                                <td><?php echo $item['wilayah_binaan']; ?></td>
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
