<?php
//Make sure that the content type is POST
if (strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0) {
    $logString = "Request method must be POST!";
    file_put_contents('error.txt', $logString . PHP_EOL, FILE_APPEND);

    throw new Exception('Request method must be POST!');
}

//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strcasecmp($contentType, 'application/json') != 0) {
    $logString = "Content type must be: application/json";
    file_put_contents('error.txt', $logString . PHP_EOL, FILE_APPEND);

    throw new Exception('Content type must be: application/json');
}

//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);

//If json_decode failed, the JSON is invalid.
if (!is_array($decoded)) {
    $logString = "Received content contained invalid JSON!";
    file_put_contents('error.txt', $logString . PHP_EOL, FILE_APPEND);
    file_put_contents('error.txt', $decoded . PHP_EOL, FILE_APPEND);

    throw new Exception('Received content contained invalid JSON!');
}

//Decode and save data
$dev_id = $decoded['end_device_ids']['device_id'];
$time = $decoded['uplink_message']['received_at'];
$temperature = $decoded['uplink_message']['decoded_payload']['temp'];
$hum = $decoded['uplink_message']['decoded_payload']['hum'];
$co2 = $decoded['uplink_message']['decoded_payload']['co2'];

$mysql = new mysqli("localhost", "raphi", "Luusbueb#02", "CO2");
$mysql->set_charset("utf8");

                                                                                                         //Add 1 hour because of timezone
$stmt = $mysql->prepare("INSERT INTO `sensor_data` (`dev_id`, `time`, `temp`, `hum`, `co2`) VALUES(?,DATE_ADD(?, INTERVAL 1 HOUR),?,?,?)");
$stmt->bind_param("ssdii", $dev_id, $time, $temperature, $hum, $co2);

if (!$stmt->execute()) {
    $logString = "Didn't save Entry ----";
    file_put_contents('error.txt', $logString . PHP_EOL, FILE_APPEND);
    file_put_contents('error.txt', $stmt->error . PHP_EOL, FILE_APPEND);
    file_put_contents('error.txt', $decoded . PHP_EOL, FILE_APPEND);
    throw new Exception("Didn't save Entry");
}

$stmt->close();