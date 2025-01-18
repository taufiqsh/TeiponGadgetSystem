<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the customer is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login/login.php?error=Access denied");
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <div class="container my-5">
        <h1 class="text-center mb-4">Payment for Order #<?= $order['orderID']; ?></h1>

        <?php if (isset($_SESSION['message'])): ?>
            <script>
                // Get the message from the session
                let fileError = <?= json_encode($_SESSION['message']); ?>;
                console.log("file_error content:", fileError.type);

                // Check for file error
                if (fileError.type === 'error') {
                    // Check for file error specifics (type and size)
                    if (fileError.text.includes('Invalid file type')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: fileError.text,
                            showCancelButton: false,
                            confirmButtonText: 'Close',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            }
                        });
                    } else if (fileError.text.includes('File size is too large')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: fileError.text,
                            showCancelButton: false,
                            confirmButtonText: 'Close',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            }
                        });
                    } else {
                        // General error alert
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: fileError.text,
                            showCancelButton: false,
                            confirmButtonText: 'Close',
                            customClass: {
                                confirmButton: 'btn btn-danger'
                            }
                        });
                    }
                } else if (fileError.type === 'success') {
                    // Success message handling
                    Swal.fire({
                        icon: 'success',
                        title: fileError.text,
                        showCancelButton: true,
                        confirmButtonText: 'Go to Homepage',
                        cancelButtonText: 'View Payment Page',
                        showConfirmButton: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        customClass: {
                            confirmButton: 'btn btn-success',
                            cancelButton: 'btn btn-primary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../customer/customer_home.php';
                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                            window.location.href = 'view_payment.php?orderID=<?= $orderID; ?>';
                        }
                    });
                }
                // Unset the session message after displaying it
                <?php unset($_SESSION['message']); ?>
            </script>
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
                    <input type="file" class="form-control" id="paymentReceipt" name="paymentReceipt"
                        accept="image/*,application/pdf" required>
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