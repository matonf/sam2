<html>
<head>
<meta charset="utf-8">
<title>SAM pilote ma maison</title>
</head>
<body bgcolor="#f8f8f6">
<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");
//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();
echo "<b>Mode interactif :</b><br>";
//parcours des items connus
foreach($conf_mamaison as $var => $val)
{
	//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
	if (! item_valide($var)) continue;
	//sortie si inexistant
	if (! isset($conf_mamaison[$var])) break;
	else $item_cur = $conf_mamaison[$var];
	//action ouvrir/fermer OU allumer/éteindre selon le type de l'item
	echo item_desc($item_cur) . " <a href=\"?etat=on&item=" . $var . "\">" . "on" . "</a> <a href=\"?etat=off&item=" . $var . "\">" . "off" . "</a>";
	echo "<br>";
} 

echo "<br>";
//ACTION demandée par l'utilisateur
if ($_GET)
{
	//récupère l'élément concerné par l'action
	$items = item_expl(item_items($conf_mamaison[$_GET["item"]]), " ");
	//activation des objets en mode manuel : "on" pour les ouvrir et "off" pour les fermer
	for ($i=0; $i<count($items); $i++) activer($items[$i], $_GET['etat']);
}
?>
<!-- configurer -->
<a href="configurer.php">Configurer</a>
  </body>
</html>
