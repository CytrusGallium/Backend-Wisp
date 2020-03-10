<?php

// require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');

// Product -----------------------------------------------------------------------------------------------------------------------------------
$e = new WispEntity('product', 'Produit', 'Pipe');
$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
$e->AddProperty(new WispEntityPropertyText('PRICE_RETAIL', 'Prix de détail', 1));
$e->AddProperty(new WispEntityPropertyText('PRICE_RETAILER', 'Prix revendeur', 1));
$e->AddProperty(new WispEntityPropertyText('PRICE_ENTERPRISE', "Prix d'entreprise", 1));
$e->AddProperty(new WispEntityPropertySubInstance('FAMILY', 'Famille', 'family','$NAME$'));

$p = new WispEntityPropertyText('BARCODE', 'Code Bar', 1);
$p->EnableUniqueValue();
$e->AddProperty($p);

$e->AddProperty(new WispEntityPropertyInteger('AMOUNT', 'Quantité Disponible'));
WispEntity::$LHP->SetEditable(false);

$e->SetImportantProperties('NAME', 'BARCODE', 'AMOUNT');
WispEntityManager::Get()->RegisterEntity($e);

// Family ------------------------------------------------------------------------------------------------------------------------------------
$e = new WispEntity('family', 'Famille', 'Label');
$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));

$e->SetImportantProperties('NAME', 'NAME', 'NAME');
WispEntityManager::Get()->RegisterEntity($e);

// Provider ----------------------------------------------------------------------------------------------------------------------------------
$e = new WispEntity('provider', 'Fournisseur', 'delivery-truck-silhouette');
$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
$e->AddProperty(new WispEntityPropertyText('ADDRESS', 'Adresse', 1));
$e->AddProperty(new WispEntityPropertyText('PHONE', 'Téléphone', 1));
$e->AddProperty(new WispEntityPropertyText('MOBILE', 'Mobile', 1));
$e->AddProperty(new WispEntityPropertyText('E_MAIL', 'eMail', 1));
$e->AddProperty(new WispEntityPropertyText('COMMERCIAL_REGISTER_ID', 'N° Registre Commerce', 1));
$e->AddProperty(new WispEntityPropertyText('NIS', 'NID Statistique', 1));

$e->SetImportantProperties('NAME', 'MOBILE', 'E_MAIL');
WispEntityManager::Get()->RegisterEntity($e);

// customer
$e = new WispEntity('customer', 'Client', 'man');
$e->AddProperty(new WispEntityPropertyText('NAME', 'Designation', 1));
$e->AddProperty(new WispEntityPropertyText('ADDRESS', 'Adresse', 1));
$e->AddProperty(new WispEntityPropertyText('PHONE', 'Téléphone', 1));
$e->AddProperty(new WispEntityPropertyText('MOBILE', 'Mobile', 1));
$e->AddProperty(new WispEntityPropertyText('E_MAIL', 'eMail', 1));
$e->AddProperty(new WispEntityPropertyText('COMMERCIAL_REGISTER_ID', 'N° Registre Commerce', 1));
$e->AddProperty(new WispEntityPropertyText('NIS', 'NID Statistique', 1));

$e->SetImportantProperties('NAME', 'MOBILE', 'E_MAIL');
WispEntityManager::Get()->RegisterEntity($e);

// reception
$e = new WispEntity('reception', 'Reception', 'collapse-window-option');
$e->AddProperty(new WispEntityPropertySubInstance('PROVIDER', 'Fournissseur', 'provider','$NAME$'));
WispEntityManager::Get()->RegisterEntity($e);

// sale
$e = new WispEntity('sale', 'Vente', 'shopping-cart-black-shape');

$p = new WispEntityPropertyDate('DATE', 'Date', 1);
$p->SetDefaultValue(new WispDefaultValueCurrentDate());
$e->AddProperty($p);

$p = new WispEntityPropertyText('TOTAL', 'Total', 1);
$p->SetDefaultValue(new WispDefaultValueSimple('0'));
$e->AddProperty($p);

//$p = new WispEntityPropertyMultiSubInstance($PropertyName, $PropertyLabel, $SubInstanceName, $SubInstanceVisiblePropertiesArray, $OperationParametersArray);
//$p = new WispEntityPropertyMultiSubInstance('PRODUCT', 'Produits', 'product', 'BARCODE', true, 'AMOUNT', 'PRICE', 'TOTAL');
//$e->AddProperty($p);

$p = new WispEntityPropertyMultiSubInstance('SALE_ITEM', 'Articles', 'product', '$NAME$', array('NAME','AMOUNT'), array('AMOUNT'));
$e->AddProperty($p);

$e->SetImportantProperties('DATE', 'TOTAL', 'DATE');
WispEntityManager::Get()->RegisterEntity($e);

?>