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
        <h3>Admin Dashboard</h3>
    </div>
    <a href="../admin_dashboard/admin_dashboard.php">Dashboard</a>
    <a href="../register_staff/register_staff.php">Register Staff</a>
    <a href="../manage_staff/manage_staff.php">Manage Staff</a>
    <a href="../manage_customer/manage_customer.php">Manage Customer</a>
    <a href="../logout/logout.php">Logout</a>
</div>
