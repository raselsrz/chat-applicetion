<?php 
$body = file_get_contents("php://input");
$data = json_decode($body, true);
$name = $data['name'];
$message = $data['message'];

 

 
$db = new SQLite3('chat.db'); 

// / drop table messages
// $db->exec("DROP TABLE messages");
//id auto ,name  , message  , time  
$db->exec("CREATE TABLE IF NOT EXISTS messages (id INTEGER PRIMARY KEY, name TEXT, message TEXT, time TEXT)");
$db->exec(query: "INSERT INTO messages (name, message, time) VALUES ('$name', '$message', datetime('now'))");
// echo json_encode(['name' => $name, 'message' => $message]);
// mysql


echo json_encode(['name' => $name, 'message' => $message]);