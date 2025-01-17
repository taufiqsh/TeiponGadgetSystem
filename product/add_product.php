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
                "sssdsssssss",
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
                    if (!isset($errorMessage)) {
                        $variantSql = "INSERT INTO productvariant (productID,variantName,productColor, productStorage, 
                                   productRam, productStock, createdAt, updatedAt)
                                   VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
                        $variantStmt = $conn->prepare($variantSql);

                        if (empty($_POST['variantName']) || !is_array($_POST['variantName'])) {
                            $errorMessage = "At least one product variant is required.";
                        } else {
                            foreach ($_POST['variantName'] as $index => $variantName) {
                                $color = isset($_POST['productColor'][$index]) ? $_POST['productColor'][$index] : '';
                                $storage = isset($_POST['productStorage'][$index]) ? $_POST['productStorage'][$index] : '';
                                $ram = isset($_POST['productRam'][$index]) ? $_POST['productRam'][$index] : '';
                                $stock = isset($_POST['productVariantStock'][$index]) ?
                                    filter_var($_POST['productVariantStock'][$index], FILTER_VALIDATE_INT) : null;

                                if (empty($variantName)) {
                                    $errorMessage = "Variant name is required for all variants.";
                                    break;
                                }
                                if (empty($color)) {
                                    $errorMessage = "Color is required for all variants.";
                                    break;
                                }
                                if (empty($storage)) {
                                    $errorMessage = "Storage is required for all variants.";
                                    break;
                                }
                                if (empty($ram)) {
                                    $errorMessage = "RAM is required for all variants.";
                                    break;
                                }
                                if ($stock === false || $stock === null) {
                                    $errorMessage = "Valid stock quantity is required for all variants.";
                                    break;
                                }

                                $variantStmt->bind_param("isssii", $productID, $variantName, $color, $storage, $ram, $stock);

                                if (!$variantStmt->execute()) {
                                    $errorMessage = "Error adding product variant: " . $conn->error;
                                    break;
                                }
                            }

                            if (!isset($errorMessage)) {
                                $successMessage = "Product and all variants added successfully!";
                            }
                        }
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        input::placeholder,
        textarea::placeholder {
            color: rgb(157, 159, 161);
            opacity: 1;
        }

        select {
            color: rgba(0, 0, 0, 0.5);
            background-color: white;
            opacity: 1;
        }

        select:invalid {
            color: rgb(157, 159, 161);
        }

        select:valid {
            color: #000;
        }

        select:disabled {
            opacity: 0.5;
        }
    </style>
</head>

<style>
    .variant {
        border: 1px solid #ddd;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 5px;
    }

    .main-content {
        transition: margin-left 0.3s;
        min-height: 100vh;
        background-color: #f8f9fa;
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }
    }

    option {
        color: #000 !important;
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
    <div class="main-content" style="margin-left: 250px;">
        <div class="container-fluid px-4 py-4">
            <div class="row justify-content-center">
                <div class="col-xl-11">
                    <h1 class="mb-4">Add New Product</h1>
                    <!-- Success or error message -->
                    <?php if (isset($successMessage)): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
                    <?php elseif (isset($errorMessage)): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
                    <?php endif; ?>

                    <!-- Add Product Form -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <form id="addProductForm" action="add_product.php" method="POST"
                                enctype="multipart/form-data" onsubmit="return validateForm();">
                                <!-- Product Details -->
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="productName" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" id="productName" name="productName"
                                            placeholder="Enter product name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="productBrand" class="form-label">Product Brand</label>
                                        <select class="form-select" id="productBrand" name="productBrand"
                                            onchange="toggleOtherInput('productBrand')" required>
                                            <option value="" disabled selected>Select Product Brand</option>
                                            <option value="Apple">Apple</option>
                                            <option value="Samsung">Samsung</option>
                                            <option value="Huawei">Huawei</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <input type="text" class="form-control mt-2" id="productBrandOther"
                                            name="productBrandOther" style="display: none;"
                                            placeholder="Enter other brand">
                                    </div>

                                    <div class="col-12">
                                        <label for="productDescription" class="form-label">Product Description</label>
                                        <textarea class="form-control" id="productDescription" name="productDescription"
                                            rows="3" placeholder="Enter product description" required></textarea>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productPrice" class="form-label">Product Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">RM</span>
                                            <input type="number" class="form-control" id="productPrice"
                                                name="productPrice" step="0.01" placeholder="Enter product price"
                                                required>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productScreenSize" class="form-label">Product Screen Size</label>
                                        <select class="form-select" id="productScreenSize" name="productScreenSize"
                                            onchange="toggleOtherInput('productScreenSize')" required>
                                            <option value="" disabled selected>Select Screen Size</option>
                                            <option value="5.5 inches">5.5 inches</option>
                                            <option value="6.1 inches">6.1 inches</option>
                                            <option value="6.7 inches">6.7 inches</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="input-group mt-2">
                                            <input type="text" class="form-control" id="productScreenSizeOther"
                                                name="productScreenSize" style="display: none;"
                                                placeholder="Enter other screen size">
                                            <span class="input-group-text" id="inchText" style="display: none;">"</span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productBatteryCapacity" class="form-label">Product Battery
                                            Capacity</label>
                                        <select class="form-select" id="productBatteryCapacity"
                                            name="productBatteryCapacity"
                                            onchange="toggleOtherInput('productBatteryCapacity')" required>
                                            <option value="" disabled selected>Select Battery Capacity</option>
                                            <option value="3000 mAh">3000 mAh</option>
                                            <option value="4000 mAh">4000 mAh</option>
                                            <option value="5000 mAh">5000 mAh</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="input-group mt-2">
                                            <input type="text" class="form-control" id="productBatteryCapacityOther"
                                                name="productBatteryCapacity" style="display: none;"
                                                placeholder="Enter other battery capacity">
                                            <span class="input-group-text" id="mAhText"
                                                style="display: none;">mAh</span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productCameraSpecs" class="form-label">Product Camera
                                            Specifications</label>
                                        <select class="form-select" id="productCameraSpecs" name="productCameraSpecs"
                                            onchange="toggleOtherInput('productCameraSpecs')" required>
                                            <option value="" disabled selected>Select Camera Specs</option>
                                            <option value="12MP">12MP</option>
                                            <option value="48MP">48MP</option>
                                            <option value="108MP">108MP</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="input-group mt-2">
                                            <input type="text" class="form-control" id="productCameraSpecsOther"
                                                name="productCameraSpecs" style="display: none;"
                                                placeholder="Enter other camera specifications">
                                            <span class="input-group-text" id="mpText" style="display: none;">MP</span>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productProcessor" class="form-label">Product Processor</label>
                                        <input type="text" class="form-control" id="productProcessor"
                                            name="productProcessor" placeholder="Enter product processor">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productOS" class="form-label">Product OS</label>
                                        <input type="text" class="form-control" id="productOS" name="productOS"
                                            placeholder="Enter product OS">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productReleaseDate" class="form-label">Product Release Date</label>
                                        <input type="date" class="form-control" id="productReleaseDate"
                                            name="productReleaseDate">
                                    </div>

                                    <div class="col-md-6">
                                        <label for="productImage" class="form-label">Product Image</label>
                                        <input type="file" class="form-control" id="productImage" name="productImage"
                                            required>
                                    </div>
                                </div>

                                <!-- Product Variants -->
                                <div class="card shadow-sm mt-4">
                                    <div class="card-header">
                                        <h2 class="card-title mb-0">Product Variants</h2>
                                    </div>
                                    <div class="card-body">
                                        <div id="variantContainer">
                                            <div class="variant mb-4">
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label for="variantName" class="form-label">Variant Name</label>
                                                        <input type="text" class="form-control" name="variantName[]"
                                                            placeholder="e.g., Black 64GB 8GB RAM" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="productColor[]" class="form-label">Color</label>
                                                        <select class="form-select" id="productColor[]"
                                                            name="productColor[]" required
                                                            onchange="toggleOtherInput('productColor[]')">
                                                            <option value="" disabled selected>Select Color</option>
                                                            <option value="Black">Black</option>
                                                            <option value="White">White</option>
                                                            <option value="Blue">Blue</option>
                                                            <option value="Other">Other</option>
                                                        </select>

                                                        <div id="productColor[]Other" style="display:none;">
                                                            <div class="input-group mt-2">
                                                                <input type="text" id="productColorOther[]"
                                                                    name="productColor[]" class="form-control"
                                                                    placeholder="Enter custom color"
                                                                    aria-label="Enter custom color" />
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="productStorage" class="form-label">Storage</label>
                                                        <select class="form-select" name="productStorage[]" required>
                                                            <option value="">Select Storage</option>
                                                            <option value="64">64GB</option>
                                                            <option value="128">128GB</option>
                                                            <option value="256">256GB</option>
                                                            <option value="512">512GB</option>
                                                            <option value="1024">1TB</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="productRam[]" class="form-label">RAM</label>
                                                        <select class="form-select" id="productRam[]"
                                                            name="productRam[]" required
                                                            onchange="toggleOtherInput('productRam[]')">
                                                            <option value="" disabled selected>Select RAM</option>
                                                            <option value="4">4GB</option>
                                                            <option value="8">8GB</option>
                                                            <option value="16">16GB</option>
                                                            <option value="Other">Other</option>
                                                        </select>
                                                        <div id="productRam[]Other" style="display:none;">
                                                            <div class="input-group mt-2">
                                                                <input type="number" id="productRamOther[]"
                                                                    name="productRam[]" class="form-control"
                                                                    placeholder="Enter RAM size"
                                                                    aria-label="Enter RAM size" />
                                                                <span class="input-group-text">GB</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label for="productVariantStock"
                                                            class="form-label">Stock</label>
                                                        <input type="number" class="form-control"
                                                            name="productVariantStock[]" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-warning mt-3 text-dark"
                                            onclick="addVariant()">
                                            <i class="bi bi-plus-circle me-2"></i> Add Variant
                                        </button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-outline-secondary px-4"
                                        onclick="location.href='manage_product.php'; return false;">
                                        <i class="bi bi-arrow-left me-2"></i> Back
                                    </button>
                                    <button type="submit" class="btn btn-success px-4">
                                        <i class="bi bi-save me-2"></i> Add Product
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script src="../assets/js/bootstrap.bundle.min.js"></script>
        <script>
            function toggleOtherInput(fieldId) {
                const selectElement = document.querySelector(`[id="${fieldId}"]`);
                const otherInput = document.querySelector(`[id="${fieldId}Other"]`);

                // For each field, we'll target the corresponding span text
                const inchText = document.getElementById('inchText');
                const mpText = document.getElementById('mpText');
                const gbText = document.getElementById('gbText');
                const mAhText = document.getElementById('mAhText');
                // Check if the value is explicitly 'Other' and show/hide the input accordingly
                if (selectElement.value === 'Other') {
                    otherInput.style.display = 'block';
                    otherInput.name = fieldId; // Assign the correct name to the input
                    selectElement.name = "";  // Remove the name from the select element
                    otherInput.required = true; // Make the input required
                    otherInput.querySelector('input').required = true; // Make the input required when 'Other' is selected

                    // Show corresponding span text based on the field
                    if (fieldId === 'productScreenSize') {
                        inchText.style.display = 'block'; // Show the inch symbol for screen size
                    } else if (fieldId === 'productCameraSpecs') {
                        mpText.style.display = 'block'; // Show MP for camera specs
                    } else if (fieldId === 'productRam') {
                        gbText.style.display = 'block'; // Show GB for RAM
                    }
                    else if (fieldId === 'productBatteryCapacity') {
                        mAhText.style.display = 'block';
                    }

                } else {
                    otherInput.style.display = 'none';
                    otherInput.name = ""; // Clear the name of the input
                    selectElement.name = fieldId; // Restore the name to the select element
                    otherInput.required = false; // Make the input optional
                    otherInput.querySelector('input').required = false; // Make the input optional when not 'Other'

                    // Hide corresponding span text based on the field
                    if (fieldId === 'productScreenSize') {
                        inchText.style.display = 'none'; // Hide the inch symbol for screen size
                    } else if (fieldId === 'productCameraSpecs') {
                        mpText.style.display = 'none'; // Hide MP for camera specs
                    } else if (fieldId === 'productRam') {
                        gbText.style.display = 'none'; // Hide GB for RAM
                    }
                    else if (fieldId === 'productBatteryCapacity') {
                        mAhText.style.display = 'none'; // Hide the mAh symbol for battery capacity
                    }
                }
            }
        </script>
        <script>
            // Form submission handling
            document.querySelector("form").addEventListener("submit", function (event) {
                const variants = document.querySelectorAll('.variant');

                variants.forEach(variant => {
                    const colorSelect = variant.querySelector('.product-color');
                    const customColor = variant.querySelector('.custom-color');
                    if (colorSelect && colorSelect.value === 'Other' && customColor) {
                        colorSelect.disabled = true; // Disable the select to prevent its value from being submitted
                        customColor.name = 'productColor[]';
                    }

                    const ramSelect = variant.querySelector('.product-ram');
                    const customRam = variant.querySelector('.custom-ram');
                    if (ramSelect && ramSelect.value === 'Other' && customRam) {
                        ramSelect.disabled = true; // Disable the select to prevent its value from being submitted
                        customRam.name = 'productRam[]';
                    }
                });
            });

            function initializeVariantListeners(variantElement) {
                const colorSelect = variantElement.querySelector('.product-color');
                const ramSelect = variantElement.querySelector('.product-ram');

                if (colorSelect) {
                    colorSelect.addEventListener('change', function () {
                        const otherInput = this.nextElementSibling;
                        const customInput = otherInput.querySelector('.custom-color');

                        if (this.value === 'Other') {
                            otherInput.style.display = 'block';
                            customInput.name = 'productColor[]';
                            customInput.required = true;
                        } else {
                            otherInput.style.display = 'none';
                            customInput.name = '';
                            customInput.required = false;
                            this.name = 'productColor[]';
                        }
                    });
                }

                if (ramSelect) {
                    ramSelect.addEventListener('change', function () {
                        const otherInput = this.nextElementSibling;
                        const customInput = otherInput.querySelector('.custom-ram');

                        if (this.value === 'Other') {
                            otherInput.style.display = 'block';
                            customInput.name = 'productRam[]';
                            customInput.required = true;
                        } else {
                            otherInput.style.display = 'none';
                            customInput.name = '';
                            customInput.required = false;
                            this.name = 'productRam[]';
                        }
                    });
                }
            }
        </script>
        <script>
            function addVariant() {
                const variantContainer = document.getElementById('variantContainer');
                const newVariant = `
        <div class="variant mt-4 p-4 border rounded bg-light shadow-sm">
            <div class="row g-3">
                <!-- Variant Name -->
                <div class="col-md-12">
                    <label for="variantName" class="form-label">Variant Name</label>
                    <input type="text" class="form-control" name="variantName[]"
                        placeholder="e.g., Black 64GB 8GB RAM" required>
                </div>
                <!-- Product Color -->
                <div class="col-md-6">
                    <label for="productColor" class="form-label">Color</label>
                    <select class="form-select product-color" name="productColor[]" required>
                        <option value="" disabled selected>Select Color</option>
                        <option value="Black">Black</option>
                        <option value="White">White</option>
                        <option value="Blue">Blue</option>
                        <option value="Other">Other</option>
                    </select>

                    <div class="other-input productColorOther" style="display:none;">
                        <div class="input-group mt-2">
                            <input type="text" class="form-control custom-color" 
                                placeholder="Enter custom color" aria-label="Enter custom color" />
                        </div>
                    </div>
                </div>
                <!-- Product Storage -->
                <div class="col-md-6">
                    <label for="productStorage" class="form-label">Storage</label>
                    <select class="form-select" name="productStorage[]" required>
                        <option value="" disabled selected>Select Storage</option>
                        <option value="64">64GB</option>
                        <option value="128">128GB</option>
                        <option value="256">256GB</option>
                        <option value="512">512GB</option>
                        <option value="1024">1TB</option>
                    </select>
                </div>
                <!-- Product RAM -->
                <div class="col-md-6">
                    <label for="productRam" class="form-label">RAM</label>
                    <select class="form-select product-ram" name="productRam[]" required>
                        <option value="" disabled selected>Select RAM</option>
                        <option value="4">4GB</option>
                        <option value="8">8GB</option>
                        <option value="16">16GB</option>
                        <option value="Other">Other</option>
                    </select>
                    <div class="other-input productRamOther" style="display:none;">
                        <div class="input-group mt-2">
                            <input type="number" class="form-control custom-ram"
                                placeholder="Enter RAM size" aria-label="Enter RAM size" />
                            <span class="input-group-text">GB</span>
                        </div>
                    </div>
                </div>

                <!-- Product Stock -->
                <div class="col-md-6">
                    <label for="productVariantStock" class="form-label">Stock</label>
                    <input type="number" class="form-control" name="productVariantStock[]"
                        placeholder="e.g., 50" required>
                </div>
                <!-- Remove Button -->
                <div class="col-12 text-end">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeVariant(this)">
                        Remove Variant
                    </button>
                </div>
            </div>
        </div>`;
                variantContainer.insertAdjacentHTML('beforeend', newVariant);
                const lastVariant = variantContainer.lastElementChild;
                initializeVariantListeners(lastVariant);
            }

            function removeVariant(button) {
                const variant = button.closest('.variant');
                variant.remove();
            }
            function initializeVariantListeners(variantElement) {
                const colorSelect = variantElement.querySelector('.product-color');
                const ramSelect = variantElement.querySelector('.product-ram');

                colorSelect.addEventListener('change', function () {
                    const otherInput = this.nextElementSibling;
                    const customInput = otherInput.querySelector('.custom-color');

                    if (this.value === 'Other') {
                        otherInput.style.display = 'block';
                        customInput.name = 'productColor[]';
                        this.name = '';
                        customInput.required = true;
                    } else {
                        otherInput.style.display = 'none';
                        customInput.name = '';
                        this.name = 'productColor[]';
                        customInput.required = false;
                    }
                });

                ramSelect.addEventListener('change', function () {
                    const otherInput = this.nextElementSibling;
                    const customInput = otherInput.querySelector('.custom-ram');

                    if (this.value === 'Other') {
                        otherInput.style.display = 'block';
                        customInput.name = 'productRam[]';
                        this.name = '';
                        customInput.required = true;
                    } else {
                        otherInput.style.display = 'none';
                        customInput.name = '';
                        this.name = 'productRam[]';
                        customInput.required = false;
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function () {
                const firstVariant = document.querySelector('.variant');
                if (firstVariant) {
                    initializeVariantListeners(firstVariant);
                }
            });

            document.querySelector("form").addEventListener("submit", function (event) {
                const variants = document.querySelectorAll('.variant');

                variants.forEach(variant => {
                    const colorSelect = variant.querySelector('.product-color');
                    const customColor = variant.querySelector('.custom-color');
                    if (colorSelect.value === 'Other' && customColor) {
                        colorSelect.name = '';
                        customColor.name = 'productColor[]';
                    }

                    const ramSelect = variant.querySelector('.product-ram');
                    const customRam = variant.querySelector('.custom-ram');
                    if (ramSelect.value === 'Other' && customRam) {
                        ramSelect.name = '';
                        customRam.name = 'productRam[]';
                        if (customRam.value) {
                            customRam.value = customRam.value; // Ensure the value is set
                        }
                    }
                });
            });
        </script>


</body>

</html>

<?php
// Close the database connection
$conn->close();
?>