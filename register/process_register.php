<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Default username for local MySQL
$password = "root"; // Default password for local MySQL
$dbname = "teipon_gadget"; // Database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']); // Username field
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $postal_code = mysqli_real_escape_string($conn, $_POST['postal_code']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);

    // Validate passwords
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Ensure no field is empty
    if (empty($password) || empty($state) || empty($postal_code) || empty($city) || empty($address)) {
        die("All fields are required.");
    }

    // Hash the password for secure storage
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

   // SQL query to insert new user into the Customer table
   $sql = "INSERT INTO Customer (customerName,customerPhoneNumber, customerUsername, customerEmail, customerPassword, customerState, customerPostalCode, customerCity, customerAddress) 
   VALUES ('$name','$phoneNumber', '$username', '$email', '$hashed_password', '$state', '$postal_code', '$city', '$address')";

if ($conn->query($sql) === TRUE) {
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
$error = "Error: " . $conn->error;
header("Location: register.php?error=" . urlencode($error));
exit();
}

// Close connection
$conn->close();
}
?>