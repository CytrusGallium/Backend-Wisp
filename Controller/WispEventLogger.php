<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

Class WispEventLogger
{
    private static $singleton;

    // Constructor
    private function __construct()
    {

    }

    // Get Singleton
    public static function Get()
    {
        if (empty(WispEventLogger::$singleton)) {
            WispEventLogger::$singleton = new WispEventLogger ();
            WispEventLogger::$singleton->ScaffoldLogEventTable();
        }

        return WispEventLogger::$singleton;
    }

    public function LogInstanceCreation($ParamInstance, $EventDescription, $ParamOriginalScript)
    {
        $instance = $this->CreateEventInstance("INSTANCE_CREATE", $ParamOriginalScript);
        $instance->GetPropertyByName('ENTITY')->SetValue($ParamInstance->GetEntityName());
        $instance->GetPropertyByName('INSTANCE_ID')->SetValue($ParamInstance->GetID());
        $instance->GetPropertyByName('EVENT_DESCRIPTION')->SetValue($EventDescription);
        $instance->AddToDb();
    }

    private function CreateEventInstance($ParamEventName, $ParamOriginalScript)
    {
        $instance = new WispEntityInstance(WispEntityManager::Get()->GetEntityByName('log_event'));
        $instance->GetPropertyByName('USER_ID')->SetValue($_SESSION["uid"]);
        $instance->GetPropertyByName('IP_ADDRESS')->SetValue(getenv('REMOTE_ADDR'));
        $instance->GetPropertyByName('IP_HTTP_X_FORWARDED_FOR')->SetValue(getenv('HTTP_X_FORWARDED_FOR'));
        $clientInfo = $this->getBrowser();
        $instance->GetPropertyByName('CLIENT_INFO')->SetValue(implode(" | ", $clientInfo));
        $instance->GetPropertyByName('EVENT_NAME')->SetValue($ParamEventName);
        $instance->GetPropertyByName('DATE')->SetValue(date('Y-m-d H:i:s', time()));
        $instance->GetPropertyByName('SCRIPT')->SetValue($ParamOriginalScript);

        return $instance;
    }

    function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";
        $ub = 'Undefined';
        $pattern = 'Undefined';

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && !preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');

        if ($ub == 'Undefined')
            return array(
                'userAgent' => $u_agent,
                'name' => $bname,
                'version' => $version,
                'platform' => $platform,
                'pattern' => $pattern
            );

        $pattern = '#(?<browser>' . join('|', $known) .
            ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if ($i != 1) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        // check if we have a number
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

    public function LogInstanceRead($ParamInstance, $EventDescription, $ParamOriginalScript)
    {
        $instance = $this->CreateEventInstance("INSTANCE_UPDATE", $ParamOriginalScript);
        $instance->GetPropertyByName('ENTITY')->SetValue($ParamInstance->GetEntityName());
        $instance->GetPropertyByName('INSTANCE_ID')->SetValue($ParamInstance->GetID());
        $instance->GetPropertyByName('EVENT_DESCRIPTION')->SetValue($EventDescription);
        $instance->AddToDb();
    }

    public function LogInstanceUpdate($ParamInstance, $EventDescription, $ParamOriginalScript)
    {
        $instance = $this->CreateEventInstance("INSTANCE_UPDATE", $ParamOriginalScript);
        $instance->GetPropertyByName('ENTITY')->SetValue($ParamInstance->GetEntityName());
        $instance->GetPropertyByName('INSTANCE_ID')->SetValue($ParamInstance->GetID());
        $instance->GetPropertyByName('EVENT_DESCRIPTION')->SetValue($EventDescription);
        $instance->AddToDb();
    }

    public function LogInstanceDeletion($ParamInstance, $EventDescription, $ParamOriginalScript)
    {

    }

    public function LogInstanceLoginAttempt($ParamInstance, $EventDescription)
    {

    }


    // From : https://stackoverflow.com/questions/10902438/get-browser-in-chrome-returns-default-browser

    private function ScaffoldLogEventTable()
    {
        // Event Table
        $e = new WispEntity('log_event', 'Log Event', 'coffee-cup-on-a-plate-black-silhouettes');
        $e->AddProperty(new WispEntityPropertyInteger('USER_ID', 'User', 1));
        $e->AddProperty(new WispEntityPropertyText('IP_ADDRESS', 'IP Address', 1));
        $e->AddProperty(new WispEntityPropertyText('IP_HTTP_X_FORWARDED_FOR', 'IP Address 2', 1));
        $e->AddProperty(new WispEntityPropertyText('CLIENT_INFO', 'Client info', 1));
        $e->AddProperty(new WispEntityPropertyText('EVENT_NAME', 'Event Name', 1));
        $e->AddProperty(new WispEntityPropertyText('ENTITY', 'Entity Name', 1));
        $e->AddProperty(new WispEntityPropertyInteger('INSTANCE_ID', 'Instance ID', 1));
        $e->AddProperty(new WispEntityPropertyText('EVENT_DESCRIPTION', 'Event Description', 1));
        $e->AddProperty(new WispEntityPropertyDate('DATE', 'Date and Time'));
        $e->AddProperty(new WispEntityPropertyText('SCRIPT', 'Script where event happened', 1));

        $e->SetImportantProperties('EVENT_NAME', 'EVENT_DESCRIPTION', 'DATE');
        WispEntityManager::Get()->RegisterEntity($e);
    }
}

?>