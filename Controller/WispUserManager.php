<?php

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	class WispUserManager
	{
		// private static $singleton;
		private $connectedUsers;

		private function __construct ()
		{
			$this->connectedUsers = array();
		}

		public static function Get ($ParamUseTestDatabase = false)
        {
			if (empty($_SESSION['UserManager']))
			{
				$_SESSION['UserManager'] = new WispUserManager ();
			}
			
            return $_SESSION['UserManager'];
		}
		
		public function RegisterUserLogin ($ParamUserName)
		{
			if (!in_array($ParamUserName, $this->connectedUsers))
			{
				array_push($this->connectedUsers, $ParamUserName);
				print_r($this->connectedUsers);
			}
		}

		public function GetUserArray ()
		{
			return $this->connectedUsers;
		}
		
	}