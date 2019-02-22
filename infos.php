<?php
/*
Par Matthieu ONFRAY

*/
require_once("fonctions.php");

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();

?>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width">
<title>SAM pilote ma maison</title>
<link rel="stylesheet" href="sam.css" type="text/css" />
</head>
<body>
	
<!-- Add icon library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="icon-bar">
  <a href="/"><i class="fa fa-home"></i></a>
  <a href="configurer.php"><i class="fa fa-clock-o"></i></a>
  <a class="active" href="infos.php"><i class="fa fa-info"></i></a>
</div> 
<br>
<?php
//echo "<b>Informations</b><br>";

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

//affiche quelques infos 
echo "Nous sommes le " . strftime("%A %e %B.<br>Il est %R") . ".<br>";
//heures solaires
$mois = date("m");
$jour = date("d");
echo "Le soleil se lève à " . lever_solaire($mois, $jour, $latitude, $longitude) . ".<br>\n";
echo "Le soleil se couche à " . coucher_solaire($mois, $jour, $latitude, $longitude) . ".<br>\n";

//météo
$url_meteo = "http://www.prevision-meteo.ch/services/json/lat=" . number_format($latitude, 3) . "lng=" . number_format($longitude, 3);
$json_meteo = json_decode(file_get_contents($url_meteo));
echo "Il fait " . $json_meteo->current_condition->tmp . "°C ";
echo "<img title=\"" . $json_meteo->current_condition->condition . "\" src=" . $json_meteo->current_condition->icon . "><br>\n";

//infos hardware
echo "Vous utilisez la version " . VERSION . " de Sam.<br>\n";
echo "Le matériel est un ";
if (! is_null(Pi_PIN)) echo "Raspberry Pi";
else echo "Arduino";
echo ".<br>\n";
echo "Le numéro d'émetteur de Sam est " . SENDER . ".<br>\n";
if (! is_null(NOTIF_PORTABLE)) echo "La notification est activée via " . NOTIF_PORTABLE . ".";
?>
  </body>
</html>
