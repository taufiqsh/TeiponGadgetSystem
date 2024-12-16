<?php
session_start(); // Start session to access logged-in admin details

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Ensure the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Check if admin is logged in
  if (!isset($_SESSION['adminID'])) {
    header("Location: ../admin_login/admin_login.php?error=Unauthorized access");
    exit();
  }

  $adminID = $_SESSION['adminID']; // Get adminID from session

  // Capture form data
  $staffName = trim($_POST['staffName']);
  $staffUsername = trim($_POST['staffUsername']);
  $staffEmail = trim($_POST['staffEmail']);
  $staffPassword = $_POST['staffPassword'];

  // Validate required fields
  if (empty($staffName) || empty($staffUsername) || empty($staffEmail) || empty($staffPassword)) {
    header("Location: register_staff.php?error=All fields are required");
    exit();
  }

  // Check if email is valid
  if (!filter_var($staffEmail, FILTER_VALIDATE_EMAIL)) {
    header("Location: register_staff.php?error=Invalid email address");
    exit();
  }

  // Check if username or email already exists
  $checkSql = "SELECT * FROM Staff WHERE staffUsername = ? OR staffEmail = ?";
  $stmt = $conn->prepare($checkSql);
  $stmt->bind_param("ss", $staffUsername, $staffEmail);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // Redirect back if username or email already exists
    header("Location: register_staff.php?error=Staff already exists");
    exit();
  }

  // Hash the password for security
  $hashedPassword = password_hash($staffPassword, PASSWORD_DEFAULT);

  // Insert the new staff record into the database with the adminID
  $sql = "INSERT INTO Staff (staffName, staffUsername, staffEmail, staffPassword, adminID) VALUES (?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssi", $staffName, $staffUsername, $staffEmail, $hashedPassword, $adminID);

  if ($stmt->execute()) {
    // Display success message with redirect using inline HTML and JS
    echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Success</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
          <script>
            // Redirect to admin dashboard after 3 seconds
            setTimeout(function () {
              window.location.href = "../admin_dashboard/admin_dashboard.php";
            }, 3000);
          </script>
        </head>
        <body class="bg-light d-flex justify-content-center align-items-center min-vh-100">
          <div class="alert alert-success text-center shadow p-4" style="width: 100%; max-width: 400px;">
            <h4 class="alert-heading">Success!</h4>
            <p>Staff member has been registered successfully.</p>
            <hr>
            <p class="mb-0">Redirecting to the admin dashboard...</p>
          </div>
        </body>
        </html>';
    exit();
  } else {
    // Redirect back with error message
    $error = "Error: " . $stmt->error;
    header("Location: register_staff.php?error=" . urlencode($error));
    exit();
  }
}

$conn->close();
