<?php
session_start(); // Start session

// Check if the admin is logged in
if (!isset($_SESSION['userID']) || !isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: ../admin_login/admin_login.php?error=Please login to access the dashboard");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch all staff from the Staff table
$sql = "SELECT * FROM Staff";
$result = $conn->query($sql);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Staff</title>
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
    <?php include('../sidebar/admin_sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container">
            <h1 class="mb-4">Manage Staff</h1>
            <!-- Success or error message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <!-- Staff Table -->
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are staff records in the database
                    if ($result->num_rows > 0) {
                        // Output data for each row
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['staffID'] . "</td>"; // Assuming staffID is the primary key
                            echo "<td>" . htmlspecialchars($row['staffName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['staffUsername']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['staffEmail']) . "</td>";
                            echo "<td>
                                <a href='edit_staff.php?id=" . $row['staffID'] . "' class='btn btn-sm btn-warning'>Edit</a>
                                <!-- Delete Button that triggers the modal -->
                                <a href='#' class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal" . $row['staffID'] . "'>Delete</a>
                              </td>";
                            echo "</tr>";

                            // Modal for confirmation
                            echo "
                            <div class='modal fade' id='deleteModal" . $row['staffID'] . "' tabindex='-1' aria-labelledby='deleteModalLabel" . $row['staffID'] . "' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='deleteModalLabel" . $row['staffID'] . "'>Confirm Deletion</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p><strong>Are you sure you want to delete this staff member?</strong></p>
                                            <p>This action <span class='text-danger'>cannot</span> be undone. Once deleted, all the staff's data will be permanently removed.</p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                            <a href='delete_staff.php?id=" . $row['staffID'] . "' class='btn btn-danger'>Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No staff members found</td></tr>";
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
