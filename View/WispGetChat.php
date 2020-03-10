<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array("user","start","offset"), array("s")))->result;

WispAccessManager::Get()->CheckUserLogin("json", $input["s"]);

Respond($input["user"], $input["start"], $input["offset"]);

// ---------------------------------------------------------------------------------------------------------------------

function Respond($ParamUser, $ParamStart, $ParamOffset)
{
    $messages = WispChatManager::GetChat($ParamUser, $ParamStart, $ParamOffset);
    echo json_encode($messages);
    exit();
}