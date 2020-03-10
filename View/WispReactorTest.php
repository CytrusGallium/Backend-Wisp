<?php
require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

echo "Sending message...\n";
//WispReactor::Send($_GET["message"]);
$_SESSION["Message"] = "Hey!!!";
echo "Message sent...\n";

?>