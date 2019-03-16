<?php

	class WispStringTools
	{
		static function GenerateRandomString($length) 
		{
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
			    $randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
	}

// ===================================================================================================================================

	class WispDynamicString
	{
		protected $rawString;
		protected $pairArray = array();
		protected $counter = 0;

		function __Construct (string $ParamRawString)
		{
			$this->rawString = $ParamRawString;
		}

		function PrepareArrayFromString()
		{
			$currentPiece = '';
			$onVariable = false;
			$lastChar = '';
			
			for ($i = 0; $i < strlen($this->rawString); $i++)
			{
			    $char = $this->rawString[$i];

			    if ($char == '$')
			    {
			    	if ($lastChar == '$')
			    	{
			    		$onVariable = false;
			    		$this->AddPairToArray('$', false);
			    		//$currentPiece = '';
			    		//$lastChar = '';
			    	}

			    	if ($onVariable)
			    	{
			    		$onVariable = false;
			    		$this->AddPairToArray($currentPiece, true);
			    		$currentPiece = '';
			    	}
			    	else
			    	{
			    		$onVariable = true;
			    		$this->AddPairToArray($currentPiece, false);
			    		$currentPiece = '';
			    	}
			    }
			    else
			    {
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