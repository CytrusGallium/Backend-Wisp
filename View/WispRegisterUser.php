<?php

// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

// =====================================================================
if (isset($_POST["s"])) {
    $Session_ID = $_POST["s"];
} else if (isset($_GET["s"])) {
    $Session_ID = $_GET["s"];
} else {
    $Session_ID = "";
}

WispAccessManager::Get()->CheckUserLogin("json", $Session_ID);
// =====================================================================

$user = $_GET["user"];
$pass = $_GET["pass"];
$mail = $_GET["mail"];
$phone = $_GET["phone"];
$firstname = $_GET["firstname"];
$familyname = $_GET["familyname"];
$sex = (int)$_GET["sex"];

// sleep(10);

$result = WispAccessManager::Get()->AddNewUserAdvanced($user, $pass, $mail, $phone, $firstname, $familyname, $sex);

if ($result == 1) {
    echo "OK";
} else {
    echo "ERROR";
}

// echo "hello";

?>