<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
Toutes question sur le blog ou par mail, possibilité de m'envoyer des bières via le blog
*/
session_start();
require_once("constantes.php");
require_once("id.php");

//filtre des variables postées
foreach ($_REQUEST as $key => $val) 
{
	$val = trim(stripslashes(@htmlentities($val)));
	$_REQUEST[$key] = $val;
}
 

//l'utilisateur a soumis des informations de connexion
if (! empty($_POST['playerlogin']) && ! empty($_POST['playerpass']))
{
	//parcours des utilisateurs autorisés
	for ($i=0; $i<count($utilisateurs); $i++)
	{
		//vérification du couple login && mdp
		if ($utilisateurs[$i][0] == $_POST["playerlogin"] && $utilisateurs[$i][1] == $_POST["playerpass"]) 
		{
			//vérification du captcha
			if ($_SESSION["captcha"] == $_POST["bot"])
			{				
				// on envoie le cookie avec le mode httpOnly
				setcookie("cookie_sam" . VERSION . "_id", $utilisateurs[$i][0], time()+COOKIE_EXPIRE, null, null, false, true);
				setcookie("cookie_sam" . VERSION . "_mdp", md5($utilisateurs[$i][1]), time()+COOKIE_EXPIRE, null, null, false, true);
				header("Location: index.php");
				exit();
			}
		}
	}
}

//pas connecté : formulaire
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>SAM m'identifie</title>
<head>
<body bgcolor="#f9f9f9">

<form method=post>
Utilisateur<br><input type=text name=playerlogin><br>Mot de passe<br>
<input type=password name=playerpass>
<br>Vérification<br> 
<?php
//tirage aléatoire de deux nombres
$n1 = rand(1,9);
$n2 = rand(1,9);
//sauve la somme dans le contexte mémoire
$_SESSION['captcha'] = $n1+$n2;
echo "$n1 + $n2 = ";
?>
 <input type=text name=bot size=2>
<br><br><button type=submit>Entrer</button>
</form>
</body>
</html>
