<?php
// Check for error in the query string
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php include('../navbar/navbar.php'); ?>
<div class="container d-flex justify-content-center align-items-center min-vh-100">
  <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
    <h2 class="text-center mb-4">Admin Login</h2>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form action="process_admin_login.php" method="POST">
      <div class="mb-3">
        <label for="adminUsername" class="form-label">Username</label>
        <input type="text" class="form-control" id="adminUsername" name="adminUsername" placeholder="Enter your username" required>
      </div>
      <div class="mb-3">
        <label for="adminPassword" class="form-label">Password</label>
        <input type="password" class="form-control" id="adminPassword" name="adminPassword" placeholder="Enter your password" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
      </div>
    </form>

    <!-- Register Admin link -->
    <div class="text-center mt-3">
      <a href="../register_admin/register_admin.php" class="btn btn-secondary">Register as Admin</a>
    </div>
  </div>
</div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
