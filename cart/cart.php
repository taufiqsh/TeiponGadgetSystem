<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Ensure user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$customerID = $_SESSION['userID'];

// Check if the cart exists in the database
$sql = "
    SELECT 
        c.cartID, c.productID, c.quantity, 
        p.productName AS name, p.productPrice AS price, 
        p.productImage AS productImage
    FROM 
        cart c
    JOIN 
        product p ON c.productID = p.productID
    WHERE 
        c.customerID = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

// Process cart items
$cart = [];
$totalPrice = 0;

if ($result->num_rows > 0) {
    while ($item = $result->fetch_assoc()) {
        $cart[] = $item;
        $totalPrice += $item['price'] * $item['quantity'];
    }
    $cartEmpty = false;
} else {
    $cartEmpty = true;
}

$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <div class="container my-5">
        <h1>Your Shopping Cart</h1>

        <?php if ($cartEmpty): ?>
            <!-- Display a Bootstrap alert when the cart is empty -->
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                There are no products in your cart.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php else: ?>
            <!-- Display cart items if the cart is not empty -->
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                        <tr>
                            <td>
                                <img src="../uploads/<?= htmlspecialchars($item['productImage'] ?? 'default.jpg'); ?>"
                                     alt="Product Image"
                                     class="img-thumbnail"
                                     style="width: 100px; height: auto;">
                            </td>
                            <td><?= htmlspecialchars($item['name']); ?></td>
                            <td>RM <?= number_format($item['price'], 2); ?></td>
                            <td><?= $item['quantity']; ?></td>
                            <td>RM <?= number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="remove_from_cart.php?id=<?= $item['cartID']; ?>" class="btn btn-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Total Price: RM <?= number_format($totalPrice, 2); ?></h3>

            <a href="../checkout/checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        <?php endif; ?>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>
