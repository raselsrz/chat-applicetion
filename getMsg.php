<?php 
// set max time out
set_time_limit(0);

 
$db = new SQLite3('chat.db'); 
$name = $_GET['name'];
$offset = $_GET['offset'] ?? 0;
$offset = (int)$offset;
 
$messages = $db->query("SELECT * FROM messages ORDER BY id DESC LIMIT 10 OFFSET $offset");
 
$messagesArray = [];
while ($row = $messages->fetchArray()) {
    $messagesArray[] = $row;
}
// reverse array
$messagesArray = array_reverse($messagesArray);
echo json_encode($messagesArray);
