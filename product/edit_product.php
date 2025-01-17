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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
                <div class="container py-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="productName"
                                value="<?php echo htmlspecialchars($product['productName']); ?>" required>
                        </div>

                        <!-- Brand -->
                        <div class="col-md-6">
                            <label for="productBrand" class="form-label">Brand</label>
                            <select class="form-control" id="productBrand" name="productBrand"
                                onchange="toggleOtherInput('productBrand')" required>
                                <option value="" disabled>Select Product Brand</option>
                                <option value="Apple" <?php echo isset($product['productBrand']) && $product['productBrand'] == 'Apple' ? 'selected' : ''; ?>>Apple</option>
                                <option value="Samsung" <?php echo isset($product['productBrand']) && $product['productBrand'] == 'Samsung' ? 'selected' : ''; ?>>Samsung</option>
                                <option value="Huawei" <?php echo isset($product['productBrand']) && $product['productBrand'] == 'Huawei' ? 'selected' : ''; ?>>Huawei</option>

                                <?php
                                // Define valid brands
                                $validBrands = ['Apple', 'Samsung', 'Huawei'];
                                $otherBrand = '';
                                if (isset($product['productBrand']) && !in_array($product['productBrand'], $validBrands) && !empty($product['productBrand'])) {
                                    $otherBrand = htmlspecialchars($product['productBrand']);
                                }

                                // Show the custom brand option if the product brand is not in the predefined list
                                if (!empty($otherBrand)): ?>
                                    <option value="<?php echo $otherBrand; ?>" <?php echo isset($product['productBrand']) && $product['productBrand'] == $otherBrand ? 'selected' : ''; ?>>
                                        <?php echo $otherBrand; ?>
                                    </option>
                                <?php endif; ?>

                                <!-- Always show the "Other" option for users to manually enter a brand -->
                                <option value="Other" <?php echo isset($product['productBrand']) && $product['productBrand'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <input type="text" class="form-control mt-2" id="productBrandOther" name="productBrandOther"
                                value="<?php echo isset($product['productBrand']) && $product['productBrand'] == 'Other' ? htmlspecialchars($product['productBrandOther']) : ''; ?>"
                                style="display: none;" placeholder="Enter other brand">
                        </div>
                        <div class="col-md-12">
                            <label for="productDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="productDescription" name="productDescription" rows="2"
                                required><?php echo htmlspecialchars($product['productDescription']); ?></textarea>
                        </div>

                        <!-- Price -->
                        <div class="col-md-6">
                            <label for="productPrice" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">RM</span>
                                <input type="number" class="form-control" id="productPrice" name="productPrice"
                                    step="0.01" value="<?php echo $product['productPrice']; ?>" required>
                            </div>
                        </div>

                        <!-- Screen Size -->
                        <div class="col-md-6">
                            <label for="productScreenSize" class="form-label">Screen Size</label>
                            <select class="form-control" id="productScreenSize" name="productScreenSize"
                                onchange="toggleOtherInput('productScreenSize')" required>
                                <option value="" disabled>Select Screen Size</option>
                                <option value="5.5 inches" <?php echo $product['productScreenSize'] == '5.5 inches' ? 'selected' : ''; ?>>5.5 inches</option>
                                <option value="6.1 inches" <?php echo $product['productScreenSize'] == '6.1 inches' ? 'selected' : ''; ?>>6.1 inches</option>
                                <option value="6.7 inches" <?php echo $product['productScreenSize'] == '6.7 inches' ? 'selected' : ''; ?>>6.7 inches</option>

                                <?php
                                // Define valid screen sizes
                                $validScreenSizes = ['5.5 inches', '6.1 inches', '6.7 inches'];
                                $otherScreenSize = '';
                                // Check if the productScreenSize is not in the predefined list
                                if (!in_array($product['productScreenSize'], $validScreenSizes) && !empty($product['productScreenSize'])) {
                                    $otherScreenSize = htmlspecialchars($product['productScreenSize']);
                                }

                                // Show the custom screen size option if the productScreenSize is not in the predefined list
                                if (!empty($otherScreenSize)): ?>
                                    <option value="<?php echo $otherScreenSize; ?>" <?php echo $product['productScreenSize'] == $otherScreenSize ? 'selected' : ''; ?>>
                                        <?php echo $otherScreenSize . ' inches'; ?>
                                    </option>
                                <?php endif; ?>

                                <!-- Always show the "Other" option for users to manually enter a screen size -->
                                <option value="Other" <?php echo $product['productScreenSize'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="input-group mt-2">
                                <input type="text" class="form-control" id="productScreenSizeOther"
                                    name="productScreenSizeOther"
                                    value="<?php echo $product['productScreenSize'] == 'Other' ? htmlspecialchars($product['productScreenSizeOther']) : ''; ?>"
                                    style="display: none;" placeholder="Enter other screen size">
                                <span class="input-group-text" id="inchText" style="display: none;">"</span>
                            </div>
                        </div>


                        <!-- Battery Capacity -->
                        <div class="col-md-6">
                            <label for="productBatteryCapacity" class="form-label">Battery Capacity</label>
                            <select class="form-control" id="productBatteryCapacity" name="productBatteryCapacity"
                                onchange="toggleOtherInput('productBatteryCapacity')" required>
                                <option value="" disabled>Select Battery Capacity</option>
                                <option value="3000 mAh" <?php echo $product['productBatteryCapacity'] == '3000 mAh' ? 'selected' : ''; ?>>3000 mAh</option>
                                <option value="4000 mAh" <?php echo $product['productBatteryCapacity'] == '4000 mAh' ? 'selected' : ''; ?>>4000 mAh</option>
                                <option value="5000 mAh" <?php echo $product['productBatteryCapacity'] == '5000 mAh' ? 'selected' : ''; ?>>5000 mAh</option>

                                <?php
                                // Define valid battery capacities
                                $validBatteryCapacities = ['3000 mAh', '4000 mAh', '5000 mAh'];
                                $otherBatteryCapacity = '';
                                // Check if the productBatteryCapacity is not in the predefined list
                                if (!in_array($product['productBatteryCapacity'], $validBatteryCapacities) && !empty($product['productBatteryCapacity'])) {
                                    $otherBatteryCapacity = htmlspecialchars($product['productBatteryCapacity']);
                                }

                                // Show the custom battery capacity option if the productBatteryCapacity is not in the predefined list
                                if (!empty($otherBatteryCapacity)): ?>
                                    <option value="<?php echo $otherBatteryCapacity; ?>" <?php echo $product['productBatteryCapacity'] == $otherBatteryCapacity ? 'selected' : ''; ?>>
                                        <?php echo $otherBatteryCapacity; ?>
                                    </option>
                                <?php endif; ?>

                                <!-- Always show the "Other" option for users to manually enter a battery capacity -->
                                <option value="Other" <?php echo $product['productBatteryCapacity'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="input-group mt-2">
                                <input type="text" class="form-control" id="productBatteryCapacityOther"
                                    name="productBatteryCapacityOther"
                                    value="<?php echo $product['productBatteryCapacity'] == 'Other' ? htmlspecialchars($product['productBatteryCapacityOther']) : ''; ?>"
                                    style="display: none;" placeholder="Enter other battery capacity">
                                <span class="input-group-text" id="mAhText" style="display: none;">mAh</span>
                            </div>
                        </div>

                        <!-- Camera Specs -->
                        <div class="col-md-6">
                            <label for="productCameraSpecs" class="form-label">Camera Specs</label>
                            <select class="form-control" id="productCameraSpecs" name="productCameraSpecs"
                                onchange="toggleOtherInput('productCameraSpecs')" required>
                                <option value="" disabled>Select Camera Specs</option>
                                <option value="12MP" <?php echo $product['productCameraSpecs'] == '12MP' ? 'selected' : ''; ?>>12MP</option>
                                <option value="48MP" <?php echo $product['productCameraSpecs'] == '48MP' ? 'selected' : ''; ?>>48MP</option>
                                <option value="108MP" <?php echo $product['productCameraSpecs'] == '108MP' ? 'selected' : ''; ?>>108MP</option>

                                <?php
                                // Define valid camera specs
                                $validCameraSpecs = ['12MP', '48MP', '108MP'];
                                $otherCameraSpecs = '';
                                // Check if the productCameraSpecs is not in the predefined list
                                if (!in_array($product['productCameraSpecs'], $validCameraSpecs) && !empty($product['productCameraSpecs'])) {
                                    $otherCameraSpecs = htmlspecialchars($product['productCameraSpecs']);
                                }

                                // Show the custom camera specs option if the productCameraSpecs is not in the predefined list
                                if (!empty($otherCameraSpecs)): ?>
                                    <option value="<?php echo $otherCameraSpecs; ?>" <?php echo $product['productCameraSpecs'] == $otherCameraSpecs ? 'selected' : ''; ?>>
                                        <?php echo $otherCameraSpecs; ?>
                                    </option>
                                <?php endif; ?>

                                <!-- Always show the "Other" option for users to manually enter camera specs -->
                                <option value="Other" <?php echo $product['productCameraSpecs'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                            <div class="input-group mt-2">
                                <input type="text" class="form-control" id="productCameraSpecsOther"
                                    name="productCameraSpecsOther"
                                    value="<?php echo $product['productCameraSpecs'] == 'Other' ? htmlspecialchars($product['productCameraSpecsOther']) : ''; ?>"
                                    style="display: none;" placeholder="Enter other camera specifications">
                                <span class="input-group-text" id="mpText" style="display: none;">MP</span>
                            </div>
                        </div>


                        <!-- Processor -->
                        <div class="col-md-6">
                            <label for="productProcessor" class="form-label">Processor</label>
                            <input type="text" class="form-control" id="productProcessor" name="productProcessor"
                                value="<?php echo htmlspecialchars($product['productProcessor']); ?>" required>
                        </div>

                        <!-- Operating System -->
                        <div class="col-md-6">
                            <label for="productOS" class="form-label">Operating System</label>
                            <input type="text" class="form-control" id="productOS" name="productOS"
                                value="<?php echo htmlspecialchars($product['productOS']); ?>" required>
                        </div>

                        <!-- Release Date -->
                        <div class="col-md-6">
                            <label for="productReleaseDate" class="form-label">Release Date</label>
                            <input type="date" class="form-control" id="productReleaseDate" name="productReleaseDate"
                                value="<?php echo htmlspecialchars($product['productReleaseDate']); ?>" required>
                        </div>

                        <!-- Product Image -->
                        <div class="col-md-12">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="productImage" name="productImage"
                                accept="image/*">
                            <?php if (!empty($currentImage) && file_exists('../uploads/' . $currentImage)): ?>
                                <div class="mt-2">
                                    <a href='../uploads/<?php echo htmlspecialchars($currentImage); ?>'
                                        data-fancybox='gallery'>
                                        <img src='../uploads/<?php echo htmlspecialchars($currentImage); ?>'
                                            alt='Product Image' class='product-image' style='max-width: 200px;'>
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="mt-2 text-muted">No Image Available</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="container-fluid py-4">
                        <div class="card shadow-lg border-0 rounded-3">
                            <div class="card-header bg-light py-3">
                                <h5 class="card-title mb-0">Product Variants</h5>
                            </div>
                            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                                <div class="accordion" id="variantAccordion">
                                    <?php foreach ($productVariants as $index => $variant): ?>
                                        <div class="accordion-item border mb-3 rounded-3">
                                            <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?> fw-bold text-dark" type="button"
                                                data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>"
                                                aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $index; ?>">
                                                <?php echo $variant['productColor']; ?> -
                                                <?php echo $variant['productStorage'] == '1024' ? '1TB' : $variant['productStorage'] . 'GB'; ?> -
                                                <?php echo $variant['productRam']; ?>GB RAM
                                            </button>
                                            </h2>
                                            <div id="collapse<?php echo $index; ?>"
                                                class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>"
                                                aria-labelledby="heading<?php echo $index; ?>"
                                                data-bs-parent="#variantAccordion">
                                                <div class="accordion-body bg-light">
                                                    <input type="hidden" name="variantID[]" value="<?php echo $variant['variantID']; ?>">
                                                    <div class="row g-4">
                                                        <div class="col-md-6">
                                                            <div class="form-floating">
                                                                <select class="form-select" id="variantColor<?php echo $index; ?>"
                                                                    name="variantColor[<?php echo $index; ?>]"
                                                                    onchange="toggleVariantColorOtherInput(<?php echo $index; ?>)"
                                                                    required>
                                                                    <option value="Black" <?php echo $variant['productColor'] == 'Black' ? 'selected' : ''; ?>>Black</option>
                                                                    <option value="White" <?php echo $variant['productColor'] == 'White' ? 'selected' : ''; ?>>White</option>
                                                                    <option value="Blue" <?php echo $variant['productColor'] == 'Blue' ? 'selected' : ''; ?>>Blue</option>
                                                                    <?php if (!in_array($variant['productColor'], ['Black', 'White', 'Blue', 'Other'])): ?>
                                                                        <option value="<?php echo $variant['productColor']; ?>" selected>
                                                                            <?php echo $variant['productColor']; ?>
                                                                        </option>
                                                                    <?php endif; ?>
                                                                    <option value="Other" <?php echo $variant['productColor'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                                                </select>
                                                                <label for="variantColor<?php echo $index; ?>">Color</label>
                                                            </div>
                                                            <input type="text"
                                                                class="form-control mt-2"
                                                                id="variantColorOther<?php echo $index; ?>"
                                                                name="variantColorOther[<?php echo $index; ?>]"
                                                                value="<?php echo ($variant['productColor'] == 'Other') ? htmlspecialchars($variant['productColorOther'] ?? '') : ''; ?>"
                                                                style="display: <?php echo ($variant['productColor'] == 'Other') ? 'block' : 'none'; ?>;"
                                                                placeholder="Enter other color">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-floating">
                                                                <select class="form-select" id="variantStorage<?php echo $index; ?>"
                                                                    name="variantStorage[<?php echo $index; ?>]"
                                                                    required>
                                                                    <option value="64" <?php echo $variant['productStorage'] == '64' ? 'selected' : ''; ?>>64GB</option>
                                                                    <option value="128" <?php echo $variant['productStorage'] == '128' ? 'selected' : ''; ?>>128GB</option>
                                                                    <option value="256" <?php echo $variant['productStorage'] == '256' ? 'selected' : ''; ?>>256GB</option>
                                                                    <option value="512" <?php echo $variant['productStorage'] == '512' ? 'selected' : ''; ?>>512GB</option>
                                                                    <option value="1024" <?php echo $variant['productStorage'] == '1024' ? 'selected' : ''; ?>>1TB</option>
                                                                </select>
                                                                <label for="variantStorage<?php echo $index; ?>">Storage</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-floating">
                                                                <select class="form-select" id="variantRam<?php echo $index; ?>"
                                                                    name="variantRam[<?php echo $index; ?>]"
                                                                    required>
                                                                    <option value="4" <?php echo $variant['productRam'] == '4' ? 'selected' : ''; ?>>4GB</option>
                                                                    <option value="8" <?php echo $variant['productRam'] == '8' ? 'selected' : ''; ?>>8GB</option>
                                                                    <option value="16" <?php echo $variant['productRam'] == '16' ? 'selected' : ''; ?>>16GB</option>
                                                                    <?php if (!in_array($variant['productRam'], ['4', '8', '16', 'Other'])): ?>
                                                                        <option value="<?php echo $variant['productRam']; ?>" selected>
                                                                            <?php echo $variant['productRam']; ?>GB
                                                                        </option>
                                                                    <?php endif; ?>
                                                                    <option value="Other" <?php echo $variant['productRam'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                                                </select>
                                                                <label for="variantRam<?php echo $index; ?>">RAM</label>
                                                            </div>
                                                            <input type="text"
                                                                class="form-control mt-2"
                                                                id="variantRamOther<?php echo $index; ?>"
                                                                name="variantRamOther[<?php echo $index; ?>]"
                                                                value="<?php echo ($variant['productRam'] == 'Other') ? htmlspecialchars($variant['productRamOther'] ?? '') : ''; ?>"
                                                                style="display: <?php echo ($variant['productRam'] == 'Other') ? 'block' : 'none'; ?>;"
                                                                placeholder="Enter other RAM">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-floating">
                                                                <input type="number" class="form-control" id="variantStock<?php echo $index; ?>" name="variantStock[<?php echo $index; ?>]" value="<?php echo $variant['productStock']; ?>" required>
                                                                <label for="variantStock<?php echo $index; ?>">Stock</label>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Center-aligned Buttons -->
                <div class="d-flex justify-content-end gap-3 mt-4">
                    <button class="btn btn-outline-secondary" onclick="location.href='manage_product.php'; return false;">
                        <i class="bi bi-arrow-left me-2"></i> Back
                    </button>
                    <button type="submit" class="btn btn-warning px-4">
                        <i class="bi bi-pencil-square me-2"></i> Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <script>
        $(document).ready(function () {
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
    <script>
        function toggleOtherInput(fieldId) {
            const selectElement = document.getElementById(fieldId);
            const otherInput = document.getElementById(fieldId + 'Other');

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

                // Hide corresponding span text based on the field
                if (fieldId === 'productScreenSize') {
                    inchText.style.display = 'none'; // Hide the inch symbol for screen size
                } else if (fieldId === 'productCameraSpecs') {
                    mpText.style.display = 'none'; // Hide MP for camera specs
                } else if (fieldId === 'productRam') {
                    gbText.style.display = 'none'; // Hide GB for RAM
                }
                else if (fieldId === 'productBatteryCapacity') {
                    mAhText.style.display = 'none';
                }
            }
        }
    </script>
    <script>
        // Handle color "Other" option for variantColor dropdown
        function toggleVariantColorOtherInput(index) {
            const selectElement = document.getElementById('variantColor' + index);
            const otherInput = document.getElementById('variantColorOther' + index);

            if (selectElement.value === 'Other') {
                otherInput.style.display = 'block';
                otherInput.required = true;
            } else {
                otherInput.style.display = 'none';
                otherInput.required = false;
            }
        }

        // Update the select field to use the input value if 'Other' is selected
        function updateColorValueBeforeSubmit(index) {
            const selectElement = document.getElementById('variantColor' + index);
            const otherInput = document.getElementById('variantColorOther' + index);

            if (selectElement.value === 'Other' && otherInput.value.trim() !== '') {
                const newOption = document.createElement('option');
                newOption.value = otherInput.value.trim();
                newOption.text = otherInput.value.trim();
                selectElement.add(newOption);
                selectElement.selectedIndex = selectElement.length - 1;
            }
        }

        // Handle "Other" option for variantRam dropdown
        function toggleVariantRamOtherInput(index) {
            const selectElement = document.getElementById('variantRam' + index);
            const otherInput = document.getElementById('variantRamOther' + index);

            if (selectElement.value === 'Other') {
                otherInput.style.display = 'block';
                otherInput.required = true;
            } else {
                otherInput.style.display = 'none';
                otherInput.required = false;
            }
        }

        // Update the select field to use the input value if 'Other' is selected
        function updateRamValueBeforeSubmit(index) {
            const selectElement = document.getElementById('variantRam' + index);
            const otherInput = document.getElementById('variantRamOther' + index);

            if (selectElement.value === 'Other' && otherInput.value.trim() !== '') {
                const newOption = document.createElement('option');
                newOption.value = otherInput.value.trim();
                newOption.text = otherInput.value.trim();
                selectElement.add(newOption);
                selectElement.selectedIndex = selectElement.length - 1;
            }
        }

        // Ensure 'Other' input values are included before form submission
        document.querySelector('form').addEventListener('submit', function () {
            <?php foreach ($productVariants as $index => $variant): ?>
                updateColorValueBeforeSubmit(<?php echo $index; ?>); // Update color
                updateRamValueBeforeSubmit(<?php echo $index; ?>);   // Update RAM
            <?php endforeach; ?>
        });

        // Event listeners for dropdown changes
        document.querySelectorAll('select[id^="variantColor"]').forEach(select => {
            select.addEventListener('change', () => {
                const index = select.id.replace('variantColor', '');
                toggleVariantColorOtherInput(index);
            });
        });

        document.querySelectorAll('select[id^="variantRam"]').forEach(select => {
            select.addEventListener('change', () => {
                const index = select.id.replace('variantRam', '');
                toggleVariantRamOtherInput(index);
            });
        });
    </script>
    <script>
        document.querySelector("form").addEventListener("submit", function (event) {
            var batteryCapacity = document.getElementById("productBatteryCapacity").value;
            var cameraSpecs = document.getElementById("productCameraSpecs").value;

            // If 'Other' is selected, append "mAh" or "MP"
            if (batteryCapacity === 'Other') {
                var otherBatteryValue = document.getElementById("productBatteryCapacityOther").value;
                if (otherBatteryValue) {
                    document.getElementById("productBatteryCapacityOther").value = otherBatteryValue + " mAh";
                }
            }

            if (cameraSpecs === 'Other') {
                var otherCameraValue = document.getElementById("productCameraSpecsOther").value;
                if (otherCameraValue) {
                    document.getElementById("productCameraSpecsOther").value = otherCameraValue + "MP";
                }
            }
        });
    </script>
</body>

</html>