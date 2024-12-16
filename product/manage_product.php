<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['adminID']) || !isset($_SESSION['adminUsername'])) && (!isset($_SESSION['staffID']) || !isset($_SESSION['staffUsername']))) {
    // Redirect to the appropriate login page
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

// Determine user type and session details
if (isset($_SESSION['adminID'])) {
    $userType = 'Admin';
    $userName = $_SESSION['adminName'];
} elseif (isset($_SESSION['staffID'])) {
    $userType = 'Staff';
    $userName = $_SESSION['staffUsername'];
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch all products from the Products table
$sql = "SELECT * FROM Product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
    <style>
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Centering the image in Fancybox */
        .fancybox-image {
            max-width: 90%;
            max-height: 80vh;
            margin: 0 auto;
            display: block;
            border-radius: 8px;
            cursor: default;
        }

        .fancybox-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .fancybox-caption {
            text-align: center;
            font-size: 16px;
            margin-top: 10px;
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
            <h1 class="mb-4">Manage Products</h1>
            <!-- Success or error message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            <!-- Add Product Button -->
            <a href="add_product.php" class="btn btn-success mb-3">Add New Product</a>
            <!-- Products Table -->
            <table class="table table-striped table-bordered table-hover">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>Product Name</th>
                        <th>Product Description</th>
                        <th>Product Price</th>
                        <th>Product Stock</th>
                        <th>Product Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if there are products in the database
                    if ($result->num_rows > 0) {
                        // Output data for each row
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['productID'] . "</td>"; // Assuming productID is the primary key
                            echo "<td>" . htmlspecialchars($row['productName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['productDescription']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['productPrice']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['productStock']) . "</td>";
                            echo "<td>";
                            // Display the image if available
                            if (!empty($row['productImage']) && file_exists('../uploads/' . $row['productImage'])) {
                                $imagePath = '../uploads/' . htmlspecialchars($row['productImage']);
                                echo "<a href='$imagePath' data-fancybox='gallery' data-caption='Product Image'>";
                                echo "<img src='$imagePath' alt='Product Image' class='product-image'>";
                                echo "</a>";
                            } else {
                                echo "No Image";
                            }
                            echo "</td>";
                            echo "<td>
                                <a href='edit_product.php?id=" . $row['productID'] . "' class='btn btn-sm btn-warning'>Edit</a>
                                <!-- Delete Button that triggers the modal -->
                                <a href='#' class='btn btn-sm btn-danger' data-bs-toggle='modal' data-bs-target='#deleteModal" . $row['productID'] . "'>Delete</a>
                            </td>";
                            echo "</tr>";

                            // Modal for confirmation
                            echo "
                            <div class='modal fade' id='deleteModal" . $row['productID'] . "' tabindex='-1' aria-labelledby='deleteModalLabel" . $row['productID'] . "' aria-hidden='true'>
                                <div class='modal-dialog'>
                                    <div class='modal-content'>
                                        <div class='modal-header'>
                                            <h5 class='modal-title' id='deleteModalLabel" . $row['productID'] . "'>Confirm Deletion</h5>
                                            <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                                        </div>
                                        <div class='modal-body'>
                                            <p><strong>Are you sure you want to delete this product?</strong></p>
                                            <p>This action <span class='text-danger'>cannot</span> be undone. Once deleted, the product will be permanently removed from the system.</p>
                                        </div>
                                        <div class='modal-footer'>
                                            <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                            <a href='delete_product.php?id=" . $row['productID'] . "' class='btn btn-danger'>Delete</a>
                                        </div>
                                    </div>
                                </div>
                            </div>";
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>No products found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fancybox JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Fancybox for all images with data-fancybox attribute
            $('[data-fancybox="gallery"]').fancybox({
                maxWidth: '90%',
                maxHeight: '80vh',
                closeClickOutside: true, // Close when clicking outside the image
                buttons: ["fullScreen", "close"], // Customize buttons in the lightbox
                loop: true, // Allow looping through images if there are multiple
                // Ensure the image is centered
                fitToView: true,
                padding: 0, // Remove padding for better centering
                zoom: false,
                clickContent: false
            });
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Close database connection
$conn->close();
?>
