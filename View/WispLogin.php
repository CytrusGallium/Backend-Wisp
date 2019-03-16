<?php

	// require_once (dirname(__FILE__, 2). '/Controller/WispAccesManager.php');

	require_once (dirname(__FILE__, 2) . "\WispIncludeEverything.php");

	// ----------------------- Syntax Check ------------------------

	if ($_SERVER['REQUEST_METHOD'] === 'POST') 
	{ 
		if(isset($_POST['r']))
		{
			$responseType = $_POST['r'];
		}

		if(isset($_POST['user']) && isset($_POST['pass']))
		{
			$user = $_POST["user"];
			$pass = $_POST["pass"];
		}
		else
		{
			if ($responseType == "json")
			{
				echo "Syntax error.";
				exit();
			}

			$user = "";
			$pass = "";

		}
	} 
	elseif ($_SERVER['REQUEST_METHOD'] === 'GET') 
	{ 
		if(isset($_GET['r']))
		{
			$responseType = $_GET['r'];
		}

		if (isset($_GET['user']) && isset($_GET['pass']))
		{
			$user = $_GET["user"];
			$pass = $_GET["pass"];
		} 
		else
		{
			if ($responseType == "json")
			{
				echo "Syntax error.";
				exit();
			}

			$user = "";
			$pass = "";
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
			Respond_Html ($user, $pass);
		}
		else if ($responseType == "bootstrap")
		{
			Respond_Bootstrap ($user, $pass);
		}
		else if ($responseType == "json")
		{
			Respond_Json ($user, $pass);
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
		Respond_Json ($user, $pass);
	}

	// ------------------------- Syntax Check Done -------------------

	function Respond_Html ($ParamUser, $ParamPass)
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

	function Respond_Bootstrap ($ParamUser, $ParamPass)
	{
		echo "Feature not implemented.";
		exit();
	}

	function Respond_Json ($ParamUser, $ParamPass)
	{
		echo WispAccesManager::Get()->Login($ParamUser, $ParamPass);
		exit();
	}

	

?>