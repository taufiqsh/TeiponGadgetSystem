<?php
session_start();

// Check if the cart exists in the session
if (isset($_SESSION['cart'])) {
    echo json_encode(['cart' => $_SESSION['cart']]); // Send cart data back
} else {
    echo json_encode(['cart' => []]); // Empty cart if no session
}
?>
