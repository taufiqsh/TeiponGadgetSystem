<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Function to check if the customer is logged in
function checkLogin()
{
    if (!isset($_SESSION['userID'])) {
        header("Location: ../login/login.php");
        exit();
    }
    return $_SESSION['userID'];
}

// Function to cancel an order
function cancelOrder($conn, $orderID, $customerID)
{
    // Check if the order belongs to the logged-in customer and is in 'To Pay' status
    $sql = "SELECT orderStatus FROM orders WHERE orderID = ? AND customerID = ? AND orderStatus = 'Pending Payment'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $orderID, $customerID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update the order status to 'Order Cancelled'
        $updateSql = "UPDATE orders SET orderStatus = 'Order Cancelled' WHERE orderID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $orderID);
        $updateStmt->execute();

        return $updateStmt->affected_rows > 0;
    }
    return false;
}

// Function to fetch orders by status
function getOrdersByStatus($conn, $customerID, $status)
{
    $sql = "SELECT orderID, orderDate, totalAmount, orderStatus FROM orders WHERE customerID = ? AND orderStatus = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $customerID, $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Function to fetch orders based on multiple statuses
function getAllOrderData($conn, $customerID)
{
    $orderData = [];
    $statuses = ['Pending Payment', 'Processing Payment', 'Order Shipped', 'Order Completed', 'Order Cancelled'];

    foreach ($statuses as $status) {
        $orderData[$status] = getOrdersByStatus($conn, $customerID, $status);
    }

    return $orderData;
}

// Function to update order status to 'Order Completed'
// Function to mark order as completed
function markOrderCompleted($conn, $orderID, $customerID)
{
    // Check if the order belongs to the logged-in customer and is in 'Order Shipped' status
    $sql = "SELECT orderStatus FROM orders WHERE orderID = ? AND customerID = ? AND orderStatus = 'Order Shipped'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $orderID, $customerID);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update the order status to 'Order Completed'
        $updateSql = "UPDATE orders SET orderStatus = 'Order Completed' WHERE orderID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $orderID);
        $updateStmt->execute();

        return $updateStmt->affected_rows > 0;
    }
    return false;
}

function getOrderProducts($conn, $orderID)
{
    $sql = "SELECT p.productImage, p.productName, op.quantity, op.price
            FROM orderproducts op
            JOIN product p ON op.productID = p.productID
            WHERE op.orderID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderID);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}



// Process the mark order completed POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['completeOrder']) && isset($_POST['orderID'])) {
    $customerID = checkLogin();
    $orderID = $_POST['orderID'];

    $success = markOrderCompleted($conn, $orderID, $customerID);
    echo json_encode(['success' => $success]);
    exit();
}

// Process the cancel order POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancelOrder']) && isset($_POST['orderID'])) {
    $customerID = checkLogin();
    $orderID = $_POST['orderID'];

    $success = cancelOrder($conn, $orderID, $customerID);
    echo json_encode(['success' => $success]);
    exit();
}
$customerID = checkLogin();
$orderData = getAllOrderData($conn, $customerID);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>

    <?php include('../navbar/customer_navbar.php'); ?>
    <div id="alertContainer" class="position-fixed top-0 start-0 w-100 z-index-10"></div>
    <div class="container my-5">
        <h1 class="text-center mb-4">My Purchases</h1>

        <!-- Tabs -->
        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
            <?php foreach ($orderData as $status => $orders): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $status === 'Pending Payment' ? 'active' : ''; ?>" id="<?= strtolower(str_replace(' ', '-', $status)) ?>-tab" data-bs-toggle="tab" data-bs-target="#<?= strtolower(str_replace(' ', '-', $status)) ?>" type="button" role="tab" aria-controls="<?= strtolower(str_replace(' ', '-', $status)) ?>" aria-selected="<?= $status === 'Pending Payment' ? 'true' : 'false'; ?>">
                        <?= $status ?>
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="orderTabContent">
            <?php foreach ($orderData as $status => $orders): ?>
                <div class="tab-pane fade <?= $status === 'Pending Payment' ? 'show active' : ''; ?>" id="<?= strtolower(str_replace(' ', '-', $status)) ?>" role="tabpanel" aria-labelledby="<?= strtolower(str_replace(' ', '-', $status)) ?>-tab">
                    <?php if (count($orders) > 0): ?>
                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Order Date</th>
                                    <th>Total Amount</th>
                                    <th>Order Status</th>
                                    <?php if ($status === 'Pending Payment' || $status === 'Order Shipped'): ?>
                                        <th>Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['orderID']); ?></td>
                                        <td><?= htmlspecialchars($row['orderDate']); ?></td>
                                        <td>RM <?= number_format($row['totalAmount'], 2); ?></td>
                                        <td><?= htmlspecialchars($row['orderStatus']); ?></td>
                                        <?php if ($status === 'Pending Payment' || $status === 'Order Shipped'): ?>
                                            <td>
                                                <!-- Actions for 'Pending Payment' and 'Order Shipped' -->
                                                <?php if ($status === 'Pending Payment'): ?>
                                                    <a href="payment.php?orderID=<?= $row['orderID']; ?>" class="btn btn-primary">Make Payment</a>
                                                    <button class="btn btn-danger" onclick="cancelOrder(<?= $row['orderID']; ?>)">Cancel Order</button>
                                                <?php elseif ($status === 'Order Shipped'): ?>
                                                    <button class="btn btn-success" data-order-id="<?= $row['orderID']; ?>">Mark as Completed</button>
                                                <?php endif; ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>

                                    <!-- Display the products below the Order ID -->
                                    <tr>
                                        <td colspan="<?= $status === 'Pending Payment' || $status === 'Order Shipped' ? 6 : 5; ?>">
                                            <?php
                                            $orderProducts = getOrderProducts($conn, $row['orderID']);
                                            if (count($orderProducts) > 0):
                                            ?>
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th>Product Image</th>
                                                                <th>Product Name</th>
                                                                <th>Quantity</th>
                                                                <th>Price</th>
                                                                <th>Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($orderProducts as $item): ?>
                                                                <tr>
                                                                    <td>
                                                                        <img src="../uploads/<?= htmlspecialchars($item['productImage'] ?? 'default.jpg'); ?>"
                                                                            alt="Product Image"
                                                                            class="img-fluid"
                                                                            style="max-width: 100px; height: auto;">
                                                                    </td>
                                                                    <td><?= htmlspecialchars($item['productName']); ?></td>
                                                                    <td><?= htmlspecialchars($item['quantity']); ?></td>
                                                                    <td>RM <?= number_format($item['price'], 2); ?></td>
                                                                    <td>RM <?= number_format($item['quantity'] * $item['price'], 2); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else: ?>
                                                <p>No products for this order.</p>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php else: ?>
                        <p class="text-center mt-3">No orders found in <?= $status; ?>.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
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

    <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to cancel this order?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-danger" id="confirmCancelBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Mark as Completed -->
    <div class="modal fade" id="completeOrderModal" tabindex="-1" aria-labelledby="completeOrderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="completeOrderModalLabel">Complete Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to mark this order as completed?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                    <button type="button" class="btn btn-success" id="confirmCompleteBtn">Yes</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="orderItemsModal" tabindex="-1" aria-labelledby="orderItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderItemsModalLabel">Order Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Items will be dynamically inserted here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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

        function cancelOrder(orderID) {
            // Open the modal
            $('#cancelOrderModal').modal('show');

            // Set the order ID to be cancelled
            $('#confirmCancelBtn').off('click').on('click', function() {
                // Close the modal
                $('#cancelOrderModal').modal('hide');

                // Proceed with cancelling the order
                $.ajax({
                    url: '', // The current page, so the cancellation will be handled here
                    method: 'POST',
                    data: {
                        cancelOrder: true, // This will trigger the cancellation logic
                        orderID: orderID
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Show success message
                            showAlert('Order has been cancelled.', 'success');
                            location.reload(); // Refresh the page to update the order status
                        } else {
                            // Show error message
                            showAlert('Failed to cancel the order. Please try again.', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", status, error);
                        // Show error message
                        showAlert('An error occurred while canceling the order.', 'danger');
                    }
                });
            });
        }

        // Function to display alerts
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');

            // Create the alert element
            const alertElement = document.createElement('div');
            alertElement.classList.add('alert', `alert-${type}`, 'alert-dismissible', 'fade', 'show');
            alertElement.setAttribute('role', 'alert');
            alertElement.innerHTML = `
        <strong>${type === 'success' ? 'Success!' : 'Error!'}</strong> ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;

            // Append the alert to the container at the top of the page
            alertContainer.prepend(alertElement);

            // Automatically remove the alert after 5 seconds
            setTimeout(() => {
                alertElement.classList.remove('show');
                alertElement.classList.add('fade');
            }, 5000);

            // Optionally, you can remove the alert after the fade-out transition ends
            setTimeout(() => {
                alertElement.remove();
            }, 6000); // This ensures the alert is fully removed after the fade effect
        }

        // Function to mark an order as completed
        function markOrderAsCompleted(orderID) {
            // Open the modal
            $('#completeOrderModal').modal('show');

            // Set up the confirmation for completion
            $('#confirmCompleteBtn').off('click').on('click', function() {
                // Close the modal
                $('#completeOrderModal').modal('hide');

                // Send AJAX request to mark the order as 'Order Completed'
                $.ajax({
                    url: '', // The current page, so it handles the request
                    method: 'POST',
                    data: {
                        completeOrder: true, // This will trigger the status update
                        orderID: orderID
                    },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            // Show success message
                            showAlert('Order has been marked as completed.', 'success');
                            location.reload(); // Refresh the page to update the order status
                        } else {
                            // Show error message
                            showAlert('Failed to mark the order as completed. Please try again.', 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error:", status, error);
                        // Show error message
                        showAlert('An error occurred while updating the order status.', 'danger');
                    }
                });
            });
        }

        // Attach the event to the button for Marking as Completed
        $('.btn-success').click(function() {
            var orderID = $(this).data('order-id'); // Get the order ID
            markOrderAsCompleted(orderID); // Call the function to mark as completed
        });


        // Add the event listener to the button in the HTML (in the loop where you're rendering orders)
        $('.btn-success').click(function() {
            var orderID = $(this).data('order-id'); // Get the order ID
            markOrderAsCompleted(orderID); // Call the function to mark as completed
        });

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

        // Show the ordered items in a modal when the button is clicked
        $('.btn-info').click(function() {
            var orderID = $(this).data('order-id'); // Get the order ID
            fetchOrderItems(orderID); // Call the function to fetch and display items
        });

        // Function to fetch and display order items in a modal
        function fetchOrderItems(orderID) {
            $.ajax({
                url: '', // The current page, so PHP will handle fetching the items
                method: 'POST',
                data: {
                    orderID: orderID
                },
                success: function(response) {
                    const items = JSON.parse(response); // Get the order items
                    let itemsHtml = '';

                    // Build the items list to display in the modal
                    items.forEach(function(item) {
                        itemsHtml += `
                    <div class="item">
                        <p><strong>${item.productName}</strong></p>
                        <p>Quantity: ${item.quantity}</p>
                        <p>Price: RM ${item.price.toFixed(2)}</p>
                    </div>
                `;
                    });

                    // Show the modal with the items
                    $('#orderItemsModal .modal-body').html(itemsHtml);
                    $('#orderItemsModal').modal('show');
                }
            });
        }
    </script>
</body>

</html>