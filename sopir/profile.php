<?php
session_start();
require_once '../config/database.php';

// Proteksi: Hanya Driver yang boleh masuk ke sini
if(!isLoggedIn() || $_SESSION['role'] != 'driver') {
    header("Location: ../dashboard/index.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'];

// Cek apakah data driver ini sudah ada di tabel sopir
$driver = fetchOne(query("SELECT * FROM sopir WHERE nama_sopir = '$nama_user'"));

// Jika belum ada datanya sama sekali, buatkan baris datanya secara otomatis
if(!$driver) {
    query("INSERT INTO sopir (nama_sopir) VALUES ('$nama_user')");
    $driver = fetchOne(query("SELECT * FROM sopir WHERE nama_sopir = '$nama_user'"));
}

// PROSES SIMPAN DATA (FOTO & NO HP)
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $foto_query = "";
    
    // Proses Upload Foto Jika Ada
    if(isset($_FILES['foto_sopir']) && $_FILES['foto_sopir']['error'] == 0){
        $dir = '../uploads/sopir/';
        if(!file_exists($dir)) mkdir($dir, 0777, true); // Buat folder otomatis jika belum ada
        
        $ext = pathinfo($_FILES['foto_sopir']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . $_SESSION['user_id'] . '.' . $ext;
        
        if(move_uploaded_file($_FILES['foto_sopir']['tmp_name'], $dir.$filename)){
            $foto_query = ", foto_sopir = '$filename'";
        }
    }
    
    // Update ke database
    $driver_id = $driver['id'];
    if(query("UPDATE sopir SET no_hp = '$no_hp' $foto_query WHERE id = $driver_id")){
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