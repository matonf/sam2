<?php
/*
Par Matthieu ONFRAY

*/
require_once("fonctions.php");

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();

//ACTION demandée par l'utilisateur
if ($_GET)
{
	//vibration simple 1s
	//echo "<script>	navigator.vibrate = navigator.vibrate ||                  navigator.webkitVibrate ||                  navigator.mozVibrate ||                   navigator.msVibrate;navigator.vibrate;                  navigator.vibrate(1000);	</script>";
	if (isset($conf_mamaison[$_GET["item"]])) activer_item_radio($_GET['item'], $_GET['etat']);
	//renvoie pour ne pas garder l'url avec les get en mémoire dans le navigateur
	header("Location: index.php");
	exit();
}
?>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>SAM pilote ma maison</title>
<link rel="stylesheet" href="sam.css" type="text/css" />
</head>
<body>

<!-- Add icon library : vertical bar-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="icon-bar">
  <a class="active" href="/"><i class="fa fa-home"></i></a>
  <a href="configurer.php"><i class="fa fa-clock-o"></i></a>
  <a href="infos.php"><i class="fa fa-info"></i></a>
</div> 
<br>
<?php
//parcours des items connus
foreach($conf_mamaison as $var => $val)
{
	//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
	if (! item_valide($var)) continue;
	//sortie si inexistant
	if (! isset($conf_mamaison[$var])) break;
	else $item_cur = $conf_mamaison[$var];
	//action ouvrir/fermer OU allumer/éteindre selon le type de l'item
	echo ucfirst(item_desc($item_cur));
	//état on/off parcourus
	echo "<ul class=\"listeBouton\">";
	foreach ($tab_actions = array("on", "off") as $action) echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"?etat=" . $action . "&item=" . $var . "\" title=\"" . texte($action, item_desc($item_cur), "fr") . "\"><button class=\"" . $action . "\">" . texte($action, item_desc($item_cur), "fr") . "</button></a>";
	echo "</ul><br>\n";
} 
?>

  </body>
</html>
