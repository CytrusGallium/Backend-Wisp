<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	echo WispAccesManager::Get()->Logoff();

?>