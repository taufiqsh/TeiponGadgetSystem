<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    // Redirect to the appropriate login page
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

// Check if the orderID is set in the POST request (since the form sends a POST request)
if (isset($_POST['orderID'])) {
    // Get the orderID from the POST data
    $orderID = $_POST['orderID'];

    // Validate that the orderID is an integer
    if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
        // If the orderID is invalid, redirect with an error message
        header("Location: manage_order.php?error=Invalid order ID");
        exit();
    }

    // Include the database configuration
    require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

    // Prepare the SQL query to delete the order
    $sql = "DELETE FROM orders WHERE orderID = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($sql)) {
        // Bind the parameter (orderID)
        $stmt->bind_param("i", $orderID);

        // Execute the statement
        if ($stmt->execute()) {
            // Redirect to the manage orders page with a success message
            header("Location: manage_order.php?success=Order deleted successfully");
        } else {
            // Redirect with an error message if the query failed
            header("Location: manage_order.php?error=Failed to delete order");
        }

        // Close the statement
        $stmt->close();
    } else {
        // Redirect with an error message if the SQL query preparation failed
        header("Location: manage_order.php?error=Database error");
    }

    // Close the database connection
    $conn->close();
} else {
    // Redirect if no orderID is provided in the POST request
    header("Location: manage_order.php?error=No order selected for deletion");
    exit();
}
?>
