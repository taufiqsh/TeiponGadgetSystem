<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');
//dsdasdasda
// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header('Location: login.php');
    exit();
}
$userID = $_SESSION['userID'];

// Fetch all products from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$minPrice = isset($_GET['minPrice']) && is_numeric($_GET['minPrice']) ? intval($_GET['minPrice']) : 0;
$maxPrice = isset($_GET['maxPrice']) && is_numeric($_GET['maxPrice']) ? intval($_GET['maxPrice']) : 10000;
$descriptionFilter = isset($_GET['descriptionFilter']) ? $_GET['descriptionFilter'] : '';

$sql = "SELECT productID, productName, productDescription, productPrice, productImage FROM Product WHERE productPrice BETWEEN ? AND ?";
$params = [$minPrice, $maxPrice];

if ($search) {
    $sql .= " AND productName LIKE ?";
    $likeSearch = "%" . $search . "%";
    $params[] = $likeSearch;
}

if ($descriptionFilter) {
    $sql .= " AND productDescription LIKE ?";
    $likeDescription = "%" . $descriptionFilter . "%";
    $params[] = $likeDescription;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Home</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .product-image {
            width: 300px;
            height: 300px;
            object-fit: contain;
        }   
        .product-item {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            text-align: center;
            margin-bottom: 2rem;
            padding: 1rem; /* Adds spacing inside the box */
            border: 1px solid #ddd; /* Adds a light gray border */
            border-radius: 8px; /* Rounds the corners slightly */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Adds a subtle shadow for a modern look */
            background-color: #fff; /* Ensures a consistent background color */
            transition: transform 0.2s, box-shadow 0.2s; /* Adds a hover effect */
        }
        .product-item:hover {
            transform: translateY(-5px); /* Slightly "lifts" the card */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
            .product-details {
            flex-grow: 1;
            margin-bottom: 1rem;
        }

        .product-price {
            margin-top: top;
            font-weight: bold;
        }

        .product-add-to-cart {
            margin-top: auto; /* Push the button to the bottom */
        }

        .button {
            width: 100%;
        }

    </style>
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Hero Section -->
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
    </section>

    <!-- Search and Filter Bar -->
    <div class="container my-4">
        <form method="GET" action="">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search for products..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" name="minPrice" class="form-control" placeholder="Min Price" value="<?php echo htmlspecialchars($minPrice); ?>">
                </div>
                <div class="col-md-2">
                    <input type="number" name="maxPrice" class="form-control" placeholder="Max Price" value="<?php echo htmlspecialchars($maxPrice); ?>">
                </div>
                <div class="col-md-3">
                    <input type="text" name="descriptionFilter" class="form-control" placeholder="Filter by description" value="<?php echo htmlspecialchars($descriptionFilter); ?>">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100" type="submit"><i class="bi bi-search"></i> Filter</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Product Display -->
    <div class="container my-5">
        <div class="row" id="productContainer">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productName = htmlspecialchars($row['productName']);
                    $productDescription = htmlspecialchars($row['productDescription']);
                    $productPrice = $row['productPrice'];
                    $productImage = htmlspecialchars($row['productImage']);

                    echo '
                    <div class="col-md-3 col-sm-4 ">
                        <div class="product-item">
                            <img src="../uploads/' . $productImage . '" alt="' . $productName . '" class="img-fluid product-image mb-3">
                            <div class="product-details">
                                <h5>' . $productName . '</h5>
                                <p>' . $productDescription . '</p>
                            </div>                                  
                            <p class="product-price">Price: RM ' . number_format($productPrice, 2) . '</p>
                            <div class="product-add-to-cart">
                                <button class="btn btn-outline-primary" onclick="addToCart(' . $row['productID'] . ', \'' . addslashes($productName) . '\', ' . $productPrice . ', \'' . addslashes($productImage) . '\')">
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-center">No products match your search and filter criteria.</p>';
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
                success: function(response) {
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
                error: function(xhr, status, error) {
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
                success: function(response) {
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
                error: function(xhr, status, error) {
                    console.error("Remove from cart error:", xhr.responseText);
                }
            });
        }

        // Initialize the cart modal when the page loads
        $(document).ready(function() {
            $.ajax({
                url: '../cart/get_cart.php',
                method: 'GET',
                success: function(response) {
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
                error: function(xhr, status, error) {
                    console.error("Get cart error:", xhr.responseText);
                }
            });
        });
    </script>

</body>

</html>
