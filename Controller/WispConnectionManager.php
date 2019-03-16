<?php

	class WispConnectionManager
	{
		protected static $singleton;
		protected $pdoConnection;
		protected $databaseName;
		
		protected function __construct ($ParamUseTestDatabase = false)
		{
			// Connect to the database
			try 
			{	
				// echo dirname(__FILE__, 2);

				// $this->pdoConnection = new PDO('mysql:host=wisp.freemyip.com;dbname=test', 'root', 'PASS');
				// $ini = $_SERVER['DOCUMENT_ROOT']. '/Wisp/Model/Connection.ini';

				$ini = dirname(__FILE__, 2). '/Model/Connection.ini';
				$iniArray = parse_ini_file($ini, true);
				// $ParamUseTestDatabase = true;
				// echo "VALUE-P = " . $ParamUseTestDatabase;

				if ($ParamUseTestDatabase == true)
				{
					$sectionName = "test_database";
					// echo "!";
				}
				else
				{
					$sectionName = "database";
				}

				$this->pdoConnection = new PDO('mysql:host=' . $iniArray[$sectionName]['host'] . ';dbname='. $iniArray[$sectionName]['db'], $iniArray[$sectionName]['user'], $iniArray[$sectionName]['pass']);
			}
			
			catch (PDOException $e) 
			{
			
				echo 'Error: ' . $e->getMessage();
				exit();
				
			}
			
			// Get the database name
			$statement = $this->pdoConnection->prepare('SELECT database();');
			$statement->execute();
			$this->databaseName = $statement->fetchColumn();
		}

		// ...
		public static function Get ($ParamUseTestDatabase = false)
        {
        	// $ParamUseTestDatabase = true;
        	// echo "VALUE = " . $ParamUseTestDatabase;
            if (empty(WispConnectionManager::$singleton))
            {
                WispConnectionManager::$singleton = new WispConnectionManager ($ParamUseTestDatabase);
                // return WispConnectionManager::$singleton;
            }
            return WispConnectionManager::$singleton;

        }
		
		// ...
		function GetPdoConnection ()
		{
		
			// if (empty(WispConnectionManager::$Singleton))
			// {
				
			// 	WispConnectionManager::$Singleton = new WispConnectionManager ();
				
			// }
			
			return WispConnectionManager::Get()->pdoConnection;
			
		}

		// ...
		function GetDatabaseName ()
		{
			return $this->databaseName;
		}

		// ...
		function ExecuteQuery (string $ParamQuery)
		{
			$this->GetPdoConnection()->prepare($ParamQuery)->execute();
		}

		// ...
		function OpenQuery (string $ParamQuery)
		{
			$statement = $this->GetPdoConnection()->prepare($ParamQuery);
			$statement->execute();

			$tmp;
			if ($statement->rowCount() > 0) $tmp = true;
			else $tmp = false;

			$result = new WispQueryResult ($statement, $tmp);
			// $result->PdoStatement = $statement;
			// $result->isRecordAvailable = $tmp;

			return $result;
		}

		// ...
		function CheckIfTableExists (string $ParamTableName)
		{
			$q = 'SELECT * FROM information_schema.tables WHERE table_schema = "' . $this->GetDatabaseName() .
				'" AND table_name ="' . $ParamTableName . '";';
			
			if ($this->OpenQuery($q)->IsRecordAvailable()) return true;

			return false;
		}

		// ...
		function CreateTable (string $ParamTableName, bool $ParamEntityTable)
		{
			if ($ParamEntityTable)
			{
				// If The Table is gonna hold entities it shall have this structure
				$q = 'CREATE TABLE ' . $ParamTableName .
				      "\n" . '(' . "\n" . ' ID INT NOT NULL AUTO_INCREMENT,' .
				      "\n" . ' ENTITY_ID INT NOT NULL DEFAULT 0,' . "\n" .
				      ' VERSION_ID INT NOT NULL DEFAULT 0,' . "\n" . ' IS_LAST BOOLEAN NULL DEFAULT FALSE,' .
				      "\n" . ' DTC TIMESTAMP NULL DEFAULT "0000-00-00",' . "\n" .
				      ' UID INTEGER NOT NULL DEFAULT 0,' . "\n" . ' IS_DELETED BOOLEAN NULL DEFAULT FALSE,' .
				      "\n" . ' PRIMARY KEY (`ID`));';

				WispConnectionManager::Get()->ExecuteQuery($q);
			}
			else
			{
				// If its another table type it shall have this structure
			    $q = 'CREATE TABLE ' . $ParamTableName .
			      "\n" . '(' . "\n" . ' ID INT NOT NULL AUTO_INCREMENT,' .
			      ' PRIMARY KEY (`ID`));';

				WispConnectionManager::Get()->ExecuteQuery($q);
			}
		}

		// ...
		function CheckIfColumnExists (string $ParamTableName, string $ParamColumnName)
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
		function ExecuteInsert(string $ParamTableName, $ParamColumns, $ParamValues)
		{
			$ColCount = count($ParamColumns);
			$ValCount = count($ParamValues);

			if ($ColCount == 0 || $ValCount == 0)
			{
				echo 'ExecuteInsert Error : Column or Value count is equal to zero';
				exit();
			}

			if ($ColCount != $ValCount)
			{
				echo 'ExecuteInsert Error : Column and Value count are not the same';
				exit();
			}

			$tmpColomns = '';
			$tmpValues = '';

			for ($i = 0; $i < $ColCount; $i++ )
			{
				$tmpColomns = $tmpColomns . $ParamColumns[$i];
				$tmpValues = $tmpValues . '"' . $ParamValues[$i] . '"';

				if ($i < $ColCount - 1)
				{
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

		
	}

	
	// =======================================================================================================================
	Class WispQueryResult
	{
		protected $PdoStatement;
		Protected $isRecordAvailable;
		protected $currentRow;
		protected $records;
		protected $currentRecordIndex;

		// ...
		function __construct($ParamPdoStatement, $ParamIsRecordAvailable)
		{
			$this->records = array();
			$this->PdoStatement = $ParamPdoStatement;
			$this->isRecordAvailable = $ParamIsRecordAvailable;
			$this->OrganizeRecords();
			$this->Next();
		}

		// ...
		protected function OrganizeRecords()
		{
			$i = 0;
			while ($row = $this->PdoStatement->fetch(PDO::FETCH_ASSOC)) 
			{	
        		
				if (!array_key_exists('TABLE_CATALOG', $row))
				{
					$this->records[$i] = $row;

					// print_r($this->records[$i]);
					// echo '<br/>';
					// echo '<br/>';
					
					$i++;
				}
    		
    		}

    		$this->currentRecordIndex = 0;

    		// $originalResult = $this->PdoStatement->fetchAll();
		}	

		// OLD / DEPRECATED
		function Next()
		{
			$this->currentRow = $this->PdoStatement->fetchAll();
		}

		// ...
		function NextRecord ()
		{
			if ($this->currentRecordIndex < $this->GetRecordCount()-1)
			{
				$this->currentRecordIndex ++;
				return true;
				exit();
			}

			return false;
		}

		// ...
		function IsRecordAvailable()
		{
			return $this->isRecordAvailable;
		}

		// ...
		function GetRecordCount ()
		{
			return $this->PdoStatement->rowCount();
		}

		// Get column ID from column Name
		public function ColumnToId(string $ParamColumnName)
		{
			$c = $this->PdoStatement->columnCount();

			if ($c == 0)
			{
				echo 'Column count = 0 !';
				exit();
			}

			for ($i = 0; $i < $c; $i++)
			{
				if ($this->PdoStatement->getColumnMeta($i)["name"] == $ParamColumnName)
				{
					return $i;
				}
			}
		}

		// Old / Deprecated
		// public function GetColumnValue(string $ParamColumnName)
		// {
		// 	$columnID = $this->ColumnToId($ParamColumnName);
		// 	return $this->currentRow[$columnID];
		// }

		// ...
		public function GetColumnValue(string $ParamColumnName)
		{
			// echo "COL = " . $ParamColumnName;
			return $this->records[$this->currentRecordIndex][$ParamColumnName];
		}

		// ...
		public function GetAllRecords()
		{
			return $this->records;
		}
	}

?>