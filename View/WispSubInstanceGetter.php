<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityProperty.php');
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

	// Example : http://localhost/Wisp/View/WispInstanceGetter.php?entity=product&property=barcode&value=689512487 for a new entity instance

	// TODO : Check GET/POST Data
	if(!isset($_GET['entity']) || !isset($_GET['property']))
	{
     	echo "Syntax error or missing variable.";
     	exit();
	}

	// ...
	if ( isset($_GET['uid']) )
	{
		$uid = $_GET['uid'];
	}
	else
	{
		$uid = '';
	}

	// Store GET/POST Data
	// $action = $_GET['action']; // if ID = 0 then create a new instance, if ID > 0 Send an old instance
	$entityName = $_GET['entity'];
	$entityProperty = $_GET['property'];
	// $propertyValue = $_GET['value'];	
	
	// Check entity existance
	$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

	if (is_null($tmpEntity))
	{
		echo 'Entity Not Found ...';
		exit();
	}

	// Check property and value
	if (is_null($entityProperty)) 
	{
		echo 'invlaid property name';
		exit();
	}

	// ...
	$subEntity = $tmpEntity->GetPropertyByName($entityProperty);
	$subEntityName = $subEntity->GetSubEntityName();
	$tmpSummaryString = $subEntity->GetSummaryString(); 

	/*
	echo $entityName;
	echo "<br/>";
	echo $entityProperty;
	echo "<br/>";
	echo $subEntity->GetName();
	echo "<br/>";
	echo $subEntityName;
	echo "<br/>";
	echo $tmpSummaryString;
	echo "<br/>";
	*/

	// Get the target property
	// Get the JSON of SubInstance Properties as if they were instances ... ?!!???! ofc because a SubInstance is an instance basically (inside another instance)
	// echo $tmpEntity->GetName();
	// $instances = $tmpEntity->GetInstancesByValue ($subEntityName, '', $uid);
	$instances = WispEntityManager::Get()->GetEntityByName($subEntityName)->GetInstancesByValue ('', '', $uid);

	$count = sizeof($instances);

	if ($count == 0)
	{
		// echo json_encode("No instance found ...");
		WispJsonMessages::ErrorMessage("NO_INSTANCE", "No instance found.");
	} 
	else
	{
		$array_meta = array
        (
            'Type' => 'SubInstanceList'
        );

        $array_subInstances = array ();

		for ($i=0; $i < sizeof($instances) ; $i++) { 
			// echo $instances[$i]->GetJson();
			$tmpProperty = new WispEntityPropertySubInstance($entityProperty, '', $subEntityName, $tmpSummaryString);
			$tmpValue = WispEntityManager::Get()->GetEntityByName($subEntityName)->GetInstancesByEntityID($instances[$i]->GetEntityID())[0];
			$tmpProperty->SetValue($tmpValue);
			
			$array_pair = array
			(
				'id' => $instances[$i]->GetEntityID(),
				'value' => $tmpProperty->GenerateSummaryString()
			);

			$array_subInstances[$i] = $array_pair;

			// echo json_encode($array_subInstances[$i]);
			// echo "<br/>";
		}

		$array_all = array
		(
			$array_meta,
			$array_subInstances
		);

		echo json_encode($array_all);
	}
?>