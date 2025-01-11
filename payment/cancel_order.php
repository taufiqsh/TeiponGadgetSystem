<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to access this feature.']);
    exit();
}

// Check if the request is POST and contains required data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelOrder'], $_POST['orderID'])) {
    $orderID = $_POST['orderID'];

    // Validate the orderID
    if (!filter_var($orderID, FILTER_VALIDATE_INT)) {
        echo json_encode(['success' => false, 'message' => 'Invalid order ID.']);
        exit();
    }

    // Include the database configuration
    require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

    // Update the order status to "Order Cancelled"
    $sql = "UPDATE orders SET orderStatus = 'Order Cancelled' WHERE orderID = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $orderID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Order cancelled successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to cancel the order.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
