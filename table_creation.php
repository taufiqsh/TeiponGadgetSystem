<?php
// Database connection details
$servername = "localhost";
$username = "root"; // Default username for local MySQL
$password = "root"; // Default password for local MySQL
$dbname = "teipon_gadget"; // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the new database
$sqlCreateDB = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sqlCreateDB) === TRUE) {
    echo "Database '$dbname' created successfully!<br>";
} else {
    die("Error creating database: " . $conn->error . "<br>");
}

$conn->select_db($dbname);

// Array of SQL queries to create tables with table names
$tables = [
    "Staff" => "CREATE TABLE IF NOT EXISTS Staff (
        staffID INT AUTO_INCREMENT PRIMARY KEY,
        staffName VARCHAR(255) NOT NULL,
        staffEmail VARCHAR(255) NOT NULL UNIQUE,
        staffPassword VARCHAR(255) NOT NULL,
        adminID INT,
        FOREIGN KEY (adminID) REFERENCES Staff(staffID) ON DELETE SET NULL
    )",
    "Customer" => "CREATE TABLE IF NOT EXISTS Customer (
        customerID INT AUTO_INCREMENT PRIMARY KEY,
        customerName VARCHAR(255) NOT NULL,
        customerUsername VARCHAR(255) NOT NULL,
        customerEmail VARCHAR(255) NOT NULL UNIQUE,
        customerPassword VARCHAR(255) NOT NULL,
        customerState VARCHAR(255) NOT NULL,
        customerPostalCode VARCHAR(255) NOT NULL,
        customerCity VARCHAR(255) NOT NULL,
        customerAddress VARCHAR(255) NOT NULL,
        staffID INT,
        FOREIGN KEY (staffID) REFERENCES Staff(staffID) ON DELETE SET NULL
    )",
    "Order" => "CREATE TABLE IF NOT EXISTS `Order` (
        orderID INT AUTO_INCREMENT PRIMARY KEY,
        orderDetails VARCHAR(255),
        orderDate DATE NOT NULL,
        customerID INT NOT NULL,
        FOREIGN KEY (customerID) REFERENCES Customer(customerID) ON DELETE CASCADE
    )",
    "Item" => "CREATE TABLE IF NOT EXISTS Item (
        itemID INT AUTO_INCREMENT PRIMARY KEY,
        itemName VARCHAR(255) NOT NULL,
        itemDescription VARCHAR(255),
        itemImage BLOB,
        itemCreatedDate DATE NOT NULL,
        staffID INT,
        orderID INT,
        FOREIGN KEY (staffID) REFERENCES Staff(staffID) ON DELETE SET NULL,
        FOREIGN KEY (orderID) REFERENCES `Order`(orderID) ON DELETE SET NULL
    )",
    "Payment" => "CREATE TABLE IF NOT EXISTS Payment (
        paymentID INT AUTO_INCREMENT PRIMARY KEY,
        paymentStatus VARCHAR(255) NOT NULL,
        paymentDate DATE NOT NULL,
        orderID INT NOT NULL,
        staffID INT,
        FOREIGN KEY (orderID) REFERENCES `Order`(orderID) ON DELETE CASCADE,
        FOREIGN KEY (staffID) REFERENCES Staff(staffID) ON DELETE SET NULL
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