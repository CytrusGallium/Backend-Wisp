<?php

    // require_once (dirname(__FILE__, 2). '/Controller/WispConnectionManager.php');
    // require_once (dirname(__FILE__, 2). '/Controller/WispEntityManager.php');
    // require_once (dirname(__FILE__, 2). '/Controller/WispEntity.php');
    // require_once (dirname(__FILE__, 2). '/Controller/WispEntityInstance.php');
    // require_once (dirname(__FILE__, 2). '/Libraries/WispJsonMessages.php');

    Class WispAccesManager
    {
        protected static $singleton;
        
        // Constructor
        protected function __construct ()
        {
            
        }

        // Get Singleton
        public static function Get ()
        {
            if (empty(WispAccesManager::$singleton))
            {
                WispAccesManager::$singleton = new WispAccesManager ();
                WispAccesManager::$singleton->ScaffoldUserTable();
            }

            return WispAccesManager::$singleton;
        }

        // ...
        function Login(string $ParamUsername, string $ParamPassword)
        {
            // Brute Force Check
            session_start();

            if (!isset($_SESSION["LoginAttempts"]))
            {
                $_SESSION["LoginAttempts"] = 1; 
            }
            else
            {
                 $_SESSION["LoginAttempts"] += 1;
            }

            // Find the user
            $q = 'SELECT ID, EP_SALT_SALT, EP_HASH_PASS FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';

            // echo $q;
            
            $result = WispConnectionManager::Get()->OpenQuery($q);

            if ($result->IsRecordAvailable())
            {
                $userID = $result->GetColumnValue('ID');
                $salt = $result->GetColumnValue('EP_SALT_SALT');
                $hash = $result->GetColumnValue('EP_HASH_PASS');

                $loginHash = hash_pbkdf2("sha256", $ParamPassword, $salt, 100000, 64);
                
                if ($loginHash == $hash)
                {
                    // Mark user state as connected
                    $_SESSION["ConnectionState"] = "Connected";
                    $_SESSION["User"] = $ParamUsername;
                    $_SESSION["uid"] = $userID;
                    // echo session_id();

                    // print_r($_SESSION);

                    // Respond
                    $array_meta = array
                    (
                        'Type' => 'Login',
                        'Response' => 'Succes',
                        'Message' => 'Welcome !',
                        'Session_ID' => session_id()
                    );

                    return json_encode($array_meta);
                }
                else
                {
                    // Respond
                    $array_meta = array
                    (
                        'Type' => 'Login',
                        'Response' => 'IncorrectPassword',
                        'Message' => 'Incorrect password.'
                    );

                    return json_encode($array_meta);
                }    
            }
            else
            {
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

        // ...
        function AddNewUser (string $ParamUsername, string $ParamPassword)
        {
            if ($ParamUsername == "" || $ParamPassword == "")
            {
                return false;
            }

            $q = 'SELECT * FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';
            // echo $q;

            if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable())
            {
                // echo "User already exists.";
                return false;
            }

            $e = WispEntityManager::Get()->GetEntityByName('wisp_user');

            $instance = new WispEntityInstance($e);
            $instance->GetPropertyByName('NAME')->SetValue($ParamUsername);
            $instance->GetPropertyByName('SALT')->SetValue(16);
            $instance->GetPropertyByName('PASS')->SetValue($ParamPassword);

            $instance->AddToDb();

            return true;
        }

        // ...
        function AddNewUserAdvanced (string $ParamUsername, string $ParamPassword, string $ParamMail, string $ParamPhone, string $ParamFirstName, string $ParamFamilyName, int $ParamSex)
        {
            if ($ParamUsername == "" || $ParamPassword == "" || $ParamPhone == "" || $ParamMail == "")
            {
                return false;
            }

            // Check username
            $q = 'SELECT ID FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';
            // echo $q;

            if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable())
            {
                // echo "User already exists.";
                return false;
            }

            // Check e-mail
            $q = 'SELECT ID FROM entity_wisp_user WHERE EP_MAIL = "' . $ParamMail . '";';
            // echo $q;

            if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable())
            {
                // echo "User already exists.";
                return false;
            }

            // Check phone number
            $q = 'SELECT ID FROM entity_wisp_user WHERE EP_PHONE_NUMBER = "' . $ParamPhone . '";';
            // echo $q;

            if (WispConnectionManager::Get()->OpenQuery($q)->IsRecordAvailable())
            {
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

            $e->SetImportantProperties('NAME', 'MAIL', 'PASS');
            WispEntityManager::Get()->RegisterEntity($e);

            // Default User
            $this->AddNewUser('admin','0000');

        }

        // ...
        function CheckUserLogin (string $ParamResponseType, string $ParamSessionID = '')
        {
            if ($ParamSessionID != '')
            {
                session_id($ParamSessionID);
            }

            session_start();
            
            // Check session existance
            // $has_session = session_status() == PHP_SESSION_ACTIVE;

            if (!isset($_SESSION["ConnectionState"]))
            {
                // Respond with JSON/HTML/TEXT
                WispJsonMessages::ErrorMessage("LOGIN_REQUIRED", "Please login before using our services.");
                exit();
            }          

            if ($_SESSION["ConnectionState"] == "Connected")
            {
                return true;
            }
        }

        // ...
        function Logoff ()
        {
            session_start();
            $_SESSION["ConnectionState"] = "Disconnected";
        }

        // ...
        function GetUserID(string $ParamUsername)
        {
            $q = 'SELECT ID FROM entity_wisp_user WHERE EP_NAME = "' . $ParamUsername . '";';
            
            return WispConnectionManager::Get()->OpenQuery($q)->GetColumnValue("ID");
        }

        // ...
        function user_onLoadFromDb()
        {
            echo "EVENT : ON LOAD FROM DATABASE.";
        }
    }

?>