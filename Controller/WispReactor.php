<?php

// Original script from : https://github.com/paulhodel/php-sockets-multiple-connections-non-blocking/blob/master/socket.php

require_once("WispSocketClientData.php");

$directory = dirname(__FILE__ , 2);
require_once($directory . '/Libraries/WispJsonMessages.php');
require_once($directory . '/Libraries/WispStringTools.php');
require_once($directory . '/Controller/WispQueryResult.php');

ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 1);

set_time_limit(0); // Don't stop this script on timeout
ob_implicit_flush(); // Don't wait until the scripts ends to output to console.
echo "\n"; // Space between command and our program output.

class WispReactor
{

    // Set the ip and port we will listen on
    private static string $address = '127.0.0.1';
    private static int $port = 6901;

    private static int $clientCount = 0;
    private static int $seconds = 0;
    private static array $clients = [];
    private static $mainSocket;
    private static $mainBind;
    private static $mainListen;

    private static function processSocketRead(string $ParamReceivedString, WispSocketClientData $ParamClientData) {

        // Check if $ParamReceivedString is json
        $isJson = WispStringTools::IsJson($ParamReceivedString);

        if ($isJson)
        {
            // Process as Json
            $json = json_decode($ParamReceivedString);

            if ($json->type != null)
            {
                if ($json->type == "authentification")
                {
                    if ($json->user != null && $json->sessionID != null)
                    {
                        self::ConLog("Trying to authenticate user : " . $json->user);

                        // Check if user is logged in
                        if (WispReactor::CheckSpecificUserLogin($json->user, $json->sessionID))
                        {
                            $ParamClientData->userName = $json->user;
                            self::ConLog($json->user . " authenticated successfully.");
                            $array_meta = array
                                (
                                'type' => 'react',
                                'action' => 'login',
                                'user' => $json->user
                            );
                            self::WriteToAll(json_encode($array_meta));
                        }
                    }
                    else
                    {
                        self::ConLog("Authentification syntax error.");
                    }
                }
                else if ($json->type == "react")
                {

                }
                else
                {
                    self::ConLog("Message type unknown.");
                }
            }
        }
        else
        {
            // Process as Text ?
            self::ConLog("Received message is a non json string.");
        }

    }

    private static function ConLog(string $ParamString, bool $ParamRemoveLinebreakFromEnd = true) {

        if ($ParamRemoveLinebreakFromEnd && (substr($ParamString, -2) === PHP_EOL))
        {
            $ParamString = substr($ParamString, 0, -2);
        }

        echo "[" . date("d,M,Y h:i:s A") . "] " . $ParamString . PHP_EOL;
    }

    private static function CheckSpecificUserLogin(string $ParamUserName, string $ParamSessionID)
    {
        // Connect to the database
        try {
            $ini = dirname(__FILE__ , 2) . '/Model/Connection.ini';
            $iniArray = parse_ini_file($ini, true);

            $sectionName = "database";

            $pdoConnection = new PDO('mysql:host=' . $iniArray[$sectionName]['host'] . ';dbname=' . $iniArray[$sectionName]['db'], $iniArray[$sectionName]['user'], $iniArray[$sectionName]['pass']);
        }
        catch (PDOException $e) {
            self::ConLog('PDO Error : ' . $e->getMessage());
            return false;
        }

        $q = "SELECT EP_SESSION_ID FROM entity_wisp_user WHERE EP_NAME = '" . $ParamUserName . "';";

        $statement = $pdoConnection->prepare($q);
        $statement->execute();

        $tmp = null;
        if ($statement->rowCount() > 0)
            $tmp = true;
        else
            $tmp = false;

        $result = new WispQueryResult($statement, $tmp);

        if ($result->GetRecordCount() > 0)
        {
            if ($result->GetColumnValue("EP_SESSION_ID") == $ParamSessionID)
            {
                return true;
            }
            else
            {
                self::ConLog("incorrect Session ID.");
                return false;
            }
        }
        else
        {
            self::ConLog("User not found : " . $ParamUserName);
            return false;
        }
    }

    private static function WriteToAll(string $ParamMessage)
    {
        foreach (self::$clients as $k => $v) {
            socket_write($v->socket, $ParamMessage, strlen($ParamMessage));
        }
    }

    public static function HostServer()
    {
        // Create a TCP Stream socket
        self::$mainSocket = socket_create(AF_INET, SOCK_STREAM, 0);

        // Bind the socket to an address/port
        self::$mainBind = socket_bind(self::$mainSocket, self::$address, self::$port);

        // Start listening for connections
        self::$mainListen = socket_listen(self::$mainSocket);

        // Non block socket type
        socket_set_nonblock(self::$mainSocket);

        if (self::$mainSocket)
        {
            self::ConLog("Server hosted on " . self::$address . " and port " . self::$port);
        }
        else
        {
            self::ConLog("Unable to host server on " . self::$address . " and port " . self::$port);
            return;
        }

        while (true) {
            // Accept new connections
            if ($newsock = socket_accept(self::$mainSocket)) {
                if (is_resource($newsock)) {
                    // Write something back to the user
                    $welcomeMessage = "{ \"type\": \"reactor-welcome\", \"message\": \"Connexion to server to succesful.\" }";
                    socket_write($newsock, $welcomeMessage, strlen($welcomeMessage));
                    // Non block for the new connection
                    socket_set_nonblock($newsock);
                    // Append the new connection to the clients array
                    $clientData = new WispSocketClientData();
                    $clientData->socket = $newsock;
                    self::$clients[] = $clientData;
                    self::$clientCount++;
                    self::ConLog("New client connected : " . "Client Count : " . self::$clientCount);
                }
            }

            // Polling for new messages
            if (self::$clientCount) {
                foreach (self::$clients as $k => $v) {
                    // Check for new messages
                    $string = '';
                    if (isset($v->socket))
                    {
                        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                        //echo "ERROR : " . $errno . " | MESSAGE : " . $errstr;
                        });

                        ($char = socket_read($v->socket, 1024));
                        if ($char != false) {
                            $string .= $char;
                            if ($string) {
                                self::ConLog("Client N° " . $k . " Sent : " . $string);
                                self::processSocketRead($string, $v);
                            }
                        }

                        restore_error_handler();
                    }

                    // New string for a connection
                    if ($string) {
                    //ConLog("Client N° " . $k . " Sent : " . $string);
                    }
                    else {
                        if (self::$seconds > 30) {

                            set_error_handler(function ($errno, $errstr, $errfile, $errline) {
                            //echo "ERROR : " . $errno . " | MESSAGE : " . $errstr;
                            });

                            $pingMessage = "{ \"type\": \"reactor-ping\", \"message\": \"ping\" }";
                            $socketWriteResult = socket_write($v->socket, $pingMessage, strlen($pingMessage));

                            restore_error_handler();

                            if (false === $socketWriteResult) {
                                // Close non-responsive connection
                                socket_close(self::$clients[$k]->socket);
                                // Remove from active connections array
                                unset(self::$clients[$k]);
                                self::$clientCount--;
                                // Log it
                                self::ConLog("Unresponsive client disconnected.");
                                self::ConLog("Client Count : " . self::$clientCount);
                            }
                            // Reset counter
                            self::$seconds = 0;
                        }
                    }
                }
            }

            // Loop management and tracking
            sleep(1);
            self::$seconds++;
        }

        // Close the master sockets
        //socket_close(self::$mainSocket);
    //</editor-fold>
    }

//</editor-fold>

}

WispReactor::HostServer();

?>