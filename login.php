<?php
// Include configuration
$config_path = __DIR__ . '/config/config.php';
if (!file_exists($config_path)) {
    die('Config file not found: ' . $config_path);
}
require_once $config_path;

// Verify required functions are loaded
if (!function_exists('isPost')) {
    die('Required helper functions not loaded from config.php');
}

// Check if user already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

if (isPost()) {
    $username = post('username');
    $password = post('password');
    
    if (empty($username) || empty($password)) {
        setFlash('danger', 'Username dan password harus diisi!');
    } else {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, username, nama_lengkap, email, role FROM users WHERE username = ? AND password = MD5(?) AND is_active = 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();
            
            setFlash('success', 'Login berhasil! Selamat datang, ' . $user['nama_lengkap']);
            redirect('dashboard.php');
        } else {
            setFlash('danger', 'Username atau password salah!');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 450px;
            width: 100%;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .login-body {
            padding: 40px 30px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-clipboard-check"></i>
                <h3 class="mb-2"><?php echo APP_NAME; ?></h3>
                <p class="mb-0"><?php echo APP_DESCRIPTION; ?></p>
            </div>
            <div class="login-body">
                <?php 
                $flash = getFlash();
                if ($flash): 
                ?>
                <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                    <?php echo $flash['message']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label"><i class="bi bi-person"></i> Username</label>
                        <input type="text" class="form-control" name="username" placeholder="Masukkan username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label"><i class="bi bi-lock"></i> Password</label>
                        <input type="password" class="form-control" name="password" placeholder="Masukkan password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-login w-100">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>
                
                <hr class="my-4">
                
                <div class="text-center text-muted">
                    <small>
                        <strong>Akun Demo:</strong><br>
                        Username: <code>admin</code> | Password: <code>admin123</code><br>
                        Username: <code>operator</code> | Password: <code>operator123</code>
                    </small>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-3 text-white">
            <small>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?> v<?php echo APP_VERSION; ?></small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
