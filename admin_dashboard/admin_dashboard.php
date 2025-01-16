<?php
session_start(); // Start session
// Check if the admin is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
  header("Location: ../login/login.php?error=Access denied");
  exit();
}

$staffName = $_SESSION['username']; // Get the admin's name from the session

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Query to get the total staff
$staffQuery = "SELECT COUNT(*) AS total_staff FROM staff";
$staffResult = $conn->query($staffQuery);
$staffCount = $staffResult->fetch_assoc()['total_staff'];

// Query to get the total customers
$customerQuery = "SELECT COUNT(*) AS total_customers FROM customer";
$customerResult = $conn->query($customerQuery);
$customerCount = $customerResult->fetch_assoc()['total_customers'];

// Query to get the total sales for the last month with 'completed' status only
$salesQuery = "SELECT SUM(totalAmount) AS total_sales 
               FROM orders 
               WHERE orderDate >= CURDATE() - INTERVAL 1 MONTH 
               AND orderStatus = 'Order Completed'";
$salesResult = $conn->query($salesQuery);
$totalSales = $salesResult->fetch_assoc()['total_sales'];

// Query to get top-selling products
$topProductsQuery = 'SELECT product.productName, SUM(orderproducts.quantity) AS total_sold FROM orderproducts INNER JOIN product ON orderproducts.productID = product.productID INNER JOIN orders ON orderproducts.orderID = orders.orderID WHERE orders.orderStatus IN ("Order Completed") GROUP BY product.productName ORDER BY total_sold DESC LIMIT 5;';
$topProductsResult = $conn->query($topProductsQuery);

// Query to get recent sales
$recentSalesQuery = 'SELECT orderID, orderDate, totalAmount FROM orders WHERE orderStatus = "Order Completed" ORDER BY orderDate DESC LIMIT 5';
$recentSalesResult = $conn->query($recentSalesQuery);

// Close the database connection
$conn->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <!-- Include Sidebar -->
  <?php include('../sidebar/admin_sidebar.php'); ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <h1 class="mb-4">Welcome, <?php echo htmlspecialchars($staffName); ?>!</h1>

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

        <!-- Card 3: Total Sales (Last 30 Days) -->
        <div class="col-md-4">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Total Sales (Last 30 Days)</h5>
              <p class="card-text fs-4">RM <?php echo number_format($totalSales ?? 0, 2); ?></p>
              <a href="../generate_sales/generate_sales_pdf.php" class="btn btn-success">Download PDF</a>
            </div>
          </div>
        </div>

        <!-- Top-Selling Products -->
        <div class="col-md-6">
          <div class="card shadow-sm">
            <div class="card-body">
              <h5 class="card-title">Top-Selling Products</h5>
              <ul>
                <?php while ($row = $topProductsResult->fetch_assoc()) { ?>
                  <li><?php echo htmlspecialchars($row['productName']) . ": " . $row['total_sold'] . " units"; ?></li>
                <?php } ?>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Sales -->
      <div class="mt-5">
        <h2>Recent Sales</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>#</th>
              <th>Order ID</th>
              <th>Date</th>
              <th>Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $recentSalesResult->fetch_assoc()) { ?>
              <tr>
                <td><?php echo $row['orderID']; ?></td>
                <td><?php echo $row['orderDate']; ?></td>
                <td>RM <?php echo number_format($row['totalAmount'], 2); ?></td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>