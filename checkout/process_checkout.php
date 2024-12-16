<?php
session_start();
require_once('db_connection.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['customerUsername'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

// Retrieve order data
$customerID = $_POST['customerID'];
$address = $_POST['address'];
$contact = $_POST['contact'];
$totalPrice = $_POST['totalPrice'];
$cartData = json_decode($_POST['cartData'], true); // Decode cart data

$orderDate = date('Y-m-d H:i:s');
$orderStatus = 'Pending';

// Insert the order into the orders table
$orderQuery = "INSERT INTO orders (customerID, orderDate, orderStatus, totalPrice, shippingAddress, contactNumber) 
               VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("issdss", $customerID, $orderDate, $orderStatus, $totalPrice, $address, $contact);
$stmt->execute();
$orderID = $stmt->insert_id; // Get the generated order ID

// Insert products into order_products table
foreach ($cartData as $item) {
    $productID = $item['id']; // You need to fetch this from the database based on the product name or other criteria
    $quantity = $item['quantity'];
    $price = $item['price'];

    $orderProductQuery = "INSERT INTO order_products (orderID, productID, quantity, price) 
                          VALUES (?, ?, ?, ?)";
    $orderProductStmt = $conn->prepare($orderProductQuery);
    $orderProductStmt->bind_param("iiid", $orderID, $productID, $quantity, $price);
    $orderProductStmt->execute();
}

// Redirect to order confirmation page
header('Location: order_confirmation.php?orderID=' . $orderID);
exit();
?>
