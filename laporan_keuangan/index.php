<?php
require_once '../config/database.php';
// Akses khusus Admin atau manajemen level atas
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

$content = 'content.php';
include '../layout/main.php';
?>