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
    }

    .product-image {
      width: 250px;
      height: 250px;
      object-fit: contain;
    }

    .member-image {
      width: 150px;
      height: 165px;
      object-fit: cover;
      border-radius: 20%;
    }

    .about-us-section {
      background-color: #f8f9fa;
      padding: 130px 0;
      /* Increase padding to make it taller */
    }

    .about-us-heading {
      font-size: 2rem;
      font-weight: bold;
      margin-bottom: 30px;
    }

    .container.my-5 {
      padding: 45px 0;
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

  <!-- Product Section -->
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
              <div class="card mb-4">
                <img src="uploads/' . $product['image'] . '" alt="' . $product['name'] . '" class="card-img-top product-image">
                <div class="card-body">
                  <h5 class="card-title">' . $product['name'] . '</h5>
                  <p class="card-text">' . $product['description'] . '</p>
                  <p class="fw-bold">Price: RM ' . $product['price'] . '</p>
                  <a href="#" class="btn btn-outline-primary">Buy</a>
                </div>
              </div>
            </div>';
        }
      } else {
        echo '<p class="text-center">No products available at the moment.</p>';
      }
      ?>
    </div>

    <!-- Navigation buttons -->
    <div class="d-flex justify-content-center gap-4">
      <button id="prevBtn" class="btn btn-secondary" onclick="navigate(-1)">Previous</button>
      <button id="nextBtn" class="btn btn-secondary" onclick="navigate(1)">Next</button>
    </div>
  </div>

  <!-- About Us Section -->
  <section id="team-section" class="about-us-section">
    <div class="container text-center">
      <h2 class="about-us-heading">About Us</h2>
      <div class="row">
        <?php
        $members = [
          ["name" => "Hazik Haikal", "role" => "Team Leader", "image" => "members/hazik.jpeg"],
          ["name" => "Muhammad Naqib", "role" => "Software Requirement Analyst", "image" => "members/naqib.jpeg"],
          ["name" => "Muhammad Mahdi", "role" => "Software Designer Designergner", "image" => "members/mahdi.jpeg"],
          ["name" => "Muhammad Luqman", "role" => "Software Tester", "image" => "members/muizz.jpeg"],
          ["name" => "Eiman Damien", "role" => "Software Developer", "image" => "members/eiman.jpeg"],
          ["name" => "Mohamad Syazmir", "role" => "Software Developer", "image" => "members/syazmir.jpeg"],
          ["name" => "Muhammad Taufiq", "role" => "Software Engineer", "image" => "members/taufiq.jpeg"],
          ["name" => "Syariful Husaini", "role" => "UI/UX Designer", "image" => "members/syariful.jpeg"],
        ];

        foreach ($members as $member) {
          echo '
            <div class="col-md-3 col-sm-6 mb-4">
              <div class="text-center">
                <img src="' . $member['image'] . '" alt="' . $member['name'] . '" class="member-image mb-3">
                <h5>' . htmlspecialchars($member['name']) . '</h5>
                <p>' . htmlspecialchars($member['role']) . '</p>
              </div>
            </div>';
        }
        ?>
      </div>
    </div>
  </section>

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
        item.style.display = index >= startIndex && index <= endIndex ? 'block' : 'none';
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