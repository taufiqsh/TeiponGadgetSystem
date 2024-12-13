<?php
session_start(); // Start session

// Check if the admin is logged in
if (!isset($_SESSION['adminID']) || !isset($_SESSION['adminName'])) {
    // Redirect to the login page if not logged in
    header("Location: ../admin_login/admin_login.php?error=Please login to access the dashboard");
    exit();
}

$adminName = $_SESSION['adminName']; // Get the admin's name from the session

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "teipon_gadget"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the total number of staff
$staffQuery = "SELECT COUNT(*) AS total_staff FROM staff";
$staffResult = $conn->query($staffQuery);
$staffCount = $staffResult->fetch_assoc()['total_staff'];

// Query to get the total number of customers
$customerQuery = "SELECT COUNT(*) AS total_customers FROM customer";
$customerResult = $conn->query($customerQuery);
$customerCount = $customerResult->fetch_assoc()['total_customers'];

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
    }
  </style>
</head>
<body>
  <!-- Include Sidebar -->
  <?php include('../admin_sidebar/sidebar.php'); ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>

      <!-- Dashboard Cards -->
      <div class="row g-4">
        <!-- Card 1: Total Staff -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Staff</h5>
              <p class="card-text fs-4"><?php echo $staffCount; ?></p>
              <a href="../manage_staff/manage_staff.php" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>

        <!-- Card 2: Total Customers -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Customers</h5>
              <p class="card-text fs-4"><?php echo $customerCount; ?></p>
              <a href="../manage_customer/manage_customer.php" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>

      </div>

      <!-- Recent Activity -->
      <div class="mt-5">
        <h2>Recent Activity</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Activity</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Added a new staff</td>
              <td>2024-12-11</td>
              <td><a href="#" class="btn btn-sm btn-primary">Details</a></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Updated system settings</td>
              <td>2024-12-10</td>
              <td><a href="#" class="btn btn-sm btn-primary">Details</a></td>
            </tr>
            <tr>
              <td>3</td>
              <td>Completed a task</td>
              <td>2024-12-09</td>
              <td><a href="#" class="btn btn-sm btn-primary">Details</a></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
