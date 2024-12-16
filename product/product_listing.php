<?php
session_start(); // Start session

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Fetch all products from the database
$sql = "SELECT productName, productDescription, productPrice, productStock, productImage FROM product";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Listing</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Image styling similar to previous example */
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* Placeholder styling */
        .bd-placeholder-img {
            width: 100%;
            height: 200px;
            background-color: #e9ecef;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .product-card-body {
            padding: 20px;
        }

        .product-price {
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <?php if (isset($_SESSION['customerUsername'])) {
        // Include the customer-specific navbar if logged in
        include('../navbar/customer_navbar.php');
    } else {
        // Include the anonymous navbar if not logged in
        include('../navbar/navbar.php');
    } ?>

    <!-- Main Content -->
    <div class="container my-5">
        <h1 class="mb-4 text-center">Our Products</h1>

        <div class="row g-4">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Use null coalescing operator to avoid passing null to htmlspecialchars
                    $productImage = htmlspecialchars($row['productImage']) ?? '';
                    $productName = htmlspecialchars($row['productName'] ?? 'Unnamed Product');
                    $productDescription = trim($row['productDescription']);
                    $productDescription = htmlspecialchars($productDescription ?: 'No description available.');                    $productPrice = number_format($row['productPrice'] ?? 0, 2);
                    echo '
    <div class="col-md-4">
        <div class="card h-100 shadow-sm product-card">';

                    // Image or Placeholder
                    if ($productImage && file_exists('../uploads/' . $productImage)) {
                        // Display image from the path
                        $imagePath = '../uploads/' . $productImage;
                        echo '<img src="' . $imagePath . '" class="product-image" alt="Product Image">';
                    } else {
                        // Fallback to placeholder if image doesn't exist
                        echo '<svg class="bd-placeholder-img" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Placeholder: No Image" preserveAspectRatio="xMidYMid slice" focusable="false">
                        <title>No Image</title>
                        <rect width="100%" height="100%" fill="#e9ecef"></rect>
                        <text x="50%" y="50%" fill="#6c757d" dy=".3em" text-anchor="middle">No Image</text>
                    </svg>';
                    }

                    // Product Details
                    echo '
            <div class="product-card-body">
                <h5 class="card-title">' . $productName . '</h5>
                <p class="card-text">' . $productDescription . '</p>
                <p class="product-price">Price: RM' . $productPrice . '</p>
            </div>
            <div class="card-footer text-center">
                <a href="#" class="btn btn-primary">Add to Cart</a>
            </div>
        </div>
    </div>';
                }
            }
            ?>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>