<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();

//NOTIF SMARTPHONE PUSHBULLET
if (NOTIF_PORTABLE)
{
	$message = null;
	$titre = "SAM m'informe";
	for ($i=1; $i<$argc; $i++)
	{
		//sépare le nom de l'item de l'état demandé
		$details = explode('/', $argv[$i]);
		$item = $details[0];
		$etat = $details[1];
		$fonction_texte = "texte_" . $etat;
		if (isset($message)) $message .= "\n";
		$message .= "SAM va " . $fonction_texte(item_desc($conf_mamaison[$item]), "fr") . " " . strtolower(item_desc($conf_mamaison[$item])) . ".";
	}
	//on lance la commande pushbullet sur le système
	system(CHEMIN . 'pushbullet.sh "' . $message . '" "' . $titre . '"');
	//attente de quelques secondes pour laisser le temps de lire la notification
	sleep(10);
}


//BEAM RADIO !!!
//récupère les arguments passés au script
for ($i=1; $i<$argc; $i++)
{
	//sépare le nom de l'item de l'état demandé
	$details = explode('/', $argv[$i]);
	$item = $details[0];
	$etat = $details[1];
	activer_item_radio($item, $etat);
}


?>
