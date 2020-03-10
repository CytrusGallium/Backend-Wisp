<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

// ----------------------- Syntax Check ------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['r'])) {
        $responseType = $_POST['r'];
    }

    if (isset($_POST['type'])) {
        $type = $_POST["type"];
    } else {
        if ($responseType == "json") {
            echo "Syntax error.";
            exit();
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['r'])) {
        $responseType = $_GET['r'];
    }

    if (isset($_GET['type'])) {
        $type = $_GET["type"];
    } else {
        if ($responseType == "json") {
            echo "Syntax error.";
            exit();
        }
    }
} else {
    http_response_code(405);
    die();
}

if (isset($responseType) && !is_null($responseType)) {
    if ($responseType == "html") {

    } else if ($responseType == "json") {

    } else {
        echo "Invalid Response Type.";
        exit();
    }
} else {
    $responseType = "json";
}

// ------------------------- Syntax Check Done -------------------

function SubscribeToEntity($ParamEntityName)
{

}

function SubscribeToInstance($ParamEntityName, $ParamInstanceID)
{

}

function SubscribeToChat($ParamEntityName)
{
    // Subscribe automaticly when login ?
}

?>