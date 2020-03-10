<?php

class WispEntityProperty
{
    protected $name = '';
    protected $labelText = '';
    protected $dbColNamePrefix = '';
    protected $dbColNameSuffix = '';
    protected $displayInGrid = true;
    protected $displayInEditor = true;
    protected $editable = true;
    protected $defaultValue; // WispDefaultValue
    protected $groupName = '';
    protected $parentEntity;
    protected $value;
    protected $tableNamePrefix = ''; // For properties with multiple sub instances
    protected $isUnifield = true; // when it uses one field in a database table
    protected $uniqueValue = false;
    protected $searchableWithString; // Pictures for example are not searchable with a string, ints can be searched with a string like "80"

    function GetParentEntity()
    {
        return $this->parentEntity;
    }

    function SetParentEntity(WispEntity $ParamEntity)
    {
        $this->parentEntity = $ParamEntity;
        return $this->parentEntity;
    }

    function GetDefaultValue(WispDefaultValue $ParamDefaultValueObject, $ParamEntityProperty, $ParamInstanceId)
    {
        // return string // What is this ?????
    }

    function SetDefaultValue(WispDefaultValue $ParamDefaultValue)
    {
        $this->defaultValue = $ParamDefaultValue;
    }

    function GenerateDefaultValue()
    {
        if (isset($this->defaultValue)) {
            $this->value = $this->defaultValue->GetValue();
        }
    }

    function Scaffold()
    {
        // Override ...
    }

    function GetTableName()
    {
        // Override ...
        return '';
    }

    function SetValueFromDb(WispQueryResult $ParamSourceResult)
    {
        $this->SetValue($ParamSourceResult->GetColumnValue($this->GetDbColumnName()));
    }

    function GetDbColumnName()
    {
        return $this->dbColNamePrefix . $this->name . $this->dbColNameSuffix;
    }

    function IsEditable()
    {
        return $this->editable;
    }

    function SetEditable(bool $ParamIsEditable)
    {
        $this->editable = $ParamIsEditable;
    }

    function IsUnifield()
    {
        return $this->isUnifield;
    }

    function GetJson()
    {

        return json_encode($this->GetJsonArray());

    }

    function GetJsonArray()
    {
        $array_info = array();

        $array_info["name"] = $this->GetName();
        $array_info["value"] = $this->GetValue();
        $array_info["type"] = $this->GetTypeString();
        $array_info["label"] = $this->GetLabel();
        $array_info["readonly"] = $this->GetReadOnlyString();
        $array_info["unique"] = $this->GetIfUniqueValueAsString();

        return $array_info;

    }

    function GetName()
    {
        return $this->name;
    }

    function GetValue()
    {
        return $this->value;
    }

    function SetValue($ParamValue)
    {
        $this->value = $ParamValue;
    }

    function GetTypeString()
    {
        return "generic";
    }

    function GetLabel()
    {
        return $this->labelText;
    }

    function GetReadOnlyString()
    {
        if (!$this->editable)
            return "true";
        else
            return "false";

    }

    function GetIfUniqueValueAsString()
    {
        if ($this->uniqueValue)
            return "true";
        else
            return "false";

    }

    function OnUpdate()
    {

    }

    function GetSummaryValue()
    {

    }

    function EnableUniqueValue()
    {
        $this->uniqueValue = true;
    }

    function IsStringSearchable()
    {
        return $this->searchableWithString;
    }

}

// ==========================================================================================================================
class WispEntityPropertyText extends WispEntityProperty
{
    protected $lineCount;

    function __construct($ParamPropertyName, $ParamLabelText, $ParamLineCount)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        $this->dbColNamePrefix = 'EP_';
        $this->defaultValue = NULL;
        $this->lineCount = $ParamLineCount;
        $this->searchableWithString = true;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'VARCHAR(255)', 'NULL DEFAULT ""');
        }
    }

    function GetTypeString()
    {
        return "text";
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }
}

// ==========================================================================================================================
class WispEntityPropertyInteger extends WispEntityProperty
{
    function __construct($ParamPropertyName, $ParamLabelText)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        $this->dbColNamePrefix = 'EP_';
        $this->searchableWithString = false;
        // $this->defaultValue = 0;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'INT', 'NULL DEFAULT 0');
        }
    }

    function GetTypeString()
    {
        return "int";
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }
}

// ==========================================================================================================================
// Display a list (WispEntityGrid)
class WispEntityPropertyShoppingList extends WispEntityProperty
{
    protected $parentEntityPrefix = 'ID_';
    protected $childEntityPrefix = 'ID_';
    protected $targetSubEntity;
    protected $searchProperty;
    protected $isDestocker;
    protected $stockProperty;
    protected $priceProperty;
    protected $totalPriceProperty;

    function __construct($ParamPropertyName, $ParamLabelText, $ParamTargetSubEntity, $ParamSearchProperty, $ParamIsDestocker, $ParamStockProperty, $ParamPriceProperty, $ParamTotalPriceProperty)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        // $this->dbColNamePrefix = 'EP_';
        $this->defaultValue = NULL;
        $this->isUnifield = false;

        $this->targetSubEntity = $ParamTargetSubEntity;
        $this->searchProperty = $ParamSearchProperty;
        $this->isDestocker = $ParamIsDestocker;
        $this->stockProperty = $ParamStockProperty;
        $this->priceProperty = $ParamPriceProperty;
        $this->totalPriceProperty = $ParamTotalPriceProperty;

        $this->searchableWithString = false;
    }

    function Scaffold()
    {
        // ------------------------------------------------------------------------------------------------------
        $b = WispConnectionManager::Get()->CheckIfTableExists($this->GetTableName());

        if (!$b) {
            WispConnectionManager::Get()->CreateTable($this->GetTableName(), false);
        }

        // ------------------------------------------------------------------------------------------------------
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->GetTableName(), $this->GetParentEntityDbColumnName());


        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->GetTableName(), $this->GetParentEntityDbColumnName(), 'INT', 'NOT NULL DEFAULT 0');
        }

        // ------------------------------------------------------------------------------------------------------
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->GetTableName(), $this->GetParentEntityDbColumnName() . "_VERSION");


        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->GetTableName(), $this->GetParentEntityDbColumnName() . "_VERSION", 'INT', 'NOT NULL DEFAULT 0');
        }

        // ------------------------------------------------------------------------------------------------------
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->GetTableName(), $this->GetChildEntityDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->GetTableName(), $this->GetChildEntityDbColumnName(), 'INT', 'NOT NULL DEFAULT 0');
        }

        // ------------------------------------------------------------------------------------------------------
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->GetTableName(), "AMOUNT");

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->GetTableName(), "AMOUNT", 'INT', 'NOT NULL DEFAULT 0');
        }

        // ------------------------------------------------------------------------------------------------------
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->GetTableName(), "IS_STOCKED");

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->GetTableName(), "IS_STOCKED", 'BIT', 'NOT NULL DEFAULT 0');
        }
    }

    function GetTableName()
    {
        return 'entity_' . $this->parentEntity->GetName() . '_children_' . $this->name;
    }

    function GetParentEntityDbColumnName()
    {
        return $this->parentEntityPrefix . $this->GetParentEntity()->GetName();
    }

    function GetChildEntityDbColumnName()
    {
        return $this->childEntityPrefix . $this->targetSubEntity;
    }

    function GetSummaryValue()
    {
        $q = "SELECT * FROM " . $this->GetTableName() . " WHERE ID_" . $this->parentEntity->GetName() . " = " . $this->parentEntity->GetEntityID() . ";";

        return (string)WispConnectionManager::Get()->OpenQuery($q)->GetRecordCount();
    }

    function SetValueFromDb(WispQueryResult $ParamSourceResult)
    {
        $this->SetValue('');
    }

    function GetJsonArray()
    {
        $array_info = array();

        $array_info["name"] = $this->GetName();
        $array_info["value"] = $this->GetValue();
        $array_info["type"] = $this->GetTypeString();
        $array_info["label"] = $this->GetLabel();
        $array_info["readonly"] = $this->GetReadOnlyString();
        $array_info["searchEntity"] = $this->targetSubEntity;
        $array_info["searchProperty"] = $this->searchProperty;
        $array_info["priceProperty"] = $this->priceProperty;
        $array_info["totalPriceProperty"] = $this->totalPriceProperty;

        return $array_info;

    }

    function GetValue()
    {
        return '';
    }

    function GetTypeString()
    {
        return "shopping_list";
    }

    function IsDestocker()
    {
        return $this->isDestocker;
    }

    function GetSubEntity()
    {
        return $this->targetSubEntity;
    }

    function GetStockProperty()
    {
        return $this->stockProperty;
    }

    function OnUpdate()
    {
        // Find the table where the property is stocked
        $tableName = $this->GetTableName();

        // Check if its stocker or destocker
        $destocker = $this->isDestocker;

        // Select current version records

    }
}

// ==========================================================================================================================
class WispEntityPropertySalt extends WispEntityProperty
{
    // Protected variables

    // Public variables

    // Properties

    // Methodes
    function __construct(string $ParamPropertyName)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->dbColNamePrefix = 'EP_SALT_';
        $this->defaultValue = NULL;
        $this->searchableWithString = false;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'VARCHAR(255)', 'NULL DEFAULT ""');
        }
    }

    function SetValue($ParamValue)
    {
        $this->value = WispStringTools::GenerateRandomString($ParamValue);
    }

    function GetTypeString()
    {
        return "salt";
    }
}

// ==========================================================================================================================
class WispEntityPropertyHashPBKDF2 extends WispEntityProperty
{
    // Protected variables
    protected $saltPropertyName;
    protected $iterationCount;

    // Public variables

    // Properties

    // Methodes
    function __construct(string $ParamPropertyName, string $ParamSaltPropertyName, int $ParamIterationCount)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->dbColNamePrefix = 'EP_HASH_';
        $this->defaultValue = NULL;
        $this->saltPropertyName = $ParamSaltPropertyName;
        $this->iterationCount = $ParamIterationCount;
        $this->searchableWithString = false;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'VARCHAR(255)', 'NULL DEFAULT ""');
        }
    }

    function SetValue($ParamValue)
    {

        $saltProperty = $this->parentEntity->GetPropertyByName($this->saltPropertyName);

        $salt = ($saltProperty->GetValue());

        $hash = hash_pbkdf2("sha256", $ParamValue, $salt, $this->iterationCount, 64);

        $this->value = $hash;
    }

    function GetTypeString()
    {
        return "hash_pbkdf2";
    }
}

// ==========================================================================================================================
class WispEntityPropertyImage extends WispEntityProperty
{
    function __construct($ParamPropertyName, $ParamLabelText)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        $this->dbColNamePrefix = 'EP_';
        $this->defaultValue = NULL;
        $this->searchableWithString = false;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'VARCHAR(255)', 'NULL DEFAULT ""');
        }
    }

    function GetTypeString()
    {
        return "img";
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }

    function SetValueFromDb(WispQueryResult $ParamSourceResult)
    {
        $this->SetValue($ParamSourceResult->GetColumnValue($this->GetDbColumnName()));
    }
}

// ==========================================================================================================================
class WispEntityPropertySubInstance extends WispEntityProperty
{
    protected $subEntityName;

    function __construct($ParamPropertyName, $ParamLabelText, $ParamSubEntityName, $ParamInstanceSummaryString)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        $this->dbColNamePrefix = 'ID_';
        $this->defaultValue = NULL;
        $this->subEntityName = $ParamSubEntityName;
        $this->instanceSummaryString = $ParamInstanceSummaryString;
        $this->searchableWithString = false; // TODO : make it searchable with a string
    }

    function GetSubEntityName()
    {
        return $this->subEntityName;
    }

    function GetSummaryString()
    {
        return $this->instanceSummaryString;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'INT', 'NOT NULL');
        }
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }

    function SetValueFromDb(WispQueryResult $ParamSourceResult)
    {
        $value = $ParamSourceResult->GetColumnValue($this->GetDbColumnName());

        $tmpEntity = WispEntityManager::Get()->GetEntityByName($this->subEntityName);

        if (isset($tmpEntity->GetInstancesByEntityID($value)[0])) {
            $tmpInstance = $tmpEntity->GetInstancesByEntityID($value)[0];
        } else {
            $tmpInstance = NULL;
        }

        $this->SetValue($tmpInstance);
    }

    function GetJsonArray()
    {
        $array_info = array();

        if ($this->value != NULL) {
            $tmpValue = $this->value->GetJsonArray();
        } else {
            $tmpValue = array();
        }

        $array_info["name"] = $this->GetName();
        $array_info["value"] = $tmpValue;
        $array_info["type"] = $this->GetTypeString();
        $array_info["label"] = $this->GetLabel();
        $array_info["readonly"] = $this->GetReadOnlyString();
        $array_info["unique"] = $this->GetIfUniqueValueAsString();
        $array_info["summaryString"] = $this->GenerateSummaryString();

        return $array_info;
    }

    function GetTypeString()
    {
        return "sub";
    }

    function GenerateSummaryString()
    {
        $s = '';
        $tmpDynamicString = new WispDynamicString($this->instanceSummaryString);
        $tmpArray = $tmpDynamicString->PrepareArrayFromString();

        if (!isset($this->value))
            return "";

        for ($i = 0; $i < sizeof($tmpArray); $i++) {
            $tmpSubArray = $tmpArray[$i];

            if ($tmpSubArray['v'] == true) {
                $s = $s . $this->value->GetPropertyByName($tmpSubArray['s'])->GetValue();
            } else {
                $s = $s . $tmpSubArray['s'];
            }
        }

        return $s;
    }


}

// ==========================================================================================================================
class WispEntityPropertyMultiSubInstance extends WispEntityProperty
{
    //protected $subEntityName;
    protected $subEntity;
    protected $subInstanceVisiblePropertiesArray;
    protected $operationParametersArray;
    protected $instanceSummaryString;

    function __construct($ParamPropertyName, $ParamPropertyLabel, $ParamSubInstanceName, $ParamInstanceSummaryString, $ParamSubInstanceVisiblePropertiesArray, $ParamOperationParametersArray)
    {
        $subEntity = WispEntityManager::Get()->GetEntityByName($ParamSubInstanceName);
        
        if (isset($subEntity))
            $this->subEntity = $subEntity;
        else
            WispJsonMessages::ErrorMessage("WRONG_ENTITY_NAME", "Wrong entity name while trying to create MultiSubInstance property.");

        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamPropertyLabel;
        $this->defaultValue = NULL;
        $this->subInstanceVisiblePropertiesArray = $ParamSubInstanceVisiblePropertiesArray;
        $this->operationParametersArray = $ParamOperationParametersArray;
        $this->searchableWithString = false; // TODO : make it searchable with a string
        $this->isUnifield = false;
        $this->instanceSummaryString = $ParamInstanceSummaryString;
    }

    function GetSubEntityName()
    {
        return $this->subEntity->GetEntityName();
    }

    function GetSummaryString()
    {
        return $this->instanceSummaryString;
    }

    function Scaffold()
    {
        // ------------------------------------------------------------------------------------------------------
        $tableName = $this->parentEntity->GetTableName() . "_wisp_sub_" . $this->name;

        $b = WispConnectionManager::Get()->CheckIfTableExists($tableName);

        if (!$b) {
            WispConnectionManager::Get()->CreateTable($tableName, false);
        }
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }

    function SetValueFromDb(WispQueryResult $ParamSourceResult)
    {
        /*
        $value = $ParamSourceResult->GetColumnValue($this->GetDbColumnName());

        $tmpEntity = WispEntityManager::Get()->GetEntityByName($this->subEntityName);

        if (isset($tmpEntity->GetInstancesByEntityID($value)[0])) {
            $tmpInstance = $tmpEntity->GetInstancesByEntityID($value)[0];
        } else {
            $tmpInstance = NULL;
        }

        $this->SetValue($tmpInstance);
        */
    }

    function GetJsonArray()
    {
        $array_info = array();

        if ($this->value != NULL) {
            $tmpValue = $this->value->GetJsonArray();
        } else {
            $tmpValue = array();
        }

        $array_info["name"] = $this->GetName();
        $array_info["value"] = $tmpValue;
        $array_info["type"] = $this->GetTypeString();
        $array_info["label"] = $this->GetLabel();
        $array_info["readonly"] = $this->GetReadOnlyString();
        $array_info["unique"] = $this->GetIfUniqueValueAsString();
        $array_info["summaryString"] = $this->GenerateSummaryString();
        $array_info["subEntityName"] = $this->subEntity->GetEntityName();
        $array_info["subEntityDisplayName"] = $this->subEntity->GetDisplayName();

        return $array_info;
    }

    function GetTypeString()
    {
        return "multi_sub";
    }

    function GenerateSummaryString()
    {
        return "20";
    }
}

// ==========================================================================================================================
class WispEntityPropertyDate extends WispEntityProperty
{
    function __construct($ParamPropertyName, $ParamLabelText)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        $this->dbColNamePrefix = 'EP_';
        $this->defaultValue = NULL;
        $this->searchableWithString = false;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'DATE', 'NULL DEFAULT UNIX_TIMESTAMP()');
        }
    }

    function GetTypeString()
    {
        return "date";
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }
}

// ==========================================================================================================================
class WispEntityPropertyBool extends WispEntityProperty
{
    function __construct($ParamPropertyName, $ParamLabelText)
    {
        // TODO : inherited create
        $this->name = $ParamPropertyName;
        $this->labelText = $ParamLabelText;
        $this->dbColNamePrefix = 'EP_';
        $this->defaultValue = NULL;
    }

    function Scaffold()
    {
        $b = WispConnectionManager::Get()->CheckIfColumnExists($this->parentEntity->GetTableName(), $this->GetDbColumnName());

        if (!$b) {
            WispConnectionManager::Get()->CreateColumn($this->parentEntity->GetTableName(), $this->GetDbColumnName(), 'BIT', '');
        }
    }

    function GetTypeString()
    {
        return "bool";
    }

    function GetSummaryValue()
    {
        return Parent::GetValue();
    }

    function SetValue($ParamValue)
    {
        $this->value = filter_var($ParamValue, FILTER_VALIDATE_BOOLEAN);
    }
}

?>