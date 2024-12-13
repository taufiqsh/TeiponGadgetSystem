<?php
session_start(); // Start the session

// Check if the admin is already logged in
if (isset($_SESSION['adminID'])) {
    header("Location: admin_dashboard.php"); // Redirect to the dashboard
    exit();
}

// Check for error in the query string
$errorMessage = isset($_GET['error']) ? $_GET['error'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">
  <div class="card p-4 shadow" style="width: 100%; max-width: 400px;">
    <h2 class="text-center mb-4">Register Admin</h2>

    <!-- Error Message -->
    <?php if (!empty($errorMessage)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($errorMessage); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form action="process_register_admin.php" method="POST">
      <div class="mb-3">
        <label for="adminName" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="adminName" name="adminName" placeholder="Enter your full name" required>
      </div>
      <div class="mb-3">
        <label for="adminUsername" class="form-label">Username</label>
        <input type="text" class="form-control" id="adminUsername" name="adminUsername" placeholder="Enter your username" required>
      </div>
      <div class="mb-3">
        <label for="adminEmail" class="form-label">Email</label>
        <input type="email" class="form-control" id="adminEmail" name="adminEmail" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label for="adminPassword" class="form-label">Password</label>
        <input type="password" class="form-control" id="adminPassword" name="adminPassword" placeholder="Enter your password" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-success">Register</button>
      </div>
    </form>

    <div class="text-center mt-3">
      <a href="../admin_login/admin_login.php" class="btn btn-secondary">Back to Login</a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
