<?php
session_start();

// TEMPORARY: auto-login for testing
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;  // dummy user id
}

// Dummy username
$username = "Jane Doe";
?>
