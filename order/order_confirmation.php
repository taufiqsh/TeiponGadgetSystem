<?php
session_start();

// Check if the order total price is passed via URL
if (isset($_GET['total'])) {
    $totalPrice = $_GET['total'];
} else {
    // If there's no total price, redirect back to the cart or an error page
    header("Location: cart.php");
    exit();
}

// Check if the customer is logged in
if (!isset($_SESSION['customerID']) || !isset($_SESSION['customerUsername'])) {
    header("Location: ../login/login.php?error=" . urlencode("Please login to view your order confirmation"));
    exit();
}

// Fetch customer details (Optional: show customer information on confirmation page)
$customerID = $_SESSION['customerID'];
$customerUsername = $_SESSION['customerUsername'];
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch customer data from the database
$sql = "SELECT customerName, customerEmail, customerUsername FROM Customer WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();

// Insert order into the orders table
$orderDate = date("Y-m-d");
$orderStatus = 'Pending';  // Initially set order status as Pending
$orderDetails = 'Order placed through customer portal';  // Add more details if needed
$orderSql = "INSERT INTO orders (orderDetails, orderDate, totalAmount, orderStatus, customerID) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($orderSql);
$stmt->bind_param("sssii", $orderDetails, $orderDate, $totalPrice, $orderStatus, $customerID);
$stmt->execute();
$orderID = $stmt->insert_id;  // Get the ID of the newly created order

// Insert products from the cart into orderProducts table
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $productID = $item['productID'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $totalItemPrice = $price * $quantity;
        
        $orderProductSql = "INSERT INTO orderProducts (orderID, productID, quantity, price, totalPrice) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($orderProductSql);
        $stmt->bind_param("iiidi", $orderID, $productID, $quantity, $price, $totalItemPrice);
        $stmt->execute();
    }
}

// Insert payment details into the payment table (assuming payment is confirmed)
$paymentStatus = 'Pending';  // Change to 'Paid' after payment is processed
$paymentDate = date("Y-m-d");
$staffID = $_SESSION['staffID'];  // Staff ID who confirmed the payment, if applicable

$paymentSql = "INSERT INTO payment (paymentStatus, paymentDate, orderID, staffID) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($paymentSql);
$stmt->bind_param("ssii", $paymentStatus, $paymentDate, $orderID, $staffID);
$stmt->execute();

// Clear the cart after successful payment
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Thank You for Your Order!</h1>

        <div class="alert alert-success">
            <h4 class="alert-heading">Order Placed Successfully</h4>
            <p>We have received your order and it will be processed shortly.</p>
            <hr>
            <p class="mb-0">Total Price: RM <?= number_format($totalPrice, 2); ?></p>
        </div>

        <div class="order-details">
            <h3>Order Summary</h3>
            <p><strong>Customer Name:</strong> <?= htmlspecialchars($customer['customerName']); ?></p>
            <p><strong>Username:</strong> <?= htmlspecialchars($customer['customerUsername']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($customer['customerEmail']); ?></p>

            <!-- Display products ordered -->
            <h4>Products Purchased</h4>
            <ul>
                <?php
                // Display all products in the cart from the session
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        echo "<li>" . htmlspecialchars($item['name']) . " (x" . $item['quantity'] . ") - RM " . number_format($item['price'] * $item['quantity'], 2) . "</li>";
                    }
                }
                ?>
            </ul>

            <hr>
            <p class="mb-0">Thank you for shopping with us!</p>

            <!-- Optionally, you can add a link to continue shopping or view order history -->
            <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
