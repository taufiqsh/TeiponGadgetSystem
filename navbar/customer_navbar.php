<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch products from the database
$productQuery = "SELECT productName, productID FROM Product"; // Adjust table/column names
$productResult = $conn->query($productQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teipon Gadget</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

  <!-- Customer-Specific Navbar (Visible only when logged in) -->
  <?php if (isset($_SESSION['customerUsername'])): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-dark border-bottom border-body" data-bs-theme="dark">
      <div class="container justify-content-between">
        <!-- Left Logo -->
        <a href="/TeiponGadgetSystem/customer/customer_home.php" class="navbar-brand d-flex align-items-center">
          <i class="bi bi-phone"></i>
          <h4 class="text-white m-0">Teipon Gadget System</h4>
        </a>

        <!-- Toggler Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Items -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav align-items-center">
            <!-- Our Products Dropdown -->
            <li class="nav-item">
              <a class="nav-link text-white" href="/TeiponGadgetSystem/product/product_listing.php">Our Products</a>
            </li>

            <!-- About Us Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link text-white" href="#">About Us</a>
            </li>

            <!-- Cart Icon -->
            <li class="nav-item">
              <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="bi bi-cart3"></i> Cart (<span id="cartCount">
                  <?php
                  $cartCount = 0;
                  if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                      $cartCount += $item['quantity'];  // Sum up the quantities
                    }
                  }
                  echo $cartCount;
                  ?>
                </span>)
              </a>
            </li>


            <!-- Customer Profile Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../assets/css/bootstrap-icons/icons/person-fill.svg" alt="Profile Icon" width="24" height="24">
                <span class="ms-2"><?php echo htmlspecialchars($_SESSION['customerUsername']); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="/TeiponGadgetSystem/settings_page/customer_settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="/TeiponGadgetSystem/logout/logout.php">Logout</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  <?php else: ?>
    <!-- If not logged in, display a message or redirect -->
    <p>Please log in to access the customer dashboard.</p>
  <?php endif; ?>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// $conn->close(); // Close the database connection
?>