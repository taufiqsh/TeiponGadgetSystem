<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the customer is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$orderID = $_GET['orderID']; // Get the order ID from the URL
$customerID = $_SESSION['userID'];

// Fetch order details from the database
$sql = "SELECT * FROM orders WHERE orderID = ? AND customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orderID, $customerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Invalid order or you do not have permission to view this order.";
    exit();
}

$order = $result->fetch_assoc();

// Generate QR code for the payment
$paymentLink = "https://paymentgateway.com/pay?orderID=" . $orderID . "&amount=" . $order['totalAmount']; // Example payment link
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($paymentLink) . "&size=200x200"; // Using a free QR code generation API
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Payment for Order #<?= $order['orderID']; ?></h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type']; ?>">
                <?= htmlspecialchars($_SESSION['message']['text']); ?>
            </div>
            <?php unset($_SESSION['message']); // Clear the message after displaying 
            ?>
        <?php endif; ?>

        <div class="alert alert-info">
            <h4>Scan the QR code below to make payment</h4>
            <p>Once the payment is completed, please upload the payment receipt or PDF for verification.</p>
        </div>

        <!-- Display the QR code -->
        <div class="text-center mb-4">
            <img src="<?= $qrCodeUrl; ?>" alt="QR Code for payment">
        </div>

        <div class="payment-form">
            <h3>Upload Payment Receipt</h3>
            <form action="process_payment.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="paymentReceipt" class="form-label">Upload Receipt (PDF or Image)</label>
                    <input type="file" class="form-control" id="paymentReceipt" name="paymentReceipt" accept="image/*,application/pdf" required>
                </div>
                <input type="hidden" name="orderID" value="<?= $order['orderID']; ?>">
                <button type="submit" class="btn btn-primary">Submit Receipt</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
$conn->close();
?>