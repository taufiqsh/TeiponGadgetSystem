<?php
session_start();

// Database connection variables
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "teipon_gadget";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capture form data
    $adminUsername = trim($_POST['adminUsername']);
    $adminPassword = trim($_POST['adminPassword']);

    // Check if fields are filled
    if (empty($adminUsername) || empty($adminPassword)) {
        header("Location: admin_login.php?error=Please fill in all fields");
        exit();
    }

    // Check credentials in the database
    $sql = "SELECT * FROM Admin WHERE adminUsername = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $adminUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $admin = $result->fetch_assoc();

        // Verify password
        if (password_verify($adminPassword, $admin['adminPassword'])) {
            // Set session variables
            $_SESSION['adminID'] = $admin['adminID'];
            $_SESSION['adminName'] = $admin['adminName'];

            // Redirect to the dashboard
            header("Location: ../admin_dashboard/admin_dashboard.php");
            exit();
        } else {
            // Invalid password
            header("Location: admin_login.php?error=Invalid username or password");
            exit();
        }
    } else {
        // Invalid username
        header("Location: admin_login.php?error=Invalid username or password");
        exit();
    }
}

$conn->close();
?>
