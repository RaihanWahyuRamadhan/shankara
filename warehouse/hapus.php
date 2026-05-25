<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

$id = intval($_GET['id']);

// Proteksi: Cek apakah gudang ini sedang menyimpan stok barang
$cek_stok = fetchOne(query("SELECT COUNT(*) as total FROM stok WHERE warehouse_id = $id AND jumlah > 0"));

// Proteksi: Cek apakah gudang ini tercatat di riwayat pengiriman
$cek_pengiriman = fetchOne(query("SELECT COUNT(*) as total FROM pengiriman WHERE warehouse_id = $id"));

if($cek_stok['total'] > 0 || $cek_pengiriman['total'] > 0) {
    // Tolak penghapusan jika gudang tidak kosong / dipakai di riwayat
    header("Location: index.php?error=in_use");
    exit();
}

$sql = "DELETE FROM warehouse WHERE id = $id";
if(query($sql)) {
    header("Location: index.php?success=1");
} else {
    header("Location: index.php?error=1");
}
?>