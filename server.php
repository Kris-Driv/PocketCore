<?php

define("API", 1);

require __DIR__ . '/vendor/autoload.php';

use Ratchet\Server\IoServer;
use pocketcore\Master;
use pocketcore\utils\Logger;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

$logger = new Logger();
$st = microtime(true);

$ip = '0.0.0.0';
$port = 27095;

$logger->info("Starting...");
$logger->info("Accepting connections to ".$ip.":".$port);

$server = IoServer::factory(new HttpServer(new WsServer(new Master($logger))), $port, $ip);
    
$server->run();
$logger->info("Server stopped!");