<?php
session_start(); // Start session

// Check if the admin is logged in
if (!(isset($_SESSION['userID']))) {
    // Redirect to the login page if neither is logged in
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch customer data if id is set
if (isset($_GET['id'])) {
    $customerID = $_GET['id'];
    $sql = "SELECT * FROM Customer WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
    } else {
        echo "Customer not found!";
        exit();
    }
}

// Update customer details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phoneNumber'];
    $state = $_POST['state'];
    $postalCode = $_POST['postalCode'];
    $city = $_POST['city'];
    $address = $_POST['address'];

    $updateSql = "UPDATE Customer SET customerName = ?, customerEmail = ?, customerPhoneNumber = ?, customerState = ?, customerPostalCode = ?, customerCity = ?, customerAddress = ? WHERE customerID = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("ssssissi", $name, $email, $phone, $state, $postalCode, $city, $address, $customerID);

    if ($updateStmt->execute()) {
        header("Location: manage_customer.php?success=Customer details updated successfully");
        exit();
    } else {
        $error = "Error updating customer: " . $updateStmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <?php include('../sidebar/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Edit Customer</h1>
            <!-- Display error message -->
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- Edit Form -->
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
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phoneNumber" name="phoneNumber" value="<?php echo htmlspecialchars($customer['customerPhoneNumber']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="state" class="form-label">State</label>
                    <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($customer['customerState']); ?>">
                </div>
                <div class="mb-3">
                    <label for="postalCode" class="form-label">Postal Code</label>
                    <input type="number" class="form-control" id="postalCode" name="postalCode" value="<?php echo htmlspecialchars($customer['customerPostalCode']); ?>">
                </div>
                <div class="mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($customer['customerCity']); ?>">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($customer['customerAddress']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
