<?php
require_once 'includes/auth.php';
$pageTitle = 'Dashboard';
require_once 'includes/header.php';

$db = Database::getInstance();

// Statistik
$totalSekolah = $db->query("SELECT COUNT(*) as total FROM master_sekolah WHERE is_active = 1")->fetch_assoc()['total'];
$totalPengawas = $db->query("SELECT COUNT(*) as total FROM master_pengawas WHERE is_active = 1")->fetch_assoc()['total'];
$totalPenilaian = $db->query("SELECT COUNT(*) as total FROM transaksi_penilaian")->fetch_assoc()['total'];
$totalSNP = $db->query("SELECT COUNT(*) as total FROM master_snp WHERE is_active = 1")->fetch_assoc()['total'];

// Data Penilaian Terbaru
$penilaianTerbaru = $db->query("
    SELECT 
        t.kode_penilaian,
        s.nama_sekolah,
        t.tahun_ajaran,
        t.tanggal_penilaian,
        t.status
    FROM transaksi_penilaian t
    LEFT JOIN master_sekolah s ON t.sekolah_id = s.id
    ORDER BY t.created_at DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Data untuk Chart - Penilaian per Bulan (6 bulan terakhir)
$chartData = $db->query("
    SELECT 
        DATE_FORMAT(tanggal_penilaian, '%Y-%m') as bulan,
        COUNT(*) as jumlah
    FROM transaksi_penilaian
    WHERE tanggal_penilaian >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal_penilaian, '%Y-%m')
    ORDER BY bulan
")->fetch_all(MYSQLI_ASSOC);

$bulanLabels = json_encode(array_column($chartData, 'bulan'));
$jumlahData = json_encode(array_column($chartData, 'jumlah'));
?>

<div class="page-header">
    <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
    <p class="text-muted">Selamat datang di sistem monitoring Standar Nasional Pendidikan</p>
</div>

<!-- Statistik Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card border-start border-primary border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Sekolah</h6>
                        <h2 class="mb-0"><?php echo $totalSekolah; ?></h2>
                    </div>
                    <div class="icon text-primary">
                        <i class="bi bi-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card border-start border-success border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Pengawas</h6>
                        <h2 class="mb-0"><?php echo $totalPengawas; ?></h2>
                    </div>
                    <div class="icon text-success">
                        <i class="bi bi-person-badge"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card border-start border-warning border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Penilaian</h6>
                        <h2 class="mb-0"><?php echo $totalPenilaian; ?></h2>
                    </div>
                    <div class="icon text-warning">
                        <i class="bi bi-clipboard-data"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card stat-card border-start border-info border-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Jumlah SNP</h6>
                        <h2 class="mb-0"><?php echo $totalSNP; ?></h2>
                    </div>
                    <div class="icon text-info">
                        <i class="bi bi-list-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Chart Penilaian -->
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Grafik Penilaian SNP</h5>
            </div>
            <div class="card-body">
                <canvas id="chartPenilaian" height="80"></canvas>
            </div>
        </div>
    </div>
    
    <!-- 8 SNP -->
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> 8 Standar Nasional Pendidikan</h5>
            </div>
            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                <?php
                $snpList = $db->query("SELECT * FROM master_snp WHERE is_active = 1 ORDER BY urutan")->fetch_all(MYSQLI_ASSOC);
                foreach ($snpList as $snp):
                ?>
                <div class="d-flex align-items-start mb-3">
                    <div class="badge bg-primary me-2"><?php echo $snp['kode_snp']; ?></div>
                    <div>
                        <strong><?php echo $snp['nama_snp']; ?></strong>
                        <br><small class="text-muted"><?php echo $snp['deskripsi']; ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Penilaian Terbaru -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Penilaian Terbaru</h5>
                <a href="<?php echo BASE_URL; ?>modules/penilaian-list.php" class="btn btn-sm btn-primary">
                    Lihat Semua <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kode Penilaian</th>
                                <th>Sekolah</th>
                                <th>Tahun Ajaran</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($penilaianTerbaru)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p>Belum ada data penilaian</p>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($penilaianTerbaru as $item): ?>
                                <tr>
                                    <td><span class="badge bg-secondary"><?php echo $item['kode_penilaian']; ?></span></td>
                                    <td><?php echo $item['nama_sekolah']; ?></td>
                                    <td><?php echo $item['tahun_ajaran']; ?></td>
                                    <td><?php echo formatTanggal($item['tanggal_penilaian']); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = [
                                            'draft' => 'bg-warning',
                                            'selesai' => 'bg-success',
                                            'terverifikasi' => 'bg-info'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $badgeClass[$item['status']]; ?>">
                                            <?php echo strtoupper($item['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>modules/penilaian-detail.php?kode=<?php echo $item['kode_penilaian']; ?>" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> Lihat
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart Penilaian
const ctx = document.getElementById('chartPenilaian').getContext('2d');
const chartPenilaian = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo $bulanLabels; ?>,
        datasets: [{
            label: 'Jumlah Penilaian',
            data: <?php echo $jumlahData; ?>,
            borderColor: '#667eea',
            backgroundColor: 'rgba(102, 126, 234, 0.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
