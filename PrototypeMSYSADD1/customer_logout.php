<?php
session_start();

// Destroy customer session data
unset($_SESSION['customer_loggedin']);
unset($_SESSION['customer_id']);
unset($_SESSION['customer_username']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);

// Redirect to customer login page
header("Location: customer_login.php");
exit();
?>
