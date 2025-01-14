<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if (isset($_GET['productID']) && is_numeric($_GET['productID'])) {
    $productID = intval($_GET['productID']);
    
    // Fetch product details
    $sql = "SELECT * FROM Product WHERE productID = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $productID);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();

        if ($product) {
            // Fetch variants if the product exists
            $variantSQL = "SELECT * FROM productVariant WHERE productID = ?";
            $variantStmt = $conn->prepare($variantSQL);
            if ($variantStmt) {
                $variantStmt->bind_param("i", $productID);
                $variantStmt->execute();
                $variantResult = $variantStmt->get_result();
                $variants = [];

                while ($variant = $variantResult->fetch_assoc()) {
                    $variants[] = $variant;
                }

                $product['variants'] = $variants;
                echo json_encode($product); // Send product details along with variants
            } else {
                echo json_encode(['error' => 'Failed to fetch variants']);
            }
        } else {
            echo json_encode(['error' => 'Product not found']);
        }
    } else {
        echo json_encode(['error' => 'Failed to fetch product details']);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
}
?>
