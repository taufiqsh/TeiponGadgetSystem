<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    // Redirect to the appropriate login page
    header("Location: ../login/login.php?error=Please login to access the dashboard");
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

// Fetch all orders from the Orders table
$sql = "SELECT orders.orderID, orders.orderDate, orders.totalAmount, orders.orderStatus, orders.receiptPath, customer.customerName 
        FROM orders 
        INNER JOIN customer ON orders.customerID = customer.customerID";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-actions {
            display: flex;
            gap: 5px;
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
            <h1 class="mb-4">Manage Orders</h1>
            <!-- Success or error message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <!-- Orders Table -->
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>Customer Name</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Order Status</th>
                        <th>Receipt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are orders in the database
                    if ($result->num_rows > 0) {
                        // Output data for each row
                        while ($row = $result->fetch_assoc()) {
                            // Format the order date to DD/MM/YYYY
                            $formattedOrderDate = date("d-m-Y", strtotime($row['orderDate']));

                            // Construct the full path to the receipt file
                            $receiptFullPath = isset($row['receiptPath']) && !empty($row['receiptPath'])
                                ? '/TeiponGadgetSystem/uploads/receipts/' . htmlspecialchars($row['receiptPath'])
                                : '';

                            echo "<tr>";
                            echo "<td>" . $row['orderID'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerName']) . "</td>";
                            echo "<td>" . htmlspecialchars($formattedOrderDate) . "</td>"; // Display formatted date
                            echo "<td>" . htmlspecialchars($row['totalAmount']) . "</td>";

                            // Displaying order status with a dropdown for inline editing
                            echo "<td><select class='form-control form-control-sm order-status' data-order-id='" . $row['orderID'] . "' onChange='updateOrderStatus(this)' 
                                        " . ($row['orderStatus'] == 'Order Cancelled' ? 'disabled' : '') . ">
                                    <option value='Pending Payment' " . ($row['orderStatus'] == 'Pending Payment' ? 'selected' : '') . ">Pending Payment</option>
                                    <option value='Processing Payment' " . ($row['orderStatus'] == 'Processing Payment' ? 'selected' : '') . ">Processing Payment</option>
                                    <option value='Order Shipped' " . ($row['orderStatus'] == 'Order Shipped' ? 'selected' : '') . ">Order Shipped</option>
                                    <option value='Order Completed' " . ($row['orderStatus'] == 'Order Completed' ? 'selected' : '') . ">Completed</option>
                                    <option value='Order Cancelled' " . ($row['orderStatus'] == 'Order Cancelled' ? 'selected' : '') . ">Order Cancelled</option>
                                    <option value='Order Rejected' " . ($row['orderStatus'] == 'Order Rejected' ? 'selected' : '') . ">Order Rejected</option>
                                </select></td>";

                            // If there's no receipt, show "No Receipt" button, otherwise show the receipt link
                            if (empty($receiptFullPath)) {
                                echo "<td><button class='btn btn-sm btn-warning' data-bs-toggle='modal' data-bs-target='#noReceiptModal'>No Receipt</button></td>";
                            } else {
                                echo "<td><a href='" . $receiptFullPath . "' class='btn btn-sm btn-info' target='_blank'>View Receipt</a></td>";
                            }

                            echo "<td class='table-actions'>
    <a href='view_order.php?orderID=" . $row['orderID'] . "' class='btn btn-sm btn-info'>View</a>
    <button class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal' data-order-id='" . $row['orderID'] . "'>Delete</button>
</td>";

                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No orders found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this order?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" action="delete_order.php">
                        <input type="hidden" name="orderID" id="deleteOrderID">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <script>
        function updateOrderStatus(selectElement) {
            const orderID = selectElement.getAttribute('data-order-id'); // Get the order ID from the dropdown
            const status = selectElement.value; // Get the selected status

            // Create an AJAX request
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "update_order.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        if (xhr.responseText.trim() === "success") {
                            alert("Order status updated successfully!");
                        } else {
                            alert("Failed to update order status. Please try again.");
                            // Optionally, revert the dropdown to its previous value on failure
                        }
                    } else {
                        alert("An error occurred while updating the status.");
                    }
                }
            };

            // Send the POST data
            xhr.send("orderID=" + orderID + "&status=" + encodeURIComponent(status));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const deleteModal = document.getElementById('deleteModal');
            deleteModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget; // Button that triggered the modal
                const orderID = button.getAttribute('data-order-id'); // Extract orderID
                const deleteInput = document.getElementById('deleteOrderID'); // Hidden input in the form
                deleteInput.value = orderID; // Set the orderID in the hidden input
            });
        });
    </script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>