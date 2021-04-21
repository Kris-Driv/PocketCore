<?php

require __DIR__ . "/vendor/autoload.php";

$client = new WebSocket\Client("ws://localhost:27095");

$running = true;
$counter = 0;
$pingRate = 5;
$shouldRead = false;

while($running) {
    $response = null;
    
    if($counter % $pingRate === 0) {
        send("ping");
    }

    if($shouldRead) {
        $response = $client->receive();

        $shouldRead = false;
    }

    if($response) {
        echo $response . PHP_EOL;
    }
    sleep(1);

    $counter ++;
}

function send($data) {
    global $client, $shouldRead;
    $client->text($data);

    $shouldRead = true;
}

$client->close();