<?php
// Set the Content-Type header to JSON
header('Content-Type: application/json');

// Database connection
$host = 'localhost';
$dbname = 'teipon_gadget';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['fulfillmentText' => 'Database connection failed: ' . $e->getMessage()]));
}

// Helper function to fetch data from the database
function fetchFromDatabase($conn, $query, $params = [])
{
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get JSON payload from Dialogflow
$request = file_get_contents('php://input');
$requestJson = json_decode($request, true);
$intentName = $requestJson['queryResult']['intent']['displayName']; // Intent name
$responseText = ''; // Initialize response

try {
    switch ($intentName) {
        case 'Default Welcome Intent':
            $responseText = "Hi! I'm Kojek. Here's how I can assist you: -<br>" .
                "<ul>" .
                "<li>List Android or iPhone phones</li>" .
                "<li>Check phone brands</li>" .
                "<li>Price inquiry</li>" .
                "<li>Get full specifications</li>" .
                "<li>Phone comparisons</li>" .
                "<li>List all available phones</li>" .
                "<li>Check phone availability</li>" .
                "<li>Find cheapest or most expensive phones</li>" .
                "<li>Find phones within a budget</li>" .
                "<li>Search phones by RAM, storage, or camera specs</li>" .
                "</ul>" .
                "Just type your query, and I'll help you out! 😊";
            break;

        case 'Default Fallback Intent':
            $responseText = "I'm sorry, I couldn't understand that. Here's what you can ask me:<br>" .
                "<ul>" .
                "<li>List Android or iPhone phones</li>" .
                "<li>Check phone brands</li>" .
                "<li>Price inquiry</li>" .
                "<li>Get full specifications</li>" .
                "<li>Phone comparisons</li>" .
                "<li>List all available phones</li>" .
                "<li>Check phone availability</li>" .
                "<li>Find cheapest or most expensive phones</li>" .
                "<li>Find phones within a budget</li>" .
                "<li>Search phones by RAM, storage, or camera specs</li>" .
                "</ul>" .
                "Try again, and I'll help you out! 😊";
            break;

        case 'Check Availability':
            $productName = strtolower(trim($requestJson['queryResult']['parameters']['productName']));
            $stmt = $conn->prepare("
                        SELECT SUM(pv.productStock) AS totalStock, p.productName
                        FROM productvariant pv
                        JOIN product p ON pv.productID = p.productID
                        WHERE LOWER(p.productName) LIKE :productName
                        GROUP BY p.productName;
                    ");
            $likeProductName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $productName) . '%';
            $stmt->bindParam(':productName', $likeProductName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                if ($result['totalStock'] > 0) {
                    $responseText = "Yes, we have {$result['totalStock']} units of {$result['productName']} in stock.";
                } else {
                    $responseText = "Sorry, {$result['productName']} is currently out of stock.";
                }
            } else {
                $responseText = "I couldn't find any information about $productName.";
            }
            break;

        case 'Price Inquiry':
            $productName = strtolower(trim($requestJson['queryResult']['parameters']['productName']));
            $stmt = $conn->prepare("
                        SELECT productPrice, productName 
                        FROM product 
                        WHERE LOWER(productName) LIKE :productName
                    ");
            $likeProductName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $productName) . '%';
            $stmt->bindParam(':productName', $likeProductName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $responseText = "The price of {$result['productName']} is RM {$result['productPrice']}.";
            } else {
                $responseText = "I couldn't find any information about $productName.";
            }
            break;

        case 'Full Specifications':
            $productName = strtolower(trim($requestJson['queryResult']['parameters']['productName']));
            $stmt = $conn->prepare("
                        SELECT * 
                        FROM product 
                        WHERE LOWER(productName) LIKE :productName
                    ");
            $likeProductName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $productName) . '%';
            $stmt->bindParam(':productName', $likeProductName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $responseText = "<ul>";
                $responseText .= "<li>Brand: {$result['productBrand']}</li>";
                $responseText .= "<li>Screen Size: {$result['productScreenSize']}</li>";
                $responseText .= "<li>Battery Capacity: {$result['productBatteryCapacity']}</li>";
                $responseText .= "<li>Camera: {$result['productCameraSpecs']}</li>";
                $responseText .= "<li>Processor: {$result['productProcessor']}</li>";
                $responseText .= "<li>OS: {$result['productOS']}</li>";
                $responseText .= "<li>Price: RM {$result['productPrice']}</li>";
                $responseText .= "<li>Release Date: {$result['productReleaseDate']}</li>";
                $responseText .= "</ul>";
            } else {
                $responseText = "I couldn't find any information about $productName.";
            }
            break;

        case 'Phone Comparisons':
            $productName1 = strtolower(trim($requestJson['queryResult']['parameters']['productName1']));
            $productName2 = strtolower(trim($requestJson['queryResult']['parameters']['productName2']));

            $stmt = $conn->prepare("
                        SELECT * 
                        FROM product 
                        WHERE LOWER(productName) LIKE :productName1 OR LOWER(productName) LIKE :productName2
                    ");
            $likeProductName1 = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $productName1) . '%';
            $likeProductName2 = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $productName2) . '%';
            $stmt->bindParam(':productName1', $likeProductName1);
            $stmt->bindParam(':productName2', $likeProductName2);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) >= 2) {
                $responseText = "Comparison between products:";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']}:</li><ul>";
                    $responseText .= "<li>Price: RM {$product['productPrice']}</li>";
                    $responseText .= "<li>Screen Size: {$product['productScreenSize']}</li>";
                    $responseText .= "<li>Battery Capacity: {$product['productBatteryCapacity']}</li>";
                    $responseText .= "<li>Processor: {$product['productProcessor']}</li>";
                    $responseText .= "</ul>";
                }
            } else {
                $responseText = "I couldn't find enough data for comparison.";
            }
            break;


        case 'List Android Phones':
            $stmt = $conn->prepare("
                                SELECT p.productName, p.productBrand, p.productPrice 
                                FROM product p 
                                WHERE LOWER(p.productOS) LIKE '%android%'
                            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the Android phones we have: ";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']} ({$product['productBrand']})<br>RM {$product['productPrice']}</li>";
                }
            } else {
                $responseText = "We currently do not have any Android phones in our inventory.";
            }
            break;

        case 'List Available Phones':
            $stmt = $conn->prepare("
                                SELECT p.productName, p.productBrand, p.productPrice, p.productOS 
                                FROM product p 
                                WHERE LOWER(p.productOS) LIKE '%android%' OR LOWER(p.productOS) LIKE '%ios%' 
                                ORDER BY p.productBrand, p.productName
                            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here is the list of available phones:<ul>";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']} ({$product['productBrand']}) - RM {$product['productPrice']} [OS: {$product['productOS']}]</li>";
                }
                $responseText .= "</ul>";
            } else {
                $responseText = "Sorry, there are no phones currently available in our inventory.";
            }
            break;

        case 'List Iphone Phones':
            $stmt = $conn->prepare("
                                SELECT p.productName, p.productBrand, p.productPrice 
                                FROM product p 
                                WHERE LOWER(p.productOS) LIKE '%ios%'
                            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the iPhones we have: ";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']} ({$product['productBrand']})<br>RM {$product['productPrice']}</li>";
                }
            } else {
                $responseText = "We currently do not have any iPhones in our inventory.";
            }
            break;

        case 'Check Phone Brand':
            $phoneBrand = strtolower(trim($requestJson['queryResult']['parameters']['phoneBrand']));
            $stmt = $conn->prepare("
                                SELECT p.productName, p.productPrice 
                                FROM product p 
                                WHERE LOWER(p.productBrand) LIKE :phoneBrand
                            ");
            $likePhoneBrand = "%$phoneBrand%";
            $stmt->bindParam(':phoneBrand', $likePhoneBrand);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Yes, we have the following phones from $phoneBrand: ";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']}<br>RM {$product['productPrice']}</li>";
                }
            } else {
                $responseText = "Sorry, we currently do not have any phones from $phoneBrand in our inventory.";
            }
            break;

        case 'Get Cheapest Phones':
            $stmt = $conn->prepare("
                                SELECT p.productName, p.productBrand, p.productPrice 
                                FROM product p 
                                ORDER BY p.productPrice ASC LIMIT 10
                            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the 10 cheapest phones we have: ";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']} ({$product['productBrand']})<br>RM {$product['productPrice']}</li>";
                }
            } else {
                $responseText = "Sorry, we currently have no phones in our inventory.";
            }
            break;

        case 'Get Expensive Phones':
            $stmt = $conn->prepare("
                                SELECT p.productName, p.productBrand, p.productPrice 
                                FROM product p 
                                ORDER BY p.productPrice DESC LIMIT 10
                            ");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the 10 most expensive phones we have: ";
                foreach ($results as $product) {
                    $responseText .= "<li>{$product['productName']} ({$product['productBrand']})<br>RM {$product['productPrice']}</li>";
                }
            } else {
                $responseText = "Sorry, we currently have no phones in our inventory.";
            }
            break;


        case 'Phones Within Budget':
            $budget = floatval($requestJson['queryResult']['parameters']['budget']);
            $stmt = $conn->prepare("
                        SELECT p.productName, p.productBrand, p.productPrice 
                        FROM product p 
                        WHERE p.productPrice <= :budget 
                        ORDER BY p.productPrice ASC
                    ");
            $stmt->bindParam(':budget', $budget);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $phones = [];
                foreach ($results as $product) {
                    $phones[] = "<ul><li>{$product['productName']} ({$product['productBrand']}) <br> RM {$product['productPrice']}</li></ul>";
                }
                $responseText = "Here are the phones you can get within a budget of RM{$budget}:\n" . implode("\n", $phones);
            } else {
                $responseText = "Sorry, there are no phones available within a budget of RM{$budget}.";
            }
            break;

        case 'Check Phone by RAM':
            $ram = strtolower(trim($requestJson['queryResult']['parameters']['ram']));

            if (!empty($ram)) {
                // Query to find phones with specified RAM
                $stmt = $conn->prepare("
                            SELECT p.productName, p.productBrand, p.productPrice 
                            FROM product p 
                            WHERE LOWER(p.productRam) LIKE :ram
                        ");
                $likeRam = "%$ram%";
                $stmt->bindParam(':ram', $likeRam);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($results) {
                    $responseText = "Here are the phones with {$ram} RAM: ";
                    foreach ($results as $product) {
                        $responseText .= "<li>{$product['productName']} ({$product['productBrand']}) <br> RM {$product['productPrice']}</li>";
                    }
                } else {
                    $responseText = "Sorry, we don't have any phones with {$ram}GB RAM in our inventory.";
                }
            } else {
                $responseText = "Please specify a valid RAM size.";
            }
            break;

        case 'Check Phone by Storage':
            $storage = strtolower(trim($requestJson['queryResult']['parameters']['storage']));

            if (!empty($storage)) {
                // Query to find phones with specified storage
                $stmt = $conn->prepare("
                            SELECT p.productName, p.productBrand, p.productPrice 
                            FROM product p 
                            WHERE LOWER(p.productStorage) LIKE :storage
                        ");
                $likeStorage = "%$storage%";
                $stmt->bindParam(':storage', $likeStorage);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($results) {
                    $responseText = "Here are the phones with {$storage} storage: ";
                    foreach ($results as $product) {
                        $responseText .= "<li>{$product['productName']} ({$product['productBrand']}) <br> RM {$product['productPrice']}</li>";
                    }
                } else {
                    $responseText = "Sorry, we don't have any phones with {$storage}GB storage in our inventory.";
                }
            } else {
                $responseText = "Please specify a valid storage size.";
            }
            break;

        case 'Check Phone by Camera Specs':
            $cameraSpecs = strtolower(trim($requestJson['queryResult']['parameters']['cameraSpecs']));

            if (!empty($cameraSpecs)) {
                // Query to find phones with specified camera specs
                $stmt = $conn->prepare("
                            SELECT p.productName, p.productBrand, p.productPrice 
                            FROM product p 
                            WHERE LOWER(p.productCameraSpecs) LIKE :cameraSpecs
                        ");
                $likeCameraSpecs = "%$cameraSpecs%";
                $stmt->bindParam(':cameraSpecs', $likeCameraSpecs);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($results) {
                    $responseText = "Here are the phones with '{$cameraSpecs}' camera specs: ";
                    foreach ($results as $product) {
                        $responseText .= "<li>{$product['productName']} ({$product['productBrand']}) <br> RM {$product['productPrice']}</li>";
                    }
                } else {
                    $responseText = "Sorry, we don't have any phones with '{$cameraSpecs}MP' camera in our inventory.";
                }
            } else {
                $responseText = "Please specify valid camera specifications.";
            }
            break;

        default:
            $responseText = "I couldn't handle your request.";
            break;
    }

    //handle error
} catch (Exception $e) {
    $responseText = "An error occurred: " . $e->getMessage();
}

// Send response
$response = ['fulfillmentText' => $responseText];
echo json_encode($response);
