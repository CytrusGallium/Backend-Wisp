<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');
	// require_once (dirname(__FILE__, 2). '/Model/MyEntities.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispConnectionManager.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityInstance.php');
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

	// Example : http://localhost/Wisp/View/WispEntityEditor.php?entity=product&id=0 for a new entity instance
	// Example : http://localhost/Wisp/View/WispEntityEditor.php?entity=product&id=3 for an existing entity instance

	// Store GET/POST Data
	// $action = $_GET['action']; // if ID = 0 then create a new instance, if ID > 0 Send an old instance
	$entityName = $_GET['entity'];
	$entityID = $_GET['id'];
	
	// Check entity existance
	$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

	if (is_null($tmpEntity))
	{
		echo 'Entity Not Found ...';
		exit();
	}

	$instance = new WispEntityInstance($tmpEntity);

	if ($entityID == '0')
	{
		// Add the empty instance to DB
		$id = $instance->AddToDb();
		$instance->LoadFromDb($id);

	} else {
		// load from DB
		$instance->LoadFromDb($entityID);
	}

	// Reply with the JSON array
	echo $instance->GetJson();
?>