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

	// Note : add does not work for now, so we use id=0 in WispEntityEditor.php
	// Example : GET : http://localhost/wisp/View/WispEntityEditor.php?entity=product&id=0
	
	// Example : http://localhost/Wisp/View/WispEntityEditor.php?action=update&entity=product&id=3 for updating an instance
	// Example : http://localhost/Wisp/View/WispEntityEditor.php?action=delete&entity=product&id=3 to delete an instance

	if(!isset($_GET['action']) || !isset($_GET['entity']) || !isset($_GET['id']))
	{
     	echo "Syntax error or missing variable.";
     	exit();
	}

	// Store GET/POST Data
	$action = $_GET['action']; // action = add / update / delete / Duplicate
	$entityName = $_GET['entity'];
	$entityID = $_GET['id'];
	
	// Check entity existance
	$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

	if (is_null($tmpEntity))
	{
		echo 'Entity Not Found ...';
		exit();
	}

	// ...
	$propertiesAndValues = array();

	$instance = new WispEntityInstance($tmpEntity);

	if ($action == 'update')
	{
		// build properties array
    	for ($i = 0; $i < $tmpEntity->GetPropertyCount(); $i++)
    	{
			$tmpPropertyName = $tmpEntity->GetPropertyByIndex($i)->GetName();
			$tmpPropertyValue = $_GET[$tmpPropertyName];
			$propertiesAndValues[$tmpPropertyName] = $tmpPropertyValue;
			$instance->GetPropertyByName($tmpPropertyName)->SetValue($tmpPropertyValue);
    	}

    	$instance->ChangeID ($entityID);
    	$instance->AddNewVersionToDb();
	}
	elseif ($action == 'delete')
	{
		// $instance->ChangeID ($entityID);
		// echo "EntityID = " . $instance->GetEntityID();
		$instance->LoadFromDb($entityID);
		$instance->MarkAsDeleted();

		echo '{"Type":"Delete_Instance","Response":"Succes","Message":"Instance Deleted Succefully."}';
	} 

?>