<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array("id"), array("s")))->result;

WispAccessManager::Get()->CheckUserLogin("json", $input["s"]);

Respond();

// ---------------------------------------------------------------------------------------------------------------------

function Respond($ParamInput)
{
    WispOperationManager::CancelOperation($ParamInput["id"]);
}

?>