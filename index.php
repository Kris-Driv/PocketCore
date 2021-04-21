<?php

const TARGET_ADDRESS = 'localhost';
const TARGET_PORT = 27095;

try {
    
    if(!($sock = socket_create(AF_INET, SOCK_STREAM, 0)))
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
         
        throw new \Exception($errormsg, $errorcode);
    }
    //Connect socket to remote server
    if(!socket_connect($sock , TARGET_ADDRESS, TARGET_PORT))
    {
        $errorcode = socket_last_error();
        $errormsg = socket_strerror($errorcode);
         
        throw new \Exception($errormsg, $errorcode);
    }
     
} catch (\Exception $e){
    die("Following error occured ".$e->getCode()."#: ".$e->getMessage());
}

echo "Connected successfully!";

$message = json_encode([
    'message' => 'Hello, World!'
]);
    
//Send the message to the server
if( !$r = socket_send ( $sock , $message , strlen($message) , 0))
{
    $errorcode = socket_last_error();
    $errormsg = socket_strerror($errorcode);
        
    die("Could not send data: [$errorcode] $errormsg \n");
    }
    
    
?>
