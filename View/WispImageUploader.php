<?php

// require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');
// require_once (dirname(__FILE__, 2). '/Model/MyEntities.php');
// require_once (dirname(__FILE__, 2). '/Controller/WispConnectionManager.php');
// require_once (dirname(__FILE__, 2). '/Controller/WispEntityInstance.php');
// require_once (dirname(__FILE__, 2). '/Libraries/WispJsonMessages.php');
// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');

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

// TODO : Check GET/POST Data
if (!isset($_POST['entity']) || !isset($_POST['property']) || !isset($_POST['id']) || !isset($_POST['version']) || !isset($_FILES['theFile']) || !isset($_POST['imgType'])) {
    echo "Syntax error or missing variable.";
    exit();
}

// Get file name after upload (from temporary files folder)
$tmpFileName = $_FILES['theFile']['tmp_name'];

// print("size: " . $_FILES['theFile']['size'] . " ");
// print("mime: " . $_FILES['theFile']['type'] . " ");
// print("name: " . $_FILES['theFile']['name'] . " ");

// Store GET/POST Data
$entityName = $_POST['entity'];
$entityPropertyName = $_POST['property'];
$id = $_POST['id'];
$version = $_POST['version'];
$imgType = $_POST['imgType'];

// Check entity existance
$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

if (is_null($tmpEntity)) {
    echo 'Entity Not Found ...';
    exit();
}

// Check property and value
$tmpProperty = $tmpEntity->GetPropertyByName($entityPropertyName);

if (is_null($tmpProperty)) {
    echo 'Property Not Found ...';
    exit();
}

// Generate file name
$finalImageName = $entityName . "-" . $entityPropertyName . "-" . $id . "-" . $version . $imgType;

// Save the image
move_uploaded_file($tmpFileName, dirname(__FILE__, 2) . "/images/" . $finalImageName);

// Build the query
$q = "UPDATE " . $tmpEntity->GetTableName() . " SET " . $tmpProperty->GetDbColumnName() . " = '" . $finalImageName . "' WHERE ENTITY_ID = " . $id . " AND IS_LAST = 1;";

// Update DB field
WispConnectionManager::Get()->ExecuteQuery($q);

// Respond
WispJsonMessages::Feedback("UPLOADED", "Image Uploaded succesfully.");

?>