<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();

//ACTION demandée par l'utilisateur
if ($_GET)
{
	if (isset($_GET["vacances"]))
	{
		ecrire_log("a passé le mode vacances à " . $_GET["vacances"]);
		activer_mode_vacances($_GET["vacances"]);
		//force le recalcul immédiat de la crontab
		require("cron.php");
	}

	if (isset($conf_mamaison[$_GET["item"]])) activer_item_radio($_GET["item"], $_GET['etat']);

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

<!-- définit la fonction pour le mode vacances -->
<script language="javascript">
function mode_vacances()
{
	var vacances = 0;
	if (document.getElementById("mode_vacances").checked) vacances = 1;
	document.location.href = "?vacances=" + vacances;
}
</script>
</head>
<body>
<?php
//fixe les dates en FR
setlocale (LC_TIME, 'fr_FR.utf8','fra'); 

//récupère les cordonnées géographiques
//récupération des coordonnées de la ville choisie dans la liste
if ($conf_mamaison["ville_utilisateur"] != "Géolocalisée")
{
	$latitude = $villes[$conf_mamaison["ville_utilisateur"]][0];
	$longitude = $villes[$conf_mamaison["ville_utilisateur"]][1];
}
else //géolocalisée
{
	$tab_c = explode(",", $conf_mamaison["coord_utilisateur"]);
	$latitude = $tab_c[0];
	$longitude = $tab_c[1];
}

//affiche quelques infos en home
echo "Bienvenue !<br><br>\n<b>Informations</b><br>\n";
echo "Nous sommes le " . strftime("%A %d %B") . ".<br>\n";
$mois = date("m");
$jour = date("d");

echo "Le soleil se lève à " . lever_solaire($mois, $jour, $latitude, $longitude) . ".<br>\n";
echo "Le soleil se couche à " . coucher_solaire($mois, $jour, $latitude, $longitude) . ".<br><br>\n";

echo "<b>Mode interactif</b><br>";
//parcours des items connus
foreach($conf_mamaison as $var => $val)
{
	//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
	if (! item_valide($var)) continue;
	//sortie si inexistant
	if (! isset($conf_mamaison[$var])) break;
	else $item_cur = $conf_mamaison[$var];
	//action ouvrir/fermer OU allumer/éteindre selon le type de l'item
	echo ucfirst(item_desc($item_cur)) . " <a href=\"?etat=on&item=" . $var . "\" title=\"" . texte_on(item_desc($item_cur), "fr") . "\">" . texte_on(item_desc($item_cur)) . "</a> &nbsp;<a href=\"?etat=off&item=" . $var . "\" title=\"" . texte_off(item_desc($item_cur), "fr") . "\">" . texte_off(item_desc($item_cur)) . "</a><br>\n";
} 
?>

<br><a href="configurer.php">Configurer</a><br><br>

<div class="floating-box">Mode absence&nbsp;&nbsp;</div> 
<div class="floating-box">
	<div class="onoffswitch">
        <input onclick="setTimeout(mode_vacances, 1000)" type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="mode_vacances" <?php if (est_en_mode_vacances()) echo "checked"; ?>>
        <label class="onoffswitch-label" for="mode_vacances">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
  	</div>
</div>
  </body>
</html>
