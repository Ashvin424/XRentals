<?php
require_once __DIR__ . '/config/config.php';
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session. (This will delete the session cookie).
session_destroy();

// Redirect to login page
header("location: admin_login.php");
exit;
?>