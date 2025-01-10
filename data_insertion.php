<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/TeiponGadgetSystem/config/db_creation_config.php');
ob_start();

// Bootstrap styling header
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">';

$conn->select_db($dbname);

$adminUsername = 'admin';
$adminPassword = '$2a$12$MTkrwrZoblu7LrxeipevJOXIoCwpcR2CsuhssVFgjBKEcmGQLVnLy';
$adminEmail = 'admin@yopmail.com';
$adminName = 'Admin User';

$custUsername = 'customer';
$custName = 'Damien';
$custPassword = '$2a$12$r96rnr2VsaejCUsNGHczaOVnPMQfNtPG72n8QIxEqSywpWr87trWK';
$custEmail = 'cust@yopmail.com';
$custPhoneNumber = '+60123456789';
$custState = 'Selangor';
$custPostalCode = '43000';
$custCity = 'Kajang';
$custAddress = '123, Jalan Kajang, Taman Kajang, 43000 Kajang, Selangor';
$custStatus = 1;

// Insert admin user
$sqlInsertAdmin = "INSERT IGNORE INTO staff (staffName, staffUsername, staffEmail, staffPassword) 
                   VALUES ('$adminName', '$adminUsername', '$adminEmail', '$adminPassword')";

if ($conn->query($sqlInsertAdmin) === TRUE) {
    $adminID = $conn->insert_id; // Get the newly inserted admin's staffID

    // Update the adminID field to point to itself
    $sqlUpdateAdminID = "UPDATE staff SET adminID = $adminID WHERE staffID = $adminID";
    if ($conn->query($sqlUpdateAdminID) === TRUE) {
        echo '<div class="alert alert-success">Admin user inserted and adminID updated successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error updating adminID: ' . $conn->error . '</div>';
    }
} else {
    echo '<div class="alert alert-danger">Error inserting admin: ' . $conn->error . '</div>';
}

$sqlInsertCustomer = "INSERT INTO customer (
    customerUsername,customerName , customerPassword,customerEmail,customerPhoneNumber,customerState,customerPostalCode,customerCity,customerAddress,
    status ) VALUES ('$custUsername','$custName','$custPassword','$custEmail','$custPhoneNumber','$custState','$custPostalCode','$custCity','$custAddress',$custStatus);";

if ($conn->query($sqlInsertCustomer) === TRUE) {
    $custID = $conn->insert_id;
    echo '<div class="alert alert-success" role="alert">
    Customer inserted successfully!
  </div>';
} else {
    echo '<div class="alert alert-danger">Error inserting customer: ' . $conn->error . '</div>';
}


$productValues = "
	(1, 'Apple iPhone 15', 'Apple', 4499.96, 'Latest iPhone with A17 Bionic chip and improved camera.', '6.1 inches', '3279 mAh', '48MP + 12MP + 12MP', 'A17 Bionic', 'iOS 17', '2023-09-22', 'apple-iphone-15.jpg', '2024-12-20 09:13:20', '2025-01-07 03:00:47', 1),
	(2, 'Samsung Galaxy S23 Ultra', 'Samsung', 5399.96, 'Premium flagship with S Pen and 200MP quad-camera.', '6.8 inches', '5000 mAh', '200MP + 12MP + 10MP + 10MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-02-01', 'samsung-galaxy-s23-ultra-5g.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:02', 1),
	(3, 'Google Pixel 8 Pro', 'Google', 4049.96, 'Google’s latest flagship with AI-powered photography.', '6.7 inches', '4950 mAh', '50MP + 48MP + 48MP', 'Google Tensor G3', 'Android 14', '2023-10-12', 'google-pixel-8-pro.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:26', 1),
	(4, 'OnePlus 11', 'OnePlus', 3149.96, 'Flagship phone with Hasselblad camera system.', '6.7 inches', '5000 mAh', '50MP + 48MP + 32MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-01-04', 'oneplus-11.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:15', 1),
	(5, 'Xiaomi 13 Pro', 'Xiaomi', 3599.96, 'High-performance phone with Leica optics.', '6.73 inches', '4820 mAh', '50.3MP + 50MP + 50MP', 'Snapdragon 8 Gen 2', 'MIUI 14 (Android 13)', '2023-03-06', 'xiaomi-redmi-note-13-pro-5g.jpg', '2024-12-20 09:13:20', '2025-01-07 03:09:29', 1),
	(6, 'Samsung Galaxy Z Flip 5', 'Samsung', 4499.96, 'Foldable phone with high performance, innovative and beautiful design.', '6.7 inches (main), 3.4 inches (cover)', '3700 mAh', '12MP + 12MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-08-11', 'samsung-galaxy-z-flip5-5g.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:13', 1),
	(7, 'Sony Xperia 1 V', 'Sony', 5849.96, 'Sony’s flagship with cutting-edge camera technology.', '6.5 inches', '5000 mAh', '52MP + 12MP + 12MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-07-01', 'sony-xperia-1-vi-red.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:37', 1),
	(8, 'Realme GT 5', 'Realme', 2924.96, 'High-performance phone with Snapdragon 8 Gen 2.', '6.74 inches', '4600 mAh', '50MP + 8MP + 2MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-08-20', 'realme-gt5-150w.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:42', 1),
	(9, 'Google Pixel Fold', 'Google', 8099.96, 'Google’s first foldable with stunning design.', '7.6 inches (main), 5.8 inches (cover)', '4821 mAh', '48MP + 10.8MP + 10.8MP', 'Google Tensor G2', 'Android 14', '2023-05-10', 'google-pixel-fold.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:39', 1),
	(10, 'Apple iPhone SE (2024)', 'Apple', 1934.96, 'Compact and affordable iPhone with A16 Bionic chip.', '4.7 inches', '2018 mAh', '12MP', 'A16 Bionic', 'iOS 17', '2024-03-01', 'apple-iphone-SE.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:09', 1),
	(11, 'Samsung Galaxy Z Fold 5', 'Samsung', 1799.99, 'Premium foldable phone with innovative multitasking features.', '7.6 inches (main), 6.2 inches (cover)', '4400 mAh', '50MP + 12MP + 10MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-07-26', 'samsung-galaxy-z-fold5-5g.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:30', 1),
	(12, 'Oppo Find N3 Flip', 'Oppo', 1099.99, 'Clamshell foldable with advanced camera system.', '6.8 inches (main), 3.26 inches (cover)', '4300 mAh', '50MP + 32MP + 48MP', 'Dimensity 9200', 'Android 14', '2023-08-30', 'oppo-find-n3-flip.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:31', 1),
	(13, 'Xiaomi Mix Fold 3', 'Xiaomi', 1599.99, 'Xiaomi’s ultra-slim foldable with Leica optics.', '8.03 inches (main), 6.56 inches (cover)', '4800 mAh', '50MP + 10MP + 12MP + 20MP', 'Snapdragon 8 Gen 2', 'Android 14', '2023-08-16', 'xiaomi-mix-fold3-.jpg', '2024-12-20 09:13:20', '2025-01-07 03:09:18', 1),
	(14, 'Vivo X100 Pro+', 'Vivo', 1299.99, 'High-end flagship with advanced imaging capabilities.', '6.78 inches', '5000 mAh', '50MP + 50MP + 64MP + 12MP', 'Dimensity 9300', 'Android 14', '2023-12-15', 'vivo-x100-pro.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:55', 1),
	(15, 'Asus ROG Phone 7 Ultimate', 'Asus', 1399.99, 'Gaming smartphone with top-notch performance.', '6.78 inches', '6000 mAh', '50MP + 13MP', 'Snapdragon 8 Gen 2', 'Android 13', '2023-04-13', 'asus-rog-phone-7-ultimate.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:15', 1),
	(16, 'Honor Magic V2', 'Honor', 1599.99, 'Ultra-thin foldable with premium design.', '7.92 inches (main), 6.43 inches (cover)', '5000 mAh', '50MP + 50MP + 20MP', 'Snapdragon 8 Gen 2', 'Android 14', '2023-07-12', 'honor-magic-2.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:44', 1),
	(17, 'Realme Narzo 60 Pro', 'Realme', 329.99, 'Affordable phone with flagship-grade features.', '6.7 inches', '5000 mAh', '100MP + 2MP', 'Dimensity 7050', 'Android 13', '2023-06-15', 'realme-narzo60-pro-5g.jpg', '2024-12-20 09:13:20', '2025-01-07 03:07:11', 1),
	(18, 'Infinix Zero Ultra', 'Infinix', 299.99, 'Budget phone with 200MP camera and fast charging.', '6.8 inches', '4500 mAh', '200MP + 13MP + 2MP', 'Dimensity 920', 'Android 12', '2023-05-12', 'infinix-zero-ultra.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:04', 1),
	(19, 'Huawei Mate 60 Pro', 'Huawei', 1199.99, 'Premium flagship with HarmonyOS that is only avaiable only on China.', '6.82 inches', '5000 mAh', '50MP + 48MP + 12MP', 'Kirin 9000S', 'HarmonyOS 4.0', '2023-09-20', 'huawei-mate-60-pro.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:59', 1),
	(20, 'Tecno Phantom V Fold', 'Tecno', 999.99, 'Affordable foldable phone with powerful hardware.', '7.85 inches (main), 6.42 inches (cover)', '5000 mAh', '50MP + 50MP + 13MP', 'Dimensity 9000+', 'Android 13', '2023-03-02', 'tecno-phantom-v-fold.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:50', 1),
	(21, 'Samsung Galaxy A54', 'Samsung', 2024.95, 'Mid-range phone with durable design and solid performance.', '6.4 inches', '5000 mAh', '50MP + 12MP + 5MP', 'Exynos 1380', 'Android 13', '2023-03-15', 'samsung-galaxy-a54.jpg', '2024-12-20 09:13:20', '2025-01-07 03:07:23', 1),
	(22, 'Motorola Edge 40', 'Motorola', 2699.95, 'Stylish mid-range with curved-edge display.', '6.55 inches', '4400 mAh', '50MP + 13MP', 'Dimensity 8020', 'Android 13', '2023-05-23', 'motorola-edge-40.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:09', 1),
	(23, 'Apple iPhone 14 Pro', 'Apple', 4949.95, 'Flagship phone with Dynamic Island and 48MP camera.', '6.1 inches', '3200 mAh', '48MP + 12MP + 12MP', 'A16 Bionic', 'iOS 16', '2022-09-16', 'apple-iphone-14-pro.jpg', '2024-12-20 09:13:20', '2025-01-07 03:03:25', 1),
	(24, 'Google Pixel 7', 'Google', 2699.95, 'Compact phone with advanced AI features.', '6.3 inches', '4355 mAh', '50MP + 12MP', 'Google Tensor G2', 'Android 13', '2022-10-13', 'google-pixel7-new.jpg', '2024-12-20 09:13:20', '2025-01-07 03:05:32', 1),
	(25, 'OnePlus Nord 3', 'OnePlus', 2249.95, 'Affordable phone with flagship-grade specs.', '6.74 inches', '5000 mAh', '50MP + 8MP + 2MP', 'Dimensity 9000', 'Android 13', '2023-07-01', 'oneplus-nord-3r.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:22', 1),
	(26, 'Xiaomi Redmi Note 12 Pro+', 'Xiaomi', 1709.95, 'Mid-range phone with 200MP main camera.', '6.67 inches', '5000 mAh', '200MP + 8MP + 2MP', 'Dimensity 1080', 'Android 13', '2022-12-01', 'xiaomi-redmi-note-12-pro-plus.jpg', '2024-12-20 09:13:20', '2025-01-07 03:09:26', 1),
	(27, 'Samsung Galaxy S22', 'Samsung', 3599.95, 'Compact flagship with pro-grade cameras.', '6.1 inches', '3700 mAh', '50MP + 10MP + 12MP', 'Exynos 2200/Snapdragon 8 Gen 1', 'Android 12', '2022-02-25', 'samsung-galaxy-s22-5g.jpg', '2024-12-20 09:13:20', '2025-01-07 03:07:42', 1),
	(28, 'Sony Xperia 10 V', 'Sony', 2024.95, 'Lightweight and waterproof phone with good cameras.', '6.1 inches', '5000 mAh', '48MP + 8MP', 'Snapdragon 695', 'Android 13', '2023-06-15', 'sony-xperia-10-v-10.jpg', '2024-12-20 09:13:20', '2025-01-07 03:08:45', 1),
	(29, 'Vivo X90 Pro', 'Vivo', 4499.95, 'Flagship camera phone with Zeiss optics.', '6.78 inches', '4870 mAh', '50MP + 50MP + 12MP', 'Dimensity 9200', 'Android 13', '2023-01-31', 'vivo-x90-pro.jpg', '2024-12-20 09:13:20', '2025-01-07 03:09:00', 1),
	(30, 'Realme C55', 'Realme', 899.95, 'Affordable phone with Mini Capsule feature.', '6.72 inches', '5000 mAh', '64MP + 2MP', 'Helio G88', 'Android 13', '2023-04-01', 'realme-c55.jpg', '2024-12-20 09:13:20', '2025-01-07 03:06:37', 1);";

$sqlInsertProducts = "INSERT INTO product (
    productID,productName, productBrand, productPrice, productDescription, 
    productScreenSize, productBatteryCapacity, productCameraSpecs, 
    productProcessor, productOS, productReleaseDate, productImage,productCreatedAt,productUpdatedAt , staffID
) VALUES $productValues";

if ($conn->query($sqlInsertProducts) === TRUE) {
    echo '<div class="alert alert-success" role="alert">
            Product inserted successfully!
          </div>';
} else {
    echo '<div class="alert alert-danger" role="alert">
            Error: ' . $conn->error . '
          </div>';
}

$productVariantValues = "
    (1, 1, 'Blue', 128, 4, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(2, 1, 'Blue', 256, 6, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(3, 1, 'Blue', 512, 8, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(4, 1, 'Black', 128, 4, 12, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(5, 1, 'Black', 256, 6, 9, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(6, 1, 'Black', 512, 8, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(7, 1, 'White', 128, 4, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(8, 1, 'White', 256, 6, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(9, 1, 'White', 512, 8, 4, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(10, 2, 'Black', 256, 8, 20, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(11, 2, 'Black', 512, 12, 15, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(12, 2, 'Black', 1024, 16, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(13, 2, 'Cream', 256, 8, 18, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(14, 2, 'Cream', 512, 12, 12, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(15, 2, 'Cream', 1024, 16, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(16, 2, 'Red', 256, 8, 15, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(17, 2, 'Red', 512, 12, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(18, 2, 'Red', 1024, 16, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(19, 3, 'Obsidian', 128, 6, 13, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(20, 3, 'Obsidian', 256, 8, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(21, 3, 'Obsidian', 512, 12, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(22, 3, 'Porcelain', 128, 6, 12, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(23, 3, 'Porcelain', 256, 8, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(24, 3, 'Porcelain', 512, 12, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(25, 4, 'Titan', 128, 6, 15, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(26, 4, 'Titan', 256, 8, 12, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(27, 4, 'Titan', 512, 12, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(28, 4, 'Emerald Green', 128, 6, 14, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(29, 4, 'Emerald Green', 256, 8, 11, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(30, 4, 'Emerald Green', 512, 12, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(31, 5, 'White', 256, 8, 18, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(32, 5, 'White', 512, 12, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(33, 5, 'White', 1024, 16, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(34, 5, 'Silver', 256, 8, 16, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(35, 5, 'Silver', 512, 12, 9, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(36, 5, 'Silver', 1024, 16, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(37, 6, 'Graphite', 128, 6, 20, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(38, 6, 'Graphite', 256, 8, 14, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(39, 6, 'Graphite', 512, 12, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(40, 6, 'Lavender', 128, 6, 18, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(41, 6, 'Lavender', 256, 8, 12, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(42, 6, 'Lavender', 512, 12, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(43, 7, 'Forest Green', 256, 8, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(44, 7, 'Forest Green', 512, 12, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(45, 7, 'Forest Green', 1024, 16, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(46, 7, 'Burgundy', 256, 8, 9, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(47, 7, 'Burgundy', 512, 12, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(48, 7, 'Burgundy', 1024, 16, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(49, 8, 'Yellow', 256, 8, 16, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(50, 8, 'Yellow', 512, 12, 11, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(51, 8, 'Yellow', 1024, 16, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(52, 8, 'Lime Green', 256, 8, 14, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(53, 8, 'Lime Green', 512, 12, 9, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(54, 8, 'Lime Green', 1024, 16, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(55, 9, 'Porcelain', 256, 8, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(56, 9, 'Porcelain', 512, 12, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(57, 9, 'Porcelain', 1024, 16, 4, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(58, 9, 'Black', 256, 8, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(59, 9, 'Black', 512, 12, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(60, 9, 'Black', 1024, 16, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(61, 10, 'Midnight', 128, 4, 9, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(62, 10, 'Midnight', 256, 6, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(63, 10, 'Midnight', 512, 8, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(64, 10, 'Red', 128, 4, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(65, 10, 'Red', 256, 6, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(66, 10, 'Red', 512, 8, 6, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(67, 30, 'Sunrise Yellow', 128, 4, 12, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(68, 30, 'Sunrise Yellow', 256, 6, 8, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(69, 30, 'Sunrise Yellow', 512, 8, 5, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(70, 30, 'Sunset Orange', 128, 4, 10, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(71, 30, 'Sunset Orange', 256, 6, 7, '2025-01-06 16:17:05', '2025-01-06 16:28:42'),
	(72, 30, 'Sunset Orange', 512, 8, 4, '2025-01-06 16:17:05', '2025-01-06 16:28:42');";

$sqlInsertProductVariant = "INSERT INTO productvariant (
        variantID,productID, productColor, productStorage, productRam, 
        productStock, createdAt, updatedAt
    ) VALUES $productVariantValues";


if ($conn->query($sqlInsertProductVariant) === TRUE) {
    echo '<div class="alert alert-success" role="alert">
            Product Variant inserted successfully!
          </div>';
    // Optionally redirect after a short delay
    echo '<script>
            setTimeout(function() {
                window.location.href = "home.php";
            }, 2000); // Redirect after 2 seconds
          </script>';
} else {
    echo '<div class="alert alert-danger" role="alert">
            Error: ' . $conn->error . '
          </div>';
}


$conn->close();
// Bootstrap closing tags
echo '</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>';

// End output buffering
ob_end_flush();
