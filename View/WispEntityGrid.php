<?php

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	// ----------------------- Syntax Check ------------------------

	if ($_SERVER['REQUEST_METHOD'] === 'POST') 
	{ 
		if(isset($_POST['r']))
		{
			$responseType = $_POST['r'];
		}

		if( isset($_POST["s"]) )
		{
			$Session_ID = $_POST["s"];
		}
		else
		{
			$Session_ID = "";
		}
	} 
	elseif ($_SERVER['REQUEST_METHOD'] === 'GET') 
	{ 
		if(isset($_GET['r']))
		{
			$responseType = $_GET['r'];
		}

		if( isset($_GET["s"]) )
		{
			$Session_ID = $_GET["s"];
		}
		else
		{
			$Session_ID = "";
		}

	} 
	else 
	{ 
		http_response_code(405); 
		die();
	}

	if (isset($responseType) && !is_null($responseType))
	{
		if ($responseType == "html")
		{
			// Respond_Html ();
		}
		else if ($responseType == "bootstrap")
		{
			// Respond_Bootstrap ();
		}
		else if ($responseType == "json")
		{
			// Respond_Json ();
		}
		else
		{
			echo "Invalid Response Type.";
			exit();
		}
	}
	else
	{
		$responseType = "json";
		// Respond_Json ();
	}

	// ------------------------- Syntax Check Done -------------------

	WispAccesManager::Get()->CheckUserLogin("json", $Session_ID);

	// Check GET/POST
	if(!isset($_GET['entity']))
	{
     	echo "Syntax error or missing variable.";
     	exit();
	}

	// Check if we shall send details
	$detailMode = false;

	if(isset($_GET['details']))
	{
     	if ($_GET['details'] == "true" || $_GET['details'] == "1")
     		$detailMode = true;
     	else
     		$detailMode = false;
	}
	else
	{
     	$detailMode = false;
	}

	// Store GET/POST Data
	$entityName = $_GET['entity'];
	
	// Check entity existance
	$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

	if (is_null($tmpEntity))
	{
		echo 'Entity Not Found ...';
		exit();
	}

	// Open query
	$q = 'SELECT * FROM ' . $tmpEntity->GetTableName() . ' WHERE IS_LAST="1" AND IS_DELETED="0";';
	$result = WispConnectionManager::Get()->OpenQuery($q);
	$recordCount = $result->GetRecordCount();

	// Check record count
	if ($recordCount == 0)
	{
		echo 'No instance found ...';
		exit();
	}

	$records = $result->GetAllRecords();

	// Create instance Array
	$array_entity_instances = array ();

	// ...
	if (!$detailMode)
	{
		for ($i = 0; $i < $recordCount; $i++)
	    {
			$instance = new WispEntityInstance($tmpEntity);
			$instance->LoadFromDb($records[$i]['ID']);

			$entity_info_array = array();

			if ($instance->GetPrimaryPropertyName() == "")
			{
				$entity_info_array['PrimaryInfo'] = "";
			}
			else
			{
				$entity_info_array['PrimaryInfo'] = $instance->GetPropertyByName($instance->GetPrimaryPropertyName())->GetSummaryValue();
			}

			if ($instance->GetSecondaryPropertyName() == "")
			{
				$entity_info_array['SecondaryInfo'] = "";
			}
			else
			{
				$entity_info_array['SecondaryInfo'] = $instance->GetPropertyByName($instance->GetSecondaryPropertyName())->GetSummaryValue();
			}

			if ($instance->GetThirdiaryPropertyName() == "")
			{
				$entity_info_array['ThirdiaryInfo'] = "";
			}
			else
			{
				$entity_info_array['ThirdiaryInfo'] = $instance->GetPropertyByName($instance->GetThirdiaryPropertyName())->GetSummaryValue();
			}

			$array_entity_instances[$records[$i]['ID']] = $entity_info_array;

	    }
	} 
	else 
	{
		for ($i = 0; $i < $recordCount; $i++)
	    {

			$instance = new WispEntityInstance($tmpEntity);
			$instance->LoadFromDb($records[$i]['ID']);

			$array_entity_instances[$records[$i]['ID']] = $instance->GetJsonArray();

	    }
	}

    // Create property array, usable to search in a certain property
    $array_properties = array ();

    for ($i=0; $i < $tmpEntity->GetPropertyCount(); $i++) {

    	$tmpProperty = $tmpEntity->GetPropertyByIndex($i);
    	
    	if ($tmpProperty->isStringSearchable())
    	{
    		$array_properties[$tmpProperty->GetName()] = $tmpProperty->GetLabel();	
    	}

    }

	// Create Main Array
    $array_all = array
    (
        'Type' => 'EntityGrid',
        'Entities' => $array_entity_instances,
        'stringSearchableProperties' => $array_properties
    );

	if ($responseType == "html")
	{
		Respond_Html ($array_all);
	}
	else if ($responseType == "bootstrap")
	{
		// Respond_Bootstrap ();
	}
	else if ($responseType == "json")
	{
		Respond_Json ($array_all);
	}
	else
	{
		echo "Invalid Response Type.";
		exit();
	}

	function Respond_Json ($Param_Instance_Array)
	{
		// Reply with the JSON array
		echo json_encode($Param_Instance_Array);
	}

	function Respond_Html ($Param_Instance_Array)
	{

		echo "<html><body>";
		echo '<table border="1">';

		foreach ($Param_Instance_Array['Entities'] as $key => $value) {

			echo "<tr>";

			echo "<td>";
			echo $key;
			echo "</td>";

			echo "<td>";
			echo $value['PrimaryInfo'];
			echo '<br/>';
			echo $value['SecondaryInfo'];
			echo '<br/>';
			echo $value['ThirdiaryInfo'];
			echo '<br/>';
			echo "</td>";

			echo "</tr>";

		}

		echo "</table>";
		echo "</body></html>";
	}

?>