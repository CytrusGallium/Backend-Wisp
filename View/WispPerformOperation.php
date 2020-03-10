<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array("type","entity","id","property","amount"), array("s")))->result;

WispAccessManager::Get()->CheckUserLogin("json", $input["s"]);

if ($input["type"] == "add")
{
    Respond_Add($input);
}
else if ($input["type"] == "sub")
{
    Respond_Subtract();
}
else
{
    WispJsonMessages::ErrorMessage("invalid_operation_name","Invalid operation name");
}

// ---------------------------------------------------------------------------------------------------------------------

function Respond_Add($ParamInput)
{
    WispOperationManager::PerformOperation($ParamInput["type"], $ParamInput["entity"], $ParamInput["property"], $ParamInput["id"], $ParamInput["amount"]);
}

function Respond_Subtract($ParamEntity, $ParamID, $ParamAmount)
{

}

?>