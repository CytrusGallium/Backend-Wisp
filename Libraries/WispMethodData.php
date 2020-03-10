<?php

    Class WispMethodData
    {
        private $result;
        private $missingOptionalCount;

        public function __construct(array $ParamGetArray, array $ParamPostArray, array $ParamRequired, array $ParamOptional = null)
        {
            $this->result = array();
            $this->missingOptionalCount = 0;

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                // Required
                foreach ($ParamRequired as $key => $value) {
                    if (array_key_exists($value,$ParamPostArray))
                    {
                        $this->result[$value] = $ParamPostArray[$value];
                    }
                    else
                    {
                        WispJsonMessages::ErrorMessage("missing_parameter", "Missing Parameter.");
                    }
                }

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
                        WispJsonMessages::ErrorMessage("missing_parameter", "Missing Parameter.");
                    }
                }

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
        }

        public function __get($result) {
            if (property_exists($this, $result)) {
                return $this->$result;
            }
        }


    }

?>