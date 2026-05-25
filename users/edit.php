<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $password_raw = $_POST['password'];
    
    // Logika Alokasi Warehouse untuk Logistik & Driver saat Edit
    $warehouse_id = "NULL"; 
    if(($role == 'logistik' || $role == 'driver') && !empty($_POST['warehouse_id'])) {
        $wid = intval($_POST['warehouse_id']);
        
        // PROTEKSI: Cek apakah posisi ini di gudang tersebut sudah dipakai akun lain (selain akun ini sendiri)
        $cek_gudang = fetchOne(query("SELECT id FROM users WHERE warehouse_id = $wid AND role = '$role' AND id != $id"));
        if($cek_gudang) {
            header("Location: index.php?error=warehouse_used");
            exit();
        }
        $warehouse_id = $wid;
    }
    
    // Cek duplikasi username (kecuali akun ini sendiri)
    $cek_username = fetchOne(query("SELECT id FROM users WHERE username = '$username' AND id != $id"));
    if($cek_username) {
        header("Location: index.php?error=username_exists");
        exit();
    }
    
    // Query Dasar Update
    $update_query = "nama_lengkap = '$nama_lengkap', username = '$username', role = '$role', warehouse_id = $warehouse_id";
    
    // Jika password diisi, update password
    if(!empty($password_raw)) {
        $password_hashed = password_hash($password_raw, PASSWORD_DEFAULT);
        $update_query .= ", password = '$password_hashed'";
    }
    
    $sql = "UPDATE users SET $update_query WHERE id = $id";
    
    if(query($sql)) {
        if($id == $_SESSION['user_id']) {
            $_SESSION['role'] = $role;
            $_SESSION['nama'] = $nama_lengkap;
        }
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
}
?>