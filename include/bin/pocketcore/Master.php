<?php
namespace pocketcore;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use pocketcore\utils\Logger;
use pocketcore\Server;

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
    
    /**
     * Called when new server is trying to connect
     * 
     * @void
     */
    public function onOpen(ConnectionInterface $conn){
        $this->getLogger()->info($conn->remoteAddress . " connected.");
    }
    
    /**
     * Called when server is making a request (API)
     * 
     * @param ConnectionInterface $conn
     * @param string $msg
     */
    public function onMessage(ConnectionInterface $conn, $message){
        
    }
    
    public function onClose(ConnectionInterface $conn){
        
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e){
        $this->getLogger()->warning("The following error occured ".$e->getCode()."#: ".$e->getMessage());
        
        $this->connections->detach($conn);
    }
    
    
}