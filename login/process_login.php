<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');


$user = trim($_POST['username']);
$pass = trim($_POST['password']);
$role = trim($_POST['role']);

if ($role === 'customer') {
    $table = "customer";
    $usernameField = "customerUsername";
    $passwordField = "customerPassword";
    $idField = "customerID";
    $redirectPath = "../customer/customer_home.php";
} elseif ($role === 'staff') {
    $table = "staff";
    $usernameField = "staffUsername";
    $passwordField = "staffPassword";
    $idField = "staffID";
} else {
    header("Location: login.php?error=invalid_role");
    exit();
}

$sql = "SELECT * FROM $table WHERE $usernameField = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL error: " . $conn->error);
}

$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

// Check user existence
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($pass, $row[$passwordField])) {
        // Set session variables
        $_SESSION['userID'] = $row[$idField];
        $_SESSION['username'] = $row[$usernameField];
        $_SESSION['role'] = $role;

        // Redirect based on role
        if ($role === 'staff') {
            if (is_null($row['adminID'])) {
                header("Location: ../staff_dashboard/staff_dashboard.php");
            } else {
                $_SESSION['adminID'] = $row['adminID'];
                header("Location: ../admin_dashboard/admin_dashboard.php");
            }
        } else {
            header("Location: $redirectPath");
        }
        exit();
    } else {
        header("Location: login.php?error=incorrect_password");
        exit();
    }
} else {
    header("Location: login.php?error=no_user_found");
    exit();
}

$conn->close();
