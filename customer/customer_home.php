<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['userID'];

// Fetch all products from the database
$sql = "SELECT productID, productName, productDescription, productPrice, productImage FROM Product";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Home</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Hero Section -->
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
    </section>

    <!-- Product Display -->
    <div class="container my-5">
        <div class="row" id="productContainer">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productName = htmlspecialchars($row['productName']);
                    $productDescription = htmlspecialchars($row['productDescription']);
                    $productPrice = $row['productPrice'];
                    $productImage = htmlspecialchars($row['productImage']);

                    echo '
                    <div class="col-md-3 col-sm-4 product-item">
                        <div class="text-center">
                            <img src="../uploads/' . $productImage . '" alt="' . $productName . '" class="img-fluid product-image mb-3">
                            <h5>' . $productName . '</h5>
                            <p>' . $productDescription . '</p>
                            <p class="fw-bold">Price: RM ' . number_format($productPrice, 2) . '</p>
                            <button class="btn btn-outline-primary" onclick="addToCart(' . $row['productID'] . ', \'' . addslashes($productName) . '\', ' . $productPrice . ', \'' . addslashes($productImage) . '\')">
                                Add to Cart
                            </button>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-center">No products available at the moment.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cartItems"></div>
                    <h5 class="text-end mt-3">Total: RM <span id="cartTotal">0.00</span></h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="../checkout/checkout.php" class="btn btn-primary">Checkout</a>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Add product to cart
        function addToCart(productID, productName, productPrice, productImage) {
            $.ajax({
                url: '../cart/add_to_cart.php',
                method: 'POST',
                data: {
                    productID: productID,
                    productName: productName,
                    productPrice: productPrice,
                    productImage: productImage
                },
                success: function(response) {
                    const responseData = JSON.parse(response);
                    const cartCountElement = document.getElementById('cartCount');
                    cartCountElement.innerText = responseData.cartCount; // Update cart count
                    updateCartModal(responseData.cart); // Update the modal with new cart data
                }
            });
        }

        // Update the cart modal with current cart data
        function updateCartModal(cart) {
            var cartItemsHTML = '';
            var total = 0;

            // Loop through each cart item and display it
            cart.forEach(function(item) {
                total += item.price * item.quantity; // Calculate total price
                cartItemsHTML += `
                <div class="cart-item d-flex justify-content-between mb-3">
                    <div class="d-flex">
                        <img src="../uploads/${item.image}" alt="${item.name}" width="50" class="me-3">
                        <div>
                            <span>${item.name} (x${item.quantity})</span><br>
                            <small>RM ${item.price.toFixed(2)}</small>
                        </div>
                    </div>
                    <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">Remove</button>
                </div>`;
            });

            // Update cart content and total
            $('#cartItems').html(cartItemsHTML);
            $('#cartTotal').text(total.toFixed(2)); // Update total price
        }

        // Remove product from cart
        function removeFromCart(productId) {
            $.ajax({
                url: '../cart/remove_from_cart.php',
                method: 'POST',
                data: {
                    id: productId
                },
                success: function(response) {
                    const responseData = JSON.parse(response);
                    updateCartModal(responseData.cart); // Update the modal with new cart data
                    const cartCountElement = document.getElementById('cartCount');
                    cartCountElement.innerText = responseData.cartCount; // Update cart count
                }
            });
        }

        // Initialize the cart modal when the page loads
        $(document).ready(function() {
            $.ajax({
                url: '../cart/get_cart.php', // You can use this endpoint to retrieve cart data on page load if needed
                method: 'GET',
                success: function(response) {
                    const cart = response.cart || [];
                    updateCartModal(cart); // Populate the modal with current cart items
                }
            });
        });
    </script>
</body>

</html>