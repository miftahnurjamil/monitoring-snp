<?php
require_once '../includes/auth.php';

// Only admin can access this page
if (!hasRole('admin')) {
    setFlash('danger', 'Anda tidak memiliki akses ke halaman ini!');
    redirect('dashboard.php');
}

$db = Database::getInstance();

// Handle Delete BEFORE any output
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Prevent deleting own account
    if ($id == $_SESSION['user_id']) {
        setFlash('danger', 'Anda tidak dapat menghapus akun sendiri!');
    } else {
        $db->query("DELETE FROM users WHERE id = $id");
        setFlash('success', 'User berhasil dihapus!');
    }
    redirect('modules/user.php');
}

// Handle Toggle Active Status
if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    
    // Prevent deactivating own account
    if ($id == $_SESSION['user_id']) {
        setFlash('danger', 'Anda tidak dapat menonaktifkan akun sendiri!');
    } else {
        $db->query("UPDATE users SET is_active = NOT is_active WHERE id = $id");
        setFlash('success', 'Status user berhasil diubah!');
    }
    redirect('modules/user.php');
}

// Handle Add/Edit BEFORE any output
if (isPost()) {
    $id = post('id');
    $username = post('username');
    $nama_lengkap = post('nama_lengkap');
    $email = post('email');
    $role = post('role');
    $password = post('password');
    $is_active = post('is_active', 1);
    
    // Validation
    if (empty($username) || empty($nama_lengkap)) {
        setFlash('danger', 'Username dan nama lengkap harus diisi!');
        redirect('modules/user.php');
    }
    
    if ($id) {
        // Update
        if (!empty($password)) {
            // Update with new password
            $stmt = $db->prepare("UPDATE users SET username=?, password=MD5(?), nama_lengkap=?, email=?, role=?, is_active=? WHERE id=?");
            $stmt->bind_param("sssssii", $username, $password, $nama_lengkap, $email, $role, $is_active, $id);
        } else {
            // Update without changing password
            $stmt = $db->prepare("UPDATE users SET username=?, nama_lengkap=?, email=?, role=?, is_active=? WHERE id=?");
            $stmt->bind_param("ssssii", $username, $nama_lengkap, $email, $role, $is_active, $id);
        }
        $stmt->execute();
        setFlash('success', 'Data user berhasil diupdate!');
    } else {
        // Insert - password is required for new user
        if (empty($password)) {
            setFlash('danger', 'Password harus diisi untuk user baru!');
            redirect('modules/user.php');
        }
        
        // Check if username already exists
        $check = $db->query("SELECT id FROM users WHERE username = '" . $db->escape($username) . "'")->fetch_assoc();
        if ($check) {
            setFlash('danger', 'Username sudah digunakan!');
            redirect('modules/user.php');
        }
        
        $stmt = $db->prepare("INSERT INTO users (username, password, nama_lengkap, email, role, is_active) VALUES (?, MD5(?), ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $username, $password, $nama_lengkap, $email, $role, $is_active);
        $stmt->execute();
        setFlash('success', 'User baru berhasil ditambahkan!');
    }
    redirect('modules/user.php');
}

$pageTitle = 'Manajemen User';
require_once '../includes/header.php';

// Get data for edit
$editData = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $editData = $db->query("SELECT * FROM users WHERE id = $id")->fetch_assoc();
}

// Get all users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<div class="page-header">
    <h2><i class="bi bi-people"></i> <?php echo $pageTitle; ?></h2>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-<?php echo $editData ? 'pencil' : 'plus'; ?>-circle"></i>
                    <?php echo $editData ? 'Edit' : 'Tambah'; ?> User
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="username" 
                               value="<?php echo $editData['username'] ?? ''; ?>" 
                               required <?php echo $editData ? 'readonly' : ''; ?>>
                        <?php if ($editData): ?>
                        <small class="text-muted">Username tidak dapat diubah</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password <?php echo $editData ? '' : '<span class="text-danger">*</span>'; ?></label>
                        <input type="password" class="form-control" name="password" 
                               <?php echo $editData ? '' : 'required'; ?>>
                        <?php if ($editData): ?>
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_lengkap" 
                               value="<?php echo $editData['nama_lengkap'] ?? ''; ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               value="<?php echo $editData['email'] ?? ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="role" required>
                            <option value="admin" <?php echo (isset($editData) && $editData['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                            <option value="penilik" <?php echo (isset($editData) && $editData['role'] == 'penilik') ? 'selected' : ''; ?>>Penilik</option>
                            <option value="operator" <?php echo (isset($editData) && $editData['role'] == 'operator') ? 'selected' : ''; ?>>Operator</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="is_active">
                            <option value="1" <?php echo (!isset($editData) || $editData['is_active'] == 1) ? 'selected' : ''; ?>>Aktif</option>
                            <option value="0" <?php echo (isset($editData) && $editData['is_active'] == 0) ? 'selected' : ''; ?>>Nonaktif</option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Simpan
                        </button>
                        <?php if ($editData): ?>
                        <a href="user.php" class="btn btn-secondary">
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
                <h5 class="mb-0"><i class="bi bi-list"></i> Daftar User</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th width="5%">No</th>
                                <th width="15%">Username</th>
                                <th width="25%">Nama Lengkap</th>
                                <th width="20%">Email</th>
                                <th width="10%">Role</th>
                                <th width="10%">Status</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            foreach ($users as $user): 
                                $isCurrentUser = ($user['id'] == $_SESSION['user_id']);
                            ?>
                            <tr <?php echo $isCurrentUser ? 'class="table-info"' : ''; ?>>
                                <td><?php echo $no++; ?></td>
                                <td>
                                    <strong><?php echo $user['username']; ?></strong>
                                    <?php if ($isCurrentUser): ?>
                                    <br><small class="badge bg-info">You</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $user['nama_lengkap']; ?></td>
                                <td><?php echo $user['email'] ?: '-'; ?></td>
                                <td>
                                    <?php 
                                    $roleBadge = '';
                                    switch($user['role']) {
                                        case 'admin':
                                            $roleBadge = '<span class="badge bg-danger">Admin</span>';
                                            break;
                                        case 'penilik':
                                            $roleBadge = '<span class="badge bg-primary">Penilik</span>';
                                            break;
                                        case 'operator':
                                            $roleBadge = '<span class="badge bg-secondary">Operator</span>';
                                            break;
                                    }
                                    echo $roleBadge;
                                    ?>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?edit=<?php echo $user['id']; ?>" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if (!$isCurrentUser): ?>
                                        <a href="?toggle=<?php echo $user['id']; ?>" 
                                           class="btn btn-info" 
                                           title="<?php echo $user['is_active'] ? 'Nonaktifkan' : 'Aktifkan'; ?>"
                                           onclick="return confirm('Ubah status user ini?')">
                                            <i class="bi bi-<?php echo $user['is_active'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                        </a>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           class="btn btn-danger" title="Hapus"
                                           onclick="return confirm('Yakin hapus user ini?')">
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
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
