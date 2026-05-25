<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nama_sopir = mysqli_real_escape_string($conn, $_POST['nama_sopir']);
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $status_aktif = mysqli_real_escape_string($conn, $_POST['status_aktif']);
    
    // Logika Jika Ada Upload Foto Baru saat Edit
    $foto_query = "";
    if(isset($_FILES['foto_sopir']) && $_FILES['foto_sopir']['error'] == 0) {
        $dir = '../uploads/sopir/';
        if(!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['foto_sopir']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $target = $dir . $filename;
        
        if(move_uploaded_file($_FILES['foto_sopir']['tmp_name'], $target)) {
            $foto_path = 'uploads/sopir/' . $filename;
            $foto_query = ", foto_sopir = '$foto_path'"; // Tambahan SQL Query
        }
    }
    
    $sql = "UPDATE sopir SET nama_sopir = '$nama_sopir', no_hp = '$no_hp', status_aktif = '$status_aktif' $foto_query WHERE id = $id";
    
    if(query($sql)) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
}
?>