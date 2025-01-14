<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'], $_POST['variantID'])) {
    $productID = $_POST['productID'];
    $variantID = $_POST['variantID'];

    // Ensure the user is logged in
    if (!isset($_SESSION['userID'])) {
        echo json_encode(['error' => 'User not logged in']);
        exit;
    }

    $customerID = $_SESSION['userID'];

    try {
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Fetch the current quantity of the product in the cart
        $stmt = $conn->prepare("SELECT quantity FROM CART WHERE customerID = ? AND productID = ? AND variantID = ?");
        $stmt->bind_param("iii", $customerID, $productID, $variantID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo json_encode(['error' => 'Product not found in cart']);
            exit;
        }

        $row = $result->fetch_assoc();
        $quantity = $row['quantity'];
        $stmt->close();

        if ($quantity > 0) {
            // Reduce quantity by 1
            $updateStmt = $conn->prepare("UPDATE CART SET quantity = quantity - 1, updatedAt = CURRENT_TIMESTAMP WHERE customerID = ? AND productID = ? AND variantID = ?");
            $updateStmt->bind_param("iii", $customerID, $productID, $variantID);
            $updateStmt->execute();
            $updateStmt->close();

            // Check if the quantity reaches 0 and remove the product from the cart
            if ($quantity == 1) {
                $deleteStmt = $conn->prepare("DELETE FROM CART WHERE customerID = ? AND productID = ? AND variantID = ?");
                $deleteStmt->bind_param("iii", $customerID, $productID, $variantID);
                $deleteStmt->execute();
                $deleteStmt->close();
            }
        } else {
            // If quantity is already 0, handle it (perhaps give an error message)
            echo json_encode(['error' => 'Product quantity is already 0']);
            exit;
        }

        // Fetch the updated cart items
        $cartStmt = $conn->prepare("SELECT c.productID, c.variantID, p.productName, p.productPrice, c.quantity, pv.variantName, p.productImage
                                    FROM CART c
                                    JOIN Product p ON c.productID = p.productID
                                    JOIN ProductVariant pv ON c.variantID = pv.variantID
                                    WHERE c.customerID = ?");
        $cartStmt->bind_param("i", $customerID);
        $cartStmt->execute();
        $cartResult = $cartStmt->get_result();

        $cartItems = [];
        while ($row = $cartResult->fetch_assoc()) {
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
        $cartStmt->close();

        // Fetch the updated cart count
        $countStmt = $conn->prepare("SELECT SUM(quantity) AS cartCount FROM CART WHERE customerID = ?");
        $countStmt->bind_param("i", $customerID);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $cartCountRow = $countResult->fetch_assoc();
        $cartCount = $cartCountRow['cartCount'] ? $cartCountRow['cartCount'] : 0;  // Set to 0 if NULL
        $countStmt->close();

        // If there are no items in the cart, make sure cartCount is 0
        if ($cartCount === null) {
            $cartCount = 0;
        }

        // Return the updated cart and cart count
        echo json_encode([
            'cart' => $cartItems, // Assuming you fetch cart items from the DB after the removal
            'cartCount' => $cartCount,
            'productID' => $productID, // Add the productID here
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    }

    $conn->close();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
