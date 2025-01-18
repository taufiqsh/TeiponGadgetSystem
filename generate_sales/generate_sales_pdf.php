<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');
require_once(__DIR__ . '/../vendor/autoload.php');

// Query to get total sales for the last month with 'completed' status only
$salesQuery = "SELECT SUM(totalAmount) AS total_sales 
               FROM orders 
               WHERE orderDate >= CURDATE() - INTERVAL 1 MONTH 
               AND orderStatus = 'Order Completed'";
$salesResult = $conn->query($salesQuery);
$totalSales = $salesResult->fetch_assoc()['total_sales'] ?? 0;

// Create a new TCPDF object
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Teipon Gadget System');
$pdf->SetAuthor('Teipon Gadget System');
$pdf->SetTitle('Monthly Sales Report');
$pdf->SetSubject('Sales Report');
$pdf->SetKeywords('Sales, Report, Teipon, PDF');

// Set header and footer
$pdf->SetHeaderData('', 0, 'Teipon Gadget System', "Monthly Sales Report\nGenerated on: " . date('Y-m-d'));
$pdf->setFooterData('', '', 'Page: ' . $pdf->getAliasNumPage());

// Set margins
$pdf->SetMargins(15, 27, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Add a page
$pdf->AddPage();

// Add title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Sales Report for Last 30 Days', 0, 1, 'C');
$pdf->Ln(10);

// Add total sales
$pdf->SetFont('helvetica', 'B', 14);
$pdf->MultiCell(0, 10, 'Total Sales: RM ' . number_format($totalSales, 2), 0, 'L');
$pdf->Ln(10);

// Query to fetch recent orders and products sold for each order
$recentOrdersQuery = "
    SELECT o.orderID, o.orderDate, o.totalAmount, 
           p.productName, pv.variantName, op.quantity, op.totalPrice 
    FROM orders o
    INNER JOIN orderproducts op ON o.orderID = op.orderID
    INNER JOIN product p ON op.productID = p.productID
    INNER JOIN productvariant pv ON op.variantID = pv.variantID
    WHERE o.orderDate >= CURDATE() - INTERVAL 1 MONTH 
    AND o.orderStatus = 'Order Completed'
    ORDER BY o.orderDate DESC";

$recentOrdersResult = $conn->query($recentOrdersQuery);

// Current order ID tracker to group products under each order
$currentOrderID = null;

// Table headers for orders and products
$pdf->SetFont('helvetica', 'B', 12);

$orderCount = 0; // Track the number of orders per page

while ($row = $recentOrdersResult->fetch_assoc()) {
    // Check if we are processing a new order
    if ($currentOrderID !== $row['orderID']) {
        if ($currentOrderID !== null && $orderCount == 2) {
            // Add a page after every two orders
            $pdf->AddPage();
            $orderCount = 0; // Reset order count after adding a page
        }

        // Update the current order ID
        $currentOrderID = $row['orderID'];

        // Display order details
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'Order ID:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(50, 10, $row['orderID'], 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'Order Date:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(50, 10, $row['orderDate'], 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'Total Amount:', 0, 0, 'L');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Cell(50, 10, 'RM ' . number_format($row['totalAmount'], 2), 0, 1, 'L');
        $pdf->Ln(5); // Add spacing before product list

        // Add product table headers
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(65, 10, 'Product Name', 1);
        $pdf->Cell(45, 10, 'Variant', 1);
        $pdf->Cell(25, 10, 'Quantity', 1);
        $pdf->Cell(40, 10, 'Total Price (RM)', 1);
        $pdf->Ln();

        $orderCount++; // Increment the order count for each new order
    }

    // Add product details under the current order
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(65, 10, $row['productName'], 1);

    // Use MultiCell for the variant name to allow wrapping
    $pdf->MultiCell(45, 10, $row['variantName'], 1, 'L', 0, 0);

    $pdf->Cell(25, 10, $row['quantity'], 1);
    $pdf->Cell(40, 10, number_format($row['totalPrice'], 2), 1);
    $pdf->Ln();
}

// Close the database connection
$conn->close();

// Output the PDF (force download)
$pdf->Output('Monthly_Sales_Report.pdf', 'D');
?>
