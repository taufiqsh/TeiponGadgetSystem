<?php
session_start(); // Start session


// Check if the customer is logged in
if (!isset($_SESSION['customerID']) || !isset($_SESSION['customerUsername'])) {
    header("Location: ../login/login.php?error=" . urlencode("Please login to access the settings"));
    exit();
}

// Fetch customer session data
$customerID = $_SESSION['customerID'];
$customerUsername = $_SESSION['customerUsername'];

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch customer data from the database
$sql = "SELECT customerName, customerEmail, customerUsername FROM Customer WHERE customerID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $customer = $result->fetch_assoc();
} else {
    die("Customer details not found.");
}

// Handle form submission for updating customer details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newName = $_POST['name'];
    $newEmail = $_POST['email'];
    $newUsername = $_POST['username'];
    $newPassword = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

    // Update customer details in the database
    if ($newPassword) {
        $updateSql = "UPDATE Customer SET customerName = ?, customerEmail = ?, customerUsername = ?, customerPassword = ? WHERE customerID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ssssi", $newName, $newEmail, $newUsername, $newPassword, $customerID);
    } else {
        $updateSql = "UPDATE Customer SET customerName = ?, customerEmail = ?, customerUsername = ? WHERE customerID = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("sssi", $newName, $newEmail, $newUsername, $customerID);
    }

    if ($updateStmt->execute()) {
        $_SESSION['customerUsername'] = $newUsername; // Update session with the new username
        header("Location: customer_settings.php?success=Details updated successfully");
        exit();
    } else {
        $error = "Error updating details: " . $updateStmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Settings</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Navbar -->
    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Customer Settings</h1>

            <!-- Success and Error Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Customer Edit Form -->
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($customer['customerName']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($customer['customerEmail']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($customer['customerUsername']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">New Password (optional)</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                </div>
                <button type="submit" class="btn btn-primary">Update Details</button>
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
