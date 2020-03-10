<?php

require_once(dirname(__FILE__, 2) . "\WispIncludeEverything.php");

Class WispChatManager
{
    static public function ScaffoldChatTable()
    {
        // Chat messages table
        $e = new WispEntity('wisp_chat_message', 'Chat message', 'coffee-cup-on-a-plate-black-silhouettes');
        $e->AddProperty(new WispEntityPropertyInteger('RECEIVER_ID', 'Receiver ID'));
        $e->AddProperty(new WispEntityPropertyText('MESSAGE', 'Message', 1));

        $e->SetImportantProperties('RECEIVER_ID', 'MESSAGE', 'MESSAGE');
        WispEntityManager::Get()->RegisterEntity($e);
    }

    static public function SendChatMessage(string $ParamReceiver, string $ParamMessage)
    {
        self::ScaffoldChatTable();

        if (/*$id = WispAccesManager::GetUserID($ParamReceiver)*/ true /*Check user existence*/)
        {
            $instance = new WispEntityInstance(WispEntityManager::Get()->GetEntityByName('wisp_chat_message'));
            $instance->GetPropertyByName('RECEIVER_ID')->SetValue($ParamReceiver);
            $instance->GetPropertyByName('MESSAGE')->SetValue($ParamMessage);
            $instance->AddToDb();
        }
        else
        {
            WispJsonMessages::ErrorMessage("CHET_MESSAGE_RECEIVER_NOT_FOUND", "The user you are trying to send a message to, has not been found.");
        }
    }

    public static function GetChat($ParamUser, $ParamStart, $ParamOffset)
    {
        if (isset($_SESSION["uid"])) {
            $uid = $_SESSION["uid"];
        } else {
            WispJsonMessages::ErrorMessage("NO_USER_ID", "User ID Undefined");
        }

        // Check user
        if ($uid == $ParamUser) {

            $array_meta = array
            (
                'type' => 'CHAT',
                'response' => 'NO_MESSAGE',
                'message' => 'User cannot chat with them selves.'
            );

            return $array_meta;
        }

        if ($ParamStart == "last")
        {
            $q = "SELECT * FROM ( SELECT * FROM entity_wisp_chat_message WHERE (UID = " . $uid . " OR " . $ParamUser
                . ") AND (EP_RECEIVER_ID = " . $ParamUser . " OR " . $uid .
                ") ORDER BY ID DESC LIMIT " . $ParamOffset . ") sub ORDER BY ID ASC;";
        }
        else
        {
            $q = "SELECT * FROM entity_wisp_chat_message WHERE (UID = " . $uid . " AND EP_RECEIVER_ID = " . $ParamUser .
                " LIMIT " . $ParamStart . ", " . $ParamOffset . ";";
        }

        $result = WispConnectionManager::Get()->OpenQuery($q);
        $records = $result->GetAllRecords();
        $recordCount = $result->GetRecordCount();

        // Check record count
        if ($recordCount == 0) {
            // Respond
            $array_meta = array
            (
                'type' => 'CHAT',
                'response' => 'NO_MESSAGE',
                'message' => 'This discussion has no messages.'
            );

            return $array_meta;
        }

        $messages = array();

        for ($i = 0; $i < $recordCount; $i++) {

            $messageInfo = array();

            $messageInfo["time"] = $records[$i]['DTC'];
            $messageInfo["sender"] = $records[$i]['UID'];
            $messageInfo["receiver"] = $records[$i]['EP_RECEIVER_ID'];
            $messageInfo["message"] = $records[$i]['EP_MESSAGE'];

            $messages[$records[$i]['ID']] = $messageInfo;
        }

        // Respond
        $array_meta = array
        (
            'type' => 'CHAT',
            'response' => $messages
        );

        return $array_meta;
    }
}

?>