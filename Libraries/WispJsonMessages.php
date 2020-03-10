<?php

class WispJsonMessages
{
    static function ErrorMessage(string $ParamCode, string $ParamMessage)
    {
        $array = array();

        $array ["type"] = "ErrorMessage";
        $array ["code"] = $ParamCode;
        $array ["msg"] = $ParamMessage;

        echo json_encode($array);

        exit();
    }

    static function Feedback(string $ParamCode, string $ParamMessage)
    {
        $array = array();

        $array ["type"] = "Feedback";
        $array ["code"] = $ParamCode;
        $array ["msg"] = $ParamMessage;

        echo json_encode($array);

        exit();
    }
}

?>