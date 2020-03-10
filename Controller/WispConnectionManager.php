<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

class WispConnectionManager
{
    protected static $singleton;
    protected $pdoConnection;
    protected $databaseName;

    protected function __construct($ParamUseTestDatabase = false)
    {
        // Connect to the database
        try {
            // echo dirname(__FILE__, 2);

            // $this->pdoConnection = new PDO('mysql:host=wisp.freemyip.com;dbname=test', 'root', 'PASS');
            // $ini = $_SERVER['DOCUMENT_ROOT']. '/Wisp/Model/Connection.ini';

            $ini = dirname(__FILE__, 2) . '/Model/Connection.ini';
            $iniArray = parse_ini_file($ini, true);
            // $ParamUseTestDatabase = true;
            // echo "VALUE-P = " . $ParamUseTestDatabase;

            if ($ParamUseTestDatabase == true) {
                $sectionName = "test_database";
                // echo "!";
            } else {
                $sectionName = "database";
            }

            $this->pdoConnection = new PDO('mysql:host=' . $iniArray[$sectionName]['host'] . ';dbname=' . $iniArray[$sectionName]['db'], $iniArray[$sectionName]['user'], $iniArray[$sectionName]['pass']);
        } catch (PDOException $e) {

            echo 'Error: ' . $e->getMessage();
            exit();

        }

        // Get the database name
        $statement = $this->pdoConnection->prepare('SELECT database();');
        $statement->execute();
        $this->databaseName = $statement->fetchColumn();
    }

    // ...

    function CheckIfTableExists(string $ParamTableName)
    {
        $q = 'SELECT * FROM information_schema.tables WHERE table_schema = "' . $this->GetDatabaseName() .
            '" AND table_name ="' . $ParamTableName . '";';
        
        if ($this->OpenQuery($q)->IsRecordAvailable()) return true;

        return false;
    }

    // ...

    function GetDatabaseName()
    {
        return $this->databaseName;
    }

    // ...

    function OpenQuery(string $ParamQuery)
    {
        $statement = $this->GetPdoConnection()->prepare($ParamQuery);
        $result = $statement->execute();

        if (!$result)
            WispJsonMessages::ErrorMessage("PDO_ERROR", $statement->errorInfo()[2]);

        $tmp = null;
        if ($statement->rowCount() > 0) $tmp = true;
        else $tmp = false;

        return new WispQueryResult ($statement, $tmp);
    }

    // ...

    function GetPdoConnection()
    {

        // if (empty(WispConnectionManager::$Singleton))
        // {

        // 	WispConnectionManager::$Singleton = new WispConnectionManager ();

        // }

        return WispConnectionManager::Get()->pdoConnection;

    }

    // ...

    public static function Get($ParamUseTestDatabase = false)
    {
        if (empty(WispConnectionManager::$singleton)) {
            WispConnectionManager::$singleton = new WispConnectionManager ($ParamUseTestDatabase);
        }
        return WispConnectionManager::$singleton;

    }

    // ...

    function CreateTable(string $ParamTableName, bool $ParamEntityTable)
    {
        if ($ParamEntityTable) {
            // If The Table is gonna hold entities it shall have this structure
            $q = 'CREATE TABLE ' . $ParamTableName .
                "\n" . '(' . "\n" . ' ID INT NOT NULL AUTO_INCREMENT,' .
                "\n" . ' ENTITY_ID INT NOT NULL DEFAULT 0,' . "\n" .
                ' VERSION_ID INT NOT NULL DEFAULT 0,' . "\n" . ' IS_LAST BOOLEAN NULL DEFAULT FALSE,' .
                "\n" . ' DTC TIMESTAMP NULL DEFAULT UNIX_TIMESTAMP(),' . "\n" .
                ' UID INTEGER NOT NULL DEFAULT 0,' . "\n" . ' IS_DELETED BOOLEAN NULL DEFAULT FALSE,' .
                "\n" . ' PRIMARY KEY (`ID`));';

            WispConnectionManager::Get()->ExecuteQuery($q);
        } else {
            // If its another table type it shall have this structure
            $q = 'CREATE TABLE ' . $ParamTableName .
                "\n" . '(' . "\n" . ' ID INT NOT NULL AUTO_INCREMENT,' .
                ' PRIMARY KEY (`ID`));';

            WispConnectionManager::Get()->ExecuteQuery($q);
        }
    }

    // ...

    function CheckIfColumnExists(string $ParamTableName, string $ParamColumnName)
    {
        $q = 'SELECT * FROM information_schema.COLUMNS WHERE table_schema = "' . $this->databaseName .
            '" AND table_name ="' . $ParamTableName . '" AND column_name = "' .
            $ParamColumnName . '";';

        if ($this->OpenQuery($q)->IsRecordAvailable()) return true;

        return false;
    }

    // ...

    function CreateColumn(string $ParamTableName, string $ParamColumnName, string $ParamFieldType, string $ParamOptions)
    {
        $q = 'ALTER TABLE ' . $ParamTableName . ' ADD ' .
            $ParamColumnName . ' ' . $ParamFieldType . ' ' . $ParamOptions . ';';
        $this->ExecuteQuery($q);
    }

    // ...

    function ExecuteQuery(string $ParamQuery)
    {
        // $result = $this->GetPdoConnection()->prepare($ParamQuery)->execute();

        $statement = $this->GetPdoConnection()->prepare($ParamQuery);
        $result = $statement->execute();

        if (!$result)
            WispJsonMessages::ErrorMessage("PDO_ERROR", $statement->errorInfo()[2]);
    }

    // ...

    function ExecuteInsert(string $ParamTableName, $ParamColumns, $ParamValues)
    {
        $ColCount = count($ParamColumns);
        $ValCount = count($ParamValues);

        if ($ColCount == 0 || $ValCount == 0) {
            echo 'ExecuteInsert Error : Column or Value count is equal to zero';
            exit();
        }

        if ($ColCount != $ValCount) {
            echo 'ExecuteInsert Error : Column and Value count are not the same';
            exit();
        }

        $tmpColomns = '';
        $tmpValues = '';

        for ($i = 0; $i < $ColCount; $i++) {
            $tmpColomns = $tmpColomns . $ParamColumns[$i];
            $tmpValues = $tmpValues . '"' . $ParamValues[$i] . '"';

            if ($i < $ColCount - 1) {
                $tmpColomns = $tmpColomns . ',';
                $tmpValues = $tmpValues . ',';
            }
        }

        $tmpColomns = '(' . $tmpColomns . ')';
        $tmpValues = '(' . $tmpValues . ')';

        $q = 'INSERT INTO ' . $ParamTableName . ' ' . $tmpColomns . ' VALUES ' .
            $tmpValues . ';';

        // echo "Insert Query : " . $q;
        // echo "<br/>";

        $this->ExecuteQuery($q);

        $q = 'SELECT LAST_INSERT_ID();';

        return $this->OpenQuery($q)->GetColumnValue('LAST_INSERT_ID()');
    }

    public static function SendToReactor(string $ParamMessage)
    {
        $_remote_ip = "127.0.0.1";
        $_remote_port = 6901;

        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($sock)
        {
            set_error_handler(function($errno, $errstr, $errfile, $errline) {
                echo"";
            });

            $connect = socket_connect($sock, $_remote_ip, $_remote_port);

            restore_error_handler();

            if ($connect)
            {
                socket_read($sock, 2048);
                socket_write($sock, $ParamMessage, strlen($ParamMessage));

                socket_close($sock);
            }

        }
    }

}

?>