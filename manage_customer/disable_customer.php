<?php
session_start(); // Start session

// Check if the admin is logged in
if (!(isset($_SESSION['userID']))) {
    // Redirect to the login page if neither is logged in
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Disable customer
if (isset($_GET['id'])) {
    $customerID = $_GET['id'];

    // Update customer status to '0' (disabled)
    $sql = "UPDATE customer SET status = 0 WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);

    if ($stmt->execute()) {
        header("Location: manage_customer.php?success=Customer disabled successfully");
    } else {
        header("Location: manage_customer.php?error=Failed to disable customer");
    }
} else {
    header("Location: manage_customer.php?error=No customer ID provided");
}


$conn->close();
?>