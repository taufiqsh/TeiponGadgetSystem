<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staffUsername = trim($_POST['staffUsername']);
    $staffPassword =trim($_POST['staffPassword']);

    // Query to check the username
    $sql = "SELECT * FROM Staff WHERE staffUsername = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $staffUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $staff = $result->fetch_assoc();
        // Verify the password
        if (password_verify($staffPassword, $staff['staffPassword'])) {
            // Set session variables
            $_SESSION['staffID'] = $staff['staffID'];
            $_SESSION['staffUsername'] = $staff['staffUsername'];

            header("Location: ../staff_dashboard/staff_dashboard.php");
            exit();
        } else {
            header("Location: staff_login.php?error=Invalid password");
            exit();
        }
    } else {
        header("Location: staff_login.php?error=Invalid username");
        exit();
    }
} else {
    header("Location: staff_login.php");
    exit();
}
?>
