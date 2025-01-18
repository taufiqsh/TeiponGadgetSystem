<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');
//dsdasdasda
// Check if the user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: ../login/login.php?error=Access denied");
    exit();
}
$userID = $_SESSION['userID'];

// Fetch all products from the database
$search = isset($_GET['search']) ? $_GET['search'] : '';
$minPrice = isset($_GET['minPrice']) && is_numeric($_GET['minPrice']) ? intval($_GET['minPrice']) : 0;
$maxPrice = isset($_GET['maxPrice']) && is_numeric($_GET['maxPrice']) ? intval($_GET['maxPrice']) : 10000;
$descriptionFilter = isset($_GET['descriptionFilter']) ? $_GET['descriptionFilter'] : '';

$sql = "SELECT productID, productName, productDescription, productPrice, productImage FROM Product WHERE productPrice BETWEEN ? AND ?";
$params = [$minPrice, $maxPrice];

if ($search) {
    $sql .= " AND productName LIKE ?";
    $likeSearch = "%" . $search . "%";
    $params[] = $likeSearch;
}

if ($descriptionFilter) {
    $sql .= " AND productDescription LIKE ?";
    $likeDescription = "%" . $descriptionFilter . "%";
    $params[] = $likeDescription;
}

$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat("s", count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Home</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="customer_home.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="customer_home.js"></script>
    <script src="../customer/chatbox.js"></script>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- SweetAlert2 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Pass PHP session variables to JavaScript
        window.userID = <?php echo json_encode($_SESSION['userID'] ?? null); ?>;
    </script>
</head>

<body>
    <?php include('../navbar/customer_navbar.php'); ?>

    <!-- Hero Section -->
    <section class="bg-dark text-white text-center py-5">
        <div class="container">
            <h1 class="display-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        </div>
    </section>

    <div class="container filter-bar mt-4">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search for products...">
            </div>
            <div class="col-md-2">
                <input type="number" id="minPriceInput" class="form-control" placeholder="Min Price">
            </div>
            <div class="col-md-2">
                <input type="number" id="maxPriceInput" class="form-control" placeholder="Max Price">
            </div>
            <div class="col-md-3">
                <input type="text" id="descriptionFilterInput" class="form-control" placeholder="Filter by description">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100" id="filterButton">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Product Display -->
    <div class="container my-5">
        <div class="row" id="productList">
            <?php
            // Fetch all products from the database (no filters applied)
            $sql = "SELECT 
            p.productID, 
            p.productName, 
            p.productDescription, 
            p.productPrice, 
            p.productImage,
            MAX(v.productStock) AS maxStock
        FROM Product p
        JOIN productVariant v ON p.productID = v.productID
        GROUP BY p.productID";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $productID = (int)$row['productID'];
                        $productName = htmlspecialchars($row['productName'] ?? "Unknown Product");
                        $productDescription = htmlspecialchars($row['productDescription'] ?? "No description available");
                        $productPrice = is_numeric($row['productPrice']) ? number_format($row['productPrice'], 2) : "0.00";
                        $productImage = htmlspecialchars($row['productImage'] ?? "default.jpg");
                        $maxStock = (int)($row['maxStock'] ?? 0); // Default stock to 0 if null

                        // Determine stock status
                        $isOutOfStock = $maxStock <= 0;
                        $stockStatusText = $isOutOfStock ? "Out of Stock" : "In Stock";

                        echo '
            <div class="col-md-3 col-sm-4 product-item" 
                 data-name="' . strtolower($productName) . '" 
                 data-price="' . $productPrice . '" 
                 data-description="' . strtolower($productDescription) . '">
                <div class="text-center">
                    <img src="../uploads/' . $productImage . '" 
                         alt="' . $productName . '" 
                         class="img-fluid product-image mb-3">
                    <h5>' . $productName . '</h5>
                    <p>' . $productDescription . '</p>
                    <p class="fw-bold">Price: RM ' . $productPrice . '</p>
                    <p class="text-danger">' . $stockStatusText . '</p>
                    <button class="btn btn-primary" 
                            onclick="showProductDetails(' . $productID . ')" 
                            ' . ($isOutOfStock ? 'disabled' : '') . '>
                        Buy
                    </button>
                </div>
            </div>
            ';
                    }
                } else {
                    echo '<p class="text-center">No products available.</p>';
                }

                $stmt->close();
            } else {
                echo '<p class="text-danger text-center">Error: Could not retrieve products.</p>';
            }
            ?>

        </div>
    </div>

    <!-- chatbox -->
    <section id="chatbox-section" style="display: none;">
        <link rel="stylesheet" href="../chatbox/chatbot.css">
        <div id="chatbox-container">
            <div id="chatbox">
                <div id="chat-header">
                    <h3>
                        <img src="../chatbox/img/teiponBot-icon.png" alt="Logo"> KOJEK
                    </h3>
                    <button id="close-btn" onclick="minimizeChat()">×</button>
                </div>
                <div id="messages"></div>
                <div id="input-area">
                    <input type="text" id="userInput" class="form-control" placeholder="Type your message here...">
                    <button id="send-btn" onclick="sendMessage()">
                        <i class="bi bi-rocket-takeoff"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>
    <button id="open-chatbox" onclick="toggleChatbox()"> </button>
    <!-- end of chatbox -->

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        
// Filter products using JavaScript
document.getElementById("filterButton").addEventListener("click", function () {
  const searchValue = document
    .getElementById("searchInput")
    .value.toLowerCase();
  const minPrice =
    parseFloat(document.getElementById("minPriceInput").value) || 0;
  const maxPrice =
    parseFloat(document.getElementById("maxPriceInput").value) || Infinity;
  const descriptionValue = document
    .getElementById("descriptionFilterInput")
    .value.toLowerCase();

            const productItems = document.querySelectorAll(".product-item");

            productItems.forEach((item) => {
                const name = item.getAttribute("data-name");
                const price = parseFloat(item.getAttribute("data-price"));
                const description = item.getAttribute("data-description");
            productItems.forEach((item) => {
                const name = item.getAttribute("data-name");
                const price = parseFloat(item.getAttribute("data-price"));
                const description = item.getAttribute("data-description");

                const matchesSearch = name.includes(searchValue);
                const matchesPrice = price >= minPrice && price <= maxPrice;
                const matchesDescription = description.includes(descriptionValue);

                if (matchesSearch && matchesPrice && matchesDescription) {
                    item.style.display = ""; // Show the product item
                } else {
                    item.style.display = "none"; // Hide the product item
                }
            });
        });
    </script>

    <!-- chatbox -->
    <section id="chatbox-section" style="display: none;">
        <link rel="stylesheet" href="../chatbox/chatbot.css">
        <div id="chatbox-container">
            <div id="chatbox">
                <div id="chat-header">
                    <h3>
                        <img src="../chatbox/img/teiponBot-icon.png" alt="Logo"> KOJEK
                    </h3>
                    <button id="close-btn" onclick="minimizeChat()">×</button>
                </div>
                <div id="messages"></div>
                <div id="input-area">
                    <input type="text" id="userInput" class="form-control" placeholder="Type your message here...">
                    <button id="send-btn" onclick="sendMessage()">
                        <i class="bi bi-rocket-takeoff"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>
    <button id="open-chatbox" onclick="toggleChatbox()"> </button>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>