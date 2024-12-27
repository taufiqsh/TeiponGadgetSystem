<?php
    require 'vendor/autoload.php';

    use Google\Cloud\Dialogflow\V2\SessionsClient;
    use Google\Cloud\Dialogflow\V2\TextInput;
    use Google\Cloud\Dialogflow\V2\QueryInput;

    // Get user input
    $data = json_decode(file_get_contents('php://input'), true);
    $userMessage = isset($data['message']) ? $data['message'] : ''; // Ensure message is set

    // Return error if no message was sent
    if (empty($userMessage)) {
        echo json_encode(['reply' => 'Please provide a valid message.']);
        exit;
    }

    // Dialogflow setup
    $projectId = 'teiponchatbot';
    $sessionId = uniqid(); // Consider saving session for repeat users
    $languageCode = 'en';

    // Initialize Dialogflow client
    $sessionsClient = new SessionsClient(['credentials' => 'dialogflow_key.json']);
    $session = $sessionsClient->sessionName($projectId, $sessionId);

    // Prepare user input for Dialogflow
    $textInput = (new TextInput())->setText($userMessage)->setLanguageCode($languageCode);
    $queryInput = (new QueryInput())->setText($textInput);

    // Send user input to Dialogflow
    try {
        $response = $sessionsClient->detectIntent($session, $queryInput);
        $sessionsClient->close();

        // Get the bot's response
        $fulfillmentText = $response->getQueryResult()->getFulfillmentText();

        if (empty($fulfillmentText)) {
            $fulfillmentText = 'Sorry, I didn\'t quite catch that. Could you please rephrase?';
        }

        echo json_encode(['reply' => $fulfillmentText]);
    } catch (Exception $e) {
        // Handle exceptions
        echo json_encode(['reply' => 'An error occurred: ' . $e->getMessage()]);
    }
?>
