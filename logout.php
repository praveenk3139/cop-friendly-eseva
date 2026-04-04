<?php
session_start();

// destroy all session data
session_unset();
session_destroy();

// redirect to admin login page
header("Location: admin_login.php");
exit();
?>