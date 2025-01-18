<?php
session_start(); // Start session

// Check if admin or staff is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
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

// Get product ID from the query parameter
$productID = $_GET['id'] ?? null;

// Include SweetAlert2 in the HTML
echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';

if ($productID) {
    $sql = "DELETE FROM Product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);

    if ($stmt->execute()) {
        // SweetAlert for success
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Success!',
                    text: 'Product deleted successfully.',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'manage_product.php';
                });
            });
        </script>";
    } else {
        // SweetAlert for error during deletion
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Error deleting product: " . addslashes($stmt->error) . "',
                    icon: 'error',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'manage_product.php';
                });
            });
        </script>";
    }
    $stmt->close();
} else {
    // SweetAlert for missing product ID
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Error!',
                text: 'No product ID provided.',
                icon: 'error',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'manage_product.php';
            });
        });
    </script>";
}
?>
