<?php
session_start(); // Start session

$errors = [];

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
    $productBrand = trim($_POST['productBrand']);
    $productDescription = trim($_POST['productDescription']);
    $productPrice = $_POST['productPrice'];
    $productScreenSize = $_POST['productScreenSize'];
    $productBatteryCapacity = $_POST['productBatteryCapacity'];
    $productCameraSpecs = $_POST['productCameraSpecs'];
    $productProcessor = $_POST['productProcessor'];
    $productOS = $_POST['productOS'];
    $productReleaseDate = $_POST['productReleaseDate'];
    $productImage = null;

    // Variant data
    $productColor = isset($_POST['productColor']) ? $_POST['productColor'] : '';
    $productStorage = isset($_POST['productStorage']) ? $_POST['productStorage'] : '';
    $productRam = isset($_POST['productRam']) ? $_POST['productRam'] : '';
    $productVariantStock = isset($_POST['productVariantStock']) ? $_POST['productVariantStock'] : '';

    // Validate all fields
    if (empty($productName)) {
        $errors[] = "Product name is required.";
    }
    if (empty($productBrand)) {
        $errors[] = "Product brand is required.";
    }
    if (empty($productDescription)) {
        $errors[] = "Product description is required.";
    }
    if (empty($productPrice) || !is_numeric($productPrice) || $productPrice <= 0) {
        $errors[] = "Product price must be a positive number.";
    }
    if (empty($productVariantStock) || !is_numeric($productVariantStock) || $productVariantStock < 0) {
        $errors[] = "Product stock must be a non-negative number.";
    }
    if (empty($productColor)) {
        $errors[] = "Product color is required.";
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

        // Insert into the Product table
        if (!isset($errorMessage)) {
            $createdDate = date("Y-m-d H:i:s");
            $sql = "INSERT INTO Product (productName, productBrand, productDescription, productPrice, 
                    productScreenSize, productBatteryCapacity, productCameraSpecs, 
                    productProcessor, productOS, productReleaseDate, productImage, 
                    productCreatedAt, productUpdatedAt)
                    VALUES (?,?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssdissssss",
                $productName,
                $productBrand,
                $productDescription,
                $productPrice,
                $productScreenSize,
                $productBatteryCapacity,
                $productCameraSpecs,
                $productProcessor,
                $productOS,
                $productReleaseDate,
                $productImage
            );

            if ($stmt->execute()) {
                $productID = $stmt->insert_id; // Get the ID of the newly inserted product

                // Insert into the productvariant table
                $variantSql = "INSERT INTO productvariant (productID, productColor, productStorage, 
                                           productRam, productStock, createdAt, updatedAt)
                                           VALUES (?, ?, ?, ?, ?, NOW(), NOW())";
                $variantStmt = $conn->prepare($variantSql);

                // Ensure productStorage is treated as a string in the bind_param
                $variantStmt->bind_param("isisi", $productID, $productColor, $productStorage, $productRam, $productVariantStock);

                if ($variantStmt->execute()) {
                    $successMessage = "Product and product variant added successfully!";
                } else {
                    $errorMessage = "Error adding product variant: " . $conn->error;
                }
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
                    <label for="productBrand" class="form-label">Product Brand</label>
                    <input type="text" class="form-control" id="productBrand" name="productBrand" required>
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
                    <label for="productScreenSize" class="form-label">Product Screen Size</label>
                    <input type="text" class="form-control" id="productScreenSize" name="productScreenSize">
                </div>

                <div class="mb-3">
                    <label for="productBatteryCapacity" class="form-label">Product Battery Capacity</label>
                    <input type="text" class="form-control" id="productBatteryCapacity" name="productBatteryCapacity">
                </div>

                <div class="mb-3">
                    <label for="productCameraSpecs" class="form-label">Product Camera Specifications</label>
                    <input type="text" class="form-control" id="productCameraSpecs" name="productCameraSpecs">
                </div>

                <div class="mb-3">
                    <label for="productProcessor" class="form-label">Product Processor</label>
                    <input type="text" class="form-control" id="productProcessor" name="productProcessor">
                </div>

                <div class="mb-3">
                    <label for="productOS" class="form-label">Product OS</label>
                    <input type="text" class="form-control" id="productOS" name="productOS">
                </div>

                <div class="mb-3">
                    <label for="productReleaseDate" class="form-label">Product Release Date</label>
                    <input type="date" class="form-control" id="productReleaseDate" name="productReleaseDate">
                </div>

                <div class="mb-3">
                    <label for="productImage" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="productImage" name="productImage" required>
                </div>

                <h2>Product Variant</h2>

                <div class="mb-3">
                    <label for="productColor" class="form-label">Color</label>
                    <input type="text" class="form-control" id="productColor" name="productColor" required>
                </div>

                <div class="mb-3">
                    <label for="productStorage" class="form-label">Storage</label>
                    <select class="form-control" id="productStorage" name="productStorage" required>
                        <option value="">Select Storage</option>
                        <option value="64">64GB</option>
                        <option value="128">128GB</option>
                        <option value="256">256GB</option>
                        <option value="512">512GB</option>
                        <option value="1">1TB</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="productRam" class="form-label">RAM</label>
                    <input type="number" class="form-control" id="productRam" name="productRam" required>
                </div>

                <div class="mb-3">
                    <label for="productVariantStock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="productVariantStock" name="productVariantStock" required>
                </div>

                <button type="submit" class="btn btn-primary">Add Product</button>
                <button class="btn btn-secondary" onclick="location.href='manage_product.php'; return false;">Back</button>
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