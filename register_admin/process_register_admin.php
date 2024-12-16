<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $adminName = trim($_POST['adminName']);
    $adminUsername = trim($_POST['adminUsername']);
    $adminEmail = trim($_POST['adminEmail']);
    $adminPassword = $_POST['adminPassword'];

    // Validate required fields
    if (empty($adminName) || empty($adminUsername) || empty($adminEmail) || empty($adminPassword)) {
        header("Location: register_admin.php?error=All fields are required");
        exit();
    }

    // Validate email
    if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        header("Location: register_admin.php?error=Invalid email address");
        exit();
    }

    // Check for existing username or email
    $checkSql = "SELECT * FROM Admin WHERE adminUsername = ? OR adminEmail = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param("ss", $adminUsername, $adminEmail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        header("Location: register_admin.php?error=Username or email already exists");
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

    // Insert the admin into the database
    $sql = "INSERT INTO Admin (adminName, adminUsername, adminPassword, adminEmail) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $adminName, $adminUsername, $hashedPassword, $adminEmail);

    if ($stmt->execute()) {
        header("Location: ../admin_login/admin_login.php?success=Registration successful. Please login.");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
        header("Location: register_admin.php?error=" . urlencode($error));
        exit();
    }
}

$conn->close();
?>
