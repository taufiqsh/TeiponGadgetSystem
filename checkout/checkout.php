<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the cart exists in session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Redirect to cart page if cart is empty
    header("Location: ../cart/cart.php");
    exit();
}

$cart = $_SESSION['cart'];
$totalPrice = 0;
foreach ($cart as $item) {
    $totalPrice += $item['price'] * $item['quantity'];
}

// Check if the user is logged in and fetch customer data
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$customerID = $_SESSION['userID'];

// Fetch customer data from the database
$sql = "SELECT customerName, customerAddress, customerState, customerPostalCode, customerCity, customerPhoneNumber FROM customer WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

// Check if customer data is found
if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
} else {
    // If customer data is not found, redirect to login
    header('Location: login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <!-- Link to Bootstrap CSS -->
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color:rgb(4, 4, 4);
        }
    </style>
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>
    <div class="container my-5">
        <h1 class="text-center mb-4">Checkout</h1>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Your Cart Items</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($cart)): ?>
                            <div class="cart-list">
                                <?php foreach ($cart as $item): ?>
                                    <div class="cart-item d-flex justify-content-between mb-3">
                                        <div class="d-flex">
                                            <img src="../uploads/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" class="me-3 img-thumbnail product-image">
                                            <div>
                                                <h5><?= htmlspecialchars($item['name']); ?> (x<?= $item['quantity']; ?>)</h5>
                                                <small class="price">RM <?= number_format($item['price'], 2); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p>Your cart is empty. <a href="shop.php">Browse products</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Order Summary</h3>
                    </div>
                    <div class="card-body">
                    <form action="process_checkout.php" method="POST">
                        <p><strong>Total Price: </strong><span class="price">RM <?= number_format($totalPrice, 2); ?></span></p>
                        <button type="submit" class="btn btn-primary w-100">Complete Purchase</button>
                    </form>    
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>