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

// Get JSON payload from Dialogflow
$request = file_get_contents('php://input');
$requestJson = json_decode($request, true);

// Intent name
$intentName = $requestJson['queryResult']['intent']['displayName'];

// Initialize response
$responseText = '';

try {
    switch ($intentName) {
        case 'Check Availability':
            $phoneName = strtolower(trim($requestJson['queryResult']['parameters']['phoneName']));
            $stmt = $conn->prepare("SELECT phonestock FROM phone WHERE LOWER(phoneName) LIKE :phoneName");
            $likePhoneName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $phoneName) . '%';

            $stmt->bindParam(':phoneName', $likePhoneName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                if ($result['phonestock'] > 0) {
                    $responseText = "Yes, we have {$result['phonestock']} units of $phoneName in stock.";
                } else {
                    $responseText = "Sorry, $phoneName is currently out of stock.";
                }
            } else {
                $responseText = "I couldn't find any information about $phoneName. ";
            }
            break;

        case 'Price Inquiry':
            $phoneName = strtolower(trim($requestJson['queryResult']['parameters']['phoneName']));
            $stmt = $conn->prepare("SELECT phonePrice FROM phone WHERE LOWER(phoneName) LIKE :phoneName");
            $likePhoneName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $phoneName) . '%';

            $stmt->bindParam(':phoneName', $likePhoneName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $responseText = "The price of $phoneName is RM {$result['phonePrice']}. ";
            } else {
                $responseText = "I couldn't find any information about $phoneName. ";
            }
            break;

        case 'Full Specifications':
            $phoneName = strtolower(trim($requestJson['queryResult']['parameters']['phoneName']));
            $stmt = $conn->prepare("SELECT * FROM phone WHERE phoneName LIKE :phoneName");
            $likePhoneName = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $phoneName) . '%';

            $stmt->bindParam(':phoneName', $likePhoneName);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $responseText = "<ul>";
                $responseText .= "<li>Brand: {$result['phoneBrand']}</li>";
                $responseText .= "<li>Screen Size: {$result['phoneScreenSize']}</li>";
                $responseText .= "<li>Battery Capacity: {$result['phoneBatteryCapacity']}</li>";
                $responseText .= "<li>Camera: {$result['phoneCameraSpecs']}</li>";
                $responseText .= "<li>Processor: {$result['phoneProcessor']}</li>";
                $responseText .= "<li>RAM: {$result['phoneRam']}</li>";
                $responseText .= "<li>Storage: {$result['phoneStorage']}</li>";
                $responseText .= "<li>OS: {$result['phoneOS']}</li>";
                $responseText .= "<li>Price: RM {$result['phonePrice']}</li>";
                $responseText .= "<li>Release Date: {$result['phoneReleaseDate']}</li>";
                $responseText .= "</ul>";
            } else {
                $responseText = "I couldn't find any information about $phoneName.";
            }
            break;

        case 'Phone Comparisons':
            $phone1 = strtolower(trim($requestJson['queryResult']['parameters']['phoneName1']));
            $phone2 = strtolower(trim($requestJson['queryResult']['parameters']['phoneName2']));

            $stmt = $conn->prepare("SELECT * FROM phone WHERE LOWER(phoneName) LIKE :phone1 OR LOWER(phoneName) LIKE :phone2");
            $likePhone1 = "%$phone1%";
            $likePhone2 = "%$phone2%";
            $stmt->bindParam(':phone1', $likePhone1);
            $stmt->bindParam(':phone2', $likePhone2);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) >= 2) {
                $responseText = "Comparison between phones: ";
                foreach ($results as $phone) {
                    $responseText .= "<li>{$phone['phoneName']}:</li><ul>";
                    $responseText .= "<li>Price: RM {$phone['phonePrice']}</li>";
                    $responseText .= "<li>RAM: {$phone['phoneRam']}</li>";
                    $responseText .= "<li>Storage: {$phone['phoneStorage']}</li>";
                    $responseText .= "<li>Battery: {$phone['phoneBatteryCapacity']}</li>";
                    $responseText .= "</ul>";
                }
            } else {
                $responseText = "<ul><li>I couldn't find enough data for comparison.</li></ul>";
            }
            break;

        case 'List Android Phones':
            $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone WHERE LOWER(phoneOS) LIKE '%android%'");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the Android phones we have: ";
                foreach ($results as $phone) {
                    $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                }
            } else {
                $responseText = "<ul><li>We currently do not have any Android phones in our inventory.</li></ul>";
            }
            break;

        case 'List Iphone Phones':
            $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone WHERE LOWER(phoneOS) LIKE '%ios%'");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the iPhones phones we have: ";
                foreach ($results as $phone) {
                    $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                }
            } else {
                $responseText = "<ul><li>We currently do not have any Iphone phones in our inventory.</li></ul>";
            }
            break;

        case 'Check Phone Brand':
            $phoneBrand = strtolower(trim($requestJson['queryResult']['parameters']['phoneBrand']));
            $stmt = $conn->prepare("SELECT phoneName, phonePrice FROM phone WHERE LOWER(phoneBrand) LIKE :phoneBrand");
            $likePhoneBrand = "%$phoneBrand%";
            $stmt->bindParam(':phoneBrand', $likePhoneBrand);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Yes, we have the following phones from $phoneBrand: ";
                foreach ($results as $phone) {
                    $responseText .= "<li>{$phone['phoneName']} - RM {$phone['phonePrice']}</li>";
                }
            } else {
                $responseText = "<ul><li>Sorry, we currently do not have any phones from $phoneBrand in our inventory.</li></ul>";
            }
            break;

        case 'Get Cheapest Phones':
            $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone ORDER BY phonePrice ASC LIMIT 10");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the 10 cheapest phones we have: ";
                foreach ($results as $phone) {
                    $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                }
            } else {
                $responseText = "<ul><li>Sorry, we currently have no phones in our inventory.</li></ul>";
            }
            break;

        case 'Get Expensive Phones':
            $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone ORDER BY phonePrice DESC LIMIT 10");
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $responseText = "Here are the 10 most expensive phones we have: ";
                foreach ($results as $phone) {
                    $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                }
            } else {
                $responseText = "<ul><li>Sorry, we currently have no phones in our inventory.</li></ul>";
            }
            break;

        case 'Phones Within Budget':
            $budget = floatval($requestJson['queryResult']['parameters']['budget']);
            $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone WHERE phonePrice <= :budget ORDER BY phonePrice ASC");
            $stmt->bindParam(':budget', $budget);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($results) {
                $phones = [];
                foreach ($results as $phone) {
                    $phones[] = "<ul><li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li></ul>";
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
                $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone WHERE LOWER(phoneRam) LIKE :ram");
                $likeRam = "%$ram%";
                $stmt->bindParam(':ram', $likeRam);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($results) {
                    $responseText = "Here are the phones with {$ram} RAM: ";
                    foreach ($results as $phone) {
                        $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                    }
                } else {
                    $responseText = "<ul><li>Sorry, we don't have any phones with {$ram} RAM in our inventory.</li></ul>";
                }
            } else {
                $responseText = "<ul><li>Please specify a valid RAM size.</li></ul>";
            }
            break;

        case 'Check Phone by Storage':
            $storage = strtolower(trim($requestJson['queryResult']['parameters']['storage']));

            if (!empty($storage)) {
                // Query to find phones with specified storage
                $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone WHERE LOWER(phoneStorage) LIKE :storage");
                $likeStorage = "%$storage%";
                $stmt->bindParam(':storage', $likeStorage);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($results) {
                    $responseText = "Here are the phones with {$storage} storage: ";
                    foreach ($results as $phone) {
                        $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                    }
                } else {
                    $responseText = "<ul><li>Sorry, we don't have any phones with {$storage} storage in our inventory.</li></ul>";
                }
            } else {
                $responseText = "<ul><li>Please specify a valid storage size.</li></ul>";
            }
            break;


        case 'Check Phone by Camera Specs':
            $cameraSpecs = strtolower(trim($requestJson['queryResult']['parameters']['cameraSpecs']));

            if (!empty($cameraSpecs)) {
                // Query to find phones with specified camera specs
                $stmt = $conn->prepare("SELECT phoneName, phoneBrand, phonePrice FROM phone WHERE LOWER(phoneCameraSpecs) LIKE :cameraSpecs");
                $likeCameraSpecs = "%$cameraSpecs%";
                $stmt->bindParam(':cameraSpecs', $likeCameraSpecs);
                $stmt->execute();
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($results) {
                    $responseText = "Here are the phones with '{$cameraSpecs}' camera specs: ";
                    foreach ($results as $phone) {
                        $responseText .= "<li>{$phone['phoneName']} ({$phone['phoneBrand']}) - RM {$phone['phonePrice']}</li>";
                    }
                } else {
                    $responseText = "<ul><li>Sorry, we don't have any phones with '{$cameraSpecs}' camera specs in our inventory.</li></ul>";
                }
            } else {
                $responseText = "<ul><li>Please specify valid camera specifications.</li></ul>";
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
