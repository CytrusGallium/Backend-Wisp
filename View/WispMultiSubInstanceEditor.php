<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array("entity", "sub_instance_property", "entity_id", "action", "sub_instance_id"), array("s")))->result;

WispAccessManager::Get()->CheckUserLogin("json", $input["s"]);

if ($input["action"] == "add")
    Respond_Add();
elseif ($input["action"] == "delete")
    Respond_Delete();
else
    WispJsonMessages::ErrorMessage("invalid_action","Invalid action name while editing a multi-sub-instance list.");

function Respond_Add()
{

}

function Respond_Delete()
{
    
}

?>