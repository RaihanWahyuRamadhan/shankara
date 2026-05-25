<?php
require_once '../config/database.php';
if(!isLoggedIn()) redirect('../auth/login.php');

$id = $_GET['id'];
$sql = "DELETE FROM proyek WHERE id=$id";

if(query($sql)) {
    query("INSERT INTO riwayat_aktivitas (user_id, aktivitas, tabel_terkait, record_id) 
           VALUES ({$_SESSION['user_id']}, 'Hapus proyek ID: $id', 'proyek', $id)");
    header("Location: index.php?deleted=1");
} else {
    header("Location: index.php?error=1");
}
?>