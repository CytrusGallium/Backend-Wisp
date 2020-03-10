<?php

// ======================================= BackWisp Generic/Intergral Test ======================================

// define(name, value);
// constant(name);

// ...
function WispAssert($ParamExpectedValue, $ParamValueToTest, $ParamTestName, $ParamExitOnFailure = false)
{
    if ($ParamExpectedValue === $ParamValueToTest) {
        echo '<p style="color: #00AA00; font-size: 10pt; font-family: arial; margin-bottom: -10px"> Test : ' . $ParamTestName . ' (SUCCESFUL)</p>';
        return true;
    } else {
        echo '<p style="color:#AA0000; font-size:10pt; font-family: arial; margin-bottom: -10px"> Test : ' . $ParamTestName . ' (FAILED)</p>';

        if ($ParamExitOnFailure) {
            exit();
        } else {
            return false;
        }

    }
}

// ...
WispAssert("A", "B", "THIS TEST SHALL FAIL");
WispAssert("A", "A", "THIS TEST SHALL PASS");

// Requirments
$directory = dirname(__FILE__, 1);

require_once($directory . '/Controller/WispConnectionManager.php');
require_once($directory . '/Controller/WispAccesManager.php');
require_once($directory . '/Controller/WispConstantManager.php');
require_once($directory . '/Controller/WispEntityManager.php');
require_once($directory . '/Controller/WispEntity.php');
require_once($directory . '/Controller/WispEntityInstance.php');
require_once($directory . '/Controller/WispEntityProperty.php');
require_once($directory . '/Controller/WispDefaultValue.php');

require_once($directory . '/Libraries/WispJsonMessages.php');
require_once($directory . '/Libraries/WispStringTools.php');

// If no include error assert OK
print_r(error_get_last()); // TODO

// Connection Test
$connection = WispConnectionManager::Get(true);

// Check database name
WispAssert("test", $connection->GetDatabaseName(), "TEST DATABASE NAME SHALL BE 'TEST'");

// Check if database is empty
$q = "SELECT COUNT(DISTINCT `table_name`) AS 'COUNT' FROM `information_schema`.`columns` WHERE `table_schema` = '" . "test" . "';";

$count = $connection->OpenQuery($q)->GetColumnValue('COUNT');
WispAssert("0", $count, "TEST DATABASE SHALL HAVE NO TABLES", false);

// Login is required to do some stuff
$loginResult = json_decode(WispAccessManager::Get()->Login("admin", "0000"));

WispAssert("Login", $loginResult->Type, "LOGIN RESULT CHECK : TYPE");
WispAssert("Succes", $loginResult->Response, "LOGIN RESULT CHECK : RESPONSE");
WispAssert(26, strlen($loginResult->Session_ID), "LOGIN RESULT CHECK : SESSION_ID");

// Create entity and add properties to it
$e = new WispEntity('customer', 'Client', 'man');
$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
WispEntityManager::Get()->RegisterEntity($e);

$e = WispEntityManager::Get()->GetEntityByName("customer");

WispAssert("customer", $e->GetName(), "ENTITY NAME");
WispAssert("Client", $e->GetDisplayName(), "ENTITY DISPLAY NAME");
WispAssert(1, $e->GetPropertyCount(), "PROPERTY COUNT");

$p = $e->GetPropertyByName("NAME");

WispAssert("Designation", $p->GetLabel(), "PROPERTY DISPLAY NAME");

// Create an instance
$i = new WispEntityInstance($e);
WispAssert("customer", $i->GetName(), "INSTANCE NAME");

// Change customer name
$i->GetPropertyByName("NAME")->SetValue("Dib");
WispAssert("Dib", $i->GetPropertyByName("NAME")->GetValue(), "CUSTOMER NAME");

// Add the instance to database
$i->AddToDb();

// Check instance count in database
$q = "SELECT COUNT(*) AS 'COUNT' FROM entity_customer;";
$count = $connection->OpenQuery($q)->GetColumnValue('COUNT');
WispAssert("1", $count, "THERE SHALL BE ONLY ONE INSTANCE IN DATABASE", false);

// Check values in the database
$q = "SELECT * FROM entity_customer;";
$result = $connection->OpenQuery($q);
WispAssert("1", $result->GetColumnValue('ID'), "CHECK INSTANCE IN DATABASE : ID");
WispAssert("1", $result->GetColumnValue('ENTITY_ID'), "CHECK INSTANCE IN DATABASE : ENTITY_ID");
WispAssert("1", $result->GetColumnValue('VERSION_ID'), "CHECK INSTANCE IN DATABASE : VERSION_ID");
WispAssert("1", $result->GetColumnValue('IS_LAST'), "CHECK INSTANCE IN DATABASE : IS_LAST");
WispAssert("1", $result->GetColumnValue('UID'), "CHECK INSTANCE IN DATABASE : UID");
WispAssert("0", $result->GetColumnValue('IS_DELETED'), "CHECK INSTANCE IN DATABASE : IS_DELETED");
WispAssert("Dib", $result->GetColumnValue('EP_NAME'), "CHECK INSTANCE IN DATABASE : EP_NAME");

// Check customer name in database
$i2 = new WispEntityInstance($e);
$i2->LoadFromDb(1);

WispAssert("Dib", $i->GetPropertyByName("NAME")->GetValue(), "CUSTOMER NAME LOADED FROM DATABASE");

// Empty the database

?>



