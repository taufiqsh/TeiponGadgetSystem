<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate inputs
    $name = htmlspecialchars(trim($_POST['name']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $state = htmlspecialchars(trim($_POST['state']));
    $postal_code = htmlspecialchars(trim($_POST['postal_code']));
    $city = htmlspecialchars(trim($_POST['city']));
    $address = htmlspecialchars(trim($_POST['address']));
    $phoneNumber = htmlspecialchars(trim($_POST['phoneNumber']));

    // Add prefix '+60' to phone number
    $phoneNumber = '+60' . $phoneNumber;

    // Ensure passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash the password for secure storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO Customer (customerName, customerPhoneNumber, customerUsername, customerEmail, customerPassword, customerState, customerPostalCode, customerCity, customerAddress) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        die("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param(
        "sssssssss", 
        $name, 
        $phoneNumber, 
        $username, 
        $email, 
        $hashed_password, 
        $state, 
        $postal_code, 
        $city, 
        $address
    );

    // Execute the prepared statement
    if ($stmt->execute()) {
        // Display success message with redirect
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Registration Success</title>
            <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
            <script>
                setTimeout(function () {
                    window.location.href = "../login/login.php";
                }, 5000);
            </script>
        </head>
        <body class="bg-light d-flex justify-content-center align-items-center min-vh-100">
            <div class="alert alert-success text-center shadow p-4" style="width: 100%; max-width: 400px;">
                <h4 class="alert-heading">Registration Successful!</h4>
                <p>Your account has been created successfully.</p>
                <hr>
                <p class="mb-0">Redirecting to the login page...</p>
            </div>
        </body>
        </html>';
        exit();
    } else {
        // Redirect back with error message
        $error = "Error: " . $stmt->error;
        header("Location: register.php?error=" . urlencode($error));
        exit();
    }

    // Close the prepared statement
    $stmt->close();
}

// Close database connection
$conn->close();
?>
