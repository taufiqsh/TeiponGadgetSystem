<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Staff</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../global.css" rel="stylesheet">
</head>

<body class="bg-light">
<?php include('../sidebar/admin_sidebar.php'); ?>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 500px;">
      <h2 class="text-center mb-4">Register New Staff</h2>

      <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success" role="alert">
          Staff member registered successfully!
        </div>
      <?php elseif (isset($_GET['error'])): ?>
        <div class="alert alert-danger" role="alert">
          <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <!-- Registration Form -->
      <form action="process_register_staff.php" method="POST">

        <div class="mb-3">
          <label for="staffName" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="staffName" name="staffName" placeholder="Enter staff full name" required>
          <div class="error-message" id="nameError"></div>
        </div>

        <div class="mb-3">
          <label for="staffUsername" class="form-label">Username</label>
          <input type="text" class="form-control" id="staffUsername" name="staffUsername" placeholder="Enter staff username" required>
          <div class="error-message" id="usernameError"></div>
        </div>

        <div class="mb-3">
          <label for="staffEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="staffEmail" name="staffEmail" placeholder="Enter staff email" required>
          <div class="error-message" id="emailError"></div>
        </div>

        <div class="mb-3">
          <label for="staffPassword" class="form-label">Password</label>
          <input type="password" class="form-control" id="staffPassword" name="staffPassword" placeholder="Enter staff password" required>
          <div class="error-message" id="passwordError"></div>
        </div>

        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
          <div class="error-message" id="confirmPasswordError"></div>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-success" onclick="validateForm()">Register Staff</button>
        </div>
      </form>

      <!-- <div class="mt-3 text-center">
        <a href="admin_login.php" class="btn btn-secondary">Back to Admin Login</a>
      </div> -->
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
  <script>
    function validateForm() {

      const staffName = document.getElementById('staffName');
      const staffUsername = document.getElementById('staffUsername');
      const staffEmail = document.getElementById('staffEmail');
      const staffPassword = document.getElementById('staffPassword');
      const confirmPassword = document.getElementById('confirm_password');

      document.querySelectorAll('.error-message').forEach(e => e.textContent = '');

      let isValid = true;

      if(!staffName.value.trim()){
        document.getElementById('nameError').textContent = 'Staff name is required';
        isValid = false;
      }

      if(!staffUsername.value.trim()){
        document.getElementById('usernameError').textContent = 'Staff username is required';
        isValid = false;
      }

      if(!staffEmail.value.trim()){
        document.getElementById('emailError').textContent = 'Staff email is required';
        isValid = false;
      }

      if(!staffPassword.value.trim()){
        document.getElementById('passwordError').textContent = 'Staff password is required';
        isValid = false;
      }

      if (staffPassword.value !== confirmPassword.value) {
        document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
        isValid = false;
      }

      // If all fields are valid, submit the form
      if (isValid) {
        document.getElementById('registerForm').submit();
      }
    }
  </script>
</body>

</html>