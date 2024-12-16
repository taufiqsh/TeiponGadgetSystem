<?php
session_start();

// Simulate data for this example
if (isset($_POST['productID'], $_POST['productName'], $_POST['productPrice'], $_POST['productImage'])) {
    $product = [
        'id' => $_POST['productID'],
        'name' => $_POST['productName'],
        'price' => $_POST['productPrice'],
        'image' => $_POST['productImage'],
        'quantity' => 1
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product already exists in the cart
    $productFound = false;
    foreach ($_SESSION['cart'] as &$cartItem) {
        if ($cartItem['id'] == $product['id']) {
            $cartItem['quantity'] += 1;  // Increment the quantity if already in the cart
            $productFound = true;
            break;
        }
    }

    // If product not found, add it to the cart
    if (!$productFound) {
        $_SESSION['cart'][] = $product;
    }

    // Send back the updated cart count
    $cartCount = array_reduce($_SESSION['cart'], function ($sum, $item) {
        return $sum + $item['quantity'];
    }, 0);

    echo json_encode(['cartCount' => $cartCount]);
}
?>
