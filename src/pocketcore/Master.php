<?php
namespace pocketcore;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use pocketcore\utils\Logger;

class Master implements MessageComponentInterface {
    
    protected array $servers = [];
    
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        
        $this->logger->info("Server started.");
    }
    
    /**
     * @return Logger
     */
    public function getLogger(){
        return $this->logger;
    }
    
    public function onOpen(ConnectionInterface $conn){
        $this->getLogger()->info($conn->remoteAddress . " connected.");
    }

    public function onClose(ConnectionInterface $conn){
        $this->getLogger()->info($conn->remoteAddress . " disconnected.");
    }
    
    public function onMessage(ConnectionInterface $conn, $message){
        var_dump($message);
        $this->getLogger('Message from ' . $conn->remoteAddress .': ' . $message);

        $conn->send($message);
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e){
        $this->getLogger()->warning("The following error occured ".$e->getCode()."#: ".$e->getMessage());
        
        $this->connections->detach($conn);
    }
    
    
}