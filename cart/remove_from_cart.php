<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['productID'])) {
    $productID = $_POST['productID'];

    // Check if the cart exists
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['id'] == $productID) {
                // Decrease the quantity or remove the item if quantity becomes 0
                if ($_SESSION['cart'][$index]['quantity'] > 1) {
                    $_SESSION['cart'][$index]['quantity']--;
                } else {
                    unset($_SESSION['cart'][$index]); // Remove the item from the cart
                }
                break; // Exit the loop after finding the item
            }
        }

        // Re-index the array to avoid gaps
        $_SESSION['cart'] = array_values($_SESSION['cart']);
    }

    // Recalculate the cart count
    $cartCount = 0;
    $cart = [];
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $cartCount += $item['quantity'];
            $cart[] = $item; // Prepare the cart items for the response
        }
    }

    // Return the updated cart data as JSON
    echo json_encode(['cart' => $cart, 'cartCount' => $cartCount]);
} else {
    // Handle invalid requests
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request.']);
}
?>
