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
    "product" => "CREATE TABLE IF NOT EXISTS product (
        productID INT AUTO_INCREMENT PRIMARY KEY,
        productName VARCHAR(255) NOT NULL,
        productDescription TEXT,
        productPrice DECIMAL(10, 2) NOT NULL,
        productStock INT NOT NULL,
        productImage VARCHAR(255) NOT NULL,
        productCreatedDate DATETIME NOT NULL,
        staffID INT,
        FOREIGN KEY (staffID) REFERENCES staff(staffID) ON DELETE SET NULL
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
    )"
];


foreach ($tables as $tableName => $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table '$tableName' created successfully!<br>";
    } else {
        echo "Error creating table '$tableName': " . $conn->error . "<br>";
    }
}

$adminUsername = 'admin';
$adminPassword = '$2a$12$MTkrwrZoblu7LrxeipevJOXIoCwpcR2CsuhssVFgjBKEcmGQLVnLy'; // Pre-hashed bcrypt password
$adminEmail = 'admin@yopmail.com';
$adminName = 'Admin User';

$sqlInsertAdmin = "INSERT INTO staff (staffName, staffUsername, staffEmail, staffPassword) 
                   VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sqlInsertAdmin);
if ($stmt) {
    $stmt->bind_param("ssss", $adminName, $adminUsername, $adminEmail, $adminPassword);
    if ($stmt->execute()) {
        $adminID = $stmt->insert_id; // Get the newly inserted admin's staffID

        // Update the adminID field to point to itself
        $sqlUpdateAdminID = "UPDATE staff SET adminID = ? WHERE staffID = ?";
        $updateStmt = $conn->prepare($sqlUpdateAdminID);
        if ($updateStmt) {
            $updateStmt->bind_param("ii", $adminID, $adminID);
            if ($updateStmt->execute()) {
                echo "Admin ID set for admin user successfully!<br>";
            } else {
                echo "Error updating adminID: " . $updateStmt->error . "<br>";
            }
            $updateStmt->close();
        } else {
            echo "Error preparing adminID update: " . $conn->error . "<br>";
        }
    } else {
        echo "Error inserting admin: " . $stmt->error . "<br>";
    }
    $stmt->close();
} else {
    echo "Error preparing admin insert: " . $conn->error . "<br>";
}
$productData = [
    [
        'productName' => 'iPhone 16',
        'productDescription' => 'The latest iPhone with Bionic chip and advanced dual-camera system.',
        'productPrice' => 1500.99,
        'productStock' => 50,
        'productImage' => '1734372950_iphone_16__c5bvots96jee_xlarge.png',
        'productCreatedDate' => date('Y-m-d H:i:s'),
        'staffID' => $adminID // Assuming admin user manages these products
    ],
    [
        'productName' => 'Samsung S24 Ultra',
        'productDescription' => 'Flagship Samsung phone.',
        'productPrice' => 1899.99,
        'productStock' => 40,
        'productImage' => '1735748792_s24 ultra.jpg',
        'productCreatedDate' => date('Y-m-d H:i:s'),
        'staffID' => $adminID
    ],
    [
        'productName' => 'Samsung S24',
        'productDescription' => 'Flagship Samsung phone.',
        'productPrice' => 1699.99,
        'productStock' => 30,
        'productImage' => '1734556623_s24.png',
        'productCreatedDate' => date('Y-m-d H:i:s'),
        'staffID' => $adminID
    ],
    [
        'productName' => 'Iphone 16 Pro Max',
        'productDescription' => 'High-performance.',
        'productPrice' => 2000.99,
        'productStock' => 25,
        'productImage' => '1734556556_iphone_16pro__erw9alves2qa_xlarge.png',
        'productCreatedDate' => date('Y-m-d H:i:s'),
        'staffID' => $adminID
    ]
];

$sqlInsertProduct = "INSERT INTO product (productName, productDescription, productPrice, productStock, productImage, productCreatedDate, staffID) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmtProduct = $conn->prepare($sqlInsertProduct);
if ($stmtProduct) {
    foreach ($productData as $product) {
        $stmtProduct->bind_param(
            "ssdisss",
            $product['productName'],
            $product['productDescription'],
            $product['productPrice'],
            $product['productStock'],
            $product['productImage'],
            $product['productCreatedDate'],
            $product['staffID']
        );

        if ($stmtProduct->execute()) {
            echo "Product '{$product['productName']}' inserted successfully!<br>";
        } else {
            echo "Error inserting product '{$product['productName']}': " . $stmtProduct->error . "<br>";
        }
    }
    $stmtProduct->close();
} else {
    echo "Error preparing product insert: " . $conn->error . "<br>";
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
