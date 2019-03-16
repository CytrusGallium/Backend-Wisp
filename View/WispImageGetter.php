<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');
	// require_once (dirname(__FILE__, 2). '/Model/MyEntities.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispConnectionManager.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityInstance.php');
	// require_once (dirname(__FILE__, 2). '/Libraries/WispJsonMessages.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	// =====================================================================
	if( isset($_POST["s"]) )
	{
		$Session_ID = $_POST["s"];
	}
	else if( isset($_GET["s"]) )
	{
		$Session_ID = $_GET["s"];
	}
	else
	{
		$Session_ID = "";
	}

	WispAccesManager::Get()->CheckUserLogin("json", $Session_ID);
	// =====================================================================

	// Check GET/POST Data
	if(!isset($_GET['entity']) || !isset($_GET['property']) || !isset($_GET['id']) || !isset($_GET['version']))
	{
     	echo "Syntax error or missing variable.";
     	exit();
	}

	// Store GET/POST Data
	$entityName = $_GET['entity'];
	$entityPropertyName = $_GET['property'];
	$id = $_GET['id'];
	$version = $_GET['version'];
	
	// Check entity existance
	$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

	if (is_null($tmpEntity))
	{
		echo 'Entity Not Found ...';
		exit();
	}

	// Check property and value
	$tmpProperty = $tmpEntity->GetPropertyByName ($entityPropertyName);

	if (is_null($tmpProperty)) 
	{
		echo 'Property Not Found ...';
		exit();
	}

	// Build the query
	$q = "SELECT " . $tmpProperty->GetDbColumnName() . " FROM " . $tmpEntity->GetTableName() . " WHERE ENTITY_ID = " . $id . " AND VERSION_ID = " . $version . ";";

	// echo $q;

	// Update DB field
	$imagePath = dirname(__FILE__, 2) . "/images/" . WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue($tmpProperty->GetDbColumnName());

	// echo $imagePath;
	// exit();

	// Store the image in a variable
	$im = file_get_contents($imagePath);

	// Respond
	// WispJsonMessages::Feedback("UPLOADED", "Image Uploaded succesfully.");
	header('content-type: image/png'); 
	echo $im;


?>