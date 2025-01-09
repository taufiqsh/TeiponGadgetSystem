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
} elseif (!isset($_SESSION['adminID'])) {
    $userType = 'Staff';
    $userName = $_SESSION['username'];
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = trim($_POST['productName']);
    $productDescription = trim($_POST['productDescription']);
    $productPrice = $_POST['productPrice'];
    $productStock = $_POST['productStock'];
    $productImage = null;
    $errors = [];

    // Validate all fields
    if (empty($productName)) {
        $errors[] = "Product name is required.";
    }
    if (empty($productDescription)) {
        $errors[] = "Product description is required.";
    }
    if (empty($productPrice) || !is_numeric($productPrice) || $productPrice <= 0) {
        $errors[] = "Product price must be a positive number.";
    }
    if (empty($productStock) || !is_numeric($productStock) || $productStock < 0) {
        $errors[] = "Product stock must be a non-negative number.";
    }
    if (!isset($_FILES['productImage']) || $_FILES['productImage']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Product image is required.";
    }

    // If there are errors, do not proceed
    if (!empty($errors)) {
        $errorMessage = implode('<br>', $errors); // Combine all error messages
    } else {
        // Handle image upload
        if ($_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
            $targetDir = "../uploads/";
            $fileName = time() . '_' . basename($_FILES['productImage']['name']);
            $targetFile = $targetDir . $fileName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

            $check = getimagesize($_FILES['productImage']['tmp_name']);
            if ($check !== false) {
                if (move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFile)) {
                    $productImage = $fileName; // Store the file name for the database
                } else {
                    $errorMessage = "Failed to upload the image.";
                }
            } else {
                $errorMessage = "The uploaded file is not a valid image.";
            }
        }

        // Insert into the database
        if (!isset($errorMessage)) {
            $createdDate = date("Y-m-d H:i:s");
            $sql = "INSERT INTO Product (productName, productDescription, productPrice, productStock, productImage, productCreatedDate)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdiss", $productName, $productDescription, $productPrice, $productStock, $productImage, $createdDate);

            if ($stmt->execute()) {
                $successMessage = "Product added successfully!";
            } else {
                $errorMessage = "Error adding product: " . $conn->error;
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
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
            <h1 class="mb-4">Add New Product</h1>
            <!-- Success or error message -->
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php elseif (isset($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <!-- Add Product Form -->
            <form id="addProductForm" action="add_product.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm();">
                <div class="mb-3">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="productName" required>
                </div>

                <div class="mb-3">
                    <label for="productDescription" class="form-label">Product Description</label>
                    <textarea class="form-control" id="productDescription" name="productDescription" rows="3" required></textarea>
                </div>

                <div class="mb-3">
                    <label for="productPrice" class="form-label">Product Price</label>
                    <input type="number" class="form-control" id="productPrice" name="productPrice" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label for="productStock" class="form-label">Product Stock</label>
                    <input type="number" class="form-control" id="productStock" name="productStock" required>
                </div>

                <div class="mb-3">
                    <label for="productImage" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*" required>
                </div>

                <div class="d-flex justify-content-end gap-3">
                    <button class="btn btn-secondary" onclick="location.href='manage_product.php'; return false;">Back</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <script>
        function validateForm() {
            const productImage = document.getElementById("productImage");
            if (!productImage.files || productImage.files.length === 0) {
                alert("Please upload a product image.");
                return false;
            }
            return true; // Proceed with submission if all fields are filled
        }
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>