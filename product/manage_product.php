<?php 
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    // Redirect to the appropriate login page
    header("Location: ../login/login.php?error=Please login to access the dashboard");
    exit();
}

// Determine user type and session details
if (isset($_SESSION['adminID'])) {
    $userType = 'Admin';
    $userName = $_SESSION['username'];
} else {
    $userType = 'Staff';
    $userName = $_SESSION['username'];
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
        /* Custom styles for better spacing */
        .product-image {
            width: 150px;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .card {
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .product-card .card-body {
            padding: 20px;
        }

        .product-card .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .btn-actions {
            display: flex;
            justify-content: space-between;
        }

        .btn-actions a {
            margin-right: 10px;
        }

        /* Fancybox settings */
        .fancybox-image {
            max-width: 90%;
            max-height: 80vh;
            margin: 0 auto;
            display: block;
            border-radius: 8px;
            cursor: default;
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
        <div class="container mt-4">
            <h1 class="mb-4 text-center">Manage Products</h1>
            
            <!-- Success or error message -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>
            
            <!-- Add Product Button -->
            <div class="text-right mb-4">
                <a href="add_product.php" class="btn btn-success">Add New Product</a>
            </div>
            
            <!-- Products Grid Layout (Cards) -->
            <div class="row">
                <?php
                // Check if there are products in the database
                if ($result->num_rows > 0) {
                    // Output data for each row in card format
                    while ($row = $result->fetch_assoc()) {
                        echo "<div class='col-md-4'>";
                        echo "<div class='card product-card'>";
                        echo "<div class='card-body'>";
                        echo "<h5 class='card-title'>" . htmlspecialchars($row['productName']) . "</h5>";
                        echo "<p><strong>Brand:</strong> " . htmlspecialchars($row['productBrand']) . "</p>";
                        echo "<p><strong>Price:</strong> RM" . htmlspecialchars($row['productPrice']) . "</p>";
                        echo "<p><strong>Description:</strong> " . htmlspecialchars($row['productDescription']) . "</p>";
                        echo "<p><strong>Release Date:</strong> " . htmlspecialchars($row['productReleaseDate']) . "</p>";
                        
                        // Display the image if available
                        if (!empty($row['productImage']) && file_exists('../uploads/' . $row['productImage'])) {
                            $imagePath = '../uploads/' . htmlspecialchars($row['productImage']);
                            echo "<a href='$imagePath' data-fancybox='gallery' data-caption='Product Image'>";
                            echo "<img src='$imagePath' alt='Product Image' class='product-image mb-3'>";
                            echo "</a>";
                        } else {
                            echo "<p>No Image Available</p>";
                        }
                        
                        // Display actions (Edit and Delete buttons)
                        echo "<div class='btn-actions'>";
                        echo "<a href='edit_product.php?id=" . $row['productID'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                        echo "<a href='#' class='btn btn-danger btn-sm' data-bs-toggle='modal' data-bs-target='#deleteModal" . $row['productID'] . "'>Delete</a>";
                        echo "</div>";
                        echo "</div>"; // End of card-body
                        echo "</div>"; // End of card
                        echo "</div>"; // End 

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
                    echo "<p class='text-center' colspan='12'>No products found</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Fancybox JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>

    <script>
        $(document).ready(function() {
            $('[data-fancybox="gallery"]').fancybox({
                maxWidth: '90%',
                maxHeight: '80vh',
                closeClickOutside: true,
                buttons: ["fullScreen", "close"],
                loop: true,
                fitToView: true,
                padding: 0,
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
