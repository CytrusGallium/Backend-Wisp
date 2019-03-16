<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispConnectionManager.php');

	//-------------------------------------------------------------------------------------------------------------------------------------------
	class WispDefaultValue
	{
		protected $value;

		function GetValue ()
		{
			// return $this->Value;
		}
	}

	//-------------------------------------------------------------------------------------------------------------------------------------------
	class WispDefaultValueSimple extends WispDefaultValue
	{
		function __Construct (string $ParamValue)
		{
			$this->value = $ParamValue;
		}

		function GetValue ()
		{
			return $this->value;
		}
	}

	//-------------------------------------------------------------------------------------------------------------------------------------------
	class WispDefaultValueCurrentDate extends WispDefaultValue
	{
		function __Construct ()
		{
			
		}

		function GetValue ()
		{
			$q = "SELECT CURDATE();";
			return WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue('CURDATE()');
		}
	}

?>