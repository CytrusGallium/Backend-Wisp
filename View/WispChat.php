<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

// ----------------------- Login Check ------------------------

if (isset($_POST["s"])) {
    $Session_ID = $_POST["s"];
} else if (isset($_GET["s"])) {
    $Session_ID = $_GET["s"];
} else {
    $Session_ID = "";
}

WispAccessManager::Get()->CheckUserLogin("json", $Session_ID);

// ----------------------- Syntax Check ------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['r'])) {
        $responseType = $_POST['r'];
    }

    if (isset($_POST['user']) && isset($_POST['message'])) {
        $user = $_POST["user"];
        $message = $_POST["message"];
    } else {
        if ($responseType == "json") {
            echo "Syntax error.";
            exit();
        }

        $user = "";
        $message = "";

    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['r'])) {
        $responseType = $_GET['r'];
    }

    if (isset($_GET['user']) && isset($_GET['message'])) {
        $user = $_GET["user"];
        $message = $_GET["message"];
    } else {
        if ($responseType == "json") {
            echo "Syntax error.";
            exit();
        }

        $user = "";
        $message = "";
    }
} else {
    http_response_code(405);
    die();
}

if (isset($responseType) && !is_null($responseType)) {
    if ($responseType == "html") {
        Respond_Html($user, $message);
    } else if ($responseType == "json") {
        Respond_Json($user, $message);
    } else {
        echo "Invalid Response Type.";
        exit();
    }
} else {
    $responseType = "json";
    Respond_Json($user, $message);
}

// ------------------------- Check Done -------------------

function Respond_Json($ParamUser, $ParamMessage)
{
    WispChatManager::SendChatMessage($ParamUser, $ParamMessage);
    exit();
}

function Respond_Html($ParamUser, $ParamMessage)
{
    echo "Feature not implemented";
    exit();
}

?>
