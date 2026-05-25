<?php
// Menerima parameter filter dari URL
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$tgl_harian = isset($_GET['tgl_harian']) ? $_GET['tgl_harian'] : date('Y-m-d');
$tgl_bulanan = isset($_GET['tgl_bulanan']) ? $_GET['tgl_bulanan'] : date('Y-m');
$tgl_tahunan = isset($_GET['tgl_tahunan']) ? $_GET['tgl_tahunan'] : date('Y');

// Membuat Query Kondisi Berdasarkan Filter (Khusus untuk Barang Keluar/Terkirim)
$where = "1=1"; 
$judul_laporan = "Rekapitulasi Keuangan Seluruh Waktu";

if ($filter == 'harian') {
    $where = "DATE(p.tanggal_pengiriman) = '$tgl_harian'";
    $judul_laporan = "Laporan Nilai Distribusi Harian: " . date('d F Y', strtotime($tgl_harian));
} elseif ($filter == 'bulanan') {
    $where = "DATE_FORMAT(p.tanggal_pengiriman, '%Y-%m') = '$tgl_bulanan'";
    $judul_laporan = "Laporan Nilai Distribusi Bulanan: " . date('F Y', strtotime($tgl_bulanan . '-01'));
} elseif ($filter == 'tahunan') {
    $where = "YEAR(p.tanggal_pengiriman) = '$tgl_tahunan'";
    $judul_laporan = "Laporan Nilai Distribusi Tahunan: $tgl_tahunan";
}

// 1. QUERY TOTAL ASET GUDANG SAAT INI (Tidak terpengaruh filter tanggal)
$asetGudang = fetchOne(query("
    SELECT SUM(s.jumlah * b.harga) as total_nilai_gudang 
    FROM stok s 
    JOIN barang b ON s.barang_id = b.id
"));
$total_aset_gudang = $asetGudang['total_nilai_gudang'] ? $asetGudang['total_nilai_gudang'] : 0;

// 2. QUERY TOTAL NILAI BARANG YANG SUDAH SAMPAI KE PROYEK (Berdasarkan Filter)
$asetTerkirim = fetchOne(query("
    SELECT SUM(dp.jumlah * b.harga) as total_nilai_terkirim 
    FROM detail_pengiriman dp 
    JOIN pengiriman p ON dp.pengiriman_id = p.id 
    JOIN barang b ON dp.barang_id = b.id 
    WHERE p.status = 'sampai' AND $where
"));
$total_aset_terkirim = $asetTerkirim['total_nilai_terkirim'] ? $asetTerkirim['total_nilai_terkirim'] : 0;

// 3. QUERY TABEL DAFTAR PENGIRIMAN DAN NILAI RUPIAHNYA
$sql = "SELECT p.no_pengiriman, p.tanggal_pengiriman, pr.nama_proyek, p.status,
               SUM(dp.jumlah * b.harga) as nilai_pengiriman
        FROM pengiriman p 
        JOIN detail_pengiriman dp ON p.id = dp.pengiriman_id
        JOIN barang b ON dp.barang_id = b.id
        JOIN proyek pr ON p.proyek_id = pr.id 
        WHERE $where 
        GROUP BY p.id
        ORDER BY p.tanggal_pengiriman DESC, p.created_at DESC";
$laporan_keuangan = fetchAll(query($sql));
?>

<!-- CSS KHUSUS UNTUK CETAK PDF (PRINT) -->
<style>
    @media print {
        .sidebar, .navbar, .btn, form, .no-print { display: none !important; }
        body, .main-content { margin: 0 !important; padding: 0 !important; width: 100% !important; background: white !important; }
        .card { border: none !important; box-shadow: none !important; }
        .table-custom { border-collapse: collapse !important; width: 100% !important; }
        .table-custom th, .table-custom td { border: 1px solid #000 !important; padding: 8px !important; color: #000 !important; }
        .print-header { display: block !important; text-align: center; margin-bottom: 20px; }
    }
    .print-header { display: none; }
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
        <h3 class="fw-bold text-dark mb-0"><i class="bi bi-cash-coin text-success"></i> Laporan Keuangan & Aset</h3>
        <p class="text-muted small">Pantau nilai aset berjalan dan alokasi budget distribusi ke proyek.</p>
    </div>
    <button class="btn btn-dark px-4 shadow-sm fw-bold rounded-pill" onclick="window.print()">
        <i class="bi bi-printer me-2"></i> Cetak Dokumen
    </button>
</div>

<!-- KARTU RINGKASAN STATISTIK KEUANGAN -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); color: white;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="small text-uppercase fw-bold opacity-75 mb-1">Nilai Inventaris Gudang Saat Ini</h6>
                    <h2 class="mb-0 fw-bold">Rp <?php echo number_format($total_aset_gudang, 0, ',', '.'); ?></h2>
                    <small class="opacity-75">*Total aset yang masih tersimpan di seluruh warehouse</small>
                </div>
                <i class="bi bi-building fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; background: linear-gradient(135deg, #198754 0%, #146c43 100%); color: white;">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="small text-uppercase fw-bold opacity-75 mb-1">Nilai Barang Sukses Terkirim</h6>
                    <h2 class="mb-0 fw-bold">Rp <?php echo number_format($total_aset_terkirim, 0, ',', '.'); ?></h2>
                    <small class="opacity-75">*Telah tervalidasi sampai di lokasi proyek (Berdasarkan filter)</small>
                </div>
                <i class="bi bi-check-circle fs-1 opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<!-- FORM FILTER PENCARIAN -->
<div class="card border-0 shadow-sm mb-4 no-print" style="border-radius: 15px;">
    <div class="card-body p-4 bg-light rounded-3">
        <form method="GET" action="">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Periode Distribusi</label>
                    <select name="filter" id="jenisFilter" class="form-select shadow-sm border-primary" onchange="ubahFilter()">
                        <option value="semua" <?php if($filter=='semua') echo 'selected'; ?>>Semua Waktu</option>
                        <option value="harian" <?php if($filter=='harian') echo 'selected'; ?>>Harian</option>
                        <option value="bulanan" <?php if($filter=='bulanan') echo 'selected'; ?>>Bulanan</option>
                        <option value="tahunan" <?php if($filter=='tahunan') echo 'selected'; ?>>Tahunan</option>
                    </select>
                </div>
                
                <!-- Input Dinamis -->
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
                    <button type="submit" class="btn btn-primary w-100 shadow-sm fw-bold"><i class="bi bi-funnel me-1"></i> Terapkan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- TABEL DATA LAPORAN KEUANGAN DISTRIBUSI -->
<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold m-0 text-dark"><i class="bi bi-table me-2 text-primary"></i>Rincian Nilai Per Surat Jalan (Resi)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-custom align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">TANGGAL PENGIRIMAN</th>
                        <th>NO. RESI</th>
                        <th>PROYEK TUJUAN</th>
                        <th>STATUS BARANG</th>
                        <th class="text-end pe-4">TOTAL NILAI MUATAN (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($laporan_keuangan)): ?>
                        <tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-wallet2 fs-1 d-block mb-2"></i>Tidak ada transaksi finansial pada periode ini.</td></tr>
                    <?php else: ?>
                        <?php foreach($laporan_keuangan as $k): ?>
                        <tr>
                            <td class="ps-4 text-secondary"><?php echo date('d/m/Y', strtotime($k['tanggal_pengiriman'])); ?></td>
                            <td><strong><?php echo $k['no_pengiriman']; ?></strong></td>
                            <td class="fw-bold text-dark"><?php echo $k['nama_proyek']; ?></td>
                            <td>
                                <?php
                                $status = $k['status'];
                                if($status == 'sampai') echo '<span class="badge bg-success rounded-pill px-3 py-1">Terkirim</span>';
                                elseif($status == 'pending') echo '<span class="badge bg-secondary rounded-pill px-3 py-1">Tertunda</span>';
                                else echo '<span class="badge bg-warning text-dark rounded-pill px-3 py-1">Proses Jalan</span>';
                                ?>
                            </td>
                            <td class="text-end pe-4 fw-bold text-primary fs-6">
                                <?php echo number_format($k['nilai_pengiriman'], 0, ',', '.'); ?>
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
function ubahFilter() {
    var jenis = document.getElementById('jenisFilter').value;
    document.getElementById('inputHarian').style.display = 'none';
    document.getElementById('inputBulanan').style.display = 'none';
    document.getElementById('inputTahunan').style.display = 'none';
    
    if(jenis === 'harian') document.getElementById('inputHarian').style.display = 'block';
    else if(jenis === 'bulanan') document.getElementById('inputBulanan').style.display = 'block';
    else if(jenis === 'tahunan') document.getElementById('inputTahunan').style.display = 'block';
}
</script>