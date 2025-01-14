<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_creation_config.php');

// Create database
$sqlCreateDB = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sqlCreateDB) === TRUE) {
    echo "Database '$dbname' created successfully!<br>";
} else {
    die("Error creating database: " . $conn->error . "<br>");
}

$conn->select_db($dbname);

// Array of SQL queries to create tables with the recursive relationship in staff
$tables = [
    "staff" => "CREATE TABLE IF NOT EXISTS staff (
        staffID INT AUTO_INCREMENT PRIMARY KEY,
        staffName VARCHAR(255) NOT NULL,
        staffUsername VARCHAR(255) NOT NULL UNIQUE,
        staffEmail VARCHAR(255) NOT NULL UNIQUE,
        staffPassword VARCHAR(255) NOT NULL,
        adminID INT DEFAULT NULL,
        FOREIGN KEY (adminID) REFERENCES staff(staffID) ON DELETE SET NULL
    )",
    "customer" => "CREATE TABLE IF NOT EXISTS customer (
        customerID INT AUTO_INCREMENT PRIMARY KEY,
        customerName VARCHAR(255) NOT NULL,
        customerUsername VARCHAR(255) NOT NULL UNIQUE,
        customerPhoneNumber VARCHAR(255) NOT NULL,
        customerEmail VARCHAR(255) NOT NULL UNIQUE,
        customerPassword VARCHAR(255) NOT NULL,
        customerState VARCHAR(255) NOT NULL,
        customerPostalCode VARCHAR(255) NOT NULL,
        customerCity VARCHAR(255) NOT NULL,
        customerAddress VARCHAR(255) NOT NULL,
        status INT(1) DEFAULT 1,
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
        receiptPath varchar(255) DEFAULT NULL,
        staffID INT,
            FOREIGN KEY (customerID) REFERENCES customer(customerID) ON DELETE CASCADE,
    FOREIGN KEY (staffID) REFERENCES staff(staffID) ON DELETE SET NULL
    )",
    "product" => "CREATE TABLE IF NOT EXISTS `product` (
        `productID` INT NOT NULL AUTO_INCREMENT,
        `productName` VARCHAR(255) NOT NULL,
        `productBrand` VARCHAR(255) NOT NULL,
        `productPrice` DECIMAL(10, 2) NOT NULL,
        `productDescription` TEXT NOT NULL,
        `productScreenSize` VARCHAR(50) DEFAULT NULL,
        `productBatteryCapacity` VARCHAR(50) DEFAULT NULL,
        `productCameraSpecs` VARCHAR(255) DEFAULT NULL,
        `productProcessor` VARCHAR(255) DEFAULT NULL,
        `productOS` VARCHAR(50) DEFAULT NULL,
        `productReleaseDate` DATE DEFAULT NULL,
        `productImage` VARCHAR(255) DEFAULT NULL,
        `productCreatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `productUpdatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `staffID` INT DEFAULT NULL,
        PRIMARY KEY (`productID`),
        KEY `staffID` (`staffID`),
        CONSTRAINT `product_ibfk_1` FOREIGN KEY (`staffID`) REFERENCES `staff` (`staffID`)
    )",
    "productvariant" => "CREATE TABLE IF NOT EXISTS `productvariant` (
        `variantID` INT NOT NULL AUTO_INCREMENT,
        `productID` INT NOT NULL,
        `variantName`VARCHAR(255) NOT NULL,
        `productColor` VARCHAR(50) NOT NULL,
        `productStorage` INT DEFAULT NULL,
        `productRam` INT DEFAULT NULL,
        `productStock` INT NOT NULL DEFAULT '0',
        `createdAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        `updatedAt` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`variantID`),
        KEY `productID` (`productID`),
        CONSTRAINT `productvariant_ibfk_1` FOREIGN KEY (`productID`) REFERENCES `product` (`productID`) ON DELETE CASCADE
    )",
    "orderProducts" => "CREATE TABLE IF NOT EXISTS orderProducts (
        orderProductId INT AUTO_INCREMENT PRIMARY KEY,
        orderID INT NOT NULL,
        productID INT NOT NULL,
        quantity INT NOT NULL,
        variantID INT NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        totalPrice DECIMAL(10, 2) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (orderID) REFERENCES `orders`(orderID) ON DELETE CASCADE,
        FOREIGN KEY (productID) REFERENCES product(productID) ON DELETE CASCADE,
        FOREIGN KEY (variantID) REFERENCES productvariant(variantID) ON DELETE CASCADE
    )",
    "cart" => "CREATE TABLE IF NOT EXISTS CART (
        cartID INT AUTO_INCREMENT PRIMARY KEY,
        productID INT NOT NULL,
        customerID INT NOT NULL,
        variantID INT NOT NULL,
        quantity INT NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (productID) REFERENCES product(productID) ON DELETE CASCADE,
        FOREIGN KEY (customerID) REFERENCES customer(customerID) ON DELETE CASCADE,
        FOREIGN KEY (variantID) REFERENCES productvariant(variantID) ON DELETE CASCADE
        )"
];


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
        <button onclick="location.href='data_insertion.php';" class="btn btn-primary mt-3">Insert Data</button>
    </div>
</body>

</html>