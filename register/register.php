<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="../global.css" rel="stylesheet">

</head>

<body class="bg-light">
<?php include('../navbar/navbar.php'); ?>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 500px;">
      <h2 class="text-center mb-4">Register</h2>
      <form action="process_register.php" method="POST">

        <!-- Email Address -->
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
          <div class="error-message" id="emailError"></div>
        </div>

        
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
          <div class="error-message" id="nameError"></div>
        </div>

        <div class="mb-3">
          <label for="phoneNumber" class="form-label">Phone Number</label>
          <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Enter your phone number" required>
          <div class="error-message" id="phoneNumberError"></div>
        </div>

        <!-- State -->
        <div class="mb-3">
          <label for="state" class="form-label">State</label>
          <input type="text" class="form-control" id="state" name="state" placeholder="Enter your state" required>
          <div class="error-message" id="stateError"></div>
        </div>

        <!-- Postal Code -->
        <div class="mb-3">
          <label for="postal_code" class="form-label">Postal Code</label>
          <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Enter your postal code" required>
          <div class="error-message" id="postalCodeError"></div>
        </div>

        <!-- City -->
        <div class="mb-3">
          <label for="city" class="form-label">City</label>
          <input type="text" class="form-control" id="city" name="city" placeholder="Enter your city" required>
          <div class="error-message" id="cityError"></div>
        </div>

        <!-- Address -->
        <div class="mb-3">
          <label for="address" class="form-label">Address</label>
          <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter your address" required></textarea>
          <div class="error-message" id="addressError"></div>
        </div>

        <!-- Username -->
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
          <div class="error-message" id="usernameError"></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter a password" required>
          <div class="error-message" id="passwordError"></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
          <div class="error-message" id="confirmPasswordError"></div>
        </div>

        <!-- Submit Button -->
        <div class="d-grid">
          <button type="submit" class="btn btn-primary" onclick="validateForm()">Register</button>
        </div>
      </form>

      <!-- Additional Links -->
      <div class="mt-3 text-center">
        <p>Already have an account? <a href="../login/login.php" class="text-decoration-none">Login</a></p>
      </div>
    </div>
  </div>
    <!-- JavaScript Validation -->
    <script>
    function validateForm() {
      // Get form fields
      const email = document.getElementById('email');
      const name = document.getElementById('name');
      const phoneNumber = document.getElementById('phoneNumberError');
      const state = document.getElementById('state');
      const postalCode = document.getElementById('postal_code');
      const city = document.getElementById('city');
      const address = document.getElementById('address');
      const username = document.getElementById('username');
      const password = document.getElementById('password');
      const confirmPassword = document.getElementById('confirm_password');

      // Clear previous error messages
      document.querySelectorAll('.error-message').forEach(el => el.textContent = '');

      // Validate fields
      let isValid = true;

      if (!email.value.trim()) {
        document.getElementById('emailError').textContent = 'Email is required.';
        isValid = false;
      }

      if (!name.value.trim()) {
        document.getElementById('nameError').textContent = 'Name is required.';
        isValid = false;
      }

      if (!phoneNumber.value.trim()) {
        document.getElementById('phoneNumberError').textContent = 'Phone number is required.';
        isValid = false;
      }

      if (!state.value.trim()) {
        document.getElementById('stateError').textContent = 'State is required.';
        isValid = false;
      }

      if (!postalCode.value.trim() || isNaN(postalCode.value)) {
        document.getElementById('postalCodeError').textContent = 'Valid postal code is required.';
        isValid = false;
      }

      if (!city.value.trim()) {
        document.getElementById('cityError').textContent = 'City is required.';
        isValid = false;
      }

      if (!address.value.trim()) {
        document.getElementById('addressError').textContent = 'Address is required.';
        isValid = false;
      }

      if (!username.value.trim()) {
        document.getElementById('usernameError').textContent = 'Username is required.';
        isValid = false;
      }

      if (!password.value.trim()) {
        document.getElementById('passwordError').textContent = 'Password is required.';
        isValid = false;
      }

      if (password.value !== confirmPassword.value) {
        document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
        isValid = false;
      }

      // If all fields are valid, submit the form
      if (isValid) {
        document.getElementById('registerForm').submit();
      }
    }
  </script>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>