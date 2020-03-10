<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

$input = (new WispMethodData($_GET, $_POST, array("entity", "property"), array("s")))->result;

WispAccessManager::Get()->CheckUserLogin("json", $input["s"]);

// =====================================================================

// Example : http://localhost/Wisp/View/WispInstanceGetter.php?entity=product&property=barcode&value=689512487 for a new entity instance

// ...
if (isset($_GET['uid'])) {
    $uid = $_GET['uid'];
} else {
    $uid = '';
}

// Store GET/POST Data
$entityName = $input['entity'];
$entityProperty = $input['property'];

// Check entity existance
$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

if (is_null($tmpEntity)) {
    WispJsonMessages::ErrorMessage("ENTITY_NOT_FOUND", "Entity not found.");
}

// Check property and value
if (is_null($entityProperty)) {
    WispJsonMessages::ErrorMessage("INVALID_PROPERTY_NAME", "Invlaid property name.");
}

// ...
$subEntity = $tmpEntity->GetPropertyByName($entityProperty);
$subEntityName = $subEntity->GetSubEntityName();
$tmpSummaryString = $subEntity->GetSummaryString();

// Get the target property
$instances = WispEntityManager::Get()->GetEntityByName($subEntityName)->GetInstancesByValue('', '', $uid);

$count = sizeof($instances);

if ($count == 0) {
    WispJsonMessages::ErrorMessage("NO_INSTANCE", "No instance found.");
} else {
    $array_meta = array
    (
        'Type' => 'SubInstanceList'
    );

    $array_subInstances = array();

    for ($i = 0; $i < sizeof($instances); $i++) {
        
        $tmpProperty = new WispEntityPropertySubInstance($entityProperty, '', $subEntityName, $tmpSummaryString);
        $tmpValue = WispEntityManager::Get()->GetEntityByName($subEntityName)->GetInstancesByEntityID($instances[$i]->GetEntityID())[0];
        $tmpProperty->SetValue($tmpValue);

        $array_pair = array
        (
            'id' => $instances[$i]->GetEntityID(),
            'value' => $tmpProperty->GenerateSummaryString()
        );

        $array_subInstances[$i] = $array_pair;
    }

    $array_all = array
    (
        $array_meta,
        $array_subInstances
    );

    echo json_encode($array_all);
}
?>