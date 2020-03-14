<?php

    Class WispMethodData
    {
        private $result;
        private $missingOptionalCount;
        private $rawInput;

        private static $counter;

        public static function IncrementScriptCounter()
        {
            $file = WispStringTools::GetApplicationDirectory()."script_counter.txt";
            $stream = null;

            if (!file_exists($file))
            {
                $stream = fopen($file, 'w');
                fwrite($stream, '1');
                fclose($stream);
                return;
            }

            $stream = fopen($file, 'r');
            $counter = fread($stream, filesize($file)) + 1;
            fclose($stream);

            $stream = fopen($file, 'w');
            fwrite($stream, $counter);
            fclose($stream);
            return;
        }

        public static function GetScriptCounter()
        {
            if (isset(self::$counter))
            {
                return self::$counter;
            }

            $file = WispStringTools::GetApplicationDirectory()."script_counter.txt";
            $stream = null;

            if (!file_exists($file))
            {
                $stream = fopen($file, 'w');
                fwrite($stream, '1');
                fclose($stream);
                return 1;
            }

            $stream = fopen($file, 'r');
            self::$counter = fread($stream, filesize($file));
            fclose($stream);

            return self::$counter;
        }

        public function __construct(array $ParamGetArray, array $ParamPostArray, array $ParamRequired, array $ParamOptional = null)
        {
            $this->result = array();
            $this->missingOptionalCount = 0;

            self::IncrementScriptCounter();
                        
            $fail = false;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // Required
                foreach ($ParamRequired as $key => $value) {
                    if (array_key_exists($value,$ParamPostArray))
                    {
                        $this->result[$value] = $ParamPostArray[$value];
                    }
                    else
                    {
                        $fail = true;
                        break;
                        //WispJsonMessages::ErrorMessage("missing_parameter", "Missing Parameter.");
                    }
                }

                $this->rawInput = $ParamPostArray;

                // Optional
                if ($ParamOptional != null)
                {
                    foreach ($ParamOptional as $key => $value) {
                        if (array_key_exists($value,$ParamPostArray))
                        {
                            $this->result[$value] = $ParamPostArray[$value];
                        }
                        else
                        {
                            $this->missingOptional++;
                        }
                    }
                }

            }
            else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

                // Required
                foreach ($ParamRequired as $key => $value) {
                    if (array_key_exists($value,$ParamGetArray))
                    {
                        $this->result[$value] = $ParamGetArray[$value];
                    }
                    else
                    {
                        $fail = true;
                        break;
                        // WispJsonMessages::ErrorMessage("missing_parameter", "Missing Parameter.");
                    }
                }
                
                $this->rawInput = $ParamGetArray;

                // Optional
                if ($ParamOptional != null)
                {
                    foreach ($ParamOptional as $key => $value) {
                        if (array_key_exists($value,$ParamGetArray))
                        {
                            $this->result[$value] = $ParamGetArray[$value];
                        }
                        else
                        {
                            $this->missingOptional++;
                        }
                    }
                }
            }
            else
            {
                WispJsonMessages::ErrorMessage("no_request_method", "Request method is neither GET nor POST.");
            }

            WispStringTools::LogInput($this->result, $this->rawInput);

            if ($fail)
                WispJsonMessages::ErrorMessage("missing_parameter", "Missing Parameter.");
        }

        public function __get($result) {
                return $this->$result;
        }

        public function __get__missingOptionalCount($missingOptionalCount) {
                return $this->$missingOptionalCount;
        }

        public function __get__rawInput($rawInput) {
                return $this->$rawInput;
        }

    }

?>