<?php
session_start();
require_once '../config/database.php';

// Proteksi: Hanya Logistik yang boleh masuk ke sini
if(!isLoggedIn() || $_SESSION['role'] != 'logistik') {
    header("Location: ../dashboard/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user logistik dan nama gudang tempatnya bertugas (jika ada)[cite: 1]
$logistik = fetchOne(query("
    SELECT u.*, w.nama_warehouse 
    FROM users u 
    LEFT JOIN warehouse w ON u.warehouse_id = w.id 
    WHERE u.id = $user_id
"));

// PROSES SIMPAN DATA (FOTO & NO HP)
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $foto_query = "";
    
    // Proses Upload Foto Jika Ada
    if(isset($_FILES['foto_user']) && $_FILES['foto_user']['error'] == 0){
        $dir = '../uploads/users/';
        if(!file_exists($dir)) mkdir($dir, 0777, true);
        
        $ext = pathinfo($_FILES['foto_user']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . $user_id . '.' . $ext;
        
        if(move_uploaded_file($_FILES['foto_user']['tmp_name'], $dir.$filename)){
            // Simpan path secara utuh persis seperti logika Driver sebelumnya
            $full_path = '/shankara_trackingbarang/uploads/users/' . $filename;
            $foto_query = ", foto_user = '$full_path'";
        }
    }
    
    // Update ke tabel users
    if(query("UPDATE users SET no_hp = '$no_hp' $foto_query WHERE id = $user_id")){
        header("Location: profile.php?success=1");
        exit();
    } else {
        header("Location: profile.php?error=1");
        exit();
    }
}

$content = 'profile_content.php';
include '../layout/main.php';
?>