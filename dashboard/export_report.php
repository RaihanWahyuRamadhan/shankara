<?php
require_once dirname(__DIR__) . '/config/database.php';
if(!isLoggedIn()) redirect('../auth/login.php');

// Ambil parameter
$type = isset($_GET['type']) ? $_GET['type'] : 'pengiriman';
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');

// =====================================================
// SET NAMA FILE BERDASARKAN JENIS LAPORAN
// =====================================================
$filename = '';
switch($type) {
    case 'pengiriman':
        $filename = 'laporan_pengiriman_' . date('Y-m-d') . '.xls';
        break;
    case 'stok':
        $filename = 'laporan_stok_barang_' . date('Y-m-d') . '.xls';
        break;
    case 'proyek':
        $filename = 'laporan_proyek_' . date('Y-m-d') . '.xls';
        break;
    case 'absensi':
        $filename = 'laporan_absensi_' . date('Y-m-d') . '.xls';
        break;
    case 'aktivitas':
        $filename = 'laporan_aktivitas_sistem_' . date('Y-m-d') . '.xls';
        break;
    default:
        $filename = 'laporan_lengkap_sistem_' . date('Y-m-d') . '.xls';
        break;
}

// Set headers untuk download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// =====================================================
// LAPORAN PENGIRIMAN
// =====================================================
if($type == 'pengiriman') {
    echo "LAPORAN PENGIRIMAN BARANG\n";
    echo "CV MUGI JAYA\n";
    echo "Periode: " . date('d/m/Y', strtotime($from_date)) . " - " . date('d/m/Y', strtotime($to_date)) . "\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    echo "No Pengiriman\tProyek\tWarehouse\tSopir\tNo Kendaraan\tTanggal\tStatus\n";
    
    $sql = "SELECT p.no_pengiriman, pr.nama_proyek, w.nama_warehouse, p.sopir, p.no_kendaraan, 
                   p.tanggal_pengiriman, p.status
            FROM pengiriman p
            JOIN proyek pr ON p.proyek_id = pr.id
            JOIN warehouse w ON p.warehouse_id = w.id
            WHERE p.tanggal_pengiriman BETWEEN '$from_date' AND '$to_date'
            ORDER BY p.tanggal_pengiriman DESC";
    
    $result = query($sql);
    while($row = fetchOne($result)) {
        echo $row['no_pengiriman'] . "\t";
        echo $row['nama_proyek'] . "\t";
        echo $row['nama_warehouse'] . "\t";
        echo $row['sopir'] . "\t";
        echo $row['no_kendaraan'] . "\t";
        echo $row['tanggal_pengiriman'] . "\t";
        echo $row['status'] . "\n";
    }
}

// =====================================================
// LAPORAN STOK
// =====================================================
elseif($type == 'stok') {
    echo "LAPORAN STOK BARANG PER WAREHOUSE\n";
    echo "CV MUGI JAYA\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    echo "Kode Barang\tNama Barang\tWarehouse\tJumlah\tSatuan\n";
    
    $sql = "SELECT b.kode_barang, b.nama_barang, b.satuan, w.nama_warehouse, s.jumlah
            FROM stok s
            JOIN barang b ON s.barang_id = b.id
            JOIN warehouse w ON s.warehouse_id = w.id
            ORDER BY w.nama_warehouse, b.nama_barang";
    
    $result = query($sql);
    while($row = fetchOne($result)) {
        echo $row['kode_barang'] . "\t";
        echo $row['nama_barang'] . "\t";
        echo $row['nama_warehouse'] . "\t";
        echo $row['jumlah'] . "\t";
        echo $row['satuan'] . "\n";
    }
}

// =====================================================
// LAPORAN PROYEK
// =====================================================
elseif($type == 'proyek') {
    echo "LAPORAN DATA PROYEK\n";
    echo "CV MUGI JAYA\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    echo "ID\tNama Proyek\tLokasi\tClient\tTanggal Mulai\tStatus\n";
    
    $sql = "SELECT id, nama_proyek, lokasi, client, tanggal_mulai, status 
            FROM proyek 
            ORDER BY created_at DESC";
    
    $result = query($sql);
    while($row = fetchOne($result)) {
        echo $row['id'] . "\t";
        echo $row['nama_proyek'] . "\t";
        echo $row['lokasi'] . "\t";
        echo $row['client'] . "\t";
        echo $row['tanggal_mulai'] . "\t";
        echo $row['status'] . "\n";
    }
}

// =====================================================
// LAPORAN ABSENSI
// =====================================================
elseif($type == 'absensi') {
    echo "LAPORAN ABSENSI KARYAWAN\n";
    echo "CV MUGI JAYA\n";
    echo "Periode: " . date('d/m/Y', strtotime($from_date)) . " - " . date('d/m/Y', strtotime($to_date)) . "\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    echo "Tanggal\tNama Karyawan\tRole\tJam Masuk\tJam Keluar\tStatus\tKeterangan\n";
    
    $sql = "SELECT a.tanggal, u.nama_lengkap, u.role, a.jam_masuk, a.jam_keluar, a.status, a.keterangan
            FROM absensi a
            JOIN users u ON a.user_id = u.id
            WHERE a.tanggal BETWEEN '$from_date' AND '$to_date'
            ORDER BY a.tanggal DESC, u.nama_lengkap";
    
    $result = query($sql);
    while($row = fetchOne($result)) {
        $jam_masuk = isset($row['jam_masuk']) ? $row['jam_masuk'] : '-';
        $jam_keluar = isset($row['jam_keluar']) ? $row['jam_keluar'] : '-';
        $keterangan = isset($row['keterangan']) ? $row['keterangan'] : '-';
        
        echo $row['tanggal'] . "\t";
        echo $row['nama_lengkap'] . "\t";
        echo $row['role'] . "\t";
        echo $jam_masuk . "\t";
        echo $jam_keluar . "\t";
        echo $row['status'] . "\t";
        echo $keterangan . "\n";
    }
}

// =====================================================
// LAPORAN AKTIVITAS
// =====================================================
elseif($type == 'aktivitas') {
    echo "LAPORAN RIWAYAT AKTIVITAS SISTEM\n";
    echo "CV MUGI JAYA\n";
    echo "Periode: " . date('d/m/Y', strtotime($from_date)) . " - " . date('d/m/Y', strtotime($to_date)) . "\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    echo "Waktu\tUser\tRole\tAktivitas\tIP Address\n";
    
    $sql = "SELECT r.created_at, u.nama_lengkap, u.role, r.aktivitas, r.ip_address
            FROM riwayat_aktivitas r
            JOIN users u ON r.user_id = u.id
            WHERE DATE(r.created_at) BETWEEN '$from_date' AND '$to_date'
            ORDER BY r.created_at DESC
            LIMIT 1000";
    
    $result = query($sql);
    while($row = fetchOne($result)) {
        $ip = isset($row['ip_address']) ? $row['ip_address'] : '-';
        
        echo $row['created_at'] . "\t";
        echo $row['nama_lengkap'] . "\t";
        echo $row['role'] . "\t";
        echo $row['aktivitas'] . "\t";
        echo $ip . "\n";
    }
}

// =====================================================
// LAPORAN LENGKAP (SEMUA DATA)
// =====================================================
else {
    echo "LAPORAN LENGKAP SISTEM TRACKING\n";
    echo "CV MUGI JAYA\n";
    echo "Tanggal Export: " . date('d/m/Y H:i:s') . "\n";
    echo "\n";
    
    // Ringkasan
    echo "========================================\n";
    echo "A. RINGKASAN DATA\n";
    echo "========================================\n";
    $totalProyek = fetchOne(query("SELECT COUNT(*) as total FROM proyek"));
    $totalPengiriman = fetchOne(query("SELECT COUNT(*) as total FROM pengiriman"));
    $totalBarang = fetchOne(query("SELECT COUNT(*) as total FROM barang"));
    $totalUser = fetchOne(query("SELECT COUNT(*) as total FROM users"));
    
    echo "Total Proyek\t: " . ($totalProyek['total'] ?? 0) . "\n";
    echo "Total Pengiriman\t: " . ($totalPengiriman['total'] ?? 0) . "\n";
    echo "Total Barang\t: " . ($totalBarang['total'] ?? 0) . "\n";
    echo "Total User\t: " . ($totalUser['total'] ?? 0) . "\n";
    echo "\n\n";
    
    // Data Pengiriman
    echo "========================================\n";
    echo "B. DATA PENGIRIMAN\n";
    echo "========================================\n";
    echo "No Pengiriman\tProyek\tWarehouse\tSopir\tTanggal\tStatus\n";
    $sql = "SELECT p.no_pengiriman, pr.nama_proyek, w.nama_warehouse, p.sopir, p.tanggal_pengiriman, p.status
            FROM pengiriman p
            JOIN proyek pr ON p.proyek_id = pr.id
            JOIN warehouse w ON p.warehouse_id = w.id
            ORDER BY p.tanggal_pengiriman DESC LIMIT 100";
    $result = query($sql);
    while($row = fetchOne($result)) {
        echo $row['no_pengiriman'] . "\t";
        echo $row['nama_proyek'] . "\t";
        echo $row['nama_warehouse'] . "\t";
        echo $row['sopir'] . "\t";
        echo $row['tanggal_pengiriman'] . "\t";
        echo $row['status'] . "\n";
    }
    echo "\n\n";
    
    // Data Barang
    echo "========================================\n";
    echo "C. DATA BARANG\n";
    echo "========================================\n";
    echo "Kode Barang\tNama Barang\tSatuan\tHarga\n";
    $sql = "SELECT kode_barang, nama_barang, satuan, harga FROM barang";
    $result = query($sql);
    while($row = fetchOne($result)) {
        echo $row['kode_barang'] . "\t";
        echo $row['nama_barang'] . "\t";
        echo $row['satuan'] . "\t";
        echo ($row['harga'] ?? '0') . "\n";
    }
}
?>