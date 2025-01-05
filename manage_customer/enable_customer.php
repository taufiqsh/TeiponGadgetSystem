<?php
session_start(); // Start session

// Check if the admin is logged in
if (!(isset($_SESSION['userID']))) {
    // Redirect to the login page if not logged in
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Enable customer
if (isset($_GET['id'])) {
    $customerID = $_GET['id'];

    // Update customer status to '1' (enabled)
    $sql = "UPDATE customer SET status = 1 WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);

    if ($stmt->execute()) {
        header("Location: manage_customer.php?success=Customer enabled successfully");
    } else {
        header("Location: manage_customer.php?error=Failed to enable customer");
    }
} else {
    header("Location: manage_customer.php?error=No customer ID provided");
}

$conn->close();
?>
