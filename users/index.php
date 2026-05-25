<?php
require_once '../config/database.php';
// Hanya Admin yang boleh kelola pengguna
if(!isLoggedIn() || $_SESSION['role'] != 'admin') redirect('../index.php');

$content = 'content.php';
include '../layout/main.php';
?>