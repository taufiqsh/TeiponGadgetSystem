<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teipon Gadget</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
  <?php if (isset($_SESSION['username'])): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-dark border-bottom border-body" data-bs-theme="dark">
      <div class="container justify-content-between">
        <!-- Left Logo -->
        <a href="/TeiponGadgetSystem/customer/customer_home.php" class="navbar-brand d-flex align-items-center">
          <i class="bi bi-phone"></i>
          <h4 class="text-white m-0">Teipon Gadget System</h4>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link text-white" href="/TeiponGadgetSystem/product/product_listing.php">Our Products</a>
            </li>
            <li class="nav-item">
              <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#cartModal">
                <i class="bi bi-cart3"></i> Cart (<span id="cartCount">
                  <?php
                  $cartCount = 0;
                  if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                      $cartCount += $item['quantity'];
                    }
                  }
                  echo $cartCount;
                  ?></span>)
              </a>
            </li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdown" data-bs-toggle="dropdown">
                <img src="../assets/css/bootstrap-icons/icons/person-fill.svg" alt="Profile Icon" width="24"> <?php echo htmlspecialchars($_SESSION['username']); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="/TeiponGadgetSystem/payment/pending_payment.php">Payments</a></li>
                <li><a class="dropdown-item" href="/TeiponGadgetSystem/settings_page/customer_settings.php">Settings</a></li>
                <li><a class="dropdown-item" href="/TeiponGadgetSystem/logout/logout.php">Logout</a></li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
  <?php endif; ?>
  <!-- Cart Modal -->
  <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="cartItems"></div>
          <h5 class="text-end mt-3">Total: RM <span id="cartTotal">0.00</span></h5>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="../checkout/checkout.php" class="btn btn-primary">Checkout</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>