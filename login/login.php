<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php include('../navbar/navbar.php'); ?>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
      <h2 class="text-center mb-4">Login</h2>
      
      <!-- Show error message if present -->
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
          <?php
          if ($_GET['error'] == 'incorrect_password') {
            echo "Incorrect password. Please try again.";
          } elseif ($_GET['error'] == 'no_user_found') {
            echo "No user found with that username.";
          }
          ?>
        </div>
      <?php endif; ?>

      <form action="process_login.php" method="POST">
        <!-- Username Field -->
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
        </div>

        <!-- Password Field -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <!-- Remember Me Checkbox -->
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="remember" name="remember">
          <label class="form-check-label" for="remember">Remember Me</label>
        </div>

        <!-- Login Button -->
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Login</button>
        </div>
      </form>

      <!-- Additional Links -->
      <div class="mt-3 text-center">
        <p class="mb-1"><a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a></p>
        <p>Don't have an account? <a href="../register/register.php" class="text-decoration-none">Sign up</a></p>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
