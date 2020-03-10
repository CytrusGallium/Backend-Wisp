<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

// =====================================================================
if (isset($_POST["s"])) {
    $Session_ID = $_POST["s"];
} else if (isset($_GET["s"])) {
    $Session_ID = $_GET["s"];
} else {
    $Session_ID = "";
}

WispAccessManager::Get()->CheckUserLogin("json", $Session_ID);
// =====================================================================

// Example : http://localhost/Wisp/View/WispInstanceGetter.php?entity=product&property=barcode&value=689512487 for a new entity instance

// TODO : Check GET/POST Data
if (!isset($_GET['entity']) || !isset($_GET['value'])) {
    echo "Syntax error or missing variable.";
    exit();
}

// Declaring default variables
$searchAll = true;
$returnMultipleInstances = false;

if (isset($_GET['property'])) {
    $searchAll = false;
    $entityProperty = $_GET['property'];
}

if (isset($_GET['multi'])) {
    if (is_null($_GET['multi'])) {
        $returnMultipleInstances = false;
    } elseif ($_GET['multi'] == "1" || $_GET['multi'] == "true") {
        $returnMultipleInstances = true;
    } else {
        $returnMultipleInstances = false;
    }
}

// PROBABLY DEPRECATED : For privilege checking we need the userID, but the sessionID is more secure and can solve the same problem
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
} else {
    $uid = '';
}

// Store GET/POST Data
// $action = $_GET['action']; // if ID = 0 then create a new instance, if ID > 0 Send an old instance
$entityName = $_GET['entity'];
$propertyValue = $_GET['value'];


if ($searchAll) {
    $entityProperty = '';
} else {
    if (is_null($entityProperty)) {
        echo 'Invalid property name ...';
        exit();
    }

    $entityProperty = $_GET['property'];
}

// Check entity existance
$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

if (is_null($tmpEntity)) {
    echo 'Entity Not Found ...';
    exit();
}

// Check property and value
if (is_null($propertyValue)) {
    echo 'Invalid value';
    exit();
}

// Get the target property
$instances = $tmpEntity->GetInstancesByValue($entityProperty, $propertyValue, $uid);

$count = sizeof($instances);

if ($count == 0) {
    // echo json_encode("No instance found ...");
    WispJsonMessages::ErrorMessage("NO_INSTANCE", "No instance found.");
} else {
    if ($returnMultipleInstances) {
        $instancesInJson = array();

        for ($i = 0; $i < $count; $i++) {
            $instancesInJson[$i] = $instances[$i]->GetJsonArray();
        }

        $multiInstanceArray = array
        (
            "Type" => "MultiEntityInstance",
            "Instances" => $instancesInJson
        );

        echo json_encode($multiInstanceArray);
    } else {
        echo $instances[0]->GetJson();
    }
}
?>