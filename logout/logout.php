<?php
session_start(); // Start the session

// Destroy all session variables
session_unset();

// Destroy the session itself
session_destroy();

// Redirect to the login page (you can change this URL if needed)
header("Location: ../admin_login/admin_login.php?success=You have logged out successfully");
exit();
?>
