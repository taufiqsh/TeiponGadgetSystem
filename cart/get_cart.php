<?php
session_start();

$response = [
    'cartHTML' => '',
    'total' => '0.00',
    'cart' => []
];

if (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
    $total = 0;
    $cartHTML = '';
    $cartData = [];

    foreach ($cart as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $total += $itemTotal;

        // Create HTML for cart items
        $cartHTML .= '<div>' . htmlspecialchars($item['name']) . ' - RM ' . number_format($item['price'], 2) . ' x ' . $item['quantity'] . '</div>';

        // Prepare data to send back (useful for updating cart in navbar and modal)
        $cartData[] = [
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity']
        ];
    }

    // Return data in JSON format
    $response['cartHTML'] = $cartHTML;
    $response['total'] = number_format($total, 2);
    $response['cart'] = $cartData;
}

echo json_encode($response);
?>
