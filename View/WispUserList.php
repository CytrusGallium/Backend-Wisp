<?php

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	// =====================================================================
	if( isset($_POST["s"]) )
	{
		$Session_ID = $_POST["s"];
	}
	else if( isset($_GET["s"]) )
	{
		$Session_ID = $_GET["s"];
	}
	else
	{
		$Session_ID = "";
	}

	WispAccesManager::Get()->CheckUserLogin("json", $Session_ID);
	// =====================================================================

	// ----------------------- Syntax Check ------------------------

	if ($_SERVER['REQUEST_METHOD'] === 'POST') 
	{ 
		if(isset($_POST['r']))
		{
			$responseType = $_POST['r'];
		}
	} 
	elseif ($_SERVER['REQUEST_METHOD'] === 'GET') 
	{ 
		if(isset($_GET['r']))
		{
			$responseType = $_GET['r'];
		}
	} 
	else 
	{ 
		http_response_code(405); 
		die();
	}
	
	if (isset($responseType) && !is_null($responseType))
	{
		if ($responseType == "html")
		{
			Respond_Html ();
		}
		else if ($responseType == "react")
		{
			Respond_React ();
		}
		else if ($responseType == "json")
		{
			Respond_Json ();
		}
		else
		{
			echo "Invalid Response Type.";
			exit();
		}
	}
	else
	{
		$responseType = "json";
		Respond_Json ();
	}

	// ------------------------- Syntax Check Done -------------------

	function Respond_Html ()
	{
		$dirPath = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/";

		$loginResponseArray = json_decode(WispAccesManager::Get()->Login($ParamUser, $ParamPass));

		if ($loginResponseArray->Type == "Login")
		{
			if ($loginResponseArray->Response == "UserNotFound")
			{
				echo "<html><body>";
				echo "<form>";
				echo '<input type="text" name="user" autofocus="autofocus" placeholder="Username ..." value="'. $ParamUser .'">';
				echo '<input type="text" name="pass" placeholder="Password ..." value="'. $ParamPass .'">';
				echo '<input type="hidden" name="r" value="html">';
				echo '<input type="submit" value="Login" formaction="' . $dirPath . 'WispLogin.php">';
				echo "</form>";
				echo "<p>" . $loginResponseArray->Message . "</p>";
				echo "</body></html>";

			}
			else if ($loginResponseArray->Response == "IncorrectPassword")
			{
				echo "<html><body>";
				echo "<form>";
				echo '<input type="text" name="user" placeholder="Username ..." value="'. $ParamUser .'">';
				echo '<input type="text" name="pass" autofocus="autofocus" placeholder="Password ..." value="'. $ParamPass .'">';
				echo '<input type="hidden" name="r" value="html">';
				echo '<input type="submit" value="Login" formaction="' . $dirPath . 'WispLogin.php">';
				echo "</form>";
				echo "<p>" . $loginResponseArray->Message . "</p>";
				echo "</body></html>";
			}
			else if ($loginResponseArray->Response == "Succes")
			{
				echo "<html><body>";
				echo "<p>" . $loginResponseArray->Message . "</p>";
				echo "<form>";
				echo '<input type="hidden" name="r" value="html">';
				echo '<input type="submit" value="Go to Main Menu" formaction="' . $dirPath . 'WispMainMenu.php">';
				echo "</form>";
				echo "</body></html>";

			}
		}
		else
		{
			echo "Unknown Error.";
			exit();
		}

		exit();
	}

	function Respond_React ()
	{
		echo "Feature not implemented.";
		exit();
	}

	function Respond_Json ()
	{
		echo json_encode(WispUserManager::Get()->GetUserArray());
		exit();
	}

	

?>