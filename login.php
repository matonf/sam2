<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
Toutes question sur le blog ou par mail, possibilité de m'envoyer des bières via le blog
*/

require_once("constantes.php");
require_once("id.php");
if (! is_null(GOOGLE_public_siteKey)) require('./recaptcha/autoload.php');
$google_errors = null;
$connecter = false;

//filtre des variables postées
foreach ($_REQUEST as $key => $val) 
{
	$val = trim(stripslashes(@htmlentities($val)));
	$_REQUEST[$key] = $val;
}

//l'utilisateur a soumis des informations de connexion
if (! empty($_POST['playerlogin']) && ! empty($_POST['playerpass']))// && isset($_POST['g-recaptcha-response']))
{
	//parcours des utilisateurs autorisés
	for ($i=0; $i<count($utilisateurs); $i++)
	{
		//vérification du couple login && mdp obligatoires
		if ($utilisateurs[$i][0] == $_POST["playerlogin"] && $utilisateurs[$i][1] == $_POST["playerpass"]) 
		{
			//reCaptcha est optionnel, test supplémentaire
			if (! is_null(GOOGLE_public_siteKey)) 
			{
				//vérification du captcha
				$recaptcha = new \ReCaptcha\ReCaptcha(GOOGLE_private_siteKey);
				$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
				if ($resp->isSuccess()) $connecter = true;
				else $google_errors = $resp->getErrorCodes();
			}
			else $connecter = true;
		}
		if ($connecter)
		{
			// on envoie le cookie avec le mode httpOnly (+ sécurisé)
			setcookie(COOKIE_ID, $utilisateurs[$i][0], time()+COOKIE_EXPIRE, null, null, false, true);
			setcookie(COOKIE_MDP, md5($utilisateurs[$i][1]), time()+COOKIE_EXPIRE, null, null, false, true);
			header("Location: index.php");
			exit();
		}
		
	}
}

//pas connecté : préparation de la page HTML avec le formulaire
echo "<html><head>";

//reCaptcha Google : https://github.com/google/ReCAPTCHA
if (! is_null(GOOGLE_public_siteKey)) 
{
	echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
}
?>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>SAM m'identifie</title>
<head>
<body bgcolor="#f9f9f9">
<form method=post action="?">
Utilisateur :<br><input type=text name=playerlogin><br>Mot de passe :<br>
<input type=password name=playerpass>
<?php
if (! is_null(GOOGLE_public_siteKey)) 
{
	echo "<br><br><div class=\"g-recaptcha\" data-sitekey=\"" . GOOGLE_public_siteKey . "\"></div>";
	if (! is_null($google_errors))
	{
		echo "<br>\nUne erreur est survenue avec le catpcha: ";
		foreach ($resp->getErrorCodes() as $code) echo '<tt>' , $code , '</tt> ';
	}
}
?>
<br><br><button type=submit>Entrer</button>
</form>
</body>
</html>
