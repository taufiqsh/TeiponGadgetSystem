<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <!-- Bootstrap CSS -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
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
          $error_message = htmlspecialchars($_GET['error']); // Sanitize the error message
          if ($error_message == 'incorrect_password') {
            echo "Incorrect password. Please try again.";
          } elseif ($error_message == 'no_user_found') {
            echo "No user found with that username.";
          } else {
            echo "An error occurred. Please try again.";
          }
          ?>
        </div>
      <?php endif; ?>

      <form action="process_login.php" method="POST">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <div class="mb-3 justify-content-center d-flex">
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="role" id="customer" value="customer" checked required>
            <label class="form-check-label" for="customer">Customer</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="role" id="staff" value="staff" required>
            <label class="form-check-label" for="staff">Staff</label>
          </div>
        </div>

        <!-- Username Field -->
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control <?php echo isset($_GET['error']) ? 'is-invalid' : ''; ?>" id="username" name="username" placeholder="Enter your username" required aria-describedby="usernameHelp">
        </div>

        <!-- Password Field -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required autocomplete="off">
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
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
