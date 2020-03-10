<?php

// Manage access to data i.e : login + Keep tracks of connected users.

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

Class WispAccessManager
{
    private static $singleton;

    // Constructor
    private function __construct()
    {
        $this->connectedUsers = array();
    }

    // Get Singleton
    public static function Get() : WispAccessManager
    {
        if (empty(WispAccessManager::$singleton)) {
            WispAccessManager::$singleton = new WispAccessManager ();
            WispAccessManager::$singleton->ScaffoldUserTable();
        }

        return WispAccessManager::$singleton;
    }

    // ...
    function Login(string $ParamUsername, string $ParamPassword) : string
    {
        // Session
        session_name($ParamUsername);
        session_start();

        // Brute Force Check
        if (!isset($_SESSION["LoginAttempts"])) {
            $_SESSION["LoginAttempts"] = 1;
        } else {
            $_SESSION["LoginAttempts"] += 1;
        }

        // Find the user
        $q = 'SELECT ID, EP_SALT_SALT, EP_HASH_PASS FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';

        $result = WispConnectionManager::Get()->OpenQuery($q);

        if ($result->IsRecordAvailable()) {
            $userID = $result->GetColumnValue('ID');
            $salt = $result->GetColumnValue('EP_SALT_SALT');
            $hash = $result->GetColumnValue('EP_HASH_PASS');

            $loginHash = hash_pbkdf2("sha256", $ParamPassword, $salt, 100000, 64);

            // Check password
            if ($loginHash == $hash) {
                // Mark user state as connected
                $_SESSION["ConnectionState"] = "Connected";
                $_SESSION["User"] = $ParamUsername;
                $_SESSION["uid"] = $userID;

                // Register user as connected
                $this->RegisterUserLogin($ParamUsername, session_id());

                // Respond
                $array_meta = array
                (
                    'Type' => 'Login',
                    'Response' => 'Success',
                    'Message' => 'Welcome !',
                    'Session_ID' => session_id(),
                    'uid' => $_SESSION["uid"]
                );

                return json_encode($array_meta);

            } else {
                // Respond
                $array_meta = array
                (
                    'Type' => 'Login',
                    'Response' => 'IncorrectPassword',
                    'Message' => 'Incorrect password.'
                );

                return json_encode($array_meta);
            }
        } else {
            // Respond
            $array_meta = array
            (
                'Type' => 'Login',
                'Response' => 'UserNotFound',
                'Message' => 'User not found.'
            );

            return json_encode($array_meta);
        }

    }

    // Register user as connected in database and reactors
    public function RegisterUserLogin(string $ParamUserName, string $ParamSessionID)
    {
        $q = "UPDATE entity_wisp_user SET EP_SESSION_ID = '" . $ParamSessionID . "' WHERE EP_NAME = '" . $ParamUserName ."';";
        WispConnectionManager::Get()->ExecuteQuery($q);

        $q = "UPDATE entity_wisp_user SET EP_IS_CONNECTED = TRUE" . " WHERE EP_NAME = '" . $ParamUserName ."';";
        WispConnectionManager::Get()->ExecuteQuery($q);

        $array_meta = array
        (
            'type' => 'authentification',
            'user' => $ParamUserName,
            'sessionID' => $ParamSessionID
        );

        WispConnectionManager::SendToReactor(json_encode($array_meta));
    }

    // ...

    function AddNewUserAdvanced(string $ParamUsername, string $ParamPassword, string $ParamMail, string $ParamPhone, string $ParamFirstName, string $ParamFamilyName, int $ParamSex)
    {
        if ($ParamUsername == "" || $ParamPassword == "" || $ParamPhone == "" || $ParamMail == "") {
            return false;
        }

        // Check username
        $q = 'SELECT ID FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';
        // echo $q;

        if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable()) {
            // echo "User already exists.";
            return false;
        }

        // Check e-mail
        $q = 'SELECT ID FROM entity_wisp_user WHERE EP_MAIL = "' . $ParamMail . '";';
        // echo $q;

        if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable()) {
            // echo "User already exists.";
            return false;
        }

        // Check phone number
        $q = 'SELECT ID FROM entity_wisp_user WHERE EP_PHONE_NUMBER = "' . $ParamPhone . '";';
        // echo $q;

        if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable()) {
            // echo "User already exists.";
            return false;
        }

        // Create entity and add

        $e = WispEntityManager::Get()->GetEntityByName('wisp_user');

        $instance = new WispEntityInstance($e);
        $instance->GetPropertyByName('NAME')->SetValue($ParamUsername);
        $instance->GetPropertyByName('SALT')->SetValue(16);
        $instance->GetPropertyByName('PASS')->SetValue($ParamPassword);
        $instance->GetPropertyByName('MAIL')->SetValue($ParamMail);
        $instance->GetPropertyByName('PHONE_NUMBER')->SetValue($ParamPhone);
        $instance->GetPropertyByName('FIRST_NAME')->SetValue($ParamFirstName);
        $instance->GetPropertyByName('FAMILY_NAME')->SetValue($ParamFamilyName);
        $instance->GetPropertyByName('SEX')->SetValue($ParamSex);

        $instance->AddToDb();

        return true;
    }

    // ...
    function ScaffoldUserTable()
    {
        // User Table
        $e = new WispEntity('wisp_user', 'Utilisateur', 'coffee-cup-on-a-plate-black-silhouettes');
        $e->AddProperty(new WispEntityPropertyText('NAME', 'Nom', 1));
        $e->AddProperty(new WispEntityPropertySalt('SALT'));
        $e->AddProperty(new WispEntityPropertyHashPBKDF2('PASS', 'SALT', 100000));
        $e->AddProperty(new WispEntityPropertyText('MAIL', 'e-mail', 1));
        $e->AddProperty(new WispEntityPropertyText('PHONE_NUMBER', 'Phone Number', 1));
        $e->AddProperty(new WispEntityPropertyText('FIRST_NAME', 'First Name', 1));
        $e->AddProperty(new WispEntityPropertyText('FAMILY_NAME', 'Family name', 1));
        $e->AddProperty(new WispEntityPropertyInteger('SEX', 'Sex', 1));
        $e->AddProperty(new WispEntityPropertyBool('IS_CONNECTED', 'Connected'));
        $e->AddProperty(new WispEntityPropertyText('LISTENER_IP', 'Tcp Listener Ip', 1));
        $e->AddProperty(new WispEntityPropertyText('SESSION_ID', 'Session ID', 1));

        $e->SetImportantProperties('NAME', 'MAIL', 'PASS');
        WispEntityManager::Get()->RegisterEntity($e);

        // Default User
        $this->AddNewUser('admin', '0000');
    }

    // Check if a user who has ParamSessionID is connected

    function AddNewUser(string $ParamUsername, string $ParamPassword)
    {
        if ($ParamUsername == "" || $ParamPassword == "") {
            return false;
        }

        $q = 'SELECT * FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';
        // echo $q;

        if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable()) {
            // echo "User already exists.";
            return false;
        }

        $e = WispEntityManager::Get()->GetEntityByName('wisp_user');

        $instance = new WispEntityInstance($e);
        $instance->GetPropertyByName('NAME')->SetValue($ParamUsername);
        $instance->GetPropertyByName('SALT')->SetValue(16);
        $instance->GetPropertyByName('PASS')->SetValue($ParamPassword);

        $instance->AddToDb(true);

        return true;
    }

    // ...

    function CheckUserLogin(string $ParamResponseType, string $ParamSessionID = '') : bool
    {
        if ($ParamSessionID != '') {
            session_id($ParamSessionID);
        }

        if (session_name() == "")
        {
            //session_name($ParamUserName);
        }

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Check session existence
        if (!isset($_SESSION["ConnectionState"])) {
            WispJsonMessages::ErrorMessage("LOGIN_REQUIRED", "Please login before using our services.");
        }

        if ($_SESSION["ConnectionState"] == "Connected") {
            return true;
        }

        WispJsonMessages::ErrorMessage("LOGIN_REQUIRED", "Please login before using our services.");
    }

    function CheckSpecificUserLogin(string $ParamUserName, string $ParamSessionID)
    {
        session_id($ParamSessionID);
        session_start();

        // Check session existence and connection state.
        if (!isset($_SESSION["ConnectionState"])) {
            return false;
        }

        if ($_SESSION["ConnectionState"] == "Connected") {
            return true;
        }
        else
        {
            return false;
        }
    }

    // ...

    function Logoff($ParamUserName)
    {
        $_SESSION["ConnectionState"] = "Disconnected";

        $q = "UPDATE entity_wisp_user SET EP_SESSION_ID = '' WHERE EP_NAME = '" . $ParamUserName ."';";
        WispConnectionManager::Get()->ExecuteQuery($q);

        $q = "UPDATE entity_wisp_user SET EP_IS_CONNECTED = FALSE" . " WHERE EP_NAME = '" . $ParamUserName ."';";
        WispConnectionManager::Get()->ExecuteQuery($q);

        $array_meta = array
        (
            'type' => 'logoff',
            'user' => $ParamUserName
        );

        WispConnectionManager::SendToReactor(json_encode($array_meta));

        return json_encode($array_meta);
    }

    // ...
    static public function GetUserID(string $ParamUsername)
    {
        $q = 'SELECT ID FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';

        return WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue("ID");
    }

    function user_onLoadFromDb()
    {
        echo "EVENT : ON LOAD FROM DATABASE.";
    }

    public static function GetConnectedUsers()
    {
        $q = 'SELECT ID, EP_FIRST_NAME, EP_FAMILY_NAME FROM entity_wisp_user WHERE EP_IS_CONNECTED = TRUE';

        $result = WispConnectionManager::Get()->OpenQuery($q);
        $recordCount = $result->GetRecordCount();

        // Check record count
        if ($recordCount == 0) {
            // Respond
            $array_meta = array
            (
                'Type' => 'USERS',
                'Response' => 'NO_USER',
                'Message' => 'No user is connected'
            );

            echo json_encode($array_meta);

            exit();
        }

        $records = $result->GetAllRecords();

        $users = array();
        for ($i = 0; $i < $recordCount; $i++) {
            $users[$records[$i]['ID']] = $records[$i]['EP_FIRST_NAME'] . " " . $records[$i]['EP_FAMILY_NAME'];
        }

        // Respond
        $array_meta = array
        (
            'type' => 'USERS',
            'response' => $users
        );

        return json_encode($array_meta);
    }

    public function SubscribeUserToGrid($ParamEntityName)
    {
        // Check if already subscribed (maybe user quit without logoff)

        // Remove if already subscribed

        // Subscribe
    }

    // Subscribe the user to the server so that they get notified about changes to a certain entity/instances

    private function ScaffoldUserSubscriptionTable()
    {
        // User subscription table
        $e = new WispEntity('user_subsription', 'User subscription', 'coffee-cup-on-a-plate-black-silhouettes');
        $e->AddProperty(new WispEntityPropertyInteger('USER_ID', 'User', 1));
        $e->AddProperty(new WispEntityPropertyText('NAME', 'Subscription Name', 1));
        $e->AddProperty(new WispEntityPropertyText('ENTITY', 'Entity Name', 1));
        $e->AddProperty(new WispEntityPropertyInteger('INSTANCE_ID', 'Instance ID', 1));

        $e->SetImportantProperties('USER_ID', 'NAME', 'ENTITY');
        WispEntityManager::Get()->RegisterEntity($e);
    }
}

?>