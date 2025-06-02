<?php
require_once __DIR__ .'/../WorkWithSQL.php';
$config = require_once __DIR__ . '/../../../config/s0188328_WEB_6.php';

$id = (int)($_GET['id'] ?? 0);

$a = deleteApp(getConnection($config), $id);
header("Location: index.php");
exit;
?>