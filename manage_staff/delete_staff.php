<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: ../admin_login/admin_login.php?error=Please login to access the dashboard");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Get staff ID to delete
if (isset($_GET['id'])) {
    $staffID = $_GET['id'];

    // Prepare and execute the deletion query
    $sql = "DELETE FROM Staff WHERE staffID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staffID);

    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: manage_staff.php?success=Staff member deleted successfully");
    } else {
        // Redirect with error message
        header("Location: manage_staff.php?error=Error deleting staff member");
    }
    $stmt->close();
}

$conn->close();
?>
