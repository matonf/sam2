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
//prépare une pause en fonction du numéro du premier item dans le but de diminuer les collissions avec d'autres process en parralèle
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
