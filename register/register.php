<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <!-- Bootstrap CSS -->
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="../global.css" rel="stylesheet">
</head>

<body class="bg-light">
  <?php include('../navbar/navbar.php'); ?>
  <div class="container d-flex justify-content-center align-items-center" style="min-height: 90vh; padding-top: 50px; padding-bottom: 50px;">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 500px;">
      <h2 class="text-center mb-4">Register</h2>
      <form action="process_register.php" method="POST">

        <!-- Email Address -->
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
          <div class="error-message" id="emailError"></div>
        </div>

        <!-- Name -->
        <div class="mb-3">
          <label for="name" class="form-label">Name</label>
          <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
          <div class="error-message" id="nameError"></div>
        </div>

        <!-- Phone Number -->
        <div class="mb-3">
          <label for="phoneNumber" class="form-label">Phone Number</label>
          <div class="input-group">
            <span class="input-group-text">+60</span>
            <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" placeholder="Enter your phone number" maxlength="10" required oninput="validatePhoneNumber(event)">
          </div>
          <div class="error-message" id="phoneNumberError"></div>
        </div>

        <script>
          // Function to validate phone number
          function validatePhoneNumber(event) {
            const input = event.target;

            // Allow only numbers (remove non-numeric characters)
            input.value = input.value.replace(/\D/g, '');

            // Restriction: First digit cannot be '0'
            if (input.value.length > 0 && input.value[0] === '0') {
              input.value = input.value.substring(1); // Remove leading '0'
            }

            // Restriction: Maximum of 10 digits after +60
            if (input.value.length > 10) {
              input.value = input.value.substring(0, 10);
            }
          }

          // When submitting the form, ensure '+60' is added to the phone number value
          document.querySelector('form').addEventListener('submit', function(event) {
            const phoneNumberInput = document.getElementById('phoneNumber');
            // Ensure the phone number always includes the prefix '+60'
            if (phoneNumberInput.value) {
              phoneNumberInput.value = '+60' + phoneNumberInput.value;
            }
          });
        </script>

        <!-- State -->
        <div class="mb-3">
          <label for="state" class="form-label">State</label>
          <select class="form-control custom-select" id="state" name="state" required>
            <option value="" disabled selected>Select your state</option>
            <option value="Johor">Johor</option>
            <option value="Kedah">Kedah</option>
            <option value="Kelantan">Kelantan</option>
            <option value="Malacca">Malacca</option>
            <option value="Negeri_Sembilan">Negeri Sembilan</option>
            <option value="Pahang">Pahang</option>
            <option value="Penang">Penang</option>
            <option value="Perak">Perak</option>
            <option value="Perlis">Perlis</option>
            <option value="Selangor">Selangor</option>
            <option value="Terengganu">Terengganu</option>
            <option value="Sabah">Sabah</option>
            <option value="Sarawak">Sarawak</option>
          </select>
          <div class="error-message" id="stateError"></div>
        </div>

        <!-- City -->
        <div class="mb-3">
          <label for="city" class="form-label">City</label>
          <select class="form-control" id="city" name="city" required>
            <option value="" disabled selected>Select your city</option>
          </select>
          <div class="error-message" id="cityError"></div>
        </div>

        <!-- Postal Code -->
        <div class="mb-3">
          <label for="postal_code" class="form-label">Postal Code</label>
          <input type="text" class="form-control" id="postal_code" name="postal_code" placeholder="Enter your postal code" maxlength="5" pattern="^\d{5}$" required oninput="validatePostalCode(event)">
          <div class="error-message" id="postalCodeError"></div>
        </div>

        <script>
          function validatePostalCode(event) {
            const input = event.target;
            input.value = input.value.replace(/\D/g, '');
          }
        </script>

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
          <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter a password" required>
            <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
              <i class="bi bi-eye-slash"></i> <!-- Eye icon to hide the password initially -->
            </span>
          </div>
          <div class="error-message" id="passwordError"></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
          <label for="confirm_password" class="form-label">Confirm Password</label>
          <div class="input-group">
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
            <span class="input-group-text" id="toggleConfirmPassword" style="cursor: pointer;">
              <i class="bi bi-eye-slash"></i> <!-- Eye icon to hide the password initially -->
            </span>
          </div>
          <div class="error-message" id="confirmPasswordError"></div>
        </div>

        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

        <!-- JavaScript to toggle password visibility -->
        <script>
          // Toggle visibility for Password
          document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('password');
            const passwordIcon = this.querySelector('i');
            
            if (passwordField.type === "password") {
              passwordField.type = "text";
              passwordIcon.classList.remove('bi-eye-slash');
              passwordIcon.classList.add('bi-eye');
            } else {
              passwordField.type = "password";
              passwordIcon.classList.remove('bi-eye');
              passwordIcon.classList.add('bi-eye-slash');
            }
          });

          // Toggle visibility for Confirm Password
          document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
            const confirmPasswordField = document.getElementById('confirm_password');
            const confirmPasswordIcon = this.querySelector('i');
            
            if (confirmPasswordField.type === "password") {
              confirmPasswordField.type = "text";
              confirmPasswordIcon.classList.remove('bi-eye-slash');
              confirmPasswordIcon.classList.add('bi-eye');
            } else {
              confirmPasswordField.type = "password";
              confirmPasswordIcon.classList.remove('bi-eye');
              confirmPasswordIcon.classList.add('bi-eye-slash');
            }
          });
        </script>

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
  // Function to validate form including password match
  function validateForm(event) {
    event.preventDefault();  // Prevent the form from submitting immediately

    // Get form fields
    const email = document.getElementById('email');
    const name = document.getElementById('name');
    const phoneNumber = document.getElementById('phoneNumber');
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

    // Confirm Password validation
    if (password.value !== confirmPassword.value) {
      document.getElementById('confirmPasswordError').textContent = 'Passwords do not match.';
      isValid = false;
    }

    // If all fields are valid, submit the form
    if (isValid) {
      document.getElementById('registerForm').submit();
    }
  }
  document.querySelector('form').addEventListener('submit', validateForm);
</script>

  <!-- Include the state-city-postal.js file -->
  <script src="state_city_postal.js"></script>

  <!-- Bootstrap JS Bundle -->
  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
