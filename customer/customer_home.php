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
                    <div class="col-md-3 col-sm-4 product-item">
                        <div class="text-center">
                            <img src="../uploads/' . htmlspecialchars($productImage) . '" 
                                 alt="' . htmlspecialchars($productName) . '" 
                                 class="img-fluid product-image mb-3">
                            <h5>' . htmlspecialchars($productName) . '</h5>
                            <p>' . htmlspecialchars($productDescription) . '</p>
                            <p class="fw-bold">Price: RM ' . number_format($productPrice, 2) . '</p>
                            <button class="btn btn-primary" 
                                    onclick="showProductDetails(' . htmlspecialchars($row['productID']) . ')">
                                Buy
                            </button>
                        </div>
                    </div>
                    ';
                }
            } else {
                echo '<p class="text-center">No products available.</p>';
            }
            ?>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        function showProductDetails(productID) {
            $.ajax({
                url: '../product/get_product_details.php',
                method: 'GET',
                data: {
                    productID: productID
                },
                success: function(response) {
                    try {
                        const product = JSON.parse(response);

                        console.log("Product details:", product);
                        console.log("Product name", product.productName); // Debugging response

                        if (product) {
                            // Use fallback values for price and variants
                            const productPrice = Number(product.productPrice) || 0;
                            const variantsHTML = product.variants ?
                                product.variants.map(variant => {
                                    const variantPrice = Number(variant.productPrice) || 0;
                                    return `<option value="${variant.variantID}">${variant.variantName} (+RM ${variantPrice.toFixed(2)})</option>`;
                                }).join('') :
                                '<option disabled>No variants available</option>';

                            console.log("Product variants", variantsHTML);

                            const modalHTML = `
                        <div class="modal fade" id="productDetailsModal_${product.productID}" tabindex="-1" aria-labelledby="productName_${product.productID}">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="productName_${product.productID}">
                                            ${product.productName || "Unknown Product"}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="../uploads/${product.productImage || "default.jpg"}" 
                                            id="productImage_${product.productID}" class="img-fluid mb-3" 
                                            alt="${product.productName || "Product"}">
                                        <p id="productDescription_${product.productID}">${product.productDescription || "No description available"}</p>
                                        <p class="fw-bold" id="productPrice_${product.productID}">
                                            Price: RM ${productPrice.toFixed(2)}
                                        </p>

                                        <!-- Variant Selection -->
                                        <div class="mb-3">
                                            <label for="variantSelect_${product.productID}" class="form-label">Choose a Variant:</label>
                                            <select id="variantSelect_${product.productID}" class="form-select">
                                                ${variantsHTML}
                                            </select>
                                        </div>

                                        <!-- Quantity Selection -->
                                        <input type="number" id="quantity_${product.productID}" class="form-control mb-2" value="1" min="1">
                                    </div>
                                    <div class="modal-footer">
                                        <button class="btn btn-primary" id="addToCartButton_${product.productID}" onclick="addToCart(${product.productID}, 
                                                document.getElementById('variantSelect_${product.productID}').value, 
                                                document.getElementById('quantity_${product.productID}').value)">
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                            // Insert the modal HTML directly into the body or a predefined container
                            document.body.insertAdjacentHTML('beforeend', modalHTML);

                            // Manage inert and focus
                            const modalElement = document.getElementById(`productDetailsModal_${product.productID}`);
                            const modal = new bootstrap.Modal(modalElement, {
                                backdrop: 'static', // Ensures the backdrop remains and blocks interactions
                                keyboard: false // Optionally disable closing modal with keyboard (Esc)
                            });

                            // When the modal is shown, prevent interaction with the background content and set focus on the first button inside the modal
                            modalElement.addEventListener('shown.bs.modal', () => {
                                // Remove aria-hidden from the modal and prevent interaction with the background content
                                modalElement.removeAttribute('aria-hidden');

                                // Apply inert only to the background content, not the modal itself
                                const backgroundContent = document.querySelector('main, .container, .content'); // Adjust based on your structure
                                if (backgroundContent) backgroundContent.setAttribute('inert', 'true');

                                // Focus on the first focusable element inside the modal (usually a button)
                                const firstFocusableElement = modalElement.querySelector('button, input, select, textarea');
                                if (firstFocusableElement) firstFocusableElement.focus();
                            });

                            // When the modal is hidden, remove inert and ensure everything is focusable again
                            modalElement.addEventListener('hidden.bs.modal', () => {
                                modalElement.setAttribute('aria-hidden', 'true');
                                const backgroundContent = document.querySelector('main, .container, .content');
                                if (backgroundContent) backgroundContent.removeAttribute('inert');
                            });

                            // Show the modal
                            modal.show();
                        }
                    } catch (error) {
                        console.error("Error parsing product details:", error);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching product details:", error);
                }
            });
        }

        // Add product to cart
        function addToCart(productID, variantID, quantity) {
            const customerID = <?php echo $_SESSION['userID']; ?>; // Fetch customerID from session (PHP side)
            const createdAt = new Date().toISOString();
            const updatedAt = createdAt;

            // Get product details dynamically
            const productNameElement = document.getElementById(`productName_${productID}`);
            const productPriceElement = document.getElementById(`productPrice_${productID}`);
            const productImageElement = document.getElementById(`productImage_${productID}`);
            let productImage = '';

            if (productImageElement) {
                const imageUrl = productImageElement.src;
                const url = new URL(imageUrl);
                productImage = url.pathname.replace(/^.*?\/TeiponGadgetSystem\//, '/'); // Adjust path if necessary
            }

            // Ensure elements exist before using them
            if (!productNameElement || !productPriceElement || !productImageElement) {
                console.error("Error: Missing product details in the DOM.");
                return; // Exit the function if any element is missing
            }

            const productName = productNameElement.innerText;
            const productPrice = Number(productPriceElement.innerText.replace('Price: RM ', '').trim());

            console.log("Adding to cart:", {
                productID,
                productName,
                productPrice,
                productImage,
                variantID: parseInt(variantID, 10),
                quantity: parseInt(quantity, 10),
                createdAt,
                updatedAt
            });

            $.ajax({
                url: '../cart/add_to_cart.php',
                method: 'POST',
                data: {
                    productID: productID,
                    productName: productName,
                    productPrice: productPrice,
                    productImage: productImage,
                    variantID: parseInt(variantID, 10),
                    quantity: parseInt(quantity, 10),
                    createdAt: createdAt,
                    updatedAt: updatedAt
                },
                success: function(response) {
                    try {
                        const responseData = JSON.parse(response);
                        console.log("Product ID", responseData.productID);
                        console.log("Cart : " + responseData.cart);
                        console.log("Parsed responseData:", responseData);
                        console.log("Cart count:", responseData.cartCount);

                        let successMessagesElement = document.getElementById('successMessages');
                        if (!successMessagesElement) {
                            successMessagesElement = document.createElement('div');
                            successMessagesElement.id = 'successMessages';
                            document.body.appendChild(successMessagesElement);
                            console.log("successMessages div created and appended");
                        }

                        // Show success message if the server response is correct
                        if (responseData.cartCount !== undefined) {
                            const successMessage = document.createElement('div');
                            successMessage.className = 'alert alert-success alert-dismissible fade show';
                            successMessage.role = 'alert';
                            successMessage.innerHTML = `Product added to cart successfully! <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
                            successMessagesElement.appendChild(successMessage);

                            setTimeout(() => {
                                successMessage.classList.remove('show');
                                successMessage.classList.add('fade');
                                setTimeout(() => {
                                    successMessage.remove();
                                }, 500);
                            }, 3000);

                            // Update cart count
                            const cartCountElement = document.getElementById('cartCount');
                            if (responseData.cartCount !== undefined) {
                                cartCountElement.innerText = responseData.cartCount;
                            }

                            // Update cart modal (refresh cart after adding item)
                            if (responseData.cart) {
                                console.log("Cart updated with new data:", responseData.cart);
                                updateCartModal(responseData.cart);
                            }

                            // Close the modal after the item has been added
                            const productModal = document.getElementById(`productDetailsModal_${productID}`);
                            const modal = bootstrap.Modal.getInstance(productModal);
                            modal.hide();
                        } else {
                            console.error("Error: Cart count not returned in the response.");
                        }
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
            console.log("Updating modal with cart:", cart); // Debug log to see the cart data
            let cartItemsHTML = '';
            let total = 0;

            // Check if cart is an array and has items
            if (Array.isArray(cart) && cart.length > 0) {
                cart.forEach(item => {
                    if (item.productName && item.quantity && item.productPrice && item.productImage) {
                        const itemPrice = Number(item.productPrice) || 0;
                        const itemQuantity = item.quantity || 1;
                        const itemTotalPrice = itemPrice * itemQuantity;

                        // Build the cart item HTML
                        cartItemsHTML += `
                    <div class="cart-item card mb-3 shadow-sm" data-product-id="${item.productID}" data-variant-id="${item.variantID}">
                        <div class="card-body d-flex justify-content-between align-items-center gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="../uploads/${item.productImage}" alt="${item.productName}" class="rounded img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                <div>
                                    <h6 class="mb-1">${item.productName} 
                                        <small class="text-muted">(x${itemQuantity})</small>
                                        ${item.variantName ? `<br><small class="text-muted">Variant: ${item.variantName}</small>` : ''}
                                    </h6>
                                    <p class="mb-0 text-primary fw-bold">RM ${(itemTotalPrice).toFixed(2)}</p>
                                </div>
                            </div>
                            <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.productID}, ${item.variantID})">
                                <i class="bi bi-trash3"></i> Remove
                            </button>
                        </div>
                    </div>
                `;


                        total += itemTotalPrice;
                    }
                });
            } else {
                cartItemsHTML = '<p class="text-center">Your cart is empty.</p>';
            }

            // Update the cart items and total price displayed in the modal
            $('#cartItems').html(cartItemsHTML);
            $('#cartTotal').text(total.toFixed(2));

            // Open modal when the cart icon is clicked
            $('#cartIcon').click(function() {
                const modal = new bootstrap.Modal(document.getElementById('cartModal'));
                modal.show();
            });
        }
        
        // Remove product from cart
        function removeFromCart(productID, variantID) {
            console.log("Removing from cart with productID:", productID, "and variantID:", variantID); // Debug log to check values

            $.ajax({
                url: '../cart/remove_from_cart.php',
                method: 'POST',
                data: {
                    productID: productID, // Make sure productID is included
                    variantID: variantID // Include variantID as well
                },
                success: function(response) {
                    try {
                        const responseData = JSON.parse(response);
                        console.log("Response from server:", response); // Debug log for server response

                        if (responseData.error) {
                            console.error("Error:", responseData.error);
                            return;
                        }

                        // Update the modal with the new cart data
                        updateCartModal(responseData.cart);

                        // Update the cart count in the UI
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
                url: '../cart/get_cart.php', // Endpoint to fetch the cart data
                method: 'GET',
                success: function(response) {
                    try {
                        const responseData = JSON.parse(response); // Parse the JSON response
                        if (responseData && responseData.cart) {
                            const cart = responseData.cart || []; // Get the cart array
                            updateCartModal(cart); // Update the modal with the cart data

                            // Update the cart count
                            const cartCount = cart.reduce((total, item) => total + item.quantity, 0);
                            document.getElementById('cartCount').innerText = cartCount; // Update the cart count on the page
                        }
                    } catch (error) {
                        console.error("Error parsing the cart data:", error); // Handle errors if parsing fails
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Get cart error:", xhr.responseText); // Handle errors from the server response
                }
            });
        });

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