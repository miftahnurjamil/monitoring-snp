<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            margin: 0;
            font-weight: 600;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .menu-item {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .sidebar-menu .menu-item:hover,
        .sidebar-menu .menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-menu .menu-item i {
            margin-right: 12px;
            font-size: 1.2rem;
            width: 25px;
        }
        
        .sidebar-menu .submenu {
            padding-left: 40px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Navbar */
        .navbar-custom {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            padding: 15px 30px;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        /* Cards */
        .stat-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        /* Page Header */
        .page-header {
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .page-header h2 {
            color: #333;
            font-weight: 600;
        }
        
        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            opacity: 0.9;
        }
        
        /* DataTable Custom */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        
        /* Badge */
        .badge-skor {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="bi bi-clipboard-check" style="font-size: 2rem;"></i>
            <h4 class="mt-2"><?php echo APP_NAME; ?></h4>
            <small>v<?php echo APP_VERSION; ?></small>
        </div>
        
        <div class="sidebar-menu">
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            
            <div class="mt-3 px-3">
                <small class="text-white-50">DATA MASTER</small>
            </div>
            
            <a href="<?php echo BASE_URL; ?>modules/master-snp.php" class="menu-item">
                <i class="bi bi-list-check"></i>
                <span>Master SNP</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>modules/master-sekolah.php" class="menu-item">
                <i class="bi bi-building"></i>
                <span>Master Sekolah</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>modules/master-penilik.php" class="menu-item">
                <i class="bi bi-person-badge"></i>
                <span>Master Penilik</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>modules/master-pertanyaan.php" class="menu-item">
                <i class="bi bi-question-circle"></i>
                <span>Master Pertanyaan</span>
            </a>
            
            <div class="mt-3 px-3">
                <small class="text-white-50">TRANSAKSI</small>
            </div>
            
            <a href="<?php echo BASE_URL; ?>modules/penilaian-list.php" class="menu-item">
                <i class="bi bi-clipboard-data"></i>
                <span>Data Penilaian</span>
            </a>
            
            <a href="<?php echo BASE_URL; ?>modules/penilaian-add.php" class="menu-item">
                <i class="bi bi-plus-circle"></i>
                <span>Input Penilaian Baru</span>
            </a>
            
            <div class="mt-3 px-3">
                <small class="text-white-50">LAPORAN</small>
            </div>
            
            <a href="<?php echo BASE_URL; ?>modules/laporan.php" class="menu-item">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Laporan SNP</span>
            </a>
            
            <?php if (hasRole('admin')): ?>
            <div class="mt-3 px-3">
                <small class="text-white-50">PENGATURAN</small>
            </div>
            
            <a href="<?php echo BASE_URL; ?>modules/user.php" class="menu-item">
                <i class="bi bi-people"></i>
                <span>Manajemen User</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-custom">
            <div class="container-fluid">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h5>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="text-decoration-none text-dark dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle" style="font-size: 1.5rem;"></i>
                            <span class="ms-2"><?php echo $_SESSION['nama_lengkap']; ?></span>
                            <span class="badge bg-primary ms-1"><?php echo strtoupper($_SESSION['role']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>modules/profile.php"><i class="bi bi-person"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Content -->
        <div class="content-wrapper">
            <?php 
            $flash = getFlash();
            if ($flash): 
            ?>
            <div class="alert alert-<?php echo $flash['type']; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-<?php echo $flash['type'] == 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                <?php echo $flash['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
