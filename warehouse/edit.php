<?php
require_once '../config/database.php';
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $nama_warehouse = mysqli_real_escape_string($conn, $_POST['nama_warehouse']);
    
    $sql = "UPDATE warehouse SET nama_warehouse = '$nama_warehouse' WHERE id = $id";
    if(query($sql)) {
        header("Location: index.php?success=1");
    } else {
        header("Location: index.php?error=1");
    }
}
?>