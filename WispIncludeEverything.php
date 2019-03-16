<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');

	$directory = dirname(__FILE__, 1);

    require_once ($directory. '/Controller/WispConnectionManager.php');
    require_once ($directory. '/Controller/WispAccesManager.php');
    require_once ($directory. '/Controller/WispConstantManager.php');
    require_once ($directory. '/Controller/WispEntityManager.php');
    require_once ($directory. '/Controller/WispEntity.php');
    require_once ($directory. '/Controller/WispEntityInstance.php');
    require_once ($directory. '/Controller/WispEntityProperty.php');
    require_once ($directory. '/Controller/WispDefaultValue.php');

    require_once ($directory. '/Libraries/WispJsonMessages.php');
    require_once ($directory. '/Libraries/WispStringTools.php');

    require_once ($directory. '/Model/MyEntities.php');

?>