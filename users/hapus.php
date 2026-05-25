<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

$id = intval($_GET['id']);

// Proteksi: Admin tidak boleh menghapus akunnya sendiri yang sedang dipakai
if($id == $_SESSION['user_id']) {
    header("Location: index.php?error=1");
    exit();
}

// 1. BERSIHKAN DATA CHILD (KETERKAITAN) TERLEBIH DAHULU
// Hapus semua notifikasi yang terikat dengan user ini
query("DELETE FROM notifikasi WHERE user_id = $id");

// Jika Anda memiliki tabel riwayat_aktivitas yang terikat dengan user, hapus tanda miring ganda di bawah ini:
// query("DELETE FROM riwayat_aktivitas WHERE user_id = $id");

// 2. HAPUS DATA PARENT (USER UTAMA)
$sql = "DELETE FROM users WHERE id = $id";

if(query($sql)) {
    header("Location: index.php?success=1");
} else {
    header("Location: index.php?error=1");
}
?>