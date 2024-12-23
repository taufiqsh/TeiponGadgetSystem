<?php
session_start();

if (isset($_POST['id'])) {
    $productID = $_POST['id'];

    // Check if the cart exists
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['id'] == $productID) {
                // Remove the item from the cart
                unset($_SESSION['cart'][$index]);
                break;
            }
        }
    }

    // Return the updated cart count and cart data
    echo json_encode([
        'cartCount' => array_sum(array_column($_SESSION['cart'], 'quantity')),
        'cart' => $_SESSION['cart']
    ]);
}
