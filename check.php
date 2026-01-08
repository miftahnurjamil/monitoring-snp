<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Check - Monitoring SNP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }
        .check-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .check-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .check-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .check-item {
            padding: 15px 30px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .check-item:last-child {
            border-bottom: none;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="check-container">
        <div class="check-card">
            <div class="check-header">
                <i class="bi bi-clipboard-check" style="font-size: 3rem;"></i>
                <h2 class="mt-3">System Check</h2>
                <p class="mb-0">Monitoring SNP Application</p>
            </div>
            
            <div class="p-4">
                <?php
                $allOk = true;
                
                // Check PHP Version
                $phpVersion = phpversion();
                $phpOk = version_compare($phpVersion, '7.4.0', '>=');
                if (!$phpOk) $allOk = false;
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-code-square"></i> PHP Version</strong>
                        <br><small class="text-muted">Required: >= 7.4</small>
                    </div>
                    <div class="<?php echo $phpOk ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $phpOk ? '✓' : '✗'; ?> <?php echo $phpVersion; ?>
                    </div>
                </div>
                
                <?php
                // Check MySQL Connection
                $db_host = 'localhost';
                $db_user = 'root';
                $db_pass = '';
                
                $mysqlOk = false;
                $mysqlError = '';
                try {
                    $conn = @new mysqli($db_host, $db_user, $db_pass);
                    if ($conn->connect_error) {
                        $mysqlError = $conn->connect_error;
                    } else {
                        $mysqlOk = true;
                        $conn->close();
                    }
                } catch (Exception $e) {
                    $mysqlError = $e->getMessage();
                }
                if (!$mysqlOk) $allOk = false;
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-database"></i> MySQL Connection</strong>
                        <br><small class="text-muted">Host: <?php echo $db_host; ?></small>
                    </div>
                    <div class="<?php echo $mysqlOk ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $mysqlOk ? '✓ Connected' : '✗ ' . $mysqlError; ?>
                    </div>
                </div>
                
                <?php
                // Check Database Exists
                $dbExists = false;
                if ($mysqlOk) {
                    try {
                        $conn = new mysqli($db_host, $db_user, $db_pass, 'monitoring_snp_app');
                        if (!$conn->connect_error) {
                            $dbExists = true;
                            $conn->close();
                        }
                    } catch (Exception $e) {
                        // Database doesn't exist
                    }
                }
                if (!$dbExists) $allOk = false;
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-server"></i> Database 'monitoring_snp_app'</strong>
                        <br><small class="text-muted">Database harus sudah di-import</small>
                    </div>
                    <div class="<?php echo $dbExists ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $dbExists ? '✓ Exists' : '✗ Not Found'; ?>
                    </div>
                </div>
                
                <?php
                // Check Tables
                $tablesOk = false;
                $tableCount = 0;
                if ($dbExists) {
                    $conn = new mysqli($db_host, $db_user, $db_pass, 'monitoring_snp_app');
                    $result = $conn->query("SHOW TABLES");
                    $tableCount = $result->num_rows;
                    $tablesOk = $tableCount >= 10;
                    $conn->close();
                }
                if (!$tablesOk && $dbExists) $allOk = false;
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-table"></i> Database Tables</strong>
                        <br><small class="text-muted">Required: >= 10 tables</small>
                    </div>
                    <div class="<?php echo $tablesOk ? 'status-ok' : ($dbExists ? 'status-error' : 'status-warning'); ?>">
                        <?php 
                        if ($dbExists) {
                            echo $tablesOk ? "✓ $tableCount tables" : "✗ $tableCount tables"; 
                        } else {
                            echo "⚠ Database not found";
                        }
                        ?>
                    </div>
                </div>
                
                <?php
                // Check config file
                $configExists = file_exists(__DIR__ . '/config/config.php');
                if (!$configExists) $allOk = false;
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-gear"></i> Config File</strong>
                        <br><small class="text-muted">config/config.php</small>
                    </div>
                    <div class="<?php echo $configExists ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $configExists ? '✓ Exists' : '✗ Not Found'; ?>
                    </div>
                </div>
                
                <?php
                // Check writable uploads folder
                $uploadsPath = __DIR__ . '/uploads';
                $uploadsWritable = is_dir($uploadsPath) && is_writable($uploadsPath);
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-folder"></i> Uploads Folder</strong>
                        <br><small class="text-muted">uploads/ (writable)</small>
                    </div>
                    <div class="<?php echo $uploadsWritable ? 'status-ok' : 'status-warning'; ?>">
                        <?php echo $uploadsWritable ? '✓ Writable' : '⚠ Create folder'; ?>
                    </div>
                </div>
                
                <?php
                // Check extensions
                $extensionsOk = extension_loaded('mysqli') && extension_loaded('mbstring');
                ?>
                
                <div class="check-item">
                    <div>
                        <strong><i class="bi bi-plug"></i> PHP Extensions</strong>
                        <br><small class="text-muted">mysqli, mbstring</small>
                    </div>
                    <div class="<?php echo $extensionsOk ? 'status-ok' : 'status-error'; ?>">
                        <?php echo $extensionsOk ? '✓ Loaded' : '✗ Missing'; ?>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <?php if ($allOk): ?>
                <div class="alert alert-success text-center">
                    <h4><i class="bi bi-check-circle"></i> Sistem Siap Digunakan!</h4>
                    <p class="mb-3">Semua komponen sudah terpasang dengan benar.</p>
                    <a href="login.php" class="btn btn-success btn-lg">
                        <i class="bi bi-box-arrow-in-right"></i> Login ke Aplikasi
                    </a>
                </div>
                <?php else: ?>
                <div class="alert alert-danger text-center">
                    <h4><i class="bi bi-exclamation-triangle"></i> Ada Masalah!</h4>
                    <p class="mb-0">Silakan perbaiki error di atas sebelum menggunakan aplikasi.</p>
                </div>
                
                <?php if (!$dbExists): ?>
                <div class="alert alert-info">
                    <strong><i class="bi bi-info-circle"></i> Cara Import Database:</strong><br>
                    1. Buka http://localhost/phpmyadmin<br>
                    2. Klik tab "SQL"<br>
                    3. Klik "Choose File" dan pilih <code>config/database_snp.sql</code><br>
                    4. Klik "Go"<br>
                    5. Refresh halaman ini
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="check.php" class="btn btn-primary">
                        <i class="bi bi-arrow-clockwise"></i> Refresh Check
                    </a>
                    <a href="INSTALL.md" class="btn btn-secondary" target="_blank">
                        <i class="bi bi-book"></i> Panduan Instalasi
                    </a>
                </div>
            </div>
            
            <div class="text-center p-3 bg-light">
                <small class="text-muted">
                    Monitoring SNP v1.0.0 | 
                    <a href="README.md" target="_blank">Documentation</a>
                </small>
            </div>
        </div>
    </div>
</body>
</html>
