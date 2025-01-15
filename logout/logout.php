<?php
session_start(); // Start the session

// Destroy all session variables
session_unset();

// Destroy the session itself
session_destroy();

header("Location: /TeiponGadgetSystem/index.php");
exit();
?>
