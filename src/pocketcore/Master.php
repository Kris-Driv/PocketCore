<?php
namespace pocketcore;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use pocketcore\utils\Logger;
use SplObjectStorage;

class Master implements MessageComponentInterface {
    
    protected \SplObjectStorage $servers;

    protected \SplObjectStorage $subscribers;

    protected Level $level;
    
    public function __construct(Logger $logger) {
        $this->logger = $logger;
        $this->level = new Level();
        
        $this->logger->info("Server started.");
        
        $this->servers = new SplObjectStorage;
        $this->subscribers = new SplObjectStorage;
    }
    
    /**
     * @return Logger
     */
    public function getLogger(){
        return $this->logger;
    }
    
    public function onOpen(ConnectionInterface $conn){
        $this->getLogger()->info($conn->remoteAddress . " connected.");

        $this->servers->attach($conn);
    }

    public function onClose(ConnectionInterface $conn){
        if($this->servers->contains($conn)) {
            $this->servers->detach($conn);
            $this->getLogger()->info($conn->remoteAddress . " server disconnected.");
            return;
        }
        if($this->subscribers->contains($conn)) {
            $this->subscribers->detach($conn);
            $this->getLogger()->info($conn->remoteAddress . " subscriber disconnected.");
            return;
        }
        $this->getLogger()->info($conn->remoteAddress . " unknown connection disconnected.");
    }
    
    public function onMessage(ConnectionInterface $conn, $message){
        try {

            $message = json_decode($message, true);

            if(!$message) {
                $conn->send(json_encode(['message' => 'could not decode recieved message: ' . $message]));
                $this->getLogger('Failed to decode message: ' . $message . ' recieved from: ' . $conn->remoteAddress);
                $conn->close();
                return;
            }

            $this->handleMessage($conn, $message);

        } catch(\Exception $e) {
            $this->getLogger()->info('Error handling message: ' . $e->getMessage());
            echo $e->getTraceAsString();
        }
    }

    public function handleMessage(ConnectionInterface $conn, array $message)
    {
        $subscriber = $this->subscribers->contains($conn);
        $server = $this->servers->contains($conn);

        if($server) {
            $this->handleServerRequest($conn, $message);
        }
        if($subscriber) {
            $this->handleSubscriberRequest($conn, $message);
        }
    }

    public function handleSubscriberRequest(connectionInterface $conn, array $message) 
    {
        if(isset($message['type'])) {

            $body = $message['body'] ?? [];

            switch(($type = $message['type'] ?? null)) {

                case null:
                    $this->getLogger()->info('Malformed subscriber request, missing type');
                    break;

                case 'level':
                    $conn->send($this->level->toResponse());
                    $this->getLogger()->info('Sent current level chunks to subscriber');
                    break;

                default:
                    throw new \Exception("Unknown request type: " . $message['type']);
            }

        }
    }

    public function handleServerRequest(ConnectionInterface $conn, array $message)
    {
        if(isset($message['type'])) {

            $body = $message['body'] ?? [];

            switch(($type = $message['type'] ?? null)) {

                case null:
                    $this->getLogger()->info('Malformed server request, missing type');
                    break;

                case 'subscribe':
                    $this->servers->detach($conn);
                    $this->subscribers->attach($conn);
                    $this->getLogger()->info($conn->remoteAddress . " subscriber connected");
                    break;

                case 'ping':
                    $this->getLogger()->info('Got ping! Timestamp: ' . $body['time']);
                    $conn->send(json_encode($message));
                    break;

                case 'message':
                    $this->getLogger()->info('<' . $conn->remoteAddress . '>: ' . $body['message']);

                    if($body['broadcast']) {
                        $this->broadcastToSubscribers(json_encode($message));
                    }
                    break;

                case 'chunk':

                    $this->getLogger()->info('Broadcasting chunk ('.$body['chunk']['x'].', '.$body['chunk']['z'].') recieved from ' . $conn->remoteAddress);
                    // Update level, to cache
                    $this->level->setChunk($body['chunk']['x'], $body['chunk']['z'], $body['chunk']);

                    $this->broadcastToSubscribers(json_encode($message));
                    break;

                case 'player.join':
                    $this->getLogger()->info('Player ' . $body['name'] . ' has joined');
                    $this->broadcastToSubscribers(json_encode($message));
                    break;
                
                case 'player.leave':
                    $this->getLogger()->info("Player " . $body['name'] . ' has left');
                    $this->broadcastToSubscribers(json_encode($message));
                    break;

                case 'entity.position':
                    $this->broadcastToSubscribers(json_encode($message));
                    break;

                default:
                    throw new \Exception("Unknown request type: " . $message['type']);
            }

        }
    }

    public function broadcastToSubscribers(string $data) {
        foreach($this->subscribers as $subscriber) {
            $subscriber->send($data);
        }
    }
    
    public function onError(ConnectionInterface $conn, \Exception $e){
        $this->getLogger()->warning("The following error occured ".$e->getCode()."#: ".$e->getMessage());
        
        $this->connections->detach($conn);
    }
    

    # TODO, handle terminate signals, and broadcast appropriate data to notify connected sockets about termination
    
}