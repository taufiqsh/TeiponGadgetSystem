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
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>
    <div class="container my-5">
        <h1 class="text-center mb-4">Checkout</h1>

        <div class="cart-items mb-4">
            <h3>Your Cart Items:</h3>
            <?php if (!empty($cart)): ?>
                <div class="cart-list">
                    <?php foreach ($cart as $item): ?>
                        <div class="cart-item d-flex justify-content-between mb-3">
                            <div class="d-flex">
                                <img src="../uploads/<?= $item['image']; ?>" alt="<?= $item['name']; ?>" width="100" class="me-3">
                                <div>
                                    <h5><?= $item['name']; ?> (x<?= $item['quantity']; ?>)</h5>
                                    <small>RM <?= number_format($item['price'], 2); ?></small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Your cart is empty. <a href="shop.php">Browse products</a></p>
            <?php endif; ?>
        </div>

        <div class="checkout-summary">
            <h3>Order Summary:</h3>
            <p><strong>Total Price: </strong>RM <?= number_format($totalPrice, 2); ?></p>

            <form action="process_checkout.php" method="POST">
                <h4 class="mt-4">Shipping Information:</h4>

                <div class="mb-3">
                    <label for="shippingName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="shippingName" name="shippingName" value="<?= htmlspecialchars($customer['customerName']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="shippingAddress" class="form-label">Address</label>
                    <input type="text" class="form-control" id="shippingAddress" name="shippingAddress" value="<?= htmlspecialchars($customer['customerAddress']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="shippingState" class="form-label">State</label>
                    <input type="text" class="form-control" id="shippingState" name="shippingState" value="<?= htmlspecialchars($customer['customerState']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="shippingCity" class="form-label">City</label>
                    <input type="text" class="form-control" id="shippingCity" name="shippingCity" value="<?= htmlspecialchars($customer['customerCity']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="shippingPostalCode" class="form-label">Postal Code</label>
                    <input type="text" class="form-control" id="shippingPostalCode" name="shippingPostalCode" value="<?= htmlspecialchars($customer['customerPostalCode']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="shippingPhone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="shippingPhone" name="shippingPhone" value="<?= htmlspecialchars($customer['customerPhoneNumber']); ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Complete Purchase</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>
