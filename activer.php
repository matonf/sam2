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
//active tous les modules un par un
foreach($tab_items as $item)
{
	activer_module_radio($item, $etat);
}
?>
