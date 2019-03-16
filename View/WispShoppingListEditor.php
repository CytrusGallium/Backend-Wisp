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

	// Example : http://localhost/Wisp/View/WispInstanceGetter.php?entity=product&property=barcode&value=689512487 for a new entity instance

	if(!isset($_GET['action']))
	{
		echo "Action not specified.";
		exit();
	}

	if ($_GET['action'] == 'add')
	{
		Add();
	}
	else if ($_GET['action'] == 'delete')
	{
		// parent
		// child
		// parentid InstanceID
		// childid InstanceID
		// versionid VersionID
		Delete();
	}
	else if ($_GET['action'] == 'append')
	{
		Append();
	}
	else if ($_GET['action'] == 'update')
	{

	}
	else
	{
		echo "Unknown Actions";
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	function Append ()
	{
		echo "Append has been deprecated.";
		exit();
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	function Delete ()
	{
		// Check GET/POST Data
		if(!isset($_GET['parent']) || !isset($_GET['child']) || !isset($_GET['parentid']) || !isset($_GET['childid']) || !isset($_GET['versionid']))
		{
	     	echo "Syntax error or missing variable.";
	     	exit();
		}

		$entityName = $_GET['parent'];
		$childName = $_GET['child'];
		$parentid = $_GET['parentid'];
		$childid = $_GET['childid'];
		$versionid = $_GET['versionid'];

		// Check entity existance
		$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

		if (is_null($tmpEntity))
		{
			echo 'Entity Not Found ...';
			exit();
		}

		// Check property existence
		$tmpEntityProperty = $tmpEntity->GetPropertyByName($childName);

		if (is_null($tmpEntityProperty))
		{
			echo 'Property Not Found ...';
			exit();
		}

		// Check child entity existance
		$tmpChildEntity = WispEntityManager::Get()->GetEntityByName($tmpEntityProperty->GetSubEntity());

		if (is_null($tmpChildEntity))
		{
			echo 'Child Entity Not Found ...';
			exit();
		}

		// Store the amount in a variable before deleting it
		$q = "SELECT AMOUNT FROM " . $tmpEntityProperty->GetTableName() . " WHERE ID_" . $tmpEntity->GetName() . " = " . $parentid . " AND ID_".
		$tmpEntityProperty->GetSubEntity() . " = " . $childid . " AND ID_" . $tmpEntity->GetName() . "_VERSION = " . $versionid . ";";

		$result = WispConnectionManager::Get()->OpenQuery($q);

		$amount = $result->GetColumnValue("AMOUNT");

		// Delete from DB
		$q = "DELETE FROM " . $tmpEntityProperty->GetTableName() . " WHERE ID_" . $tmpEntity->GetName() . " = " . $parentid . " AND ID_" . 
		$tmpEntityProperty->GetSubEntity() . " = " . $childid . " AND ID_" . $tmpEntity->GetName() . "_VERSION = " . $versionid . ";";

		//WispConnectionManager::Get()->ExecuteQuery($q); TODO : Enable

		if (true)
		{
			// Update the amount
			// Get target table and column
			$table = $tmpChildEntity->GetTableName();
			$amountColumn = $tmpChildEntity->GetPropertyByName($tmpEntityProperty->GetStockProperty())->GetDbColumnName();
			$id = $childid;

			// if is destocker amount + 1
			if ($tmpEntityProperty->IsDestocker())
			{
				$operator = "+";
			}
			// if is stocker amount - 1
			else
			{
				$operator = "-";
			}

			// Build the query
			$q = "UPDATE " . $table . " SET " . $amountColumn . " = " . $amountColumn . $operator . $amount . " WHERE ENTITY_ID = " . $id . " AND IS_LAST = 1;";

			// echo $q;
			// exit();

			WispConnectionManager::Get()->ExecuteQuery($q);

			// echo $q;

			// Respond
			WispJsonMessages::Feedback("INSTANCE_DELETED", "Element deleted succesfully.");
		}

		

		// echo $q;
	}

	// -----------------------------------------------------------------------------------------------------------------------------------------
	function Add ()
	{
		// Check GET/POST Data
		if(!isset($_GET['target']) || !isset($_GET['property']) || !isset($_GET['child']) || !isset($_GET['targetid']) || !isset($_GET['targetversion']) /*|| !isset($_GET['childinstanceid'])*/  || !isset($_GET['searchproperty']) || !isset($_GET['searchvalue']))
		{
	     	echo "Syntax error or missing variable.";
	     	exit();
		}

		// Store GET/POST Data
		$entityName = $_GET['target']; // Target entity name, which has the shopping list
		$propertyName = $_GET['property']; // Target property name, the shopping list
		$instanceID = $_GET['targetid']; // ID of the parent Target instance
		$instanceVersion = $_GET['targetversion']; // VERSION_ID of the parent Target instance
		
		$childName = $_GET['child']; // Child entity which will be added to the list // TODO : Load from property info !
		
		$childProperty = $_GET['searchproperty']; // Name of the property used to search for the instance that will be added to the list 
		// TODO : Load from property info !
		
		$childValue = $_GET['searchvalue']; // Value used to search for the instance that will be added to the list
		
		// Check entity existance
		$tmpEntity = WispEntityManager::Get()->GetEntityByName($entityName);

		if (is_null($tmpEntity))
		{
			echo 'Entity Not Found ...';
			exit();
		}

		// Check child entity existance
		$tmpChildEntity = WispEntityManager::Get()->GetEntityByName($childName);

		if (is_null($tmpChildEntity))
		{
			echo 'Child Entity Not Found ...';
			exit();
		}

		// Check property existence
		$tmpEntityProperty = $tmpEntity->GetPropertyByName($propertyName);

		if (is_null($tmpEntityProperty))
		{
			echo 'Property Not Found ...';
			exit();
		}

		// Get the target property
		$instances = $tmpChildEntity->GetInstancesByValue ($childProperty, $childValue);

		$count = sizeof($instances);

		if ($count == 0)
		{
			// echo json_encode("No instance found ...");
			WispJsonMessages::ErrorMessage("NO_INSTANCE", "No instance found.");
		} 
		else
		{
			// echo $instances[0]->GetJson(); // Reply only if the insert is succesful
		}

		// Get children table name
		$childTableName = $tmpEntityProperty->GetTableName();

		// !!! Decide Insert or Update !!!
		// Check the exsistence of what you want to insert
		$q = "SELECT * FROM " . $childTableName . " WHERE ID_" . $tmpEntity->GetName() . " = " . $instanceID . " AND ID_" . $tmpEntity->GetName() . "_VERSION = " . $instanceVersion . " AND ID_" . $tmpChildEntity->GetName() . " = " . $instances[0]->GetEntityID() . ";";

		$result = WispConnectionManager::Get()->OpenQuery($q);

		if ($result->IsRecordAvailable())
		{
			// !!! APPEND !!!
			// Change amounts
			// Change Amount in the child table (not the shopping list property table)
			$table = $tmpChildEntity->GetTableName();
			$amountColumn = $tmpChildEntity->GetPropertyByName($tmpEntityProperty->GetStockProperty())->GetDbColumnName();
			$id = $instances[0]->GetEntityID();

			// if is destocker amount - 1
			if ($tmpEntityProperty->IsDestocker())
			{
				$operator = "-";
			}
			// if is stocker then default is unstocked ie 0
			else
			{
				$operator = "+";
			}

			// Build the query
			$q = "UPDATE " . $table . " SET " . $amountColumn . " = " . $amountColumn . $operator . "1 WHERE ENTITY_ID = " . $id . " AND IS_LAST = 1;";

			WispConnectionManager::Get()->ExecuteQuery($q);

			// Respond
			WispJsonMessages::Feedback("APPENDED", "Element appended succesfully.");

			exit();
		}

		// Create columns and values arrays 
		$tmpColumns = array();
		$tmpValues = array();

		// Get default stock state
		// if is destocker then default is stocked ie 1
		if ($tmpEntityProperty->IsDestocker())
		{
			$stockState = "1";
		}
		// if is stocker then default is unstocked ie 0
		else
		{
			$stockState = "0";
		}

		// 1
		array_push($tmpColumns, 'ID_' . $tmpEntity->GetName());
		array_push($tmpValues, $instanceID);

		// 2
		array_push($tmpColumns, 'ID_' . $tmpEntity->GetName() . '_VERSION');
		array_push($tmpValues, $instanceVersion);

		// 3
		array_push($tmpColumns, 'ID_' . $tmpChildEntity->GetName());
		array_push($tmpValues, $instances[0]->GetEntityID());

		// 4
		array_push($tmpColumns, 'IS_STOCKED');
		array_push($tmpValues, $stockState);

		// 5
		array_push($tmpColumns, 'AMOUNT');
		array_push($tmpValues, '1');

		// Insert !
		$id = WispConnectionManager::Get()->ExecuteInsert($childTableName, $tmpColumns, $tmpValues);

		// echo $id;
		// echo "<br/>";

		if ($id != "0")
		{
			echo $instances[0]->GetJson();

			// Change amounts
			// Get target table and column
			$table = $tmpChildEntity->GetTableName();
			$amountColumn = $tmpChildEntity->GetPropertyByName($tmpEntityProperty->GetStockProperty())->GetDbColumnName();
			$id = $instances[0]->GetEntityID();

			// if is destocker amount - 1
			if ($tmpEntityProperty->IsDestocker())
			{
				$operator = "-";
			}
			// if is stocker then default is unstocked ie 0
			else
			{
				$operator = "+";
			}

			// Build the query
			$q = "UPDATE " . $table . " SET " . $amountColumn . " = " . $amountColumn . $operator . "1 WHERE ENTITY_ID = " . $id . " AND IS_LAST = 1;";

			WispConnectionManager::Get()->ExecuteQuery($q);

			// echo $q;

		}
	}
	
?>