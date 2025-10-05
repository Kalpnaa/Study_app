<?php
session_start();
$index = (int)($_GET['index'] ?? -1);
$status = $_GET['status'] ?? '';
if ($index >= 0 && isset($_SESSION['tasks'][$index]) && $status) {
    $_SESSION['tasks'][$index]['status'] = $status;
}
?>
