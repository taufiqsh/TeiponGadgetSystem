<?php
session_start(); // Start session

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch all products from the database
$sql = "SELECT productID, productName, productDescription, productPrice, productStock, productImage FROM product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Styling for product image */
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .product-card-body {
            padding: 20px;
        }

        .product-price {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <?php
    if (isset($_SESSION['userID'])) {
        include('../navbar/customer_navbar.php');
    } else {
        include('../navbar/navbar.php');
    } ?>

    <!-- Main Content -->
    <div class="container my-5">
        <h1 class="mb-4 text-center">Our Products</h1>

        <div class="row g-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productID = $row['productID'];
                    $productImage = htmlspecialchars($row['productImage'] ?? '');
                    $productName = htmlspecialchars($row['productName'] ?? 'Unnamed Product');
                    $productDescription = htmlspecialchars(trim($row['productDescription']) ?: 'No description available.');
                    $productPrice = number_format($row['productPrice'] ?? 0, 2);
                    echo '
    <div class="col-md-4">
        <div class="card h-100 shadow-sm product-card">';

                    if ($productImage && file_exists('../uploads/' . $productImage)) {
                        $imagePath = '../uploads/' . $productImage;
                        echo '<img src="' . $imagePath . '" class="product-image" alt="Product Image">';
                    } else {
                        echo '<svg class="bd-placeholder-img" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
                        <title>No Image</title>
                        <rect width="100%" height="100%" fill="#e9ecef"></rect>
                        <text x="50%" y="50%" fill="#6c757d" dy=".3em" text-anchor="middle">No Image</text>
                    </svg>';
                    }

                    echo '
            <div class="product-card-body">
                <h5 class="card-title">' . $productName . '</h5>
                <p class="card-text">' . $productDescription . '</p>
                <p class="product-price">Price: RM' . $productPrice . '</p>
            </div>
            <div class="card-footer text-center">
                <button class="btn btn-primary" onclick="addToCart(' . $productID . ', \'' . addslashes($productName) . '\', ' . $row['productPrice'] . ', \'' . addslashes($productImage) . '\')">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>';
                }
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
        // Function to add product to cart
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
                <div class="cart-item card mb-3 shadow-sm">
                    <div class="card-body d-flex justify-content-between align-items-center gap-3">
                        <!-- Image and Details -->
                        <div class="d-flex align-items-center gap-3">
                            <img src="../uploads/${item.image}" alt="${item.name}" class="rounded img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                            <div>
                                <h6 class="mb-1">${item.name} <small class="text-muted">(x${item.quantity})</small></h6>
                                <p class="mb-0 text-primary fw-bold">RM ${(Number(item.price)).toLocaleString(                                // Update the cart modal with current cart data
                                function updateCartModal(cart) {
                                    var cartItemsHTML = '';
                                    var total = 0;
                                
                                    // Loop through each cart item and display it
                                    cart.forEach(function(item) {
                                        total += item.price * item.quantity; // Calculate total price
                                        cartItemsHTML += `
                                        <div class="cart-item card mb-3 shadow-sm">
                                            <div class="card-body d-flex justify-content-between align-items-center gap-3">
                                                <!-- Image and Details -->
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="../uploads/${item.image}" alt="${item.name}" class="rounded img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-1">${item.name} <small class="text-muted">(x${item.quantity})</small></h6>
                                                        <p class="mb-0 text-primary fw-bold">RM ${(Number(item.price)).toLocaleString()}</p>
                                                    </div>
                                                </div>
                                                <!-- Remove Button -->
                                                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                                                    <i class="bi bi-trash3"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                        `;
                                    });
                                
                                    // Update cart content and total
                                    $('#cartItems').html(cartItemsHTML);
                                    $('#cartTotal').text(total.toLocaleString()); // Update total price
                                })}</p>
                            </div>
                        </div>
                        <!-- Remove Button -->
                        <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                            <i class="bi bi-trash3"></i> Remove
                        </button>
                    </div>
                </div>
                `;
            });

            // Update cart content and total
            $('#cartItems').html(cartItemsHTML);
            $('#cartTotal').text(total.toLocaleString()); // Update total price
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
                url: '../cart/get_cart.php', // Retrieve cart data on page load
                method: 'GET',
                success: function(response) {
                    const responseData = JSON.parse(response);
                    const cart = responseData.cart || [];
                    updateCartModal(cart); // Populate the modal with current cart items
                },
                error: function(xhr, status, error) {
                    console.error("Get cart error:", status, error); // Debug error
                }
            });
        });
    </script>
</body>

</html>