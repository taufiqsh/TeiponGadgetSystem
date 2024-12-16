<?php
session_start();

if (isset($_POST['productID'])) {
    $productID = $_POST['productID'];

    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $productID) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array after removal
                echo "Item removed from cart";
                break;
            }
        }
    }
}
?>
