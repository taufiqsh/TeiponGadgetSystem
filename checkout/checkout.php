<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the user is logged in and fetch customer data
if (!isset($_SESSION['userID'])) {
    header("Location: ../login/login.php?error=Access denied");
    exit();
}

$customerID = $_SESSION['userID'];

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Add product to cart
        function addToCart(productID, productName, productPrice, productImage) {
            console.log("Adding to cart:", {
                productID,
                productName,
                productPrice,
                productImage
            });

            $.ajax({
                url: '../cart/add_to_cart.php',
                method: 'POST',
                data: {
                    productID: productID,
                    productName: productName,
                    productPrice: productPrice,
                    productImage: productImage
                },
                success: function (response) {
                    try {
                        const responseData = JSON.parse(response);
                        const cartCountElement = document.getElementById('cartCount');
                        if (responseData.cartCount !== undefined) {
                            cartCountElement.innerText = responseData.cartCount;
                        }
                        updateCartModal(responseData.cart);
                    } catch (error) {
                        console.error("Error parsing response:", error);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Add to cart error:", xhr.responseText);
                }
            });
        }

        // Update the cart modal with current cart data
        function updateCartModal(cart) {
            let cartItemsHTML = '';
            let total = 0;

            cart.forEach(item => {
                total += item.price * item.quantity;
                cartItemsHTML += `
        <div class="cart-item card mb-3 shadow-sm">
            <div class="card-body d-flex justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="../uploads/${item.image}" alt="${item.name}" class="rounded img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                    <div>
                        <h6 class="mb-1">${item.name} <small class="text-muted">(x${item.quantity})</small></h6>
                        <p class="mb-0 text-primary fw-bold">RM ${(Number(item.price)).toFixed(2)}</p>
                    </div>
                </div>
                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                    <i class="bi bi-trash3"></i> Remove
                </button>
            </div>
        </div>`;
            });

            $('#cartItems').html(cartItemsHTML);
            $('#cartTotal').text(total.toFixed(2));
        }

        // Remove product from cart
        function removeFromCart(productId) {
            $.ajax({
                url: '../cart/remove_from_cart.php',
                method: 'POST',
                data: {
                    productID: productId
                },
                success: function (response) {
                    try {
                        const responseData = JSON.parse(response);
                        updateCartModal(responseData.cart);
                        const cartCountElement = document.getElementById('cartCount');
                        if (responseData.cartCount !== undefined) {
                            cartCountElement.innerText = responseData.cartCount;
                        }
                    } catch (error) {
                        console.error("Error parsing response:", error);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Remove from cart error:", xhr.responseText);
                }
            });
        }

        // Initialize the cart modal when the page loads
        $(document).ready(function () {
            $.ajax({
                url: '../cart/get_cart.php',
                method: 'GET',
                success: function (response) {
                    try {
                        const responseData = JSON.parse(response);
                        if (responseData && responseData.cart) {
                            const cart = responseData.cart || [];
                            updateCartModal(cart);
                            const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
                            document.getElementById('cartCount').innerText = cartCount;
                        }
                    } catch (error) {
                        console.error("Error parsing the cart data:", error);
                    }
                },
                error: function (xhr, status, error) {
                    console.error("Get cart error:", xhr.responseText);
                }
            });
        });
    </script>

</body>

</html>