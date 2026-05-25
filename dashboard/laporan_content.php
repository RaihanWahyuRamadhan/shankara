<?php
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');

// Laporan Pengiriman
$laporanPengiriman = fetchAll(query("
    SELECT p.*, pr.nama_proyek, w.nama_warehouse,
           (SELECT SUM(jumlah) FROM detail_pengiriman WHERE pengiriman_id = p.id) as total_barang
    FROM pengiriman p
    JOIN proyek pr ON p.proyek_id = pr.id
    JOIN warehouse w ON p.warehouse_id = w.id
    WHERE p.tanggal_pengiriman BETWEEN '$from_date' AND '$to_date'
    ORDER BY p.tanggal_pengiriman DESC
"));

// Laporan Stok per Warehouse
$laporanStok = fetchAll(query("
    SELECT b.nama_barang, b.kode_barang, w.nama_warehouse, s.jumlah
    FROM stok s
    JOIN barang b ON s.barang_id = b.id
    JOIN warehouse w ON s.warehouse_id = w.id
    ORDER BY w.nama_warehouse, b.nama_barang
"));

// Laporan Status Proyek
$laporanProyek = fetchAll(query("
    SELECT status, COUNT(*) as jumlah 
    FROM proyek 
    GROUP BY status
"));

// Laporan Riwayat Aktivitas
$laporanAktivitas = fetchAll(query("
    SELECT r.*, u.nama_lengkap 
    FROM riwayat_aktivitas r
    JOIN users u ON r.user_id = u.id
    ORDER BY r.created_at DESC 
    LIMIT 50
"));

// Laporan Absensi
$laporanAbsensi = fetchAll(query("
    SELECT a.*, u.nama_lengkap, u.role
    FROM absensi a
    JOIN users u ON a.user_id = u.id
    WHERE a.tanggal BETWEEN '$from_date' AND '$to_date'
    ORDER BY a.tanggal DESC
"));

// Statistik
$totalPengiriman = fetchOne(query("SELECT COUNT(*) as total FROM pengiriman WHERE tanggal_pengiriman BETWEEN '$from_date' AND '$to_date'"));
$totalPengirimanVal = isset($totalPengiriman['total']) ? $totalPengiriman['total'] : 0;
$totalProyekAktif = fetchOne(query("SELECT COUNT(*) as total FROM proyek WHERE status IN ('planning', 'ongoing')"));
$totalProyekAktifVal = isset($totalProyekAktif['total']) ? $totalProyekAktif['total'] : 0;
$totalBarang = fetchOne(query("SELECT COUNT(*) as total FROM barang"));
$totalBarangVal = isset($totalBarang['total']) ? $totalBarang['total'] : 0;
$totalUser = fetchOne(query("SELECT COUNT(*) as total FROM users"));
$totalUserVal = isset($totalUser['total']) ? $totalUser['total'] : 0;
?>

<style>
/* ========================================
   STYLE LAPORAN - VERSI RAPI
   ======================================== */
.laporan-container {
    background: #f0f2f5;
    min-height: 100vh;
    padding: 20px;
}

.laporan-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 24px;
    overflow: hidden;
}

.laporan-card-header {
    background: white;
    padding: 16px 24px;
    border-bottom: 2px solid #e9ecef;
}

.laporan-card-header h5 {
    margin: 0;
    font-weight: 700;
    font-size: 1rem;
    color: #2c3e50;
}

.laporan-card-header h5 i {
    margin-right: 8px;
    color: #3498db;
}

.laporan-card-body {
    padding: 20px 24px;
}

.stat-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 24px;
}

.stat-item {
    background: white;
    border-radius: 16px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: transform 0.2s;
}

.stat-item:hover {
    transform: translateY(-3px);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 24px;
    color: white;
}

.icon-blue { background: linear-gradient(135deg, #3498db, #2980b9); }
.icon-green { background: linear-gradient(135deg, #2ecc71, #27ae60); }
.icon-orange { background: linear-gradient(135deg, #e67e22, #d35400); }
.icon-purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.stat-number {
    font-size: 28px;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 4px;
}

.stat-label {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-bar {
    background: white;
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-group {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-input {
    display: flex;
    flex-direction: column;
}

.filter-input label {
    font-size: 12px;
    font-weight: 600;
    color: #7f8c8d;
    margin-bottom: 5px;
}

.filter-input input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
}

.btn-filter {
    background: #3498db;
    color: white;
    border: none;
    padding: 8px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
}

.btn-filter:hover {
    background: #2980b9;
}

.btn-export-group {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-export {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-excel {
    background: #27ae60;
    color: white;
}

.btn-excel:hover {
    background: #229954;
    color: white;
}

.table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.data-table th {
    background: #f8f9fa;
    padding: 12px;
    text-align: left;
    font-weight: 700;
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
}

.data-table td {
    padding: 10px 12px;
    border-bottom: 1px solid #e9ecef;
    color: #34495e;
}

.data-table tr:hover {
    background: #f8f9fa;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.badge-pending { background: #95a5a6; color: white; }
.badge-validasi { background: #3498db; color: white; }
.badge-dikirim { background: #2980b9; color: white; }
.badge-dalam_perjalanan { background: #f39c12; color: white; }
.badge-sampai { background: #27ae60; color: white; }
.badge-batal { background: #e74c3c; color: white; }
.badge-planning { background: #95a5a6; color: white; }
.badge-ongoing { background: #3498db; color: white; }
.badge-completed { background: #27ae60; color: white; }
.badge-delayed { background: #e74c3c; color: white; }
.badge-hadir { background: #27ae60; color: white; }
.badge-izin { background: #f39c12; color: white; }
.badge-sakit { background: #3498db; color: white; }
.badge-alpha { background: #e74c3c; color: white; }

.text-center { text-align: center; }
.text-muted { color: #95a5a6; }
.mt-4 { margin-top: 20px; }
.mb-3 { margin-bottom: 12px; }
.mb-4 { margin-bottom: 20px; }

.progress-bar-custom {
    background: #e9ecef;
    border-radius: 10px;
    height: 6px;
    overflow: hidden;
}

.progress-fill {
    background: #3498db;
    height: 100%;
    border-radius: 10px;
}

.two-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

@media (max-width: 768px) {
    .stat-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .two-columns {
        grid-template-columns: 1fr;
    }
    .filter-bar {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<div class="laporan-container">
    
    <!-- HEADER -->
    <div style="margin-bottom: 24px;">
        <h3 style="margin: 0 0 4px 0; font-weight: 700; color: #2c3e50;">
            <i class="bi bi-file-text" style="color: #3498db;"></i> Laporan Sistem
        </h3>
        <p style="margin: 0; color: #7f8c8d; font-size: 13px;">
            CV Mugi Jaya - Sistem Tracking Barang & Proyek
        </p>
    </div>

    <!-- FILTER & EXPORT -->
    <div class="filter-bar">
        <form method="GET" class="filter-group" style="display: flex; gap: 15px; margin: 0;">
            <div class="filter-input">
                <label><i class="bi bi-calendar"></i> Dari</label>
                <input type="date" name="from_date" value="<?php echo $from_date; ?>">
            </div>
            <div class="filter-input">
                <label><i class="bi bi-calendar"></i> Sampai</label>
                <input type="date" name="to_date" value="<?php echo $to_date; ?>">
            </div>
            <button type="submit" class="btn-filter">
                <i class="bi bi-search"></i> Filter
            </button>
        </form>
        
        <div class="btn-export-group">
            <a href="export_report.php?type=pengiriman&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn-export btn-excel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Pengiriman
            </a>
            <a href="export_report.php?type=stok" class="btn-export btn-excel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Stok
            </a>
            <a href="export_report.php?type=proyek" class="btn-export btn-excel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Proyek
            </a>
            <a href="export_report.php?type=absensi&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn-export btn-excel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Absensi
            </a>
            <a href="export_report.php?type=aktivitas&from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>" class="btn-export btn-excel">
                <i class="bi bi-file-earmark-spreadsheet"></i> Aktivitas
            </a>
        </div>
    </div>

    <!-- STATISTIK CARD -->
    <div class="stat-grid">
        <div class="stat-item">
            <div class="stat-icon icon-blue"><i class="bi bi-truck"></i></div>
            <div class="stat-number"><?php echo $totalPengirimanVal; ?></div>
            <div class="stat-label">Total Pengiriman</div>
            <div style="font-size: 11px; color: #95a5a6;">Periode ini</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon icon-green"><i class="bi bi-building"></i></div>
            <div class="stat-number"><?php echo $totalProyekAktifVal; ?></div>
            <div class="stat-label">Proyek Aktif</div>
            <div style="font-size: 11px; color: #95a5a6;">Planning & Ongoing</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon icon-orange"><i class="bi bi-box"></i></div>
            <div class="stat-number"><?php echo $totalBarangVal; ?></div>
            <div class="stat-label">Total Barang</div>
            <div style="font-size: 11px; color: #95a5a6;">Terdaftar</div>
        </div>
        <div class="stat-item">
            <div class="stat-icon icon-purple"><i class="bi bi-people"></i></div>
            <div class="stat-number"><?php echo $totalUserVal; ?></div>
            <div class="stat-label">Total User</div>
            <div style="font-size: 11px; color: #95a5a6;">Terdaftar</div>
        </div>
    </div>

    <!-- LAPORAN PENGIRIMAN -->
    <div class="laporan-card">
        <div class="laporan-card-header">
            <h5><i class="bi bi-truck"></i> Laporan Pengiriman Barang</h5>
        </div>
        <div class="laporan-card-body">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Pengiriman</th>
                            <th>Proyek</th>
                            <th>Warehouse</th>
                            <th>Sopir</th>
                            <th>No. Kendaraan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($laporanPengiriman)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 40px;">
                                    <i class="bi bi-inbox" style="font-size: 32px;"></i><br>
                                    Belum ada data pengiriman
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($laporanPengiriman as $l): ?>
                            <tr>
                                <td><?php echo $l['no_pengiriman']; ?></td>
                                <td><?php echo $l['nama_proyek']; ?></td>
                                <td><?php echo $l['nama_warehouse']; ?></td>
                                <td><?php echo $l['sopir']; ?></td>
                                <td><?php echo $l['no_kendaraan']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($l['tanggal_pengiriman'])); ?></td>
                                <td>
                                    <?php
                                    $statusText = $l['status'];
                                    $badgeClass = '';
                                    if($statusText == 'pending') $badgeClass = 'badge-pending';
                                    elseif($statusText == 'validasi') $badgeClass = 'badge-validasi';
                                    elseif($statusText == 'dikirim') $badgeClass = 'badge-dikirim';
                                    elseif($statusText == 'dalam_perjalanan') $badgeClass = 'badge-dalam_perjalanan';
                                    elseif($statusText == 'sampai') $badgeClass = 'badge-sampai';
                                    elseif($statusText == 'batal') $badgeClass = 'badge-batal';
                                    else $badgeClass = 'badge-pending';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo str_replace('_', ' ', $statusText); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2 KOLOM: PROYEK & STOK -->
    <div class="two-columns">
        <!-- Status Proyek -->
        <div class="laporan-card">
            <div class="laporan-card-header">
                <h5><i class="bi bi-pie-chart"></i> Status Proyek</h5>
            </div>
            <div class="laporan-card-body">
                <?php 
                $totalProyekAll = 0;
                foreach($laporanProyek as $p) { $totalProyekAll += $p['jumlah']; }
                $statusLabel = ['planning'=>'Planning','ongoing'=>'Ongoing','completed'=>'Selesai','delayed'=>'Terlambat'];
                $statusColor = ['planning'=>'badge-planning','ongoing'=>'badge-ongoing','completed'=>'badge-completed','delayed'=>'badge-delayed'];
                ?>
                <?php foreach($laporanProyek as $proyek): ?>
                <?php $persen = $totalProyekAll > 0 ? round(($proyek['jumlah'] / $totalProyekAll) * 100, 1) : 0; ?>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span class="badge <?php echo isset($statusColor[$proyek['status']]) ? $statusColor[$proyek['status']] : 'badge-pending'; ?>">
                            <?php echo isset($statusLabel[$proyek['status']]) ? $statusLabel[$proyek['status']] : $proyek['status']; ?>
                        </span>
                        <span style="font-size: 13px; font-weight: 600;"><?php echo $proyek['jumlah']; ?> proyek (<?php echo $persen; ?>%)</span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill" style="width: <?php echo $persen; ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Stok Barang -->
        <div class="laporan-card">
            <div class="laporan-card-header">
                <h5><i class="bi bi-boxes"></i> Stok Barang per Warehouse</h5>
            </div>
            <div class="laporan-card-body" style="max-height: 250px; overflow-y: auto;">
                <div class="table-wrapper">
                    <table class="data-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Barang</th>
                                <th>Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($laporanStok)): ?>
                                <tr><td colspan="3" class="text-center text-muted">Belum ada data</td></tr>
                            <?php else: ?>
                                <?php foreach($laporanStok as $stok): ?>
                                <tr>
                                    <td><?php echo $stok['nama_warehouse']; ?></td>
                                    <td><?php echo $stok['nama_barang']; ?></td>
                                    <td><?php echo $stok['jumlah']; ?> unit</td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- LAPORAN ABSENSI -->
    <div class="laporan-card">
        <div class="laporan-card-header">
            <h5><i class="bi bi-calendar-check"></i> Laporan Absensi</h5>
        </div>
        <div class="laporan-card-body">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Karyawan</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($laporanAbsensi)): ?>
                            <tr><td colspan="5" class="text-center text-muted">Belum ada data absensi</td></tr>
                        <?php else: ?>
                            <?php foreach($laporanAbsensi as $a): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($a['tanggal'])); ?></td>
                                <td><?php echo $a['nama_lengkap']; ?></td>
                                <td><?php echo $a['jam_masuk'] ?? '-'; ?></td>
                                <td><?php echo $a['jam_keluar'] ?? '-'; ?></td>
                                <td>
                                    <?php
                                    $absClass = '';
                                    if($a['status'] == 'hadir') $absClass = 'badge-hadir';
                                    elseif($a['status'] == 'izin') $absClass = 'badge-izin';
                                    elseif($a['status'] == 'sakit') $absClass = 'badge-sakit';
                                    else $absClass = 'badge-alpha';
                                    ?>
                                    <span class="badge <?php echo $absClass; ?>"><?php echo $a['status']; ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RIWAYAT AKTIVITAS -->
    <div class="laporan-card">
        <div class="laporan-card-header">
            <h5><i class="bi bi-clock-history"></i> Riwayat Aktivitas (50 terakhir)</h5>
        </div>
        <div class="laporan-card-body" style="max-height: 300px; overflow-y: auto;">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($laporanAktivitas)): ?>
                            <tr><td colspan="3" class="text-center text-muted">Belum ada aktivitas</td></tr>
                        <?php else: ?>
                            <?php foreach($laporanAktivitas as $a): ?>
                            <tr>
                                <td style="white-space: nowrap;"><?php echo date('d/m/Y H:i:s', strtotime($a['created_at'])); ?></td>
                                <td><?php echo $a['nama_lengkap']; ?></td>
                                <td><?php echo $a['aktivitas']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>