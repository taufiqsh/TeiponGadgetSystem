<?php
session_start();

// Check if the staff is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
  // Redirect to the login page if not logged in
  header("Location: ../login/login.php?error=Access denied");
  exit();
}

$staffName = $_SESSION['username']; // Get the staff's name from the session

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Query to get the total number of orders
$orderQuery = "SELECT COUNT(*) AS total_orders FROM orders";
$orderResult = $conn->query($orderQuery);
$orderCount = $orderResult->fetch_assoc()['total_orders'];

// Query to get the total number of products
$productQuery = "SELECT COUNT(*) AS total_products FROM product";
$productResult = $conn->query($productQuery);
$productCount = $productResult->fetch_assoc()['total_products'];

// Query to get the total sales for the last month with 'completed' status only
$salesQuery = "SELECT SUM(totalAmount) AS total_sales 
               FROM orders 
               WHERE orderDate >= CURDATE() - INTERVAL 1 MONTH 
               AND orderStatus = 'Order Completed'";
$salesResult = $conn->query($salesQuery);
$totalSales = $salesResult->fetch_assoc()['total_sales'];

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
      <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($staffName); ?>!</h1>

      <!-- Dashboard Cards -->
      <div class="row g-4">
        <!-- Card 1: Total Orders -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Orders</h5>
              <p class="card-text fs-4"><?php echo $orderCount; ?></p>
              <a href="../order/manage_order.php" class="btn btn-primary">View Details</a>
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

        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Sales (Last 30 Days)</h5>
              <p class="card-text fs-4">RM <?php echo number_format($totalSales ?? 0, 2); ?></p>
              <a href="../generate_sales/generate_sales_pdf.php" class="btn btn-success">Download PDF</a>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>