<?php

set_time_limit(300);

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array("user","pass")))->result;

Respond($input["user"], $input["pass"]);

// ---------------------------------------------------------------------------------------------------------------------

function Respond($ParamUser, $ParamPass)
{
    echo WispAccessManager::Get()->Login($ParamUser, $ParamPass);
    exit();
}

?>