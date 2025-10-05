<?php
session_start();
session_unset();  // remove all session variables
session_destroy(); // destroy session
header("Location: signup.php?logout=1");
exit();
?>
