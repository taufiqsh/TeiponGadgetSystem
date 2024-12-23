<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');


// Fetch all products from the database
$sql = "SELECT productName, productDescription, productPrice, productImage FROM Product";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Home</title>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    /* Custom styling to ensure all images are 300x300px */
    .product-image {
      width: 300px;
      height: 300px;
      object-fit: contain; /* Ensures the image covers the 300x300px area without distortion */
    }
  </style>
</head>

<body>

  <?php include('navbar/navbar.php'); ?>

  <!-- Hero Section -->
  <section class="bg-dark text-white text-center py-5">
    <div class="container">
      <h1 class="display-4">Teipon Gadget</h1>
    </div>
  </section>

  <!-- Main Content -->
  <div class="container my-5">
    <div class="row" id="productContainer">
      <?php
      if ($result->num_rows > 0) {
        $products = [];
        while ($row = $result->fetch_assoc()) {
          $productName = !empty($row['productName']) ? htmlspecialchars($row['productName']) : 'Unnamed Product';
          $productDescription = !empty($row['productDescription']) ? htmlspecialchars($row['productDescription']) : 'No description available.';
          $productPrice = !empty($row['productPrice']) ? number_format($row['productPrice'], 2) : '0.00';
          $productImage = !empty($row['productImage']) ? htmlspecialchars($row['productImage']) : 'placeholder.jpg';

          $products[] = [
            'name' => $productName,
            'description' => $productDescription,
            'price' => $productPrice,
            'image' => $productImage
          ];
        }

        // Display products in columns
        foreach ($products as $index => $product) {
          echo '
            <div class="col-md-3 col-sm-4 product-item">
              <div class="text-center">
                <img src="uploads/' . $product['image'] . '" alt="' . $product['name'] . '" class="img-fluid product-image mb-3">
                <h5>' . $product['name'] . '</h5>
                <p>' . $product['description'] . '</p>
                <p class="fw-bold">Price: RM ' . $product['price'] . '</p>
                <a href="#" class="btn btn-outline-primary">Buy</a>
              </div>
            </div>';
        }
      } else {
        echo '<p class="text-center">No products available at the moment.</p>' . $conn->error;
      }
      ?>
    </div>

    <!-- Navigation buttons -->
    <div class="d-flex justify-content-center gap-4">
      <button id="prevBtn" class="btn btn-secondary" onclick="navigate(-1)">Previous</button>
      <button id="nextBtn" class="btn btn-secondary" onclick="navigate(1)">Next</button>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-light text-center py-4">
    <p class="mb-0">© Teipon Gadget. All Rights Reserved.</p>
  </footer>

  <script src="assets/js/bootstrap.bundle.min.js"></script>
  <script>
    const productsPerPage = 4; // Number of products shown per page
    let currentPage = 1;

    // Function to navigate through pages
    function navigate(direction) {
      currentPage += direction;

      const items = document.querySelectorAll('.product-item');
      const totalItems = items.length;
      const totalPages = Math.ceil(totalItems / productsPerPage);

      // Ensure currentPage stays within bounds
      if (currentPage < 1) currentPage = 1;
      if (currentPage > totalPages) currentPage = totalPages;

      // Show/Hide products based on the current page
      items.forEach((item, index) => {
        const startIndex = (currentPage - 1) * productsPerPage;
        const endIndex = startIndex + productsPerPage - 1;
        if (index >= startIndex && index <= endIndex) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });

      // Disable/enable buttons based on the page
      document.getElementById('prevBtn').disabled = currentPage === 1;
      document.getElementById('nextBtn').disabled = currentPage === totalPages;
    }

    // Initialize product visibility on page load
    window.onload = function() {
      navigate(0); // Show the first page by default
    };
  </script>
</body>

</html>

<?php
// $conn->close();
?>
