<?php
session_start(); // Start session

// Check if the admin is logged in
if (!isset($_SESSION['adminID']) || !isset($_SESSION['adminName'])) {
    // Redirect to the login page if not logged in
    header("Location: ../admin_login/admin_login.php?error=Please login to access the dashboard");
    exit();
}

$adminName = $_SESSION['adminName']; // Get the admin's name from the session

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "teipon_gadget";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all customers from the Customers table
$sql = "SELECT * FROM Customer"; // Change 'Customers' to your actual table name for customers
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php include('../admin_sidebar/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Manage Customers</h1>
            <!-- Success or error message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <!-- Customers Table -->
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are customers records in the database
                    if ($result->num_rows > 0) {
                        // Output data for each row
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['customerID'] . "</td>"; // Assuming customerID is the primary key
                            echo "<td>" . htmlspecialchars($row['customerName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerEmail']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerPhoneNumber']) . "</td>";
                            echo "<td>
                    <a href='edit_customer.php?id=" . $row['customerID'] . "' class='btn btn-sm btn-warning'>Edit</a>
                    <a href='delete_customer.php?id=" . $row['customerID'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure you want to delete this customer?\")'>Delete</a>
                    </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No customers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
