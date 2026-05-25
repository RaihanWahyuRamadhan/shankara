<?php
require_once '../config/database.php';
// Akses untuk Admin (dan mungkin Logistik jika Anda izinkan, di sini kita set Admin saja)
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

$content = 'content.php';
include '../layout/main.php';
?>