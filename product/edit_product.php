<?php
session_start(); // Start session

// Check if admin or staff is logged in
if ((!isset($_SESSION['userID']) || !isset($_SESSION['username']))) {
    header("Location: ../login/login.php?error=Please login to access the dashboard");
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

// Fetch product details
if ($productID) {
    $sql = "SELECT * FROM Product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productID);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $currentImage = $product['productImage']; // Get the current image path
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $productName = $_POST['productName'];
    $productDescription = $_POST['productDescription'];
    $productPrice = $_POST['productPrice'];
    $productStock = $_POST['productStock'];

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
                $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            }
        } else {
            $error = "The uploaded file is not a valid image.";
        }
    } else {
        // If no new image, keep the existing one
        $imagePath = $currentImage ?? null; // Use the current image if available
    }

    // Update product details with the new image
    $sql = "UPDATE Product SET productName = ?, productDescription = ?, productPrice = ?, productStock = ?, productImage = ? WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdiss", $productName, $productDescription, $productPrice, $productStock, $imagePath, $productID);

    if ($stmt->execute()) {
        header("Location: manage_product.php?success=Product updated successfully");
        exit();
    } else {
        $error = "Error updating product: " . $stmt->error;
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
                <div class="mb-3">
                    <label for="productName" class="form-label">Product Name</label>
                    <input type="text" class="form-control" id="productName" name="productName" value="<?php echo htmlspecialchars($product['productName']); ?>" required>
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
                <!-- Image Upload Field -->
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

                <!-- Center-aligned Buttons with Spacing -->
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
</body>

</html>