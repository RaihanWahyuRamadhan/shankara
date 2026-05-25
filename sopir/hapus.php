<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

$id = intval($_GET['id']);

// Kita ambil dulu nama supir berdasarkan ID ini
$sopir = fetchOne(query("SELECT nama_sopir FROM sopir WHERE id = $id"));

if($sopir) {
    $nama = $sopir['nama_sopir'];
    
    // Proteksi: Cek apakah nama supir ini sudah pernah dipakai di tabel pengiriman
    $cek_pengiriman = fetchOne(query("SELECT COUNT(*) as total FROM pengiriman WHERE sopir = '$nama'"));

    if($cek_pengiriman['total'] > 0) {
        // Tolak penghapusan jika sudah dipakai agar riwayat logistik tidak rusak
        header("Location: index.php?error=in_use");
        exit();
    }

    // Jika aman, hapus data supir
    $sql = "DELETE FROM sopir WHERE id = $id";
    if(query($sql)) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
} else {
    header("Location: index.php?error=1");
}
?>