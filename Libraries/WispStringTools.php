<?php

class WispStringTools
{
    public static function GenerateRandomString($length)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public static function IsJson(string $ParamString) {
        json_decode($ParamString);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function Implode(string $ParamSeparator, $ParamArray) : string
    {
        if (isset($ParamArray))
        {
            return implode($ParamSeparator, $ParamArray);
        }

        return "";
    }

    public static function LogInput($ParamInput, $ParamRawInput)
    {
        $entry = array( "script_id" => WispMethodData::GetScriptCounter(), "ip" => $_SERVER['REMOTE_ADDR'], "time" => date("M,d,Y H:i:s"), "input" => $ParamInput, "raw" => $ParamRawInput);

        $stream = fopen(self::GetApplicationDirectory()."input_log.txt", 'a'); // a = append mode.
        fwrite($stream, json_encode($entry) . PHP_EOL); // PHP_EOL = end of line.
        fclose($stream);
    }

    public static function LogQuery(string $ParamQuery, string $ParamQueryType)
    {
        $entry = array( "script_id" => WispMethodData::GetScriptCounter(), "time" => date("M,d,Y H:i:s"), "Query" => $ParamQuery, "type" => $ParamQueryType);

        $stream = fopen(self::GetApplicationDirectory()."query_log.txt", 'a'); // a = append mode.
        fwrite($stream, json_encode($entry) . PHP_EOL); // PHP_EOL = end of line.
        fclose($stream);
    }

    public static function GetApplicationDirectory () : string
    {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $currentWorkingDirectory = getcwd();
        $currentChar = substr($currentWorkingDirectory, strlen($documentRoot), 1);

        if ($currentChar == "\\" or $currentChar == "/")
        {
            $result = "";

            for ($i = strlen($documentRoot) + 1; $i < strlen($currentWorkingDirectory); $i++){
                if ($currentWorkingDirectory[$i] == "\\" or $currentWorkingDirectory[$i] == "/")
                {
                    break;
                }
                else
                {
                    $result = $result . $currentWorkingDirectory[$i];
                }
            }

            return $documentRoot . "/" .$result ."/";
        }
        else
        {
            return "";
        }
    }
}

// ===================================================================================================================================

class WispDynamicString
{
    protected $rawString;
    protected $pairArray = array();
    protected $counter = 0;

    function __Construct(string $ParamRawString)
    {
        $this->rawString = $ParamRawString;
    }

    function PrepareArrayFromString()
    {
        $currentPiece = '';
        $onVariable = false;
        $lastChar = '';

        for ($i = 0; $i < strlen($this->rawString); $i++) {
            $char = $this->rawString[$i];

            if ($char == '$') {
                if ($lastChar == '$') {
                    $onVariable = false;
                    $this->AddPairToArray('$', false);
                    //$currentPiece = '';
                    //$lastChar = '';
                }

                if ($onVariable) {
                    $onVariable = false;
                    $this->AddPairToArray($currentPiece, true);
                    $currentPiece = '';
                } else {
                    $onVariable = true;
                    $this->AddPairToArray($currentPiece, false);
                    $currentPiece = '';
                }
            } else {
                $currentPiece = $currentPiece . $char;
            }

            $lastChar = $char;
        }

        return $this->pairArray;

    }

    function AddPairToArray(string $ParamString, bool $ParamIsVariable)
    {
        if ($ParamString == '')
            return;

        $this->pairArray[$this->counter] = array('s' => $ParamString, 'v' => $ParamIsVariable);

        $this->counter++;
    }
}

?>