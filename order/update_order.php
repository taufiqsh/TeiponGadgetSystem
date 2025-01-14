<?php
session_start();

// Check if the user is logged in and authorized
if (!isset($_SESSION['userID'])) {
    header("Location: ../login/login.php?error=Access denied");
    exit();
}

// Include the database configuration
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if orderID and status are set in the POST request
if (isset($_POST['orderID']) && isset($_POST['status'])) {
    $orderID = intval($_POST['orderID']); // Ensure orderID is an integer
    $status = trim($_POST['status']); // Trim extra spaces from status

    // Validate the input
    $validStatuses = [
        'Pending Payment',
        'Processing Payment',
        'Order Completed',
        'Order Shipped',
        'Order Cancelled',
        'Order Rejected'
    ];

    if (!in_array($status, $validStatuses)) {
        echo "error";
        exit();
    }

    // Update the order status in the database
    $sql = "UPDATE orders SET orderStatus = ? WHERE orderID = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("si", $status, $orderID);
        if ($stmt->execute()) {
            echo "success";
        } else {
            error_log("Database execution error: " . $stmt->error); // Log detailed error for debugging
            echo "error";
        }
        $stmt->close();
    } else {
        error_log("SQL prepare error: " . $conn->error); // Log detailed error for debugging
        echo "error";
    }

    // Close database connection
    $conn->close();
} else {
    echo "error"; // Missing required POST parameters
}
?>
