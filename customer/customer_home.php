<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
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
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
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
            }); // Debug log

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
                },
                error: function(xhr, status, error) {
                    console.error("Add to cart error:", status, error); // Debug error
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
                        <p class="mb-0 text-primary fw-bold">RM ${(Number(item.price)).toLocaleString(2)}</p>
                    </div>
                </div>
                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                    <i class="bi bi-trash3"></i> Remove
                </button>
            </div>
        </div>`;
            });

            $('#cartItems').html(cartItemsHTML);
            $('#cartTotal').text(total.toLocaleString());
        }

        // Remove product from cart
        function removeFromCart(productId) {
            $.ajax({
                url: '../cart/remove_from_cart.php', // Backend endpoint for item removal
                method: 'POST',
                data: {
                    productID: productId // Pass the specific product ID to be removed
                },
                success: function(response) {
                    const responseData = JSON.parse(response);
                    updateCartModal(responseData.cart); // Update the modal with the new cart data
                    const cartCountElement = document.getElementById('cartCount');
                    cartCountElement.innerText = responseData.cartCount; // Update the cart count
                },
                error: function(xhr, status, error) {
                    console.error("Remove from cart error:", status, error); // Debug error
                }
            });
        }

        // Initialize the cart modal when the page loads
        $(document).ready(function() {
            $.ajax({
                url: '../cart/get_cart.php', // Retrieve cart data on page load
                method: 'GET',
                success: function(response) {
                    try {
                        const responseData = JSON.parse(response); // Safely parse the response into a JavaScript object

                        // Check if responseData contains the expected 'cart' property
                        if (responseData && responseData.cart) {
                            const cart = responseData.cart || [];
                            updateCartModal(cart); // Populate the modal with current cart items
                            // Optionally, update the cart count displayed on the page
                            const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
                            document.getElementById('cartCount').innerText = cartCount; // Update cart count
                        } else {
                            console.error("Cart data is missing in the response.");
                        }
                    } catch (error) {
                        console.error("Error parsing the cart data:", error); // Debug parsing error
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Get cart error:", status, error); // Debug error on request failure
                }
            });
        });
    </script>

</body>

</html>