<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if the user is logged in and if the form was submitted
if (!isset($_SESSION['userID']) || !isset($_POST['orderID']) || !isset($_FILES['paymentReceipt'])) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid request.',
    ];
    header("Location: payment.php");
    exit();
}

$orderID = $_POST['orderID'];
$customerID = $_SESSION['userID'];

// Validate orderID
if (!is_numeric($orderID)) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid order ID.',
    ];
    header("Location: payment.php");
    exit();
}

// Check if the uploaded file is valid
$allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$fileType = $_FILES['paymentReceipt']['type'];
$fileTmpName = $_FILES['paymentReceipt']['tmp_name'];
$fileName = $_FILES['paymentReceipt']['name'];
$fileSize = $_FILES['paymentReceipt']['size'];
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/uploads/receipts/';

// Ensure upload directory exists
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Server error: Unable to create upload directory.',
    ];
    header("Location: payment.php?orderID=" . htmlspecialchars($orderID));
    exit();
}

// Check file type
if (!in_array($fileType, $allowedFileTypes)) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Invalid file type. Please upload a PDF or image.',
    ];
    header("Location: payment.php?orderID=" . htmlspecialchars($orderID));
    exit();
}

// Check file size (max 5MB)
if ($fileSize > 5 * 1024 * 1024) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'File size is too large. Max size is 5MB.',
    ];
    header("Location: payment.php?orderID=" . htmlspecialchars($orderID));
    exit();
}

// Generate a unique name for the file
$uniqueFileName = uniqid() . '_' . basename($fileName);
$uploadPath = $uploadDir . $uniqueFileName;

// Move the uploaded file to the server
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Update the order with the receipt information and change status to 'Processing Payment'
    $orderStatus = 'Processing Payment';
    $sql = "UPDATE orders SET receiptPath = ?, orderStatus = ? WHERE orderID = ? AND customerID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ssii", $uniqueFileName, $orderStatus, $orderID, $customerID);
        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Payment receipt uploaded successfully.',
            ];
            header("Location: payment.php?orderID=" . htmlspecialchars($_POST['orderID']));
            exit();
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Database error: Unable to update order.',
            ];
            header("Location: payment.php?orderID=" . htmlspecialchars($orderID));
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Database error: Unable to prepare statement.',
        ];
        header("Location: payment.php?orderID=" . htmlspecialchars($orderID));
    }
} else {
    // Log the error for debugging
    error_log("Failed to move uploaded file: $fileTmpName to $uploadPath");
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error uploading receipt.',
    ];
    header("Location: payment.php?orderID=" . htmlspecialchars($orderID));
}

$conn->close();
exit();
?>
