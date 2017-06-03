<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();
//récupère les arguments passés au script
$etat=$argv[1];
//argument incohérent (injection ?) : on sort
if (! isset($conf_mamaison[$argv[2]])) exit();
$tab_items=explode(' ', item_items($conf_mamaison[$argv[2]]));
//préparation d'une notification pour le téléphone avec pushbullet
if (NOTIF_PORTABLE)
{
	$titre = "SAM m'informe";
	$fonction_texte = "texte_" . $etat;
	$ordre = $fonction_texte(item_desc($conf_mamaison[$argv[2]]), "fr");
	$texte = "SAM va " . $ordre . " " . strtolower(item_desc($conf_mamaison[$argv[2]]));
	//on lance la commande pushbullet
	system(CHEMIN . 'pushbullet.sh "' . $texte . '" "' . $titre . '"');
	sleep(10);
}

//prépare une pause en fonction du numéro du premier item dans le but de diminuer les collisions avec d'autres process en parrallèle
$passe = false;
//active tous les modules un par un
foreach($tab_items as $item)
{
	//fait dormir le process avant la première émission radio	
	if ($passe == false) sleep((int) $item);
	activer_module_radio($item, $etat);
	$passe = true;
}
?>
