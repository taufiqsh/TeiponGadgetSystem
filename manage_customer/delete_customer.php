<?php
session_start(); // Start session

// Check if the admin is logged in
if (!isset($_SESSION['adminID']) || !isset($_SESSION['adminName'])) {
    header("Location: ../admin_login/admin_login.php?error=Please login to access the dashboard");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Delete customer
if (isset($_GET['id'])) {
    $customerID = $_GET['id'];

    $sql = "DELETE FROM Customers WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);

    if ($stmt->execute()) {
        header("Location: manage_customers.php?success=Customer deleted successfully");
    } else {
        header("Location: manage_customers.php?error=Failed to delete customer");
    }
} else {
    header("Location: manage_customers.php?error=No customer ID provided");
}

$conn->close();
?>
