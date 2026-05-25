<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_warehouse = mysqli_real_escape_string($conn, $_POST['nama_warehouse']);
    
    $sql = "INSERT INTO warehouse (nama_warehouse) VALUES ('$nama_warehouse')";
    if(query($sql)) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
}
?>