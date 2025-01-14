<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');
//dsdasdasda
// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login/login.php?error=Access denied");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Home</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="customer_home.css" rel="stylesheet">
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Hero Section -->
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
    </section>

    <div class="container filter-bar mt-4">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search for products...">
            </div>
            <div class="col-md-2">
                <input type="number" id="minPriceInput" class="form-control" placeholder="Min Price">
            </div>
            <div class="col-md-2">
                <input type="number" id="maxPriceInput" class="form-control" placeholder="Max Price">
            </div>
            <div class="col-md-3">
                <input type="text" id="descriptionFilterInput" class="form-control" placeholder="Filter by description">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100" id="filterButton">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Product Display -->
    <div class="container my-5">
        <div class="row" id="productList">
            <?php
            // Fetch all products from the database (no filters applied)
            $sql = "SELECT productID, productName, productDescription, productPrice, productImage FROM Product";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $productName = htmlspecialchars($row['productName']);
                    $productDescription = htmlspecialchars($row['productDescription']);
                    $productPrice = $row['productPrice'];
                    $productImage = htmlspecialchars($row['productImage']);

                    echo '
                    <div class="col-md-3 col-sm-4 product-item" 
                        data-name="' . strtolower($productName) . '" 
                        data-price="' . $productPrice . '" 
                        data-description="' . strtolower($productDescription) . '">
                        <div class="product-card">
                            <img src="../uploads/' . $productImage . '" alt="' . $productName . '" class="img-fluid product-image mb-3">
                            <h5 class="product-name">' . $productName . '</h5>
                            <p class="product-description">' . $productDescription . '</p>
                            <p class="product-price">RM ' . number_format($productPrice, 2) . '</p>
                            <button class="btn btn-outline-primary" onclick="addToCart(' . $row['productID'] . ', \'' . addslashes($productName) . '\', ' . $productPrice . ', \'' . addslashes($productImage) . '\')">
                                Add to Cart
                            </button>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-center">No products available.</p>';
            }
            ?>
        </div>
    </div>

    <script>
        // Filter products using JavaScript
        document.getElementById('filterButton').addEventListener('click', function() {
            const searchValue = document.getElementById('searchInput').value.toLowerCase();
            const minPrice = parseFloat(document.getElementById('minPriceInput').value) || 0;
            const maxPrice = parseFloat(document.getElementById('maxPriceInput').value) || Infinity;
            const descriptionValue = document.getElementById('descriptionFilterInput').value.toLowerCase();

            const productItems = document.querySelectorAll('.product-item');

            productItems.forEach(item => {
                const name = item.getAttribute('data-name');
                const price = parseFloat(item.getAttribute('data-price'));
                const description = item.getAttribute('data-description');

                const matchesSearch = name.includes(searchValue);
                const matchesPrice = price >= minPrice && price <= maxPrice;
                const matchesDescription = description.includes(descriptionValue);

                if (matchesSearch && matchesPrice && matchesDescription) {
                    item.style.display = ''; // Show the product item
                } else {
                    item.style.display = 'none'; // Hide the product item
                }
            });
        });
    </script>

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

    <!-- chatbox -->
    <section id="chatbox-section" style="display: none;">
        <link rel="stylesheet" href="../chatbox/chatbot.css">
        <div id="chatbox-container">
            <div id="chatbox">
                <div id="chat-header">
                    <h3>
                        <img src="../chatbox/img/teiponBot-icon.png" alt="Logo"> KOJEK
                    </h3>
                    <button id="close-btn" onclick="minimizeChat()">×</button>
                </div>
                <div id="messages"></div>
                <div id="input-area">
                    <input type="text" id="userInput" class="form-control" placeholder="Type your message here...">
                    <button id="send-btn" onclick="sendMessage()">
                        <i class="bi bi-rocket-takeoff"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>
    <button id="open-chatbox" onclick="toggleChatbox()"> </button>
    <!-- end of chatbox -->

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
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
    
    <!-- chatbox script-->
    <script>
        function toggleChatbox() {
            const chatboxSection = document.getElementById('chatbox-section');
            if (chatboxSection.style.display === 'none') {
                chatboxSection.style.display = 'block';
                sendWelcomeMessage();
            } else {
                chatboxSection.style.display = 'none';
            }
        }

        function minimizeChat(){
            const chatboxSection = document.getElementById('chatbox-section');
            chatboxSection.style.display = 'none';
        }
        
        function sendWelcomeMessage() {
            fetch('../chatbox/chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        message: "hello"
                    }), // Custom message for welcome intent
                })
                .then(response => response.json())
                .then(data => {
                    const messages = document.getElementById('messages');
                    messages.innerHTML += `
                        <div class="message bot">
                            <div class="message-content"><strong>Kojek:</strong> ${data.reply}</div>
                        </div>
                    `;
                    messages.scrollTop = messages.scrollHeight;
                })
                .catch(() => {
                    const messages = document.getElementById('messages');
                    messages.innerHTML += `
                        <div class="message bot">
                            <div class="message-content"><strong>Kojek:</strong> Sorry, there was an error initializing the chat.</div>
                        </div>
                    `;
                });
        }

        function sendMessage() {
            const userInput = document.getElementById('userInput').value.trim();
            if (!userInput) return;

            document.getElementById('userInput').value = '';

            const messages = document.getElementById('messages');
            messages.innerHTML += `
                <div class="message user">
                    <div class="message-content"><strong>You:</strong> ${userInput}</div>
                </div>
            `;

            // Add loading bubble
            const loadingBubble = document.createElement('div');
            loadingBubble.className = 'message bot';
            loadingBubble.innerHTML = `
                <div class="loading-bubble">. . .</div>
            `;
            messages.appendChild(loadingBubble);
            messages.scrollTop = messages.scrollHeight;

            setTimeout(() => {
                // Replace loading bubble with bot response
                loadingBubble.remove();
                fetch('../chatbox/chatbot.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            message: userInput
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        messages.innerHTML += `
                            <div class="message bot">
                                <div class="message-content"><strong>Kojek:</strong> ${data.reply}</div>
                            </div>
                        `;
                        messages.scrollTop = messages.scrollHeight;
                    })
                    .catch(() => {
                        messages.innerHTML += `
                            <div class="message bot">
                                <div class="message-content"><strong>Kojek:</strong> Sorry, there was an error processing your message.</div>
                            </div>
                        `;
                    });
            }, 1500); // delay for loading
        }

        document.getElementById('userInput').addEventListener('keydown', function(event) {if (event.key === 'Enter') {sendMessage();}});
    </script>
    <!-- end of chatbox script-->

</body>

</html>