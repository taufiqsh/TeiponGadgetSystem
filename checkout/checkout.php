<?php
session_start();
if (!isset($_SESSION['customerUsername'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Checkout</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 10px;
        }
    </style>
</head>

<body>

    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Hero Section -->
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Checkout</h1>
            <p class="lead">Review your order and proceed with payment</p>
        </div>
    </section>

    <!-- Checkout Content -->
    <div class="container my-5">
        <h3>Cart Summary</h3>
        <div id="cartItems">
            <!-- Cart items will be dynamically loaded here -->
        </div>

        <h5 class="text-end mt-3">Total: RM <span id="cartTotal">0.00</span></h5>

        <!-- Shipping Information Form -->
        <form id="checkoutForm" action="process_checkout.php" method="POST">
            <h4 class="mt-4">Shipping Information</h4>
            <div class="mb-3">
                <label for="address" class="form-label">Shipping Address</label>
                <textarea class="form-control" id="address" name="address" rows="4" required></textarea>
            </div>
            <div class="mb-3">
                <label for="contact" class="form-label">Contact Number</label>
                <input type="text" class="form-control" id="contact" name="contact" required>
            </div>

            <!-- Hidden Inputs for Order Details -->
            <input type="hidden" name="customerID" id="customerID" value="<?php echo $_SESSION['customerID']; ?>">
            <input type="hidden" name="totalPrice" id="totalPrice">
            <input type="hidden" name="cartData" id="cartData">

            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-4">
        <p class="mb-0">© Teipon Gadget. All Rights Reserved.</p>
    </footer>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize an empty cart (normally this would be loaded from localStorage or the session)
        let cart = [
            {
                name: 'Product 1',
                price: 10.00,
                quantity: 2,
                image: 'product1.jpg'
            },
            {
                name: 'Product 2',
                price: 20.00,
                quantity: 1,
                image: 'product2.jpg'
            }
        ];

        function refreshCart() {
            const cartContainer = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const cartDataField = document.getElementById('cartData');
            cartContainer.innerHTML = ''; // Clear existing items
            let total = 0;

            // Loop through the cart and render each item
            cart.forEach(item => {
                total += item.price * item.quantity;
                cartContainer.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <img src="../uploads/${item.image}" alt="${item.name}" class="product-image">
                            <strong>${item.name}</strong>
                            <p>RM ${item.price.toFixed(2)} x ${item.quantity}</p>
                        </div>
                        <p class="fw-bold">RM ${(item.price * item.quantity).toFixed(2)}</p>
                    </div>
                `;
            });

            cartTotal.innerText = total.toFixed(2);
            // Store the cart data and total price as hidden form fields
            cartDataField.value = JSON.stringify(cart); // Send cart data to server as JSON
            document.getElementById('totalPrice').value = total.toFixed(2); // Set the total price
        }

        // Call refreshCart() to render the cart data
        window.onload = refreshCart;
    </script>

</body>

</html>
