<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array(), array("s")))->result;

WispAccessManager::Get()->CheckUserLogin("json", $input["s"]);

Respond();

// =======================================================================================================

function Respond()
{
    echo WispAccessManager::GetConnectedUsers();
    exit();
}

?>