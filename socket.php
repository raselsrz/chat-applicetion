<?php
require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
set_time_limit(0);

 
$db = new SQLite3('chat.db'); 

// Create a WebSocket server
$wsServer = new Worker('websocket://127.0.0.1:8980');
//on new connection 
$wsServer->onWebSocketConnect = function($connection, $header){
    // $_GET is available here and we store it to $connection as dynamic property so we can use it later.
    $connection->nameX = $_GET['name'];
    var_dump($_GET);
};

// Store connected clients
$wsServer->onConnect = function ($connection) {
    // var_dump($_GET);

    echo "New connection: {$connection->id}\n";
};

// Handle incoming messages
$wsServer->onMessage = function ($connection, $message) use ($wsServer) {
    global $db;
    // var_dump($_GET);
    // echo "Received: $message\n";
    $datas = json_decode($message, true);

    $name = $connection->nameX;
    //type
    $type = $datas['type'];
    if ($type == 'typing') {
        foreach ($wsServer->connections as $client) {
            if ($connection->nameX == $client->nameX) {
                continue;
            }

            $client->send(json_encode([
                'type' => 'typing',
                'user' => $connection->nameX,
                'message' => $datas['message'],
            ]));
        }
        return;
    }

    if ($type == 'message') {
        
    //db inert
    $message = $datas['message'];
    $db->exec(query: "INSERT INTO messages (name, message, time) VALUES ('$name', '$message', datetime('now'))");
    // Broadcast the message to all connected clients
    foreach ($wsServer->connections as $client) {
        if ($connection->nameX == $client->nameX) {
            continue;
        }

        $client->send(json_encode([
            'type' => 'message',
            'user' => $connection->nameX,
            'time' => date('H:i'),
            'message' => $message,
        ]));
    }
        
    }

};

// Handle closed connections
$wsServer->onClose = function ($connection) {
    
    // var_dump($_GET);
    echo "Connection {$connection->id} closed\n";
};

// Run the server
Worker::runAll();
