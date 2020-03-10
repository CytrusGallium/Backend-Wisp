<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

// ----------------------- Syntax Check ------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['r'])) {
        $responseType = $_POST['r'];
    }

    if (isset($_POST["s"])) {
        $Session_ID = $_POST["s"];
    } else {
        $Session_ID = "";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['r'])) {
        $responseType = $_GET['r'];
    }

    if (isset($_GET["s"])) {
        $Session_ID = $_GET["s"];
    } else {
        $Session_ID = "";
    }

} else {
    http_response_code(405);
    die();
}

if (isset($responseType) && !is_null($responseType)) {
    if ($responseType == "html") {
        Respond_Html();
    } else if ($responseType == "bootstrap") {
        Respond_Bootstrap();
    } else if ($responseType == "json") {
        Respond_Json();
    } else {
        echo "Invalid Response Type.";
        exit();
    }
} else {
    $responseType = "json";
    Respond_Json();
}

// ------------------------- Syntax Check Done -------------------

WispAccessManager::Get()->CheckUserLogin("json", $Session_ID);

function Respond_Html()
{
    $dirPath = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/";

    $menuResponseArray = json_decode(WispEntityManager::Get()->GetJson());

    echo "<html><body>";

    foreach ($menuResponseArray->Entities as $key => $value) {
        // TODO : Dynamic application name (not always "wisp")
        echo '<img width=16 src="' . 'http://' . $_SERVER['HTTP_HOST'] . '/wisp' . $value[0]->GlyphPath . '"/>';
        echo '<a href="' . $dirPath . 'WispEntityGrid.php?entity=' . $value[0]->EntityName . '&r=html"> ' . $value[0]->EntityLabel . '</a>';
        echo "<br/>";
        echo "<br/>";

    }

    echo "</body></html>";
}

function Respond_Json()
{
    echo WispEntityManager::Get()->GetJson();
}

function Respond_Bootstrap()
{

}


?>