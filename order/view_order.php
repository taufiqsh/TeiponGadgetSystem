<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    // Redirect to the appropriate login page
    header("Location: ../login/login.php?error=Access denied");
    exit();
}

// Determine user type and session details
if (isset($_SESSION['adminID'])) {
    $userType = 'Admin';
    $userName = $_SESSION['username'];
} else {
    $userType = 'Staff';
    $userName = $_SESSION['username'];
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the orderID is set in the URL
if (!isset($_GET['orderID']) || empty($_GET['orderID'])) {
    die("Order ID is required");
}

$orderID = $_GET['orderID']; // Get the order ID from the URL

// Fetch order details from the database
$sql = "SELECT orders.orderID, orders.orderDate, orders.totalAmount, orders.orderStatus, customer.customerName 
        FROM orders 
        INNER JOIN customer ON orders.customerID = customer.customerID 
        WHERE orders.orderID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orderID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Order not found.");
}

$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Order</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .main-content {
            margin-left: 250px;
            /* Adjust based on your sidebar width */
            padding: 20px;
        }

        .order-details,
        .ordered-products {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .order-details h1,
        .ordered-products h3 {
            margin-bottom: 20px;
        }

        .product-image {
            width: 100px;
            height: auto;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php
    if ($userType === 'Admin') {
        include('../sidebar/admin_sidebar.php');
    } elseif ($userType === 'Staff') {
        include('../sidebar/staff_sidebar.php');
    }
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <div class="order-details mb-4">
                <h1>Order #<?= htmlspecialchars($order['orderID']); ?></h1>
                <p><strong>Customer:</strong> <?= htmlspecialchars($order['customerName']); ?></p>
                <p><strong>Order Date:</strong> <?= htmlspecialchars($order['orderDate']); ?></p>
                <p><strong>Total Amount:</strong> RM<?= htmlspecialchars(number_format($order['totalAmount'], 2)); ?></p>
                <p><strong>Order Status:</strong> <?= htmlspecialchars($order['orderStatus']); ?></p>
            </div>

            <div class="ordered-products">
                <h3>Ordered Products</h3>
                <?php
                // Fetch products ordered in this order
                $sql = "SELECT p.productName, op.quantity, p.productPrice, p.productImage
            FROM orderproducts op
            INNER JOIN product p ON op.productID = p.productID 
            WHERE op.orderID = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $orderID);
                $stmt->execute();
                $productsResult = $stmt->get_result();

                if ($productsResult->num_rows > 0) {
                    $orderTotalPrice = 0; // Initialize total price variable

                    echo "<table class='table table-hover'>
                <thead class='table-dark'>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>";
                    while ($product = $productsResult->fetch_assoc()) {
                        $totalPrice = $product['quantity'] * $product['productPrice'];
                        $orderTotalPrice += $totalPrice; // Accumulate the total price

                        echo "<tr>
                    <td><img src='../uploads/" . htmlspecialchars($product['productImage']) . "' alt='" . htmlspecialchars($product['productName']) . "' class='product-image'></td>
                    <td>" . htmlspecialchars($product['productName']) . "</td>
                    <td>" . htmlspecialchars($product['quantity']) . "</td>
                    <td>RM" . htmlspecialchars(number_format($product['productPrice'], 2)) . "</td>
                    <td>RM" . htmlspecialchars(number_format($totalPrice, 2)) . "</td>
                  </tr>";
                    }
                    echo "</tbody>";

                    // Add a row for the total price
                    echo "<tfoot class='table-dark'>
                <tr>
                    <td colspan='4' class='text-end'><strong>Total Price:</strong></td>
                    <td><strong>RM" . htmlspecialchars(number_format($orderTotalPrice, 2)) . "</strong></td>
                </tr>
              </tfoot>";
                    echo "</table>";
                } else {
                    echo "<p>No products found for this order.</p>";
                }
                ?>
            </div>

        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>