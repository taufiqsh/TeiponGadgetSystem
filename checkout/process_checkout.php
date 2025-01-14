<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if (!isset($_SESSION['userID'])) {
    header("Location: ../cart/cart.php");
    exit();
}

$customerID = $_SESSION['userID'];
$conn->begin_transaction();

try {
    // Fetch cart items from the database
    $cartSql = "
        SELECT c.productID, c.quantity, p.productPrice AS price, p.productName, c.variantID
        FROM cart c
        JOIN product p ON c.productID = p.productID
        WHERE c.customerID = ?";
    $cartStmt = $conn->prepare($cartSql);
    $cartStmt->bind_param("i", $customerID);
    $cartStmt->execute();
    $cartResult = $cartStmt->get_result();

    $cart = [];
    $totalPrice = 0;

    while ($item = $cartResult->fetch_assoc()) {
        $cart[] = $item;
        $totalPrice += $item['price'] * $item['quantity'];
    }

    if (empty($cart)) {
        throw new Exception("Cart is empty.");
    }

    // Fetch customer address details
    $customerSql = "SELECT customerAddress, customerCity, customerPostalCode, customerState 
                    FROM customer 
                    WHERE customerID = ?";
    $customerStmt = $conn->prepare($customerSql);
    $customerStmt->bind_param("i", $customerID);
    $customerStmt->execute();
    $customerResult = $customerStmt->get_result();

    if ($customerResult->num_rows === 0) {
        throw new Exception("Customer address not found.");
    }

    $customer = $customerResult->fetch_assoc();
    $orderDetails = "Shipping Address: {$customer['customerAddress']}, {$customer['customerCity']}, {$customer['customerState']}, {$customer['customerPostalCode']}";

    // Insert the order
    $orderDate = date("Y-m-d");
    $orderStatus = 'Pending Payment';

    $orderSql = "INSERT INTO orders (orderDetails, orderDate, totalAmount, orderStatus, customerID) 
                 VALUES (?, ?, ?, ?, ?)";
    $orderStmt = $conn->prepare($orderSql);
    $orderStmt->bind_param("sssss", $orderDetails, $orderDate, $totalPrice, $orderStatus, $customerID);
    $orderStmt->execute();
    $orderID = $orderStmt->insert_id;

    // Insert order products
    $checkedOutItems = []; // Track items added to the order

    foreach ($cart as $item) {
        $productID = $item['productID'];
        $variantID = isset($item['variantID']) ? $item['variantID'] : null;
        $quantity = $item['quantity'];
        $price = $item['price'];
        $totalItemPrice = $price * $quantity;

        // Insert into orderProducts
        $orderProductSql = "INSERT INTO orderProducts (orderID, productID, variantID, quantity, price, totalPrice, createdAt, updatedAt) 
                            VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        $orderProductStmt = $conn->prepare($orderProductSql);
        $orderProductStmt->bind_param("iiidid", $orderID, $productID, $variantID, $quantity, $price, $totalItemPrice);
        $orderProductStmt->execute();

        // Delete the item from the cart after processing
        $deleteCartSql = "DELETE FROM cart WHERE productID = ? AND variantID = ? AND customerID = ?";
        $deleteCartStmt = $conn->prepare($deleteCartSql);
        $deleteCartStmt->bind_param("iii", $productID, $variantID, $customerID);
        $deleteCartStmt->execute();
    }

    $conn->commit();
    header("Location: ../payment/payment.php?orderID=" . $orderID);
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
    exit();
}
