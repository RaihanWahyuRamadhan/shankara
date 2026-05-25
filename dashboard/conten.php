<?php
$totalProyek = fetchOne(query("SELECT COUNT(*) as total FROM proyek"))['total'];
$totalPengiriman = fetchOne(query("SELECT COUNT(*) as total FROM pengiriman"))['total'];
$totalBarang = fetchOne(query("SELECT COUNT(*) as total FROM barang"))['total'];
$totalWarehouse = fetchOne(query("SELECT COUNT(*) as total FROM warehouse"))['total'];

$pengirimanAktif = fetchAll(query("
    SELECT p.*, pr.nama_proyek, w.nama_warehouse 
    FROM pengiriman p 
    JOIN proyek pr ON p.proyek_id = pr.id 
    JOIN warehouse w ON p.warehouse_id = w.id 
    WHERE p.status IN ('dikirim', 'dalam_perjalanan')
    ORDER BY p.tanggal_pengiriman DESC LIMIT 5
"));

$notifikasi = fetchAll(query("
    SELECT * FROM notifikasi 
    WHERE user_id = {$_SESSION['user_id']} 
    ORDER BY created_at DESC LIMIT 10
"));

$pengirimanPerBulan = fetchAll(query("
    SELECT DATE_FORMAT(tanggal_pengiriman, '%Y-%m') as bulan, COUNT(*) as total 
    FROM pengiriman 
    WHERE tanggal_pengiriman >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(tanggal_pengiriman, '%Y-%m')
    ORDER BY bulan ASC
"));
?>

<style>
.welcome-section {
    background: linear-gradient(135deg, #4361ee 0%, #7209b7 100%);
    border-radius: 20px;
    padding: 25px 30px;
    color: white;
    margin-bottom: 30px;
}

.welcome-section h2 {
    font-weight: 800;
    margin-bottom: 8px;
}

.welcome-section p {
    opacity: 0.9;
    margin: 0;
}

.stat-card {
    border-radius: 20px;
    padding: 24px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-card h2 {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 10px 0 5px;
}

.stat-card p {
    margin: 0;
    opacity: 0.8;
    font-weight: 500;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.stat-card i {
    font-size: 35px;
}

.stat-card:nth-child(1) { background: linear-gradient(135deg, #4361ee, #7209b7); color: white; }
.stat-card:nth-child(2) { background: linear-gradient(135deg, #06d6a0, #20bf6b); color: white; }
.stat-card:nth-child(3) { background: linear-gradient(135deg, #ef476f, #f093fb); color: white; }
.stat-card:nth-child(4) { background: linear-gradient(135deg, #ffd166, #f6d365); color: #1a1a2e; }

.badge {
    padding: 6px 12px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 0.7rem;
}

.badge-pending { background: #6c757d; color: white; }
.badge-validasi { background: #4cc9f0; color: white; }
.badge-dikirim { background: #4361ee; color: white; }
.badge-dalam_perjalanan { background: #ffd166; color: #1a1a2e; }
.badge-sampai { background: #06d6a0; color: white; }
.badge-batal { background: #ef476f; color: white; }
</style>

<div class="welcome-section">
    <h2>Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?>! 👋</h2>
    <p>Berikut adalah ringkasan aktivitas sistem tracking barang dan proyek CV Mugi Jaya.</p>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-building"></i>
            <h2><?php echo $totalProyek; ?></h2>
            <p>Total Proyek</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-truck"></i>
            <h2><?php echo $totalPengiriman; ?></h2>
            <p>Total Pengiriman</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-box"></i>
            <h2><?php echo $totalBarang; ?></h2>
            <p>Total Barang</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card text-center">
            <i class="bi bi-warehouse"></i>
            <h2><?php echo $totalWarehouse; ?></h2>
            <p>Warehouse</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-graph-up"></i> Grafik Pengiriman 6 Bulan Terakhir</h5>
            </div>
            <div class="card-body">
                <canvas id="pengirimanChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-bell"></i> Notifikasi</h5>
            </div>
            <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                <?php if(empty($notifikasi)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p>Tidak ada notifikasi</p>
                    </div>
                <?php else: ?>
                    <?php foreach($notifikasi as $notif): ?>
                        <div class="border-bottom mb-2 pb-2">
                            <strong><?php echo $notif['judul']; ?></strong><br>
                            <small><?php echo $notif['pesan']; ?></small><br>
                            <small class="text-muted"><?php echo $notif['created_at']; ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5><i class="bi bi-truck"></i> Pengiriman Aktif</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>No. Pengiriman</th>
                        <th>Proyek</th>
                        <th>Warehouse</th>
                        <th>Sopir</th>
                        <th>No. Kendaraan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($pengirimanAktif)): ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada pengiriman aktif</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($pengirimanAktif as $p): ?>
                        <tr>
                            <td><?php echo $p['no_pengiriman']; ?></td>
                            <td><?php echo $p['nama_proyek']; ?></td>
                            <td><?php echo $p['nama_warehouse']; ?></td>
                            <td><?php echo $p['sopir']; ?></td>
                            <td><?php echo $p['no_kendaraan']; ?></td>
                            <td>
                                <?php
                                $badgeClass = '';
                                if($p['status'] == 'pending') $badgeClass = 'badge-pending';
                                elseif($p['status'] == 'validasi') $badgeClass = 'badge-validasi';
                                elseif($p['status'] == 'dikirim') $badgeClass = 'badge-dikirim';
                                elseif($p['status'] == 'dalam_perjalanan') $badgeClass = 'badge-dalam_perjalanan';
                                elseif($p['status'] == 'sampai') $badgeClass = 'badge-sampai';
                                elseif($p['status'] == 'batal') $badgeClass = 'badge-batal';
                                else $badgeClass = 'badge-pending';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $p['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info trackBtn" data-id="<?php echo $p['id']; ?>">
                                    <i class="bi bi-geo-alt"></i> Track
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Chart pengiriman
const ctx = document.getElementById('pengirimanChart').getContext('2d');
const chartData = <?php echo json_encode($pengirimanPerBulan); ?>;

// Debug chart data
console.log('Chart Data:', chartData);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: chartData.map(item => item.bulan),
        datasets: [{
            label: 'Jumlah Pengiriman',
            data: chartData.map(item => item.total),
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            borderWidth: 3,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#4361ee',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    drawBorder: false
                }
            }
        }
    }
});

// ========================================
// FUNGSI TRACKING YANG BENAR
// ========================================

function showTrackingDetail(pengirimanId) {
    console.log('Tracking button clicked for ID:', pengirimanId);
    
    // Tampilkan loading
    Swal.fire({
        title: 'Memuat Data...',
        text: 'Harap tunggu',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    $.ajax({
        url: '/shankara_trackingbarang/api/get_tracking.php?id=' + pengirimanId,
        type: 'GET',
        dataType: 'json',
        timeout: 10000,
        success: function(response) {
            console.log('Tracking response:', response);
            Swal.close();
            
            if (response.success && response.pengiriman) {
                let statusText = response.pengiriman.status;
                let statusLabel = '';
                if (statusText == 'pending') statusLabel = '<span class="badge badge-pending">Pending</span>';
                else if (statusText == 'validasi') statusLabel = '<span class="badge badge-validasi">Validasi</span>';
                else if (statusText == 'dikirim') statusLabel = '<span class="badge badge-dikirim">Dikirim</span>';
                else if (statusText == 'dalam_perjalanan') statusLabel = '<span class="badge badge-dalam_perjalanan">Dalam Perjalanan</span>';
                else if (statusText == 'sampai') statusLabel = '<span class="badge badge-sampai">Sampai</span>';
                else if (statusText == 'batal') statusLabel = '<span class="badge badge-batal">Batal</span>';
                else statusLabel = '<span class="badge badge-pending">' + statusText + '</span>';
                
                let trackingHistory = '';
                if (response.tracking && response.tracking.length > 0) {
                    trackingHistory = '<hr><strong><i class="bi bi-clock-history"></i> Riwayat Lokasi:</strong><div class="mt-2">';
                    response.tracking.forEach(function(t) {
                        let waktu = new Date(t.waktu).toLocaleString('id-ID');
                        trackingHistory += `
                            <div class="alert alert-sm alert-light mb-2">
                                <i class="bi bi-geo-alt-fill text-primary"></i> 
                                <strong>${t.lokasi_text || 'Lokasi tidak tersedia'}</strong><br>
                                <small class="text-muted">${waktu}</small>
                            </div>
                        `;
                    });
                    trackingHistory += '</div>';
                } else {
                    trackingHistory = '<hr><div class="alert alert-info"><i class="bi bi-info-circle"></i> Belum ada update lokasi. Sistem akan update otomatis setiap 30 detik saat kendaraan dalam perjalanan.</div>';
                }
                
                Swal.fire({
                    title: '📍 Detail Tracking Pengiriman',
                    html: `
                        <div class="text-start" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-sm table-borderless">
                                <tr><td width="40%"><strong>No Pengiriman</strong></td><td>: ${response.pengiriman.no_pengiriman}</td></tr>
                                <tr><td><strong>Sopir</strong></td><td>: ${response.pengiriman.sopir}</td></tr>
                                <tr><td><strong>No Kendaraan</strong></td><td>: ${response.pengiriman.no_kendaraan}</td></tr>
                                <tr><td><strong>Status</strong></td><td>: ${statusLabel}</td></tr>
                                <tr><td><strong>Tanggal Pengiriman</strong></td><td>: ${response.pengiriman.tanggal_pengiriman}</td></tr>
                                <tr><td><strong>Lokasi Awal</strong></td><td>: ${response.pengiriman.lokasi_awal || '-'}</td></tr>
                                <tr><td><strong>Lokasi Tujuan</strong></td><td>: ${response.pengiriman.lokasi_tujuan || '-'}</td></tr>
                            </table>
                            ${trackingHistory}
                        </div>
                    `,
                    icon: 'info',
                    width: 650,
                    confirmButtonText: 'Tutup',
                    confirmButtonColor: '#4361ee'
                });
            } else {
                Swal.fire('Error', 'Data pengiriman tidak ditemukan', 'error');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', status, error);
            console.error('Response Text:', xhr.responseText);
            Swal.close();
            Swal.fire({
                title: 'Error!',
                text: 'Gagal memuat data tracking: ' + error + '\nCek koneksi internet dan pastikan API tersedia.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
}

// Event handler untuk semua tombol Track (baik yang sudah ada maupun yang akan datang)
$(document).ready(function() {
    // Handler untuk tombol track yang sudah ada
    $('.trackBtn').off('click').on('click', function(e) {
        e.preventDefault();
        var pengirimanId = $(this).data('id');
        console.log('Track button clicked, ID:', pengirimanId);
        if (pengirimanId) {
            showTrackingDetail(pengirimanId);
        } else {
            Swal.fire('Error', 'ID Pengiriman tidak ditemukan', 'error');
        }
    });
    
    console.log('Dashboard siap, jumlah tombol track: ' + $('.trackBtn').length);
});
</script>