<?php

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	class WispCheckManager
	{
		private static $singleton;

		private function __construct ()
		{
			
		}

		public static function Get ($ParamUseTestDatabase = false)
        {
            if (empty(WispCheckManager::$singleton))
            {
                WispCheckManager::$singleton = new WispCheckManager ();
			}
			
            return WispCheckManager::$singleton;
        }
		
	}