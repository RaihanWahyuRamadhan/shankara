<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_raw = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Logika Alokasi Warehouse untuk Logistik & Driver
    $warehouse_id = "NULL"; 
    if(($role == 'logistik' || $role == 'driver') && !empty($_POST['warehouse_id'])) {
        $wid = intval($_POST['warehouse_id']);
        
        // PROTEKSI BARU: Cek apakah warehouse ini sudah dipakai akun DENGAN ROLE YANG SAMA
        // Artinya: 1 Gudang Boleh Punya 1 Logistik & 1 Driver. Tapi tidak boleh ada 2 Logistik di gudang yang sama.
        $cek_gudang = fetchOne(query("SELECT id FROM users WHERE warehouse_id = $wid AND role = '$role'"));
        if($cek_gudang) {
            header("Location: index.php?error=warehouse_used");
            exit();
        }
        $warehouse_id = $wid;
    }
    
    // Cek duplikasi username
    $cek_username = fetchOne(query("SELECT id FROM users WHERE username = '$username'"));
    if($cek_username) {
        header("Location: index.php?error=username_exists");
        exit();
    }
    
    // Enkripsi password
    $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO users (nama_lengkap, username, password, role, warehouse_id) 
            VALUES ('$nama_lengkap', '$username', '$password_hashed', '$role', $warehouse_id)";
    
    if(query($sql)) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
}
?>