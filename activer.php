<?php
/*
Par Matthieu ONFRAY 

*/
require_once("fonctions.php");
	
//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();

//NOTIF SMARTPHONE PUSHBULLET
if (! is_null(NOTIF_PORTABLE))
{
	$message = null;
	//$titre = "SAM m'informe";
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
	//envoi
	switch (NOTIF_PORTABLE)
	{
		case 'PUSHBULLET' : system('curl -u ' . PUSHBULLET_API . ': https://api.pushbullet.com/v2/pushes -d type=note -d title="SAM" -d body="' . $message . '"') ; break;
		case 'FREE' :  system('curl -w "%{http_code}" -k -G "https://smsapi.free-mobile.fr/sendmsg" -d user=' . FREE_ID . ' -d pass=' . FREE_PWD . ' -d msg="' . urlencode($message) . '"'); break;
	}
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
