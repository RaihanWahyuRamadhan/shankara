<?php
require_once dirname(__DIR__) . '/config/database.php';
if(!isLoggedIn()) redirect('../auth/login.php');

$content = 'conten.php';
include dirname(__DIR__) . '/layout/main.php';
?>