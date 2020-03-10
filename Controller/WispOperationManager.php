<?php

Class WispOperationManager
{
    private static function ScaffoldOperationTable()
    {
        // Event Table
        $e = new WispEntity('wisp_operation', 'Operation', 'coffee-cup-on-a-plate-black-silhouettes');

        $e->AddProperty(new WispEntityPropertyText('TYPE', 'Type', 1));
        $e->AddProperty(new WispEntityPropertyText('TARGET_ENTITY', 'Target Entity', 1));
        $e->AddProperty(new WispEntityPropertyInteger('TARGET_ID', 'Target ID', 1));
        $e->AddProperty(new WispEntityPropertyInteger('AMOUNT', 'Amount', 1));
        $e->AddProperty(new WispEntityPropertyInteger('PERFORMED', 'Performed'));

        $e->SetImportantProperties('NAME', 'TARGET_ENTITY', 'TARGET_ID');
        WispEntityManager::Get()->RegisterEntity($e);
    }

    public static function PerformOperation(string $ParamOperationType, string $ParamEntityName, string $ParamProperty, string $ParamID, string $ParamAmount)
    {
        self::ScaffoldOperationTable();

        if ($ParamOperationType == "add")
        {
            $instance = new WispEntityInstance(WispEntityManager::Get()->GetEntityByName('operation'));
            $instance->GetPropertyByName('TYPE')->SetValue($ParamOperationType);
            $instance->AddToDb();

            $entity = WispEntityManager::Get()->GetEntityByName($ParamEntityName);

            if (!isset($entity))
            {
                WispJsonMessages::ErrorMessage("entity_not_found", "Entity not found while trying to perform operation.");
            }

            $property = $entity->GetPropertyByName("$ParamProperty");

            if (!isset($property))
            {
                WispJsonMessages::ErrorMessage("property_not_found", "Property not found while trying to perform operation.");
            }

            $q = "UPDATE " . $entity->GetTableName() . " SET " . $property->GetDbColumnName() . " = "
            . $property->GetDbColumnName() . " + " . $ParamAmount . " WHERE ID = '" . $ParamID . "';";

            WispConnectionManager::Get()->ExecuteQuery($q);
        }
        elseif ($ParamOperationType == "sub")
        {

        }
        else
        {
            WispJsonMessages::ErrorMessage("invalid_operation_name", "Invalid Operation Name");
        }
    }
}

?>