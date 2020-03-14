<?php

class WispEntityInstance extends WispEntity
{
    Protected $ID;
    protected $entityID;
    protected $versionID;
    protected $isLast;
    protected $timeStamp;
    protected $uid;
    protected $isDeleted;

    function __Construct(WispEntity $ParamEntity)
    {
        // TODO : Find a way to clone $ParamEntity and return the cloned object as WispEntityInstance
        $this->entityName = $ParamEntity->GetEntityName();
        $this->properties = $ParamEntity->GetCopyOfProperties();
        $this->displayName = $ParamEntity->GetDisplayName();
        $this->glyphName = $ParamEntity->GetGlyphName();
        $this->displayShortcut = $ParamEntity->GetIfDisplayShortcut();
        $this->predefinedList = $ParamEntity->GetIsPredefinedList();
        $this->quickSearchProperty = $ParamEntity->GetQuickSearchProperty();
        $this->primaryPropertyName = $ParamEntity->GetPrimaryPropertyName();
        $this->secondaryPropertyName = $ParamEntity->GetSecondaryPropertyName();
        $this->thirdiaryPropertyName = $ParamEntity->GetThirdiaryPropertyName();
        $this->summaryString = $ParamEntity->GetSummaryString();

        // Change the parent of the properties + Update property info
        for ($i = 0; $i < count($this->properties); $i++) {
            $this->properties[$i]->SetParentEntity($this);
            $this->properties[$i]->GenerateDefaultValue();
        }
    }

    // ...
    function GetID()
    {
        return $this->ID;
    }

    // ...
    function GetEntityID()
    {
        return $this->entityID;
    }

    // ...
    function GetVersionID()
    {
        return $this->versionID;
    }

    // ...
    function ChangeID(string $ParamID)
    {
        $this->ID = $ParamID;
    }

    // ...
    function ChangeEntityID(string $ParamID)
    {
        $this->entityID = $ParamID;
    }

    // ...
    function ChangeVersionID(string $ParamID)
    {
        $this->versionID = $ParamID;
    }

    // ...
    function AddToDb($ParamNoUser = false)
    {
        $entityId = (int)$this->GetLastEntityID() + 1;
        
        $versionId = '1';
        $isLast = '1';
        $timeStamp = date('Y-m-d H:i:s', time());
        $isDeleted = '0';

        $uid = 0;

        if ($ParamNoUser == false)
        {
            if (isset($_SESSION["uid"])) {
                $uid = $_SESSION["uid"];
            } else {
                WispJsonMessages::ErrorMessage("NO_USER_ID", "User ID Undefined");
            }
        }

        // Create columns and values arrays
        $tmpColumns = array();
        $tmpValues = array();

        // ENTITY_ID
        array_push($tmpColumns, 'ENTITY_ID');
        array_push($tmpValues, $entityId);

        // VERSION_ID
        array_push($tmpColumns, 'VERSION_ID');
        array_push($tmpValues, $versionId);

        // IS_LAST
        array_push($tmpColumns, 'IS_LAST');
        array_push($tmpValues, $isLast);

        // DTC
        array_push($tmpColumns, 'DTC');
        array_push($tmpValues, $timeStamp);

        // UID
        array_push($tmpColumns, 'UID');
        array_push($tmpValues, $uid);

        // IS_DELETED
        array_push($tmpColumns, 'IS_DELETED');
        array_push($tmpValues, $isDeleted);

        // PROPERTIES
        for ($i = 0; $i < count($this->properties); $i++) {
            if ($this->properties[$i]->IsUnifield()) {
                array_push($tmpColumns, $this->properties[$i]->GetDbColumnName());
                array_push($tmpValues, $this->properties[$i]->GetValue());
            }
        }

        // Insert !
        //$id = WispConnectionManager::Get()->ExecuteInsert($this->GetTableName(), $tmpColumns, $tmpValues);
        //echo "ID = " . $id . "<br/>";
        //return $id;
        return WispConnectionManager::Get()->ExecuteInsert($this->GetTableName(), $tmpColumns, $tmpValues);
    }

    // ...
    function AddNewVersionToDb()
    {
        $newVersionID = (int)$this->GetLastVersionByID($this->ID) + 1;
        $this->entityID = $this->GetEntityIDFromID($this->ID);
        $this->versionID = (string)$newVersionID; // i think its not necessary to cast it to a string

        // ------------------------------------------------------------------------------------------------------
        $isLast = '1';
        $timeStamp = date('Y-m-d H:i:s', time());
        $isDeleted = '0';

        if (isset($_SESSION["uid"])) {
            $uid = $_SESSION["uid"];
        } else {
            $uid = 0;
            WispJsonMessages::ErrorMessage("NO_USER_ID", "User ID Undefined");
        }

        // Create columns and values arrays
        $tmpColumns = array();
        $tmpValues = array();

        // ENTITY_ID
        array_push($tmpColumns, 'ENTITY_ID');
        array_push($tmpValues, $this->entityID);

        // VERSION_ID
        array_push($tmpColumns, 'VERSION_ID');
        array_push($tmpValues, $this->versionID);

        // IS_LAST
        array_push($tmpColumns, 'IS_LAST');
        array_push($tmpValues, $isLast);

        // DTC
        array_push($tmpColumns, 'DTC');
        array_push($tmpValues, $timeStamp);

        // UID
        array_push($tmpColumns, 'UID');
        array_push($tmpValues, $uid);

        // IS_DELETED
        array_push($tmpColumns, 'IS_DELETED');
        array_push($tmpValues, $isDeleted);

        // PROPERTIES
        for ($i = 0; $i < count($this->properties); $i++) {
            if ($this->properties[$i]->IsUnifield()) {
                array_push($tmpColumns, $this->properties[$i]->GetDbColumnName());
                array_push($tmpValues, $this->properties[$i]->GetValue());
            }
        }

        // Insert !
        $lastInsertID = WispConnectionManager::Get()->ExecuteInsert($this->GetTableName(), $tmpColumns, $tmpValues);

        // set as last version
        $q = "UPDATE " . $this->GetTableName() . " SET IS_LAST = 0 WHERE ENTITY_ID = " . $this->entityID . " AND ID != " . $lastInsertID .
            ";";

        WispConnectionManager::Get()->ExecuteQuery($q);
    }

    // ...
    function LoadFromDb(string $ParamId)
    {
        if (!isset($ParamId))
        {
            WispJsonMessages::ErrorMessage("NULL_ID", "Null ID while trying to load instance from database.");
        }

        $q = 'SELECT * FROM ' . $this->GetTableName() . ' WHERE ID="' . $ParamId . '";';

        $result = WispConnectionManager::Get()->OpenQuery($q);

        $this->ID = $result->GetColumnValue('ID');
        $this->entityID = $result->GetColumnValue('ENTITY_ID');
        $this->versionID = $result->GetColumnValue('VERSION_ID');
        $this->isLast = $result->GetColumnValue('IS_LAST');
        $this->timeStamp = $result->GetColumnValue('DTC');
        $this->uid = $result->GetColumnValue('UID');
        $this->isDeleted = $result->GetColumnValue('IS_DELETED');

        for ($i = 0; $i < count($this->properties); $i++) {
            $this->properties[$i]->SetValueFromDb($result);
        }
    }

    function MarkAsDeleted()
    {
        if (is_null($this->entityID)) {
            // Get Entity ID from DB
            // Update IS_DELETED for all the versions
        } else {
            $q = "UPDATE entity_" . $this->entityName . " SET IS_DELETED='1' WHERE ENTITY_ID ='" . $this->entityID . "';";
            // echo $q;
            WispConnectionManager::Get()->ExecuteQuery($q);
        }
    }


    function Duplicate()
    {
        // Duplicate in the table with a new ID and ENTITY_ID
    }

    function GetJson(string $ParamPrivilege = '') : string
    {
        return json_encode($this->GetJsonArray());
    }

    // ...
    function GetJsonArray(string $ParamPrivilege = '')
    {
        $array_meta = array
        (
            'Type' => 'EntityInstance'
        );

        $array_basic = array
        (
            'EntityName' => $this->GetName(),
            'EntityLabel' => $this->GetDisplayName(),
            'ID' => $this->ID,
            'entityID' => $this->entityID,
            'versionID' => $this->versionID,
            'isLast' => $this->isLast,
            'timeStamp' => $this->timeStamp,
            'uid' => $this->uid,
            'isDeleted' => $this->isDeleted,
            '1' => $this->primaryPropertyName,
            '2' => $this->secondaryPropertyName,
            '3' => $this->thirdiaryPropertyName,
            'SummaryString' => $this->GenerateSummaryString()
        );

        $array_properties = array();

        // echo "INSTANCE OF : " . $this->GetName();
        // echo "<br/>";
        for ($i = 0; $i < count($this->properties); $i++) {
            $array_properties[$this->properties[$i]->GetName()] = $this->properties[$i]->GetJsonArray();
            // echo "P";
        }

        $array = array($array_meta, $array_basic, $array_properties);

        return $array;
    }

    // ...
    function GetJsonWithLabels(string $ParamPrivilege = '')
    {
        $array_meta = array
        (
            'Type' => 'EntityInstance'
        );

        $array_basic = array
        (
            'EntityName' => $this->GetName(),
            'EntityLabel' => $this->GetDisplayName(),
            'ID' => $this->ID,
            'entityID' => $this->entityID,
            'versionID' => $this->versionID,
            'isLast' => $this->isLast,
            'timeStamp' => $this->timeStamp,
            'uid' => $this->uid,
            'isDeleted' => $this->isDeleted,
            'SummaryString' => $this->GenerateSummaryString()
        );

        $array_properties = array();
        for ($i = 0; $i < count($this->properties); $i++) {
            $array_properties[$this->properties[$i]->GetName()] = $this->properties[$i]->GetValue();
        }

        $array = array($array_meta, $array_basic, $array_properties);

        return json_encode($array);
    }

    function GenerateSummaryString()
    {
        $bool = ($this->summaryString != "" && isset($this->summaryString));

        if (!$bool)
            return "";
        
        $s = '';
        $tmpDynamicString = new WispDynamicString($this->summaryString);
        $tmpArray = $tmpDynamicString->PrepareArrayFromString();

        /*
        if (!isset($this->value))
            return "";
        */

        for ($i = 0; $i < sizeof($tmpArray); $i++) {
            $tmpSubArray = $tmpArray[$i];

            if ($tmpSubArray['v'] == true) {
                $s = $s . $this->GetPropertyByName($tmpSubArray['s'])->GetValue();
            } else {
                $s = $s . $tmpSubArray['s'];
            }
        }

        return $s;
    }
}

?>