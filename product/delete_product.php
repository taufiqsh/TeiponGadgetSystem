<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['adminID']) && !isset($_SESSION['staffID']))) {
    header("Location: ../login/login.php?error=Access denied");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

$productID = $_GET['id'] ?? null;

if ($productID) {
    $sql = "DELETE FROM Product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);

    if ($stmt->execute()) {
        header("Location: manage_product.php?success=Product deleted successfully");
        exit();
    } else {
        header("Location: manage_product.php?error=Error deleting product: " . $stmt->error);
        exit();
    }
}
?>
