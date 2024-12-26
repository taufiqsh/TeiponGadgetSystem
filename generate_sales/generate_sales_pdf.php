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

// Set title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Sales Report for Last 30 Days', 0, 1, 'C');
$pdf->Ln(10);

// Sales details
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, 'Total Sales: RM ' . number_format($totalSales, 2), 0, 1, 'L');
$pdf->Ln(10);

// Add a table of recent sales
$recentSalesQuery = "SELECT orderID, orderDate, totalAmount 
                     FROM orders 
                     WHERE orderStatus = 'Order Completed' 
                     ORDER BY orderDate DESC LIMIT 10";
$recentSalesResult = $conn->query($recentSalesQuery);

// Table header
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Order ID', 1);
$pdf->Cell(60, 10, 'Order Date', 1);
$pdf->Cell(60, 10, 'Total Amount (RM)', 1);
$pdf->Ln();

// Table rows
$pdf->SetFont('helvetica', '', 12);
while ($row = $recentSalesResult->fetch_assoc()) {
    $pdf->Cell(40, 10, $row['orderID'], 1);
    $pdf->Cell(60, 10, $row['orderDate'], 1);
    $pdf->Cell(60, 10, number_format($row['totalAmount'], 2), 1);
    $pdf->Ln();
}

// Close the database connection
$conn->close();

// Output the PDF (force download)
$pdf->Output('Monthly_Sales_Report.pdf', 'D');
?>
