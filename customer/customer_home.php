<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');



// Check if the user is logged in
if (!isset($_SESSION['customerUsername'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit();
}

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
    <style>
        .product-image {
            width: 100%;
            /* Ensures the image fills the parent container */
            height: 200px;
            /* Set a consistent height */
            object-fit: cover;
            /* Ensures the image maintains its aspect ratio and fills the size */
            border-radius: 5px;
            /* Optional: Adds a slight curve to the corners */
            border: 1px solid #ddd;
            /* Optional: Adds a border for a clean look */
        }
    </style>
</head>

<body>

    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Hero Section -->
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION['customerUsername']); ?>!</h1>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row" id="productContainer">
            <?php
            if ($result->num_rows > 0) {
                $products = [];
                while ($row = $result->fetch_assoc()) {
                    $productName = !empty($row['productName']) ? htmlspecialchars($row['productName']) : 'Unnamed Product';
                    $productDescription = !empty($row['productDescription']) ? htmlspecialchars($row['productDescription']) : 'No description available.';
                    $productPrice = !empty($row['productPrice']) ? $row['productPrice'] : 0.00; // Keep raw value if not empty
                    $productImage = !empty($row['productImage']) ? htmlspecialchars($row['productImage']) : 'placeholder.jpg';

                    $products[] = [
                        'id' => $row['productID'], // Add productID to the array
                        'name' => $productName,
                        'description' => $productDescription,
                        'price' => $productPrice,
                        'image' => $productImage
                    ];
                }

                // Display products in columns
                foreach ($products as $product) {
                    echo '
        <div class="col-md-3 col-sm-4 product-item">
            <div class="text-center">
                <img src="../uploads/' . $product['image'] . '" 
                     alt="' . $product['name'] . '" 
                     class="img-fluid product-image mb-3">
                <h5>' . $product['name'] . '</h5>
                <p>' . $product['description'] . '</p>
                <p class="fw-bold">Price: RM ' . $product['price'] . '</p>
                <button class="btn btn-outline-primary" 
                        onclick="addToCart(' . $product['id'] . ', \'' . addslashes($product['name']) . '\', ' . $product['price'] . ', \'' . addslashes($product['image']) . '\')">
                    Buy
                </button>
            </div>
        </div>';
                }
            } else {
                echo '<p class="text-center">No products available at the moment.</p>';
            }
            ?>

        </div>

        <!-- Navigation buttons -->
        <div class="d-flex justify-content-center gap-4">
            <button id="prevBtn" class="btn btn-secondary" onclick="navigate(-1)">Previous</button>
            <button id="nextBtn" class="btn btn-secondary" onclick="navigate(1)">Next</button>
        </div>
    </div>

    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="cartItems">
                        <!-- Cart items will be dynamically loaded here -->
                    </div>
                    <h5 class="text-end mt-3">Total: RM <span id="cartTotal">0.00</span></h5>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="../checkout/checkout.php" class="btn btn-primary">Checkout</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light text-center py-4">
        <p class="mb-0">© Teipon Gadget. All Rights Reserved.</p>
    </footer>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function addToCart(productID, productName, productPrice, productImage) {
            $.ajax({
                url: '../cart/add_to_cart.php', // Path to your PHP script
                method: 'POST',
                data: {
                    productID: productID,
                    productName: productName,
                    productPrice: productPrice,
                    productImage: productImage
                },
                success: function(response) {
                    // Parse the response which contains updated cart count and cart items
                    const responseData = JSON.parse(response);

                    // Update the cart count in the navbar
                    const cartCountElement = document.getElementById('cartCount');
                    cartCountElement.innerText = responseData.cartCount;

                    // Optionally, update the cart modal if needed
                    updateCartModal(responseData.cart);
                },
                error: function(xhr, status, error) {
                    console.error("Error adding to cart:", error);
                }
            });
        }

        function removeFromCart(productId) {
            $.ajax({
                url: '../cart/remove_from_cart.php',
                method: 'POST',
                data: {
                    id: productId
                },
                success: function(response) {
                    var data = JSON.parse(response);
                    updateCart(data.cart); // Update the cart modal
                    updateCartCount(data.cart); // Update the cart count in the navbar
                }
            });
        }

        function updateCart(cart) {
            // Update the cart modal content
            var cartItemsHTML = '';
            var total = 0;
            cart.forEach(function(item) {
                total += item.price * item.quantity;
                cartItemsHTML += '<p>' + item.name + ' (x' + item.quantity + ') - RM ' + item.price.toFixed(2) + '</p>';
            });
            $('#cartItems').html(cartItemsHTML);
            $('#cartTotal').text(total.toFixed(2));
        }

        function updateCartCount(cart) {
            // Update the cart item count in the navbar
            var totalItems = cart.reduce(function(count, item) {
                return count + item.quantity;
            }, 0);
            $('#cartCount').text(totalItems);
        }

        // Initial call to update cart count on page load
        $(document).ready(function() {
            // Get the initial cart state (if any)
            $.ajax({
                url: '../cart/get_cart.php',
                method: 'GET',
                success: function(response) {
                    var cart = response.cart || [];
                    updateCart(cart);
                    updateCartCount(cart);
                }
            });
        });
    </script>
</body>

</html>