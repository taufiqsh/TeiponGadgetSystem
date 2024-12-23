<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the user is logged in
if (!isset($_SESSION['customerID'])) {
    header('Location: login.php'); // Redirect if not logged in
    exit();
}

$customerID = $_SESSION['customerID'];

// Fetch orders with a 'Pending' status for the logged-in customer
$sql = "SELECT orderID, orderDate, totalAmount, orderStatus FROM orders WHERE customerID = ? AND orderStatus = 'Pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Payments</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Pending Payments</h1>

        <?php if ($result->num_rows > 0): ?>
            <div class="list-group">
                <?php while ($order = $result->fetch_assoc()): ?>
                    <a href="payment.php?orderID=<?= $order['orderID']; ?>" class="list-group-item list-group-item-action">
                        <h5 class="mb-1">Order #<?= $order['orderID']; ?></h5>
                        <p class="mb-1">Order Date: <?= $order['orderDate']; ?></p>
                        <p>Total Amount: RM <?= number_format($order['totalAmount'], 2); ?></p>
                        <p>Status: <span class="badge bg-warning"><?= $order['orderStatus']; ?></span></p>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>You have no pending payments at the moment.</p>
        <?php endif; ?>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
