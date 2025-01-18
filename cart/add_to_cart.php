<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

// Check if required POST data is present
if (isset($_POST['productID'], $_POST['productName'], $_POST['productPrice'], $_POST['productImage'], $_POST['variantID'], $_POST['quantity'])) {
    $productID = $_POST['productID'];
    $productName = $_POST['productName'];
    $productPrice = $_POST['productPrice'];
    $productImage = $_POST['productImage'];
    $variantID = $_POST['variantID'];
    $quantity = $_POST['quantity'];
    $customerID = $_SESSION['userID']; // Assuming the customer ID is stored in the session

    // Validate data
    if (!is_numeric($productID) || !is_numeric($productPrice) || !is_numeric($quantity) || !is_numeric($variantID)) {
        echo json_encode(['error' => 'Invalid product data']);
        exit;
    }

    try {
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Step 1: Validate stock
        $stockStmt = $conn->prepare("SELECT productStock FROM ProductVariant WHERE variantID = ?");
        $stockStmt->bind_param("i", $variantID);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();

        if ($stockResult->num_rows > 0) {
            $stockRow = $stockResult->fetch_assoc();
            $currentStock = $stockRow['productStock'];

            if ($currentStock < $quantity) {
                // Not enough stock
                echo json_encode(['error' => 'Insufficient stock for the selected variant.']);
                exit;
            }
        } else {
            echo json_encode(['error' => 'Product variant not found.']);
            exit;
        }
        $stockStmt->close();

        // Step 2: Check if the product with the variant already exists in the cart
        $stmt = $conn->prepare("SELECT quantity FROM CART WHERE productID = ? AND customerID = ? AND variantID = ?");
        $stmt->bind_param("iii", $productID, $customerID, $variantID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If the product exists, update the quantity
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;

            if ($newQuantity > $currentStock) {
                echo json_encode(['error' => 'Adding this quantity exceeds available stock.']);
                exit;
            }

            $updateStmt = $conn->prepare("UPDATE CART SET quantity = ?, updatedAt = CURRENT_TIMESTAMP WHERE productID = ? AND customerID = ? AND variantID = ?");
            $updateStmt->bind_param("iiii", $newQuantity, $productID, $customerID, $variantID);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // If the product does not exist, insert a new row
            $insertStmt = $conn->prepare("INSERT INTO CART (productID, customerID, variantID, quantity, createdAt, updatedAt) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $insertStmt->bind_param("iiii", $productID, $customerID, $variantID, $quantity);
            $insertStmt->execute();
            $insertStmt->close();
        }

        $stmt->close();

        // Step 3: Reduce the stock in ProductVariant
        $updateStockStmt = $conn->prepare("UPDATE ProductVariant SET productStock = productStock - ? WHERE variantID = ?");
        $updateStockStmt->bind_param("ii", $quantity, $variantID);
        $updateStockStmt->execute();
        $updateStockStmt->close();

        // Step 4: Fetch the updated cart count
        $countStmt = $conn->prepare("SELECT SUM(quantity) AS cartCount FROM CART WHERE customerID = ?");
        $countStmt->bind_param("i", $customerID);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $cartCountRow = $countResult->fetch_assoc();
        $cartCount = $cartCountRow['cartCount'] ? $cartCountRow['cartCount'] : 0;
        $countStmt->close();

        // Fetch the updated cart items with variantName
        $cartItemsStmt = $conn->prepare("SELECT p.productID, p.productName, p.productPrice, c.quantity, p.productImage, c.variantID, pv.variantName FROM CART c 
                                        JOIN PRODUCT p ON c.productID = p.productID 
                                        JOIN ProductVariant pv ON c.variantID = pv.variantID 
                                        WHERE c.customerID = ?");
        $cartItemsStmt->bind_param("i", $customerID);
        $cartItemsStmt->execute();
        $cartItemsResult = $cartItemsStmt->get_result();

        $cartItems = [];
        while ($row = $cartItemsResult->fetch_assoc()) {
            $cartItems[] = [
                'productID' => $row['productID'],
                'variantID' => $row['variantID'],
                'productName' => $row['productName'],
                'productPrice' => $row['productPrice'],
                'quantity' => $row['quantity'],
                'productImage' => $row['productImage'],
                'variantName' => $row['variantName'],
            ];
        }
        $cartItemsStmt->close();

        // Return the updated cart count and cart items
        echo json_encode([
            'cart' => $cartItems,
            'cartCount' => $cartCount,
            'productID' => $productID,
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['error' => 'Missing required parameters']);
}
?>
