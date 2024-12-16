<?php
session_start();

// Check if the staff is logged in
if (!isset($_SESSION['staffID']) || !isset($_SESSION['staffUsername'])) {
  // Redirect to the login page if not logged in
  header("Location: ../staff_login/staff_login.php?error=Please login to access the dashboard");
  exit();
}

$staffUsername = $_SESSION['staffUsername']; // Get the staff's name from the session

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Query to get the total number of orders
$orderQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$orderResult = $conn->query($orderQuery);
$orderCount = $orderResult->fetch_assoc()['total_orders'];

// Query to get the total number of products
$productQuery = "SELECT COUNT(*) AS total_products FROM product";
$productResult = $conn->query($productQuery);
$productCount = $productResult->fetch_assoc()['total_products'];

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Staff Dashboard</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <!-- Include Sidebar -->
  <?php include('../sidebar/staff_sidebar.php'); ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($staffUsername); ?>!</h1>

      <!-- Dashboard Cards -->
      <div class="row g-4">
        <!-- Card 1: Total Orders -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Orders</h5>
              <p class="card-text fs-4"><?php echo $orderCount; ?></p>
              <a href="../manage_orders/manage_orders.php" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>

        <!-- Card 2: Total Products -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Products</h5>
              <p class="card-text fs-4"><?php echo $productCount; ?></p>
              <a href="../product/manage_product.php" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>

      </div>

      <!-- Recent Tasks -->
      <div class="mt-5">
        <h2>Recent Tasks</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Task</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>Processed an order</td>
              <td>2024-12-13</td>
              <td><a href="#" class="btn btn-sm btn-primary">Details</a></td>
            </tr>
            <tr>
              <td>2</td>
              <td>Updated product inventory</td>
              <td>2024-12-12</td>
              <td><a href="#" class="btn btn-sm btn-primary">Details</a></td>
            </tr>
            <tr>
              <td>3</td>
              <td>Responded to a customer query</td>
              <td>2024-12-11</td>
              <td><a href="#" class="btn btn-sm btn-primary">Details</a></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>