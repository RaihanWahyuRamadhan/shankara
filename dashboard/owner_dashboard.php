<?php
require_once dirname(__DIR__) . '/config/database.php';

// Pastikan user sudah login
if(!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Hanya Owner yang bisa akses
if($_SESSION['role'] != 'owner') {
    header("Location: index.php");
    exit();
}

// Ambil semua data pengiriman lengkap
$pengiriman = fetchAll(query("
    SELECT p.*, pr.nama_proyek, pr.client, w.nama_warehouse
    FROM pengiriman p 
    JOIN proyek pr ON p.proyek_id = pr.id 
    JOIN warehouse w ON p.warehouse_id = w.id 
    ORDER BY p.created_at DESC
"));

// Statistik
$totalPengiriman = count($pengiriman);
$totalSampai = 0;
$totalDalamPerjalanan = 0;
$totalDikirim = 0;
$totalPending = 0;

foreach($pengiriman as $p) {
    if($p['status'] == 'sampai') $totalSampai++;
    elseif($p['status'] == 'dalam_perjalanan' || $p['status'] == 'hampir_sampai') $totalDalamPerjalanan++;
    elseif($p['status'] == 'dikirim') $totalDikirim++;
    elseif($p['status'] == 'pending') $totalPending++;
}

$persenSampai = $totalPengiriman > 0 ? round(($totalSampai / $totalPengiriman) * 100, 1) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Owner - SHANKARA</title>
    
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    
    <!-- JS Bundle Bootstrap 5 (Wajib untuk Modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 & jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root {
            --primary-dark: #0f172a;
            --accent-color: #3b82f6;
            --bg-soft: #f8fafc;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-soft);
            color: #1e293b;
            padding: 25px;
        }

        .header-owner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.15);
        }

        .stat-card {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 25px;
            height: 100%;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .stat-card:hover { transform: translateY(-5px); }

        .icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }

        .table-container {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .custom-table thead th {
            background-color: #f1f5f9;
            color: #475569;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
            padding: 15px;
            border: none;
        }

        .custom-table tbody td {
            padding: 18px 15px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .img-thumb {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .btn-view-detail {
            background-color: var(--bg-soft);
            color: var(--primary-dark);
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.2s;
        }

        .btn-view-detail:hover {
            background-color: var(--primary-dark);
            color: white;
        }

        .progress {
            height: 12px;
            border-radius: 10px;
            background-color: #e2e8f0;
        }
    </style>
</head>
<body>

<div class="container-fluid" style="max-width: 1400px; margin: 0 auto;">
    
    <!-- HEADER -->
    <div class="header-owner d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1"><i class="bi bi-shield-check me-2"></i> Dashboard Executive</h2>
            <p class="mb-0 opacity-75">Ringkasan operasional logistik CV Mugi Jaya</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary px-3 py-2 rounded-pill">Status: Online</span>
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-light text-dark"><i class="bi bi-box-seam"></i></div>
                <h3 class="fw-bold mb-0"><?php echo $totalPengiriman; ?></h3>
                <small class="text-muted fw-500">Total Seluruh Pengiriman</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-success text-white"><i class="bi bi-check2-circle"></i></div>
                <h3 class="fw-bold mb-0"><?php echo $totalSampai; ?></h3>
                <small class="text-muted fw-500">Berhasil Terkirim</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-warning text-dark"><i class="bi bi-truck"></i></div>
                <h3 class="fw-bold mb-0"><?php echo $totalDalamPerjalanan; ?></h3>
                <small class="text-muted fw-500">Dalam Distribusi</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="icon-box bg-info text-white"><i class="bi bi-clock-history"></i></div>
                <h3 class="fw-bold mb-0"><?php echo ($totalDikirim + $totalPending); ?></h3>
                <small class="text-muted fw-500">Menunggu Validasi</small>
            </div>
        </div>
    </div>

    <!-- PROGRESS -->
    <div class="table-container mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-graph-up-arrow me-2 text-primary"></i> Capaian Target Pengiriman</h6>
            <span class="fw-bold text-primary"><?php echo $persenSampai; ?>%</span>
        </div>
        <div class="progress">
            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo $persenSampai; ?>%"></div>
        </div>
    </div>

    <!-- MAIN TABLE -->
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0">Riwayat Distribusi Barang</h5>
            <button class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="location.reload()">
                <i class="bi bi-arrow-repeat me-1"></i> Refresh Data
            </button>
        </div>
        
        <div class="table-responsive">
            <table class="table custom-table">
                <thead>
                    <tr>
                        <th>No. Pengiriman</th>
                        <th>Proyek & Client</th>
                        <th>Warehouse</th>
                        <th>Sopir</th>
                        <th>Status</th>
                        <th>Dokumentasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pengiriman as $p): ?>
                    <tr>
                        <td><span class="fw-bold text-dark"><?php echo $p['no_pengiriman']; ?></span></td>
                        <td>
                            <div class="fw-bold"><?php echo $p['nama_proyek']; ?></div>
                            <div class="text-muted small"><?php echo $p['client']; ?></div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?php echo $p['nama_warehouse']; ?></span></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span><?php echo $p['sopir'] ?: '-'; ?></span>
                                <?php if($p['foto_sopir']): ?>
                                    <i class="bi bi-camera-fill text-success ms-2" style="cursor:pointer" onclick="showFoto('<?php echo $p['foto_sopir']; ?>', 'Foto Sopir')"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <?php
                            $s = $p['status']; $c = 'secondary';
                            if($s == 'sampai') $c = 'success';
                            elseif($s == 'dalam_perjalanan' || $s == 'hampir_sampai') $c = 'warning';
                            elseif($s == 'dikirim') $c = 'primary';
                            ?>
                            <span class="status-badge bg-<?php echo $c; ?> bg-opacity-10 text-<?php echo $c; ?>">
                                <i class="bi bi-circle-fill me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                <?php echo strtoupper($s); ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <?php if($p['foto_lokasi_awal']): ?>
                                    <img src="<?php echo $p['foto_lokasi_awal']; ?>" class="img-thumb" title="Foto Awal" onclick="showFoto('<?php echo $p['foto_lokasi_awal']; ?>', 'Foto Lokasi Awal')">
                                <?php endif; ?>
                                <?php if($p['foto_lokasi_tujuan']): ?>
                                    <img src="<?php echo $p['foto_lokasi_tujuan']; ?>" class="img-thumb" title="Bukti Tiba" onclick="showFoto('<?php echo $p['foto_lokasi_tujuan']; ?>', 'Foto Bukti Tiba')">
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-view-detail" onclick="showDetailModal(<?php echo $p['id']; ?>)">
                                <i class="bi bi-eye me-1"></i> Detail
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; overflow: hidden;">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-text me-2"></i> Laporan Pengiriman Detail</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="detailModalBody">
                <!-- Data akan dimuat via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function showFoto(url, title) {
    Swal.fire({
        title: title,
        imageUrl: url,
        imageWidth: 600,
        imageHeight: 'auto',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#0f172a'
    });
}

function showDetailModal(id) {
    // 1. Tampilkan loading spinner
    $('#detailModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Sinkronisasi data operasional...</p></div>');
    
    // 2. Gunakan Vanilla JS untuk memanggil modal (Fix error TypeError)
    var myModal = new bootstrap.Modal(document.getElementById('detailModal'));
    myModal.show();
    
    // 3. Ambil data via AJAX
    $.ajax({
        url: '/shankara_trackingbarang/api/get_tracking.php?id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if(res.success && res.pengiriman) {
                let p = res.pengiriman;
                let barang = res.detail_barang || [];
                
                let barangHtml = '';
                barang.forEach(item => {
                    barangHtml += `
                        <div class="d-flex justify-content-between align-items-center p-3 mb-2 bg-light rounded-3">
                            <span class="fw-bold text-dark">${item.nama_barang}</span>
                            <span class="badge bg-primary rounded-pill px-3">${item.jumlah} ${item.satuan}</span>
                        </div>`;
                });

                let html = `
                    <div class="p-4">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-uppercase small fw-bold text-muted mb-3">Informasi Utama</h6>
                                <div class="mb-2 small"><strong>No Resi:</strong> <span class="text-primary">${p.no_pengiriman}</span></div>
                                <div class="mb-2 small"><strong>Client:</strong> ${p.client || '-'}</div>
                                <div class="mb-2 small"><strong>Warehouse:</strong> ${p.nama_warehouse}</div>
                                <div class="mb-2 small"><strong>Waktu Tiba:</strong> ${p.tiba_jam || '<span class="text-muted">Masih dalam perjalanan</span>'}</div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase small fw-bold text-muted mb-3">Muatan Barang</h6>
                                ${barangHtml || '<div class="text-muted small">Data muatan tidak tersedia</div>'}
                            </div>
                        </div>
                    </div>
                    <div class="bg-light p-4 border-top">
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div class="small fw-bold text-muted mb-2">FOTO KURIR</div>
                                ${p.foto_sopir ? `<img src="${p.foto_sopir}" class="img-fluid rounded-3 border shadow-sm">` : '<div class="p-4 border bg-white rounded text-muted small">N/A</div>'}
                            </div>
                            <div class="col-4">
                                <div class="small fw-bold text-muted mb-2">FOTO AWAL</div>
                                ${p.foto_lokasi_awal ? `<img src="${p.foto_lokasi_awal}" class="img-fluid rounded-3 border shadow-sm">` : '<div class="p-4 border bg-white rounded text-muted small">N/A</div>'}
                            </div>
                            <div class="col-4">
                                <div class="small fw-bold text-muted mb-2">BUKTI TIBA</div>
                                ${p.foto_lokasi_tujuan ? `<img src="${p.foto_lokasi_tujuan}" class="img-fluid rounded-3 border shadow-sm">` : '<div class="p-4 border bg-white rounded text-muted small">N/A</div>'}
                            </div>
                        </div>
                    </div>`;
                $('#detailModalBody').html(html);
            }
        },
        error: function() {
            $('#detailModalBody').html('<div class="alert alert-danger m-4">Gagal terhubung ke API server. Pastikan api/get_tracking.php sudah ada.</div>');
        }
    });
}
</script>
</body>
</html>