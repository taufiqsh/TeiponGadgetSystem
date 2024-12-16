<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_config.php');


$sqlCreateDB = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sqlCreateDB) === TRUE) {
    echo "Database '$dbname' created successfully!<br>";
} else {
    die("Error creating database: " . $conn->error . "<br>");
}

$conn->select_db($dbname);

// Array of SQL queries to create tables with table names
$tables = [
    "staff" => "CREATE TABLE IF NOT EXISTS staff (
        staffID INT AUTO_INCREMENT PRIMARY KEY,
        staffName VARCHAR(255) NOT NULL,
        staffUsername VARCHAR(255) NOT NULL,
        staffEmail VARCHAR(255) NOT NULL UNIQUE,
        staffPassword VARCHAR(255) NOT NULL,
        adminID INT,
        FOREIGN KEY (adminID) REFERENCES staff(staffID) ON DELETE SET NULL
    )",
    "customer" => "CREATE TABLE IF NOT EXISTS customer (
        customerID INT AUTO_INCREMENT PRIMARY KEY,
        customerName VARCHAR(255) NOT NULL,
        customerUsername VARCHAR(255) NOT NULL,
        customerPhoneNumber VARCHAR(255) NOT NULL,
        customerEmail VARCHAR(255) NOT NULL UNIQUE,
        customerPassword VARCHAR(255) NOT NULL,
        customerState VARCHAR(255) NOT NULL,
        customerPostalCode VARCHAR(255) NOT NULL,
        customerCity VARCHAR(255) NOT NULL,
        customerAddress VARCHAR(255) NOT NULL,
        staffID INT,
        FOREIGN KEY (staffID) REFERENCES staff(staffID) ON DELETE SET NULL
    )",
    "orders" => "CREATE TABLE IF NOT EXISTS `orders` (
        orderID INT AUTO_INCREMENT PRIMARY KEY,
        orderDetails VARCHAR(255),
        orderDate DATE NOT NULL,
        totalAmount DECIMAL(10,2) NOT NULL,
        orderStatus VARCHAR(50) NOT NULL,
        customerID INT NOT NULL,
        FOREIGN KEY (customerID) REFERENCES customer(customerID) ON DELETE CASCADE
    )",
    "product" => "CREATE TABLE IF NOT EXISTS product (
        productID INT AUTO_INCREMENT PRIMARY KEY,
        productName VARCHAR(255) NOT NULL,
        productDescription TEXT,
        productPrice DECIMAL(10, 2) NOT NULL,
        productStock INT NOT NULL,
        productImage VARCHAR(255) NOT NULL,
        productCreatedDate DATETIME NOT NULL,
        staffID INT,
        FOREIGN KEY (staffID) REFERENCES staff(staffID)
    )",
    "payment" => "CREATE TABLE IF NOT EXISTS payment (
        paymentID INT AUTO_INCREMENT PRIMARY KEY,
        paymentStatus VARCHAR(255) NOT NULL,
        paymentDate DATE NOT NULL,
        orderID INT NOT NULL,
        staffID INT,
        FOREIGN KEY (orderID) REFERENCES `orders`(orderID) ON DELETE CASCADE,
        FOREIGN KEY (staffID) REFERENCES staff(staffID) ON DELETE SET NULL
    )",
    "orderProducts" => "CREATE TABLE IF NOT EXISTS orderProducts (
        orderProductId INT AUTO_INCREMENT PRIMARY KEY,
        orderID INT NOT NULL,
        productID INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        totalPrice DECIMAL(10, 2) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (orderID) REFERENCES `orders`(orderID) ON DELETE CASCADE,
        FOREIGN KEY (productID) REFERENCES product(productID) ON DELETE CASCADE
    )",
    "admin" => "CREATE TABLE IF NOT EXISTS admin (
        adminID INT AUTO_INCREMENT PRIMARY KEY,
        adminName VARCHAR(255) NOT NULL,
        adminEmail VARCHAR(255) NOT NULL UNIQUE,
        adminUsername VARCHAR(255) NOT NULL UNIQUE,
        adminPassword VARCHAR(255) NOT NULL
    )"
];

// Execute each query to create the tables and echo the table name
foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$tableName' created successfully!<br>";
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Complete</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light d-flex flex-column align-items-center py-5">
    <div class="container text-center">
        <h1 class="mt-5">Setup Complete!</h1>
        <p>The database and tables have been created successfully.</p>
        <button onclick="location.href='home.php';" class="btn btn-primary mt-3">Go to Index</button>
    </div>
</body>

</html>
