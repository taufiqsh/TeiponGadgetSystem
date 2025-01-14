<?php
session_start(); // Start session

$errors = [];

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    // Redirect to the appropriate login page
    header("Location: ../login/login.php?error=Access denied");
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
    foreach ($productVariantStock as $stock) {
        if (empty($stock) || !is_numeric($stock) || $stock < 0) {
            $errors[] = "Product stock must be a non-negative number.";
        }
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

                // Insert multiple variants
                if (!isset($errorMessage)) {
                    $variantSql = "INSERT INTO productvariant (productID,variantName,productColor, productStorage, 
                               productRam, productStock, createdAt, updatedAt)
                               VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
                    $variantStmt = $conn->prepare($variantSql);

                    foreach ($_POST['productColor'] as $index => $color) {
                        $productVariantName = $_POST['variantName'][$index];
                        $storage = $_POST['productStorage'][$index];
                        $ram = $_POST['productRam'][$index];
                        $stock = $_POST['productVariantStock'][$index];

                        // Ensure all fields are valid
                        if (!empty($color) && !empty($storage) && !empty($ram) && is_numeric($stock)) {
                            $variantStmt->bind_param("issisi", $productID, $productVariantName, $color, $storage, $ram, $stock);
                            if (!$variantStmt->execute()) {
                                $errorMessage = "Error adding product variant: " . $conn->error;
                                break;
                            }
                        } else {
                            $errorMessage = "Invalid variant data provided.";
                            break;
                        }
                    }

                    if (!isset($errorMessage)) {
                        $successMessage = "Product and all variants added successfully!";
                    }
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

<style>
    .variant {
        border: 1px solid #ddd;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
    }
</style>

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
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
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

                    <h2>Product Variants</h2>
                    <div id="variantContainer">
                        <div class="variant">
                            <div class="mb-3">
                                <label for="variantName" class="form-label">Variant Name</label>
                                <input type="text" class="form-control" name="variantName[]" placeholder="e.g., Black 64GB 8GB RAM" required>
                            </div>
                            <div class="mb-3">
                                <label for="productColor" class="form-label">Color</label>
                                <input type="text" class="form-control" name="productColor[]" required>
                            </div>
                            <div class="mb-3">
                                <label for="productStorage" class="form-label">Storage</label>
                                <select class="form-control" name="productStorage[]" required>
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
                                <input type="number" class="form-control" name="productRam[]" required>
                            </div>
                            <div class="mb-3">
                                <label for="productVariantStock" class="form-label">Stock</label>
                                <input type="number" class="form-control" name="productVariantStock[]" required>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" onclick="addVariant()">Add Variant</button>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="submit" class="btn btn-primary">Add Product</button>
                        <button class="btn btn-secondary" onclick="location.href='manage_product.php'; return false;">Back</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        function addVariant() {
            const variantContainer = document.getElementById('variantContainer');
            const newVariant = `
                <div class="variant mt-4 p-3 border rounded">
                    <div class="mb-3">
                        <label for="variantName" class="form-label">Variant Name</label>
                        <input type="text" class="form-control" name="variantName[]" placeholder="e.g., Black 64GB 8GB RAM" required>
                    </div>
                    <div class="mb-3">
                        <label for="productColor" class="form-label">Color</label>
                        <input type="text" class="form-control" name="productColor[]" required>
                    </div>
                    <div class="mb-3">
                        <label for="productStorage" class="form-label">Storage</label>
                        <select class="form-control" name="productStorage[]" required>
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
                        <input type="number" class="form-control" name="productRam[]" placeholder="e.g., 8" required>
                    </div>
                    <div class="mb-3">
                        <label for="productVariantStock" class="form-label">Stock</label>
                        <input type="number" class="form-control" name="productVariantStock[]" placeholder="e.g., 50" required>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm mt-2" onclick="removeVariant(this)">Remove Variant</button>
                </div>`;
            variantContainer.insertAdjacentHTML('beforeend', newVariant);
        }

        function removeVariant(button) {
            const variant = button.closest('.variant');
            variant.remove();
        }
    </script>


</body>

</html>

<?php
// Close the database connection
$conn->close();
?>