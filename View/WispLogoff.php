<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

// =====================================================================
if (isset($_POST["s"])) {
    $Session_ID = $_POST["s"];
} else if (isset($_GET["s"])) {
    $Session_ID = $_GET["s"];
} else {
    $Session_ID = "";
}

// ----------------------- Syntax Check ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['r'])) {
        $responseType = $_POST['r'];
    }

    if (isset($_POST['user'])) {
        $user = $_POST["user"];
    } else {
        if ($responseType == "json") {
            echo WispJsonMessages::ErrorMessage("SYNTAX","Syntax error.");
            exit();
        }

        $user = "";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['r'])) {
        $responseType = $_GET['r'];
    }

    if (isset($_GET['user'])) {
        $user = $_GET["user"];
    } else {
        if ($responseType == "json") {
            echo WispJsonMessages::ErrorMessage("SYNTAX","Syntax error.");
            exit();
        }

        $user = "";
    }
} else {
    http_response_code(405);
    exit();
}

// ------------------------- Syntax Check Done -------------------

WispAccessManager::Get()->CheckUserLogin("json", $Session_ID);

// =====================================================================

echo WispAccessManager::Get()->Logoff($user);

?>