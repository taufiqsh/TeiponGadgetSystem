<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Ensure the user is logged in
if (!isset($_SESSION['userID'])) {
    echo json_encode(['error' => 'Please log in to view your cart']);
    exit;
}

$customerID = $_SESSION['userID'];

try {
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Fetch cart items for the customer
    $cartStmt = $conn->prepare("SELECT c.productID, c.variantID, p.productName, p.productPrice, c.quantity, pv.variantName, p.productImage
    FROM CART c
    JOIN Product p ON c.productID = p.productID
    JOIN ProductVariant pv ON c.variantID = pv.variantID
    WHERE c.customerID = ?");
    $cartStmt->bind_param("i", $customerID);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();

    if ($cartResult->num_rows === 0) {
        echo json_encode(['error' => 'Cart is empty']);
        exit;
    }

    $cartItems = [];
    while ($row = $cartResult->fetch_assoc()) {
        $cartItems[] = [
            'productID' => $row['productID'],
            'variantID' => $row['variantID'],
            'productName' => $row['productName'],
            'productPrice' => $row['productPrice'],
            'quantity' => $row['quantity'],
            'productImage' => $row['productImage'],
            'variantName' => $row['variantName'], // Ensure this is returned
        ];
    }
    $cartStmt->close();

    // Fetch the total cart count
    $countStmt = $conn->prepare("SELECT SUM(quantity) AS cartCount FROM CART WHERE customerID = ?");
    $countStmt->bind_param("i", $customerID);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $cartCountRow = $countResult->fetch_assoc();
    $cartCount = $cartCountRow['cartCount'] ? $cartCountRow['cartCount'] : 0;  // Set to 0 if NULL
    $countStmt->close();

    // Return the cart items and total count
    echo json_encode([
        'cart' => $cartItems,  // Ensure the cart items are returned
        'cartCount' => $cartCount
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

$conn->close();
?>
