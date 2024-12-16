<style>
.sidebar {
      height: 100vh;
      background-color: #343a40;
      color: #fff;
      position: fixed;
      width: 250px;
    }
    .sidebar a {
      color: #adb5bd;
      text-decoration: none;
      padding: 10px 20px;
      display: block;
    }
    .sidebar a:hover {
      background-color: #495057;
      color: #fff;
    }
    .main-content {
      margin-left: 250px;
      padding: 20px;
    }
  </style>
<!-- sidebar.php -->
<div class="sidebar">
    <div class="p-4">
        <h3>Staff Dashboard</h3>
    </div>
    <a href="../staff_dashboard/staff_dashboard.php">Dashboard</a>
    <a href="../manage_customer/manage_customer.php">Manage Customer</a>
    <a href="../product/manage_product.php">Manage Products</a>
    <a href="../product/manage_product.php">Manage Transactions</a>
    <a href="../settings_page/staff_settings.php">Settings</a>
    <a href="../logout/logout.php">Logout</a>
</div>
