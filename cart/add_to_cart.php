<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');

if (isset($_POST['productID'], $_POST['productName'], $_POST['productPrice'], $_POST['productImage'])) {
    $productID = $_POST['productID'];
    $productName = $_POST['productName'];
    $productPrice = $_POST['productPrice'];
    $productImage = $_POST['productImage'];

    // If the cart is not initialized
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Check if the product is already in the cart
    $productExists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $productID) {
            $item['quantity'] += 1; // Increase quantity
            $productExists = true;
            break;
        }
    }

    // If not, add the new product to the cart
    if (!$productExists) {
        $_SESSION['cart'][] = [
            'id' => $productID,
            'name' => $productName,
            'price' => $productPrice,
            'quantity' => 1,
            'image' => $productImage
        ];
    }

    // Return the updated cart count and cart data
    echo json_encode([
        'cartCount' => array_sum(array_column($_SESSION['cart'], 'quantity')),
        'cart' => $_SESSION['cart']
    ]);
}