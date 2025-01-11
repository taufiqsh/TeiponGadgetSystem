<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check for username validation
if (isset($_POST['username'])) {
    $username = htmlspecialchars(trim($_POST['username']));

    // Username must be at least 4 characters
    if (strlen($username) < 4) {
        echo "too_short";
    } else {
        $stmt = $conn->prepare("SELECT customerUsername FROM Customer WHERE customerUsername = ?");
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            echo ($stmt->num_rows > 0) ? "taken" : "available";

            $stmt->close();
        } else {
            echo "error";
        }
    }
}

// Check for email validation
if (isset($_POST['email'])) {
    $email = htmlspecialchars(trim($_POST['email']));

    // Simple email format validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "invalid_email";
    } else {
        $stmt = $conn->prepare("SELECT customerEmail FROM Customer WHERE customerEmail = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            echo ($stmt->num_rows > 0) ? "taken" : "available";

            $stmt->close();
        } else {
            echo "error";
        }
    }
}

$conn->close();
?>
