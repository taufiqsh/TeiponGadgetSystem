<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

$_SESSION['message'] = [
    'type' => 'success', // or 'error'
    'text' => 'Receipt uploaded successfully.', // or the error message
];

// Check if the user is logged in and if the form was submitted
if (!isset($_SESSION['userID']) || !isset($_POST['orderID']) || !isset($_FILES['paymentReceipt'])) {
    header("Location: payment.php?error=Invalid request");
    exit();
}

$orderID = $_POST['orderID'];
$customerID = $_SESSION['userID'];

// Check if the uploaded file is valid
$allowedFileTypes = ['image/jpeg', 'image/png', 'application/pdf'];
$fileType = $_FILES['paymentReceipt']['type'];
$fileTmpName = $_FILES['paymentReceipt']['tmp_name'];
$fileName = $_FILES['paymentReceipt']['name'];
$fileSize = $_FILES['paymentReceipt']['size'];
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/uploads/receipts/';

// Check file type
if (!in_array($fileType, $allowedFileTypes)) {
    header("Location: payment.php?orderID=" . $orderID . "&error=Invalid file type. Please upload a PDF or image.");
    exit();
}

// Check file size (max 5MB for this example)
if ($fileSize > 5 * 1024 * 1024) {
    header("Location: payment.php?orderID=" . $orderID . "&error=File size is too large. Max size is 5MB.");
    exit();
}

// Generate a unique name for the file
$uniqueFileName = uniqid() . '_' . basename($fileName);
$uploadPath = $uploadDir . $uniqueFileName;

// Move the uploaded file to the server
if (move_uploaded_file($fileTmpName, $uploadPath)) {
    // Update the order with the receipt information and change status to 'Processing'
    $orderStatus = 'Processing Payment';
    $sql = "UPDATE orders SET receiptPath = ?, orderStatus = ? WHERE orderID = ? AND customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $uniqueFileName, $orderStatus, $orderID, $customerID);
    $stmt->execute();

    header("Location: payment.php?orderID=" . $orderID . "&success=Receipt uploaded successfully.");
    exit();
} else {
    header("Location: payment.php?orderID=" . $orderID . "&error=Error uploading receipt.");
    exit();
}

$conn->close();
?>