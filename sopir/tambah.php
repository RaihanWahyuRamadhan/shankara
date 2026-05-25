<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_sopir = mysqli_real_escape_string($conn, $_POST['nama_sopir']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $status_aktif = mysqli_real_escape_string($conn, $_POST['status_aktif']);
    
    // Logika Upload Foto
    $foto_path = NULL;
    if(isset($_FILES['foto_sopir']) && $_FILES['foto_sopir']['error'] == 0) {
        $dir = '../uploads/sopir/';
        // Buat folder jika belum ada
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['foto_sopir']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $target = $dir . $filename;
        
        // Pindahkan file dari memori sementara ke folder
        if(move_uploaded_file($_FILES['foto_sopir']['tmp_name'], $target)) {
            $foto_path = 'uploads/sopir/' . $filename; // Path yang disimpan di database
        }
    }
    
    // Query dengan foto
    if($foto_path) {
        $sql = "INSERT INTO sopir (nama_sopir, no_hp, status_aktif, foto_sopir) VALUES ('$nama_sopir', '$no_hp', '$status_aktif', '$foto_path')";
    } else {
        $sql = "INSERT INTO sopir (nama_sopir, no_hp, status_aktif) VALUES ('$nama_sopir', '$no_hp', '$status_aktif')";
    }
    
    if(query($sql)) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
}
?>