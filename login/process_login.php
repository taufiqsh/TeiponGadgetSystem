<?php
session_start();
// Database connection details
$servername = "localhost";
$username = "root"; // Default username for local MySQL
$password = "root"; // Default password for local MySQL
$dbname = "teipon_gadget"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username and password from the form
$user = $_POST['username'];
$pass = $_POST['password'];

// Check if the user is trying to login as admin (e.g., username: isadmin, password: isadmin)
if ($user === 'isadmin' && $pass === 'isadmin') {
    // Redirect to admin login page
    header("Location: ../admin_login/admin_login.php");
    exit();
} else if ($user ==='isstaff' && $pass === 'isstaff'){
    header("Location: ../staff_login/staff_login.php");
    exit();
}



// Prepare SQL query to fetch regular user data
$sql = "SELECT * FROM customer WHERE customerUsername = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

// Check if the username exists
if ($result->num_rows > 0) {
    // Fetch user data
    $row = $result->fetch_assoc();
    
    // Verify the password
    if (password_verify($pass, $row['customerPassword'])) {
        // Start session and store user data
        session_start();
        $_SESSION['customerID'] = $row['customerID'];
        $_SESSION['customerUsername'] = $row['customerUsername'];
        
        header("Location: ../customer/customer_home.php");
        exit();
    } else {
        // Redirect back to login page with an error message
        header("Location: login.php?error=incorrect_password");
        exit();
    }
} else {
    // Redirect back to login page with an error message
    header("Location: login.php?error=no_user_found");
    exit();
}

// Close connection
$conn->close();
?>
