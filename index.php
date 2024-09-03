<?php
$botToken = "6476855791:AAGZ07MbW48QyFfR0gyd-XSFBCljsQ0jL1o";
$apiUrl = "https://api.telegram.org/bot" . $botToken;

$validKey = "akatsukinewgen";
$cooldownTime = 30; // Cooldown time in seconds

$update = file_get_contents("php://input");
$updateArray = json_decode($update, TRUE);

if (isset($updateArray["message"])) {
    $chatId = $updateArray["message"]["chat"]["id"];
    $text = $updateArray["message"]["text"];

    // Check if the user has already entered the correct key
    $keyFile = "key_$chatId.txt";
    if (file_exists($keyFile) && trim(file_get_contents($keyFile)) == $validKey) {
        sendWelcomeMessage($chatId);
    } else {
        // Check if the user is providing the key
        if ($text == $validKey) {
            file_put_contents($keyFile, $text);
            file_get_contents($apiUrl . "/sendMessage?chat_id=" . $chatId . "&text=You are welcome.❤️🔥");
            sendWelcomeMessage($chatId);
        } else {
            file_get_contents($apiUrl . "/sendMessage?chat_id=" . $chatId . "&text=Please provide the key to continue.");
        }
    }
} elseif (isset($updateArray["callback_query"])) {
    $callbackQuery = $updateArray["callback_query"];
    $chatId = $callbackQuery["message"]["chat"]["id"];
    $data = $callbackQuery["data"];

    // Verify the key before processing the request
    $keyFile = "key_$chatId.txt";
    if (!file_exists($keyFile) || trim(file_get_contents($keyFile)) != $validKey) {
        file_get_contents($apiUrl . "/sendMessage?chat_id=" . $chatId . "&text=Invalid key! Please contact @sigmaraven68 to get the correct key.");
        exit;
    }

    // Check cooldown
    $lastRequestFile = "last_request_$chatId.txt";
    $currentTime = time();

    if (file_exists($lastRequestFile)) {
        $lastRequestTime = file_get_contents($lastRequestFile);
        $timeSinceLastRequest = $currentTime - $lastRequestTime;

        if ($timeSinceLastRequest < $cooldownTime) {
            $remainingTime = $cooldownTime - $timeSinceLastRequest;
            $message = "Please wait $remainingTime seconds before generating another account.";
            file_get_contents($apiUrl . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message));
            exit;
        }
    }

    // Update the last request time
    file_put_contents($lastRequestFile, $currentTime);

    // Handle account generation based on button clicked
    switch ($data) {
        case "crunchyroll":
            $account = getAccountAndRemove("crunchyroll.txt");
            $message = $account ? "Here is your Crunchyroll account: " . $account : "No more Crunchyroll accounts available!";
            break;
        case "steam":
            $account = getAccountAndRemove("steam.txt");
            $message = $account ? "Here is your Steam account: " . $account : "No more Steam accounts available!";
            break;
        case "hotmail":
            $account = getAccountAndRemove("hotmail.txt");
            $message = $account ? "Here is your Hotmail account: " . $account : "No more Hotmail accounts available!";
            break;
        case "ipvanish":
            $account = getAccountAndRemove("ipvanish.txt");
            $message = $account ? "Here is your IPVanish account: " . $account : "No more IPVanish accounts available!";
            break;
        case "windscribe":
            $account = getAccountAndRemove("windscribe.txt");
            $message = $account ? "Here is your Windscribe account: " . $account : "No more Windscribe accounts available!";
            break;
        case "warp":
            $account = getAccountAndRemove("warp.txt");
            $message = $account ? "Here is your Warp account: " . $account : "No more Warp accounts available!";
            break;
        case "disneyplus":
            $account = getAccountAndRemove("disneyplus.txt");
            $message = $account ? "Here is your Disney+ account: " . $account : "No more Disney+ accounts available!";
            break;
        default:
            $message = "Invalid option!";
            break;
    }

    file_get_contents($apiUrl . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message));
}

function sendWelcomeMessage($chatId) {
    global $apiUrl;

    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => "Crunchyroll", 'callback_data' => "crunchyroll"],
                ['text' => "Steam", 'callback_data' => "steam"]
            ],
            [
                ['text' => "Hotmail", 'callback_data' => "hotmail"],
                ['text' => "IPVanish", 'callback_data' => "ipvanish"]
            ],
            [
                ['text' => "Windscribe", 'callback_data' => "windscribe"],
                ['text' => "Warp", 'callback_data' => "warp"]
            ],
            [
                ['text' => "Disney+", 'callback_data' => "disneyplus"]
            ]
        ]
    ];

    $replyMarkup = json_encode($keyboard);
    file_get_contents($apiUrl . "/sendMessage?chat_id=" . $chatId . "&text=Which account do you want to generate?&reply_markup=" . $replyMarkup);
}

function getAccountAndRemove($filePath) {
    if (file_exists($filePath) && is_readable($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        if ($lines && count($lines) > 0) {
            $account = array_shift($lines);
            file_put_contents($filePath, implode("\n", $lines));
            return $account;
        }
    }
    return null;
}
?>
