<html>
<head>
<meta charset="utf-8">
<title>SAM pilote ma maison</title>
</head>
<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");

if (isset($_POST["vacances"]))
{
	ecrire_log("a demandé à " . $_POST["vacances"]);
	switch ($_POST["vacances"])
	{
		case "passer en mode vacances" : activer_mode_vacances(); break;
		case "sortir du mode vacances" : activer_mode_vacances(false); break;
	}
	//force le recalcul immédiat de la crontab
	require("cron.php");
}

afficher_fond_page();

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
	echo ucfirst(item_desc($item_cur)) . " <a href=\"?etat=on&item=" . $var . "\">" . texte_on(item_desc($item_cur)) . "</a> / <a href=\"?etat=off&item=" . $var . "\">" . texte_off(item_desc($item_cur)) . "</a>";
	echo "<br>";
} 

echo "<br>";
//ACTION demandée par l'utilisateur
if ($_GET)
{
	//récupère l'élément concerné par l'action
	$items = item_expl(item_items($conf_mamaison[$_GET["item"]]), " ");
	//activation des objets en mode manuel : "on" pour les ouvrir et "off" pour les fermer
	for ($i=0; $i<count($items); $i++) activer_module_radio($items[$i], $_GET['etat']);
}

echo "<a href=\"configurer.php\">Configurer</a><br><br>";
if (est_en_mode_vacances()) echo "<form method=post><input type=submit name=vacances value=\"sortir du mode vacances\"></form>";
else echo "<form method=post><input type=submit name=vacances value=\"passer en mode vacances\"></form>";
?>
  </body>
</html>
