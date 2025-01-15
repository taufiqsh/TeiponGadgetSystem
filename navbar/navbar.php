<?php
// Database connection
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
  <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .navbar {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      z-index: 1000;
      transition: top 0.3s; /* Smooth transition for hiding/showing the navbar */
    }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-light bg-dark" data-bs-theme="dark"> <!-- Removed border-bottom -->
    <div class="container justify-content-between">
      <!-- Left Logo -->
      <a href="/TeiponGadgetSystem/index.php" class="navbar-brand d-flex align-items-center">
        <i class="bi bi-phone"></i>
        <h4 class="text-white m-0">Teipon Gadget System</h4>
      </a>

      <!-- Toggler Button -->
      <button
        class="navbar-toggler"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Navbar Items -->
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav align-items-center">

        
          <!-- About Us Dropdown -->
          <li class="nav-item dropdown">
            <a
              class="nav-link text-white"
              href="#team-section">
              About Us
            </a>
          </li>


          <li class="nav-item dropdown">
              <!-- Login Button -->
              <button
                type="button"
                class="btn btn-primary ms-3"
                onclick="window.location.href='/TeiponGadgetSystem/login/login.php'">
                Login
              </button>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script>
    let lastScrollTop = 0; // Variable to track scroll position

    // Listen to scroll events
    window.addEventListener('scroll', function () {
      let currentScroll = window.pageYOffset || document.documentElement.scrollTop;

      // If scrolling down, hide the navbar
      if (currentScroll > lastScrollTop) {
        document.querySelector('.navbar').style.top = "-70px"; // Adjust this value to the height of your navbar
      } 
      // If scrolling up, show the navbar
      else {
        document.querySelector('.navbar').style.top = "0";
      }
      
      // Update the last scroll position
      lastScrollTop = currentScroll <= 0 ? 0 : currentScroll; // Prevent negative scroll values
    });
  </script>

</body>

</html>

<?php
// $conn->close(); // Close the database connection
?>