<?php
// Menerima parameter filter dari URL (Default: Semua)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$tgl_harian = isset($_GET['tgl_harian']) ? $_GET['tgl_harian'] : date('Y-m-d');
$tgl_bulanan = isset($_GET['tgl_bulanan']) ? $_GET['tgl_bulanan'] : date('Y-m');
$tgl_tahunan = isset($_GET['tgl_tahunan']) ? $_GET['tgl_tahunan'] : date('Y');

// Membuat Query Kondisi Berdasarkan Filter
$where = "1=1"; 
$judul_laporan = "Semua Riwayat Transaksi";

if ($filter == 'harian') {
    $where = "DATE(p.tanggal_pengiriman) = '$tgl_harian'";
    $judul_laporan = "Laporan Transaksi Harian: " . date('d F Y', strtotime($tgl_harian));
} elseif ($filter == 'bulanan') {
    $where = "DATE_FORMAT(p.tanggal_pengiriman, '%Y-%m') = '$tgl_bulanan'";
    $judul_laporan = "Laporan Transaksi Bulanan: " . date('F Y', strtotime($tgl_bulanan . '-01'));
} elseif ($filter == 'tahunan') {
    $where = "YEAR(p.tanggal_pengiriman) = '$tgl_tahunan'";
    $judul_laporan = "Laporan Transaksi Tahunan: $tgl_tahunan";
}

// Eksekusi Query ke Database
$sql = "SELECT p.*, pr.nama_proyek, w.nama_warehouse 
        FROM pengiriman p 
        JOIN proyek pr ON p.proyek_id = pr.id 
        JOIN warehouse w ON p.warehouse_id = w.id 
        WHERE $where 
        ORDER BY p.tanggal_pengiriman DESC, p.created_at DESC";
$laporan = fetchAll(query($sql));

// Menghitung Statistik Ringkasan
$total_transaksi = count($laporan);
$total_selesai = 0;
$total_jalan = 0;
$total_pending = 0;

foreach($laporan as $row) {
    if($row['status'] == 'sampai') $total_selesai++;
    elseif($row['status'] == 'pending') $total_pending++;
    else $total_jalan++;
}
?>

<!-- CSS KHUSUS UNTUK CETAK PDF (PRINT) -->
<style>
    @media print {
        /* Sembunyikan elemen yang tidak perlu dicetak */
        .sidebar, .navbar, .btn, form, .no-print { display: none !important; }
        /* Reset margin agar cetakan penuh */
        body, .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; }
        /* Gaya tabel agar rapi di kertas */
        .card { border: none !important; box-shadow: none !important; }
        .table-custom { border-collapse: collapse !important; width: 100% !important; }
        .table-custom th, .table-custom td { border: 1px solid #000 !important; padding: 8px !important; color: #000 !important; }
        .badge { border: 1px solid #000 !important; color: #000 !important; background: transparent !important; }
        .print-header { display: block !important; text-align: center; margin-bottom: 20px; }
    }
    .print-header { display: none; } /* Sembunyikan di layar normal */
</style>

<!-- HEADER UNTUK PRINT KERTAS -->
<div class="print-header">
    <h2>SHANKARA LOGISTICS</h2>
    <h4><?php echo $judul_laporan; ?></h4>
    <p>Dicetak pada: <?php echo date('d/m/Y H:i'); ?></p>
    <hr style="border-top: 2px solid #000;">
</div>

<!-- HEADER NORMAL (LAYAR) -->
<div class="d-flex justify-content-between mb-4 align-items-center no-print">
    <div>
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check"></i> Laporan Pengiriman</h3>
        <p class="text-muted small">Filter dan rekapitulasi data transaksi logistik.</p>
    </div>
    <button class="btn btn-dark px-4 shadow-sm fw-bold rounded-pill" onclick="window.print()">
        <i class="bi bi-printer me-2"></i> Cetak / PDF
    </button>
</div>

<!-- FORM FILTER PENCARIAN (TIDAK TAMPIL SAAT DIPRINT) -->
<div class="card border-0 shadow-sm mb-4 no-print" style="border-radius: 15px;">
    <div class="card-body p-4">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Jenis Laporan</label>
                    <select name="filter" id="jenisFilter" class="form-select shadow-sm" onchange="ubahFilter()">
                        <option value="semua" <?php if($filter=='semua') echo 'selected'; ?>>Semua Waktu</option>
                        <option value="harian" <?php if($filter=='harian') echo 'selected'; ?>>Harian</option>
                        <option value="bulanan" <?php if($filter=='bulanan') echo 'selected'; ?>>Bulanan</option>
                        <option value="tahunan" <?php if($filter=='tahunan') echo 'selected'; ?>>Tahunan</option>
                    </select>
                </div>
                
                <!-- Input Dinamis Berdasarkan Filter -->
                <div class="col-md-4" id="inputHarian" style="<?php echo ($filter=='harian') ? '' : 'display:none;'; ?>">
                    <label class="form-label small fw-bold">Pilih Tanggal</label>
                    <input type="date" name="tgl_harian" class="form-control shadow-sm" value="<?php echo $tgl_harian; ?>">
                </div>
                
                <div class="col-md-4" id="inputBulanan" style="<?php echo ($filter=='bulanan') ? '' : 'display:none;'; ?>">
                    <label class="form-label small fw-bold">Pilih Bulan</label>
                    <input type="month" name="tgl_bulanan" class="form-control shadow-sm" value="<?php echo $tgl_bulanan; ?>">
                </div>
                
                <div class="col-md-4" id="inputTahunan" style="<?php echo ($filter=='tahunan') ? '' : 'display:none;'; ?>">
                    <label class="form-label small fw-bold">Pilih Tahun</label>
                    <select name="tgl_tahunan" class="form-select shadow-sm">
                        <?php 
                        $tahun_sekarang = date('Y');
                        for($i = $tahun_sekarang; $i >= $tahun_sekarang - 5; $i--){
                            $selected = ($tgl_tahunan == $i) ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 shadow-sm fw-bold"><i class="bi bi-search me-1"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KARTU RINGKASAN STATISTIK -->
<div class="row mb-4">
    <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; background-color: #f8f9fa;">
            <div class="card-body p-3">
                <h6 class="text-muted small text-uppercase fw-bold mb-1">Total Transaksi</h6>
                <h3 class="mb-0 fw-bold text-dark"><?php echo $total_transaksi; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; background-color: #e8f5e9;">
            <div class="card-body p-3">
                <h6 class="text-success small text-uppercase fw-bold mb-1">Berhasil Tiba</h6>
                <h3 class="mb-0 fw-bold text-success"><?php echo $total_selesai; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; background-color: #e3f2fd;">
            <div class="card-body p-3">
                <h6 class="text-primary small text-uppercase fw-bold mb-1">Sedang Jalan</h6>
                <h3 class="mb-0 fw-bold text-primary"><?php echo $total_jalan; ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; background-color: #fff3e0;">
            <div class="card-body p-3">
                <h6 class="text-warning small text-uppercase fw-bold mb-1">Pending/Logistik</h6>
                <h3 class="mb-0 fw-bold text-warning"><?php echo $total_pending; ?></h3>
            </div>
        </div>
    </div>
</div>

<!-- TABEL DATA LAPORAN -->
<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold m-0 text-primary"><?php echo $judul_laporan; ?></h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">TANGGAL</th>
                        <th>NO. RESI</th>
                        <th>GUDANG ASAL</th>
                        <th>PROYEK TUJUAN</th>
                        <th>DRIVER / SUPIR</th>
                        <th>STATUS AKHIR</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($laporan)): ?>
                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-folder-x fs-1 d-block mb-2"></i>Tidak ada data transaksi pada periode ini.</td></tr>
                    <?php else: ?>
                        <?php foreach($laporan as $p): ?>
                        <tr>
                            <td class="ps-4"><?php echo date('d/m/Y', strtotime($p['tanggal_pengiriman'])); ?></td>
                            <td><strong><?php echo $p['no_pengiriman']; ?></strong></td>
                            <td><?php echo $p['nama_warehouse']; ?></td>
                            <td><?php echo $p['nama_proyek']; ?></td>
                            <td><?php echo $p['sopir'] ? $p['sopir'] : '<i class="text-muted small">Belum diisi</i>'; ?></td>
                            <td>
                                <?php
                                $status = $p['status'];
                                if($status == 'sampai') echo '<span class="badge bg-success rounded-pill px-3 py-2">✅ Selesai</span>';
                                elseif($status == 'hampir_sampai') echo '<span class="badge bg-info text-dark rounded-pill px-3 py-2">📍 Hampir Tiba</span>';
                                elseif($status == 'dalam_perjalanan' || $status == 'dikirim') echo '<span class="badge bg-primary rounded-pill px-3 py-2">🚚 Di Jalan</span>';
                                else echo '<span class="badge bg-secondary rounded-pill px-3 py-2">⏳ Pending</span>';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Fungsi untuk mengganti input tanggal sesuai pilihan Harian/Bulanan/Tahunan
function ubahFilter() {
    var jenis = document.getElementById('jenisFilter').value;
    
    // Sembunyikan semua dulu
    document.getElementById('inputHarian').style.display = 'none';
    document.getElementById('inputBulanan').style.display = 'none';
    document.getElementById('inputTahunan').style.display = 'none';
    
    // Tampilkan sesuai pilihan
    if(jenis === 'harian') {
        document.getElementById('inputHarian').style.display = 'block';
    } else if(jenis === 'bulanan') {
        document.getElementById('inputBulanan').style.display = 'block';
    } else if(jenis === 'tahunan') {
        document.getElementById('inputTahunan').style.display = 'block';
    }
}
</script>