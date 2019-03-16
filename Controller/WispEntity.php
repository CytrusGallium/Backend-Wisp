<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispConnectionManager.php');
    // require_once ("WispEntityProperty.php");

	class WispEntity 
	{
		// Protected variables
		protected $entityName;
		protected $properties;
        protected $displayName;
        protected $glyphName;
        protected $displayShortcut;
        protected $predefinedList;
        protected $quickSearchProperty; //WispEntityProperty
        protected $primaryPropertyName;
        protected $secondaryPropertyName;
        protected $thirdiaryPropertyName;
        protected $ownerCanRead;
        protected $ownerCanUpdate;
        protected $ownerCanDelete;
        protected $nonOwnerCanRead;
        protected $nonOwnerCanUpdate;
        protected $nonOwnerCanDelete;

    	// Public variables
        public static $LHP; //Last Handled Property by the entity manager

    	// Getters
        function GetName ()
        {
            return $this->entityName;
        }

        function GetEntityName ()
        {
            return $this->entityName;
        }

        function GetDisplayName ()
        {
            return $this->displayName;
        }

        function GetGlyphName ()
        {
            return $this->glyphName;
        }

        function GetIfDisplayShortcut ()
        {
            return $this->displayShortcut;
        }

        function GetIsPredefinedList ()
        {
            return $this->predefinedList;
        }

        function GetQuickSearchProperty ()
        {
            return $this->quickSearchProperty;
        }

        function GetGlyphPath ()
        {
            // return $_SERVER['DOCUMENT_ROOT'] . '/Wisp/View/font-awesome/png/' . $this->glyphName . '.png';
            return '/View/font-awesome/png/' . $this->glyphName . '.png';
            // if empty ?
            // return "";
        }
    	
    	// Methods
    	function __construct($ParamName, $ParamDisplayName, $ParamGlyphName = '')
    	{
    		$this->entityName = $ParamName;
    		$this->properties = array();
    		$this->displayName = $ParamDisplayName;
    		$this->glyphName = $ParamGlyphName;
    		$this->displayShortcut = true;
    		$this->predefinedList = false;
    		$this->quickSearchProperty = NULL;
            $this->primaryPropertyName = '';
            $this->secondaryPropertyName = '';
            $this->thirdiaryPropertyName = '';
            $this->ownerCanRead = true;
            $this->ownerCanUpdate = true;
            $this->ownerCanDelete = true;
            $this->nonOwnerCanRead = true;
            $this->nonOwnerCanUpdate = true;
            $this->nonOwnerCanDelete = true;
    	}

    	function GetPropertyCount ()
    	{
    		if (empty($this->properties))
    		{
    			return 0;
    		}

    		return count($this->properties);
    	}

    	function AddProperty (WispEntityProperty $ParamProperty)
    	{  
    		// Check if the property is empty
            if (empty($ParamProperty))
    		{
    			echo 'Wisp : Empty parameter, WispEntityProperty instance expected.';
    			return;
    		}

    		// TODO : Check duplicated property by name

            // Store Last handled property
            WispEntity::$LHP = $ParamProperty;

    		// Add the property to the properties array
            array_push($this->properties, $ParamProperty);

    		$ParamProperty->SetParentEntity($this);

            // $ParamProperty->Scaffold(); // This is not the best time to scaffold properties
    	}

    	function GetPropertyByName (string $ParamPropertyName)
    	{
    		// echo "PN : " . $ParamPropertyName;

            for ($i = 0; $i < count($this->properties); $i++)
            {
                if ( strcasecmp($this->properties[$i]->GetName(), $ParamPropertyName) == 0)
                {
                    // echo "R : " . $this->properties[$i]->GetName();
                    return $this->properties[$i];
                }
            }

            // echo "!";
    	}

        function GetPropertyByIndex (int $ParamIndex)
        {
            for ($i = 0; $i < count($this->properties); $i++)
            {
                if ($i == $ParamIndex)
                {
                    return $this->properties[$i];
                }

            }
        }

    	function CheckSubEntityPresence ()
    	{
    		// return Boolean
    	}

        function CheckShoppingListPresence ()
        {
            for ($i = 0; $i < count($this->properties); $i++)
            {
                if (is_a($this->properties[$i], 'WispEntityPropertyShoppingList'))
                {
                    return true;
                }

            }

            return false;
        }

    	function GetGridQueryString ()
    	{
    		// return string
    	}

    	function GetTableName ()
    	{
    		return 'entity_' . $this->entityName;
    	}

        function GetInstanceFromDb(string $ParamID)
    	{
    		// return WispEntityInstance
    	}

        function GetCopyOfProperties ()
        {
            $clone = array_map(function ($object) { return clone $object; }, $this->properties);
            return $clone;
        }

        // ...
        function GetJson (string $ParamPrivilege = '')
        {
            /*
            $array_info = array
            (
                'EntityName' => $this->GetName(),
                'EntityLabel' => $this->GetDisplayName(),
                'GlyphPath' => $this->GetGlyphPath(),
                'PrimaryProperty' => $this->primaryPropertyName,
                'SecondayProperty' => $this->secondaryPropertyName,
                'ThirdiaryProperty' => $this->thirdiaryPropertyName,
                'DisplayShortcut' => (string)$this->displayShortcut
            );


            $array = array($array_info);

            return json_encode($array);
            */

            return json_encode($this->GetJsonArray());
        }

        // ...
        function GetJsonArray (string $ParamPrivilege = '')
        {
            // $array_meta = array
            // (
            //     'Type' => 'Entity'
            // );

            $array_info = array
            (
                'EntityName' => $this->GetName(),
                'EntityLabel' => $this->GetDisplayName(),
                'GlyphPath' => $this->GetGlyphPath(),
                'PrimaryProperty' => $this->primaryPropertyName,
                'SecondayProperty' => $this->secondaryPropertyName,
                'ThirdiaryProperty' => $this->thirdiaryPropertyName,
                'DisplayShortcut' => (string)$this->displayShortcut
            );

            return $array = array($array_info);
        }

        // ...
        function SetImportantProperties(string $ParamPrimary, string $ParamSecondary, String $ParamThirdiary)
        {
            $this->primaryPropertyName = $ParamPrimary;
            $this->secondaryPropertyName = $ParamSecondary;
            $this->thirdiaryPropertyName = $ParamThirdiary;
        }

        // ...
        function GetPrimaryPropertyName ()
        {
            return $this->primaryPropertyName;
        }

        // ...
        function GetSecondaryPropertyName ()
        {
            return $this->secondaryPropertyName;
        }

        // ...
        function GetThirdiaryPropertyName ()
        {
            return $this->thirdiaryPropertyName;
        }

        // ...
        function GetEntityIDFromID(string $ParamID)
        {
            $q = 'SELECT ENTITY_ID FROM ' . $this->GetTableName() . ' WHERE ID="' . $ParamID . '";';
            return WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue('ENTITY_ID');
        }

        // ...
        function GetLastVersionByID (string $ParamID)
        { 
            $tmpEntityID = $this->GetEntityIDFromID((string)$ParamID);
            return $this->GetLastVersionByEntityID($tmpEntityID);  
        }

        // ...
        function GetLastVersionByEntityID (string $ParamEntityID)
        {
            $q = 'SELECT MAX(VERSION_ID) FROM ' . $this->GetTableName() . ' WHERE ENTITY_ID="' . $ParamEntityID . '";';
            return WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue('MAX(VERSION_ID)');
        }

        // ...
        function GetLastEntityID ()
        {
            $q = 'SELECT MAX(ENTITY_ID) FROM ' . $this->GetTableName() . ';';
            return WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue('MAX(ENTITY_ID)');
        }

        // ...
        function GetInstancesByValue (string $ParamPropertyName, string $ParamValue = '', string $ParamUserID = '')
        {

            // Build query WHERE part
            $query_where_part = '';

            if ($ParamUserID == '' && $ParamValue == '')
            {
                $query_where_part = ' WHERE IS_LAST="1" AND IS_DELETED="0" ';
            }
            else
            {
                $query_where_part = ' WHERE IS_LAST="1" AND IS_DELETED="0" ';
            }

            // Build user query string
            $query_user_part = '';

            if ($ParamUserID != '')
            {
                $query_user_part = ' AND UID = ' . $ParamUserID;
            }
            else
            {
                $query_user_part = '';
            }

            // Build value query string
            $query_value_part = '';

            if ($ParamValue != '')
            {
                if ($ParamPropertyName == '')
                {
                    $query_value_part = $query_value_part . " AND (";
                    $properties = $this->GetCopyOfProperties();
                    $count = sizeof($properties);

                    for ($i=0; $i < $count; $i++) 
                    { 
                        // echo $properties[$i]->GetName();
                        // echo "<br/>";
                        
                        if ($properties[$i]->IsStringSearchable())
                        {
                            $query_value_part = $query_value_part . " " . $properties[$i]->GetDbColumnName() . " = '" . $ParamValue . "' OR ";

                            // if ($i < $count-1)
                            // {
                            //     $query_value_part = $query_value_part . " OR ";    
                            // }
                        }

                        if ($i == $count-1)
                        {
                            // $query_value_part = $query_value_part . " ) ";
                        }
                    }

                    $query_value_part = substr($query_value_part, 0, sizeof($query_value_part)-4);
                    $query_value_part = $query_value_part . " ) ";
                }
                else
                {
                    // ...
                    $tmpEntityProperty = $this->GetPropertyByName($ParamPropertyName);
                    $columnName = $tmpEntityProperty->GetDbColumnName();
                    $query_value_part = " AND " . $columnName . '="' . $ParamValue . '"';
                }
            }
            else
            {
                $query_value_part = '';
            }

            /*
            $q = 'SELECT ID FROM ' . $this->GetTableName() . ' WHERE ' . $columnName . '="' . $ParamValue . '" AND IS_LAST="1" AND IS_DELETED="0"' . $query_user_part . ';';
            */

            $q = 'SELECT ID FROM ' . $this->GetTableName() . $query_where_part . $query_value_part . $query_user_part . ';';

            // echo $q;
            // echo "<br/>";
            
            $result = WispConnectionManager::Get()->OpenQuery($q);
            $recordCount = $result->GetRecordCount();


            // Get records from the result set
            $records = $result->GetAllRecords();

            // Create instance Array
            $array_entity_instances = array ();

            // Write instances to the instance array
            for ($i = 0; $i < $recordCount; $i++)
            {
                $instance = new WispEntityInstance($this);
                $instance->LoadFromDb($records[$i]['ID']);
                $array_entity_instances[$i] = $instance;
                // echo "I : " . $instance->GetJson();
                // echo "<br/>";    
            }

            return $array_entity_instances;

            // echo $q;
            // echo "<br/>";

        }

        // ...
        function GetInstancesByEntityID (string $ParamID)
        {
            $q = 'SELECT ID FROM ' . $this->GetTableName() . ' WHERE ENTITY_ID = "' . $ParamID . '" AND IS_LAST="1" AND IS_DELETED="0";';

            $result = WispConnectionManager::Get()->OpenQuery($q);
            $recordCount = $result->GetRecordCount();

            // Get records from the result set
            $records = $result->GetAllRecords();

            // Create instance Array
            $array_entity_instances = array ();

            // Write instances to the instance array
            for ($i = 0; $i < $recordCount; $i++)
            {
                $instance = new WispEntityInstance($this);
                $instance->LoadFromDb($records[$i]['ID']);
                $array_entity_instances[$i] = $instance;
                // echo "I : " . $instance->GetJson();
                // echo "<br/>";    
            }

            return $array_entity_instances;
        }

        // The owner is the user who created the instance, or a version of the instance, every version has an owner
        function SetOwnerPrivileges(bool $ParamRead, bool $ParamUpdate, bool $ParamDelete)
        {
            $this->ownerCanRead = $ParamRead;
            $this->ownerCanUpdate = $ParamUpdate;
            $this->ownerCanDelete = $ParamDelete;
        }

        // The owner is the user who created the instance, or a version of the instance, every version has an owner
        function SetNonOwnerPrivileges(bool $ParamRead, bool $ParamUpdate, bool $ParamDelete)
        {
            $this->nonOwnerCanRead = $ParamRead;
            $this->nonOwnerCanUpdate = $ParamUpdate;
            $this->nonOwnerCanDelete = $ParamDelete;
        }

        function SetDisplayShortcut (bool $ParamDisplayShortcut)
        {
            $this->displayShortcut = $ParamDisplayShortcut;
        }

	}
?>