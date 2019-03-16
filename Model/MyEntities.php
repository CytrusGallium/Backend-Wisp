<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispEntity.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispEntityInstance.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');
	// require_once (dirname(__FILE__, 2). '/Controller/WispDefaultValue.php');

	// Product
	$e = new WispEntity('product', 'Produit', 'coffee-cup-on-a-plate-black-silhouettes');
	$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
	$e->AddProperty(new WispEntityPropertyText('PRICE', 'Prix', 1));
	$e->AddProperty(new WispEntityPropertyText('FAMILY', 'Famille', 1));
	$e->AddProperty(new WispEntityPropertyDate('ENTRY_DATE', 'Entry Date'));
	$e->AddProperty(new WispEntityPropertyDate('EXIT_DATE', 'Exit Date'));
	$e->AddProperty(new WispEntityPropertyText('PROJECT_NAME', 'Le nom du projet', 1));
	$e->AddProperty(new WispEntityPropertyText('CLIENT_NAME', 'Le nom du client', 1));
	$e->AddProperty(new WispEntityPropertyText('PROJECT_TYPE', 'Type de projet', 1));
	$e->AddProperty(new WispEntityPropertyDate('START_DATE', 'Date de debut', 1));
	$e->AddProperty(new WispEntityPropertyDate('END_DATE', 'Date de fin', 	1));
	$e->AddProperty(new WispEntityPropertyText('RESPONSIBLE_AGENT', 'Agent responsable', 1));
    
    $p = new WispEntityPropertyText('BARCODE', 'Code Bar', 1);
    $p->EnableUniqueValue();
    $e->AddProperty($p);

    $e->AddProperty(new WispEntityPropertyInteger('AMOUNT', 'Quantite Disponible'));
    WispEntity::$LHP->SetEditable(false);

    $e->AddProperty(new WispEntityPropertyImage('PHOTO', 'Photo'));
    $e->AddProperty(new WispEntityPropertyImage('PHOTO_ALT', 'Alternate Photo'));

	$e->SetImportantProperties('NAME', 'BARCODE', 'AMOUNT');
	WispEntityManager::Get()->RegisterEntity($e);

	// provider
	$e = new WispEntity('provider', 'Fournisseur', 'delivery-truck-silhouette');
	$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
	$e->AddProperty(new WispEntityPropertyInteger('NIS', 'NID Statistique'));

	$e->SetImportantProperties('NAME', 'NIS', 'NIS');
	WispEntityManager::Get()->RegisterEntity($e);

	// customer
	$e = new WispEntity('customer', 'Client', 'man');
	$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
	WispEntityManager::Get()->RegisterEntity($e);

	// reception
	$e = new WispEntity('reception', 'Reception', 'collapse-window-option');
	WispEntityManager::Get()->RegisterEntity($e);

	// sale
	$e = new WispEntity('sale', 'Vente', 'shopping-cart-black-shape');
	
	$p = new WispEntityPropertyText('DATE', 'Date', 1);
	$p->SetDefaultValue(new WispDefaultValueCurrentDate());
	$e->AddProperty($p);
	
	$p = new WispEntityPropertyText('TOTAL', 'Total', 1);
	$p->SetDefaultValue(new WispDefaultValueSimple('0'));
	$e->AddProperty($p);

	$p = new WispEntityPropertyShoppingList('PRODUCT', 'Produits', 'product', 'BARCODE', true, 'AMOUNT', 'PRICE', 'TOTAL');
	$e->AddProperty($p);


	$e->SetImportantProperties('DATE', 'TOTAL', 'PRODUCT');
	WispEntityManager::Get()->RegisterEntity($e);

	// Agent
	$e = new WispEntity('agent', 'Agent', 'coffee-cup-on-a-plate-black-silhouettes');
	$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
	$e->AddProperty(new WispEntityPropertyText('FUNCTION', 'Fonction', 1));
	$e->AddProperty(new WispEntityPropertyText('PHONE_NUMBER', 'Tel', 1));

	$e->SetImportantProperties('NAME', 'FUNCTION', 'PHONE_NUMBER');
	WispEntityManager::Get()->RegisterEntity($e);

	//Project
	$e = new WispEntity('project', 'Projet', 'coffee-cup-on-a-plate-black-silhouettes');
	$e->AddProperty(new WispEntityPropertyText('PROJECT_NAME', 'Nom du projet', 1));
	$e->AddProperty(new WispEntityPropertyText('CLIENT_NAME', 'Nom du client', 1));
	$e->AddProperty(new WispEntityPropertyText('PROJECT_TYPE', 'Type de projet', 1));
	$e->AddProperty(new WispEntityPropertyText('START_DATE', 'Date de debut', 1));
	$e->AddProperty(new WispEntityPropertyText('END_DATE', 'Date de fin', 	1));
	$e->AddProperty(new WispEntityPropertyText('RESPONSIBLE_AGENT', 'Agent responsable', 1));

	$e->SetImportantProperties('PROJECT_NAME', 'END_DATE', 'RESPONSIBLE_AGENT');
	WispEntityManager::Get()->RegisterEntity($e);

	// Mingle
	$e = new WispEntity('mingle', 'Mingle', 'coffee-cup-on-a-plate-black-silhouettes');
	$e->AddProperty(new WispEntityPropertyText('LABEL', 'Label', 1));
	$e->AddProperty(new WispEntityPropertySubInstance('VIDEO_1', 'Video N° 1', 'video', 'Video $IDENTIFIER$ at $VOLUME$% volume.'));
	$e->AddProperty(new WispEntityPropertySubInstance('VIDEO_2', 'Video N° 2', 'video', 'Video $IDENTIFIER$ at $VOLUME$% volume.'));
	$e->AddProperty(new WispEntityPropertySubInstance('VIDEO_3', 'Video N° 3', 'video', 'Video $IDENTIFIER$ at $VOLUME$% volume.'));
	$e->AddProperty(new WispEntityPropertySubInstance('VIDEO_4', 'Video N° 4', 'video', 'Video $IDENTIFIER$ at $VOLUME$% volume.'));

	$e->SetImportantProperties('LABEL', 'LABEL', 'LABEL');
	WispEntityManager::Get()->RegisterEntity($e);

	// Video
	$e = new WispEntity('video', 'Video', 'coffee-cup-on-a-plate-black-silhouettes');
	$e->AddProperty(new WispEntityPropertyText('IDENTIFIER', 'Identifier', 1));
	$e->AddProperty(new WispEntityPropertyInteger('VOLUME', 'Volume'));

	$e->SetImportantProperties('IDENTIFIER', 'IDENTIFIER', 'VOLUME');
	WispEntityManager::Get()->RegisterEntity($e);

	// User_Data
	$e = new WispEntity('user_data', 'Donnes Utilisateurs', 'coffee-cup-on-a-plate-black-silhouettes');
	// $e->AddProperty(new WispEntityPropertyText('IDENTIFIER', 'Identifier', 1));
	// $e->AddProperty(new WispEntityPropertyInteger('USER', 'ID Utilisateur'));
	$e->AddProperty(new WispEntityPropertyImage('PHOTO', 'Photo'));
	$e->AddProperty(new WispEntityPropertyText('CITY', 'Ville', 1));
	$e->SetDisplayShortcut(false);

	$e->SetImportantProperties('IDENTIFIER', 'IDENTIFIER', 'VOLUME');
	WispEntityManager::Get()->RegisterEntity($e);

?>