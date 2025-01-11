<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    header("Location: ../login/login.php?error=Access denied");
    exit();
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if (isset($_SESSION['adminID'])) {
    $userType = 'Admin';
    $userName = $_SESSION['username'];
} else if (!isset($_SESSION['adminID'])) {
    $userType = 'Staff';
    $userName = $_SESSION['username'];
}

$productID = $_GET['id'] ?? null;

// Fetch product details and product variants
if ($productID) {
    $sql = "
    SELECT p.*, pv.variantID, pv.productColor, pv.productStorage, pv.productRam, pv.productStock
    FROM Product p
    LEFT JOIN ProductVariant pv ON p.productID = pv.productID
    WHERE p.productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch product and its variants
    $product = null;
    $productVariants = [];
    while ($row = $result->fetch_assoc()) {
        if (!$product) {
            // Assign main product details
            $product = $row;
        }
        // Collect product variants
        $productVariants[] = [
            'variantID' => $row['variantID'],
            'productColor' => $row['productColor'],
            'productStorage' => $row['productStorage'],
            'productRam' => $row['productRam'],
            'productStock' => $row['productStock'],
        ];
    }
    $currentImage = $product['productImage']; // Get the current image path
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['productName'];
    $productBrand = $_POST['productBrand'];
    $productPrice = $_POST['productPrice'];
    $productDescription = $_POST['productDescription'];
    $productScreenSize = $_POST['productScreenSize'];
    $productBatteryCapacity = $_POST['productBatteryCapacity'];
    $productCameraSpecs = $_POST['productCameraSpecs'];
    $productProcessor = $_POST['productProcessor'];
    $productOS = $_POST['productOS'];
    $productReleaseDate = $_POST['productReleaseDate'];
    $productUpdatedAt = date("Y-m-d H:i:s");

    // Check if a new image is uploaded
    if (isset($_FILES['productImage']) && $_FILES['productImage']['error'] == 0) {
        // Directory where images will be uploaded
        $targetDir = $_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/uploads/';

        // Generate a unique file name using timestamp
        $fileName = time() . '_' . basename($_FILES['productImage']['name']);
        $targetFile = $targetDir . $fileName;

        // Get the file extension and ensure it's lowercase
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an image
        $check = getimagesize($_FILES['productImage']['tmp_name']);
        if ($check !== false) {
            // Allowed file types
            $allowedTypes = ['jpg', 'jpeg', 'png'];

            if (in_array($imageFileType, $allowedTypes)) {
                // Move the uploaded file to the target directory
                if (move_uploaded_file($_FILES['productImage']['tmp_name'], $targetFile)) {
                    // Set the image path for storing in the database
                    $imagePath = $fileName;
                } else {
                    $error = "Error uploading the image.";
                }
            } else {
                $error = "Only JPG, JPEG, PNG files are allowed.";
            }
        } else {
            $error = "The uploaded file is not a valid image.";
        }
    } else {
        // If no new image, keep the existing one
        $imagePath = $currentImage ?? null; // Use the current image if available
    }

    $conn->begin_transaction(); // Start a transaction

    try {
        // Update Product table
        $sql = "UPDATE Product 
        SET productName = ?, 
            productBrand = ?, 
            productPrice = ?, 
            productDescription = ?, 
            productScreenSize = ?, 
            productBatteryCapacity = ?, 
            productCameraSpecs = ?, 
            productProcessor = ?, 
            productOS = ?, 
            productReleaseDate = ?, 
            productImage = ?, 
            productUpdatedAt = ? 
        WHERE productID = ?";
        $stmt = $conn->prepare($sql);

        // Correct the `bind_param` placeholders and data types.
        // Example: "ssssssssssssi" for 11 strings and 1 integer
        $stmt->bind_param(
            "ssdsssssssssi",
            $productName,
            $productBrand,
            $productPrice,
            $productDescription,
            $productScreenSize,
            $productBatteryCapacity,
            $productCameraSpecs,
            $productProcessor,
            $productOS,
            $productReleaseDate,
            $imagePath,
            $productUpdatedAt,
            $productID
        );

        // Execute the statement
        $stmt->execute();

        // Update ProductVariant table
        $sqlVariant = "UPDATE ProductVariant 
                       SET productColor = ?, productStorage = ?, productRam = ?, productStock = ? 
                       WHERE variantID = ?";
        $stmtVariant = $conn->prepare($sqlVariant);

        foreach ($_POST['variantColor'] as $index => $color) {
            $storage = $_POST['variantStorage'][$index];
            $ram = $_POST['variantRam'][$index];
            $stock = $_POST['variantStock'][$index];
            $variantID = $_POST['variantID'][$index];

            $stmtVariant->bind_param("siisi", $color, $storage, $ram, $stock, $variantID);
            $stmtVariant->execute();
        }

        $conn->commit(); // Commit the transaction
        header("Location: manage_product.php?success=Product and variants updated successfully");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback the transaction on error
        $error = "Error updating product and variants: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
    <style>
        /* Optional: Add a background color to the body for a softer look */
        body {
            background-color: #f8f9fa;
        }

        /* Add some spacing to the top of the form */
        .form-container {
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Optional: Add some space between the form fields */
        .form-container .form-label {
            margin-bottom: 10px;
        }

        /* Style the heading */
        h1 {
            font-size: 2rem;
            font-weight: bold;
            color: #343a40;
        }

        /* Optional: Style the alert message */
        .alert {
            margin-bottom: 20px;
        }

        .btn {
            min-width: 120px;
        }

        /* Optional: Give some spacing between buttons */
        .d-flex {
            gap: 10px;
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

        .accordion-item {
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .accordion-button {
            font-weight: bold;
            background-color: #f8f9fa;
        }

        .accordion-button:hover {
            background-color: #e9ecef;
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

    <div class="main-content d-flex justify-content-center align-items-center min-vh-100">
        <div class="container form-container">
            <h1 class="text-center mb-4">Edit Product</h1>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Product Fields -->
                <div class="mb-3">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="productName" value="<?php echo htmlspecialchars($product['productName']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productBrand" class="form-label">Brand</label>
                    <input type="text" class="form-control" id="productBrand" name="productBrand" value="<?php echo htmlspecialchars($product['productBrand']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productDescription" class="form-label">Description</label>
                    <textarea class="form-control" id="productDescription" name="productDescription" rows="3" required><?php echo htmlspecialchars($product['productDescription']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="productPrice" class="form-label">Price</label>
                    <input type="number" class="form-control" id="productPrice" name="productPrice" step="0.01" value="<?php echo $product['productPrice']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productStock" class="form-label">Stock</label>
                    <input type="number" class="form-control" id="productStock" name="productStock" value="<?php echo $product['productStock']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productScreenSize" class="form-label">Screen Size</label>
                    <input type="text" class="form-control" id="productScreenSize" name="productScreenSize" value="<?php echo htmlspecialchars($product['productScreenSize']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productBatteryCapacity" class="form-label">Battery Capacity</label>
                    <input type="text" class="form-control" id="productBatteryCapacity" name="productBatteryCapacity" value="<?php echo htmlspecialchars($product['productBatteryCapacity']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productCameraSpecs" class="form-label">Camera Specs</label>
                    <input type="text" class="form-control" id="productCameraSpecs" name="productCameraSpecs" value="<?php echo htmlspecialchars($product['productCameraSpecs']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productProcessor" class="form-label">Processor</label>
                    <input type="text" class="form-control" id="productProcessor" name="productProcessor" value="<?php echo htmlspecialchars($product['productProcessor']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productOS" class="form-label">Operating System</label>
                    <input type="text" class="form-control" id="productOS" name="productOS" value="<?php echo htmlspecialchars($product['productOS']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="productReleaseDate" class="form-label">Release Date</label>
                    <input type="date" class="form-control" id="productReleaseDate" name="productReleaseDate" value="<?php echo htmlspecialchars($product['productReleaseDate']); ?>" required>
                </div>

                <!-- Product Image Field -->
                <div class="mb-3">
                    <label for="productImage" class="form-label">Product Image</label>
                    <input type="file" class="form-control" id="productImage" name="productImage" accept="image/*">
                    <?php
                    if (!empty($currentImage) && file_exists('../uploads/' . $currentImage)) {
                        $imagePath = '../uploads/' . htmlspecialchars($currentImage);
                        echo "<a href='$imagePath' data-fancybox='gallery' data-caption='Product Image'>";
                        echo "<img src='$imagePath' alt='Product Image' class='product-image' style='max-width: 200px; margin-top: 10px;'>";
                        echo "</a>";
                    } else {
                        echo "<p>No Image Available</p>";
                    }
                    ?>
                </div>

                <h3>Product Variants</h3>
                <div style="max-height: 500px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                    <div class="accordion" id="variantAccordion">
                        <?php foreach ($productVariants as $index => $variant): ?>
                            <div class="accordion-item mb-3"> <!-- Add a bottom margin for spacing -->
                                <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                    <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse<?php echo $index; ?>"
                                        aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                                        aria-controls="collapse<?php echo $index; ?>">
                                        Variant <?php echo $index + 1; ?> <!-- Adjusted label -->
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $index; ?>"
                                    class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                    aria-labelledby="heading<?php echo $index; ?>"
                                    data-bs-parent="#variantAccordion">
                                    <div class="accordion-body">
                                        <input type="hidden" name="variantID[]" value="<?php echo $variant['variantID']; ?>"> <!-- Hidden field for variantID -->
                                        <div class="row g-3"> <!-- Use Bootstrap Grid for Layout -->
                                            <div class="col-md-6">
                                                <label for="variantColor<?php echo $index; ?>" class="form-label">Color</label>
                                                <input type="text" class="form-control" id="variantColor<?php echo $index; ?>" name="variantColor[]" value="<?php echo htmlspecialchars($variant['productColor']); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="variantStorage<?php echo $index; ?>" class="form-label">Storage</label>
                                                <input type="number" class="form-control" id="variantStorage<?php echo $index; ?>" name="variantStorage[]" value="<?php echo $variant['productStorage']; ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="variantRam<?php echo $index; ?>" class="form-label">RAM</label>
                                                <input type="number" class="form-control" id="variantRam<?php echo $index; ?>" name="variantRam[]" value="<?php echo $variant['productRam']; ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="variantStock<?php echo $index; ?>" class="form-label">Stock</label>
                                                <input type="number" class="form-control" id="variantStock<?php echo $index; ?>" name="variantStock[]" value="<?php echo $variant['productStock']; ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>




                <!-- Center-aligned Buttons -->
                <div class="d-flex justify-content-end gap-3">
                    <button class="btn btn-secondary" onclick="location.href='manage_product.php'; return false;">Back</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
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
</body>

</html>