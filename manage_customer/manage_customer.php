<?php
session_start(); // Start session

// Check if admin or staff is logged in
if (!(isset($_SESSION['adminID']) || isset($_SESSION['staffID']))) {
    // Redirect to the login page if neither is logged in
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

// Determine user type and session details
if (isset($_SESSION['adminID'])) {
    $userType = 'Admin';
    $userName = $_SESSION['adminName'];
} else if (isset($_SESSION['staffID'])) {
    $userType = 'Staff';
    $userName = $_SESSION['staffUsername'];
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch all customers except their password
$sql = "SELECT customerID, customerName, customerEmail, customerPhoneNumber, customerState, customerPostalCode, customerCity, customerAddress FROM Customer";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Customers</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-body {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .modal-footer .btn {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .modal-header {
            background-color: #f44336;
            color: white;
        }

        .modal-content {
            border-radius: 10px;
        }

        .btn-danger {
            background-color: #e53935;
            border-color: #e53935;
        }

        .btn-danger:hover {
            background-color: #d32f2f;
            border-color: #d32f2f;
        }
    </style>
</head>

<body>
    <!-- Sidebar -->
    <?php
    if ($userType === 'Admin') {
        include('../sidebar/admin_sidebar.php');
    } elseif ($userType === 'Staff') {
        include('../sidebar/staff_sidebar.php');
    }
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Manage Customers</h1>
            <p>Welcome, <?php echo htmlspecialchars($userType . " " . $userName); ?>!</p>

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
                        <th>State</th>
                        <th>Postal Code</th>
                        <th>City</th>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are customer records in the database
                    if ($result->num_rows > 0) {
                        // Output data for each row
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['customerID'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerEmail']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerPhoneNumber']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerState']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerPostalCode']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerCity']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['customerAddress']) . "</td>";
                            echo "<td>
                                <a href='edit_customer.php?id=" . $row['customerID'] . "' class='btn btn-sm btn-warning'>Edit</a>
                                <!-- Delete Button that triggers the modal -->
                                <a href='#' class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal" . $row['customerID'] . "'>Delete</a>
                              </td>";
                            echo "</tr>";

                            // Modal for confirmation
                            echo "
                            <div class='modal fade' id='deleteModal" . $row['customerID'] . "' tabindex='-1' aria-labelledby='deleteModalLabel" . $row['customerID'] . "' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='deleteModalLabel" . $row['customerID'] . "'>Confirm Deletion</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p><strong>Are you sure you want to delete this customer?</strong></p>
                                            <p>This action <span class='text-danger'>cannot</span> be undone. Once deleted, all the customer's data will be permanently removed.</p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                            <a href='delete_customer.php?id=" . $row['customerID'] . "' class='btn btn-danger'>Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No customers found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
