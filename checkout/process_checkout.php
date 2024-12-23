<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if cart is empty or customer is not logged in
if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['customerID'])) {
    header("Location: ../cart/cart.php");
    exit();
}

$cart = $_SESSION['cart'];
$totalPrice = 0;
foreach ($cart as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

$customerID = $_SESSION['customerID'];
$shippingName = $_POST['shippingName'];
$shippingAddress = $_POST['shippingAddress'];
$shippingState = $_POST['shippingState'];
$shippingCity = $_POST['shippingCity'];
$shippingPostalCode = $_POST['shippingPostalCode'];
$shippingPhone = $_POST['shippingPhone'];

// Get current date for the order
$orderDate = date("Y-m-d");

// Set order status (initial status could be 'Pending')
$orderStatus = 'Pending';

// Create the order in the orders table
$sql = "INSERT INTO orders (orderDetails, orderDate, totalAmount, orderStatus, customerID) 
        VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$orderDetails = 'Order placed through customer portal'; // You can adjust this based on your needs

$stmt->bind_param("ssdis", $orderDetails, $orderDate, $totalPrice, $orderStatus, $customerID);
$stmt->execute();
$orderID = $stmt->insert_id;  // Get the ID of the newly created order

// Insert products from the cart into orderProducts table (Assuming you have an orderProducts table)
foreach ($cart as $item) {
    $productID = $item['id'];
    $quantity = $item['quantity'];
    $price = $item['price'];
    $totalItemPrice = $price * $quantity;

    $orderProductSql = "INSERT INTO orderProducts (orderID, productID, quantity, price, totalPrice) 
                        VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($orderProductSql);
    $stmt->bind_param("iiidi", $orderID, $productID, $quantity, $price, $totalItemPrice);
    $stmt->execute();
}

// Clear the cart after successful order creation
unset($_SESSION['cart']);

// Redirect to payment page
header("Location: payment.php?orderID=" . $orderID);
exit();
?>
