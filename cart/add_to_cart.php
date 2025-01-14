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

        // Check if the product with the variant already exists in the cart for this customer
        $stmt = $conn->prepare("SELECT quantity FROM CART WHERE productID = ? AND customerID = ? AND variantID = ?");
        $stmt->bind_param("iii", $productID, $customerID, $variantID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If the product exists, update the quantity
            $row = $result->fetch_assoc();
            $newQuantity = $row['quantity'] + $quantity;

            $updateStmt = $conn->prepare("UPDATE CART SET quantity = ?, updatedAt = CURRENT_TIMESTAMP WHERE productID = ? AND customerID = ? AND variantID = ?");
            $updateStmt->bind_param("iiii", $newQuantity, $productID, $customerID, $variantID);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            // If the product does not exist, insert a new row
            $insertStmt = $conn->prepare("INSERT INTO CART (productID, customerID, variantID, quantity, createdAt, updatedAt) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)");
            $insertStmt->bind_param("iiii", $productID, $customerID, $variantID, $quantity);  // Added variantID
            $insertStmt->execute();
            $insertStmt->close();
        }

        $stmt->close();

        // Fetch the updated cart count
        $countStmt = $conn->prepare("SELECT SUM(quantity) AS cartCount FROM CART WHERE customerID = ?");
        $countStmt->bind_param("i", $customerID);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $cartCountRow = $countResult->fetch_assoc();
        $cartCount = $cartCountRow['cartCount'] ? $cartCountRow['cartCount'] : 0;  // Set to 0 if NULL
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
                'variantName' => $row['variantName'], // Ensure this is returned
            ];
        }
        $cartItemsStmt->close();

        // Return the updated cart count and cart items
        echo json_encode([
            'cart' => $cartItems, // Assuming you fetch cart items from the DB after the removal
            'cartCount' => $cartCount,
            'productID' => $productID, // Add the productID here
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

} else {
    echo json_encode(['error' => 'Missing required parameters']);
}
?>
