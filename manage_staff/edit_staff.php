<?php
session_start(); // Start session

// Check if the admin is logged in
if (!isset($_SESSION['adminID']) || !isset($_SESSION['username'])) {
  // Redirect to the login page if not logged in
  header("Location: ../admin_login/admin_login.php?error=Please login to access the dashboard");
  exit();
}

$adminID = $_SESSION['adminID'];
$adminUsername = $_SESSION['username'];

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Get the staff ID from the URL
if (isset($_GET['id'])) {
  $staffID = $_GET['id'];

  // Fetch staff details from the database
  $sql = "SELECT * FROM Staff WHERE staffID = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $staffID);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows == 0) {
    // Redirect if staff member is not found
    header("Location: manage_staff.php?error=Staff member not found");
    exit();
  }

  // Fetch staff data
  $staff = $result->fetch_assoc();
} else {
  header("Location: manage_staff.php?error=Invalid staff ID");
  exit();
}

// Update staff details
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $staffName = trim($_POST['staffName']);
  $staffUsername = trim($_POST['staffUsername']);
  $staffEmail = trim($_POST['staffEmail']);
  $newPassword = trim($_POST['newPassword']);
  $confirmPassword = trim($_POST['confirmPassword']);

  // Validate input fields
  if (empty($staffName) || empty($staffUsername) || empty($staffEmail)) {
    header("Location: edit_staff.php?id=$staffID&error=All fields are required");
    exit();
  }

  // If a new password is provided, validate it
  if (!empty($newPassword)) {
    if ($newPassword !== $confirmPassword) {
      header("Location: edit_staff.php?id=$staffID&error=Passwords do not match");
      exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update staff data with new password
    $updateSql = "UPDATE Staff SET staffName = ?, staffUsername = ?, staffEmail = ?, staffPassword = ? WHERE staffID = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssssi", $staffName, $staffUsername, $staffEmail, $hashedPassword, $staffID);
  } else {
    // Update staff data without changing the password
    $updateSql = "UPDATE Staff SET staffName = ?, staffUsername = ?, staffEmail = ? WHERE staffID = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("sssi", $staffName, $staffUsername, $staffEmail, $staffID);
  }

  if ($stmt->execute()) {
    header("Location: manage_staff.php?success=Staff details updated successfully");
    exit();
  } else {
    header("Location: edit_staff.php?id=$staffID&error=Failed to update staff details");
    exit();
  }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Staff</title>
  <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <!-- Sidebar -->
  <?php include('../sidebar/admin_sidebar.php'); ?>

  <!-- Main Content -->
  <div class="main-content">
    <div class="container">
      <h1 class="mb-4">Edit Staff</h1>

      <!-- Error or Success message -->
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
      <?php elseif (isset($_GET['success'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
      <?php endif; ?>

      <!-- Edit Staff Form -->
      <form action="edit_staff.php?id=<?php echo $staffID; ?>" method="POST">
        <div class="mb-3">
          <label for="staffName" class="form-label">Name</label>
          <input type="text" class="form-control" id="staffName" name="staffName" value="<?php echo htmlspecialchars($staff['staffName']); ?>" required>
        </div>
        <div class="mb-3">
          <label for="staffUsername" class="form-label">Username</label>
          <input type="text" class="form-control" id="staffUsername" name="staffUsername" value="<?php echo htmlspecialchars($staff['staffUsername']); ?>" required>
        </div>
        <div class="mb-3">
          <label for="staffEmail" class="form-label">Email</label>
          <input type="email" class="form-control" id="staffEmail" name="staffEmail" value="<?php echo htmlspecialchars($staff['staffEmail']); ?>" required>
        </div>
        <!-- Password Fields -->
        <div class="mb-3">
          <label for="newPassword" class="form-label">New Password (optional)</label>
          <input type="password" class="form-control" id="newPassword" name="newPassword">
        </div>
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm New Password</label>
          <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
        </div>
        <button type="submit" class="btn btn-primary">Update Staff</button>
      </form>
    </div>
  </div>

  <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>