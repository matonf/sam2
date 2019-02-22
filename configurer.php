<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<title>SAM programme ma maison</title>
<link rel="stylesheet" href="sam.css" type="text/css" />
<script type="text/javascript">
function recompte() 
{
	var i;
	// ajoute un lien de suppression avec le nouveau numéro de ligne (à partir de la deuxième)
	for (i=2; i < document.getElementById('programmation').rows.length; i++)
	{
		document.getElementById('programmation').rows[i].cells[5].innerHTML = "<a href=\"#null\" title=\"Supprimer\" onClick=\"javascript:supprime(" + i + ");\">[suppr]</a>";
	}
}

function supprime(numligne) 
{  
	//supprime la ligne demandée
	document.getElementById('programmation').deleteRow(numligne); 
	//lance le recompte des lignes du tableau
	recompte();
}

function ajoute() 
{  
	var ancienTotal, nouvelleLigne, col ;
	ancienTotal = document.getElementById('programmation').rows.length;
	//ajoute une ligne
	nouvelleLigne = document.getElementById('programmation').insertRow(-1);
	col = document.getElementById('programmation').rows[ancienTotal-1].cells.length;
	//recopie des valeurs, incrémente le motif itemX
	for (i=0; i <col; i++)
	{
		nouvelleLigne.insertCell(i);
		nouvelleLigne.cells[i].innerHTML = document.getElementById('programmation').rows[ancienTotal-1].cells[i].innerHTML.replace("item"+(ancienTotal-1),"item"+ancienTotal);
	}
	//lance le recompte des lignes du tableau
	recompte();
}

function afficheBouton()
{
	//si l'utilisateur choisit Géolocalisée, un bouton permet de lancer le géopositionnement
	if (document.getElementById('ville').value == "Géolocalisée") document.getElementById('boutonloc').style.visibility="visible";
	else document.getElementById('boutonloc').style.visibility="hidden";
}

function mode_vacances()
{
	var vacances = 0;
	if (document.getElementById("mode_vacances").checked) vacances = 1;
	document.location.href = "?vacances=" + vacances;
}

</script>
</head>
<body onload="afficheBouton()">


<!-- Add icon library -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

<div class="icon-bar">
  <a href="/"><i class="fa fa-home"></i></a>
  <a class="active" href="configurer.php"><i class="fa fa-clock-o"></i></a>
  <a href="infos.php"><i class="fa fa-info"></i></a>
</div> 
<br>

<script>

function maPosition(position) 
{
	document.getElementById("messageloc").innerHTML = "Vous avez été localisé ! Vous pouvez enregistrer.";
	document.getElementById("latitude").value = position.coords.latitude;
	document.getElementById("longitude").value = position.coords.longitude;
	//affiche la carte Google avec des options
    var placement = {lat: position.coords.latitude, lng: position.coords.longitude};
	var map = new google.maps.Map(document.getElementById('mapGoogle'), {mapTypeControl: false, zoom: 16,center: placement});
	//définit un marqueur personnalisé sur la carte
	var marker = new google.maps.Marker({position: placement, map: map, title: "Vous"});
	//agrandit la zone de la carte
	document.getElementById('mapGoogle').style.height = "300px";
	document.getElementById('mapGoogle').style.width = "100%";
}

// Fonction de callback en cas d’erreur

function errorMsg(error)
{
		msg = {
			1: "Accès à la position non autorisé",
			2: "Position non trouvée",
			3: "Délai expiré"
		}
		document.getElementById("messageloc").innerHTML = msg[error.code];
		document.getElementById("messageloc").className = "ko";
}

function getLocation() 
{
    if (navigator.geolocation) navigator.geolocation.getCurrentPosition(maPosition, errorMsg, {enableHighAccuracy:true, timeout:60*1000});
    else document.getElementById("messageloc").innerHTML = "Ce navigateur ne supporte pas la géolocalisation";
}

</script>
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
	if (isset($_GET["vacances"]))
	{
		ecrire_log("a passé le mode vacances à " . $_GET["vacances"]);
		activer_mode_vacances($_GET["vacances"]);
		//force le recalcul immédiat de la crontab
		require("cron.php");
	}

	//renvoie pour ne pas garder l'url avec les get en mémoire dans le navigateur
	header("Location: " . basename($_SERVER["PHP_SELF"]));
	exit();
}

//récupère les nouveaux paramètres du formulaire et les stocke dans des fichiers sur disque
if ($_POST)
{
	//si la ville a changé, on le log
	if ($_POST["ville_utilisateur"] != $conf_mamaison["ville_utilisateur"]) ecrire_log("a changé sa ville de référence : " . $_POST["ville_utilisateur"]);
	
	//stockage de la conf dans une variable
	$sto_conf_mamaison = "ville_utilisateur = \"" . $_POST["ville_utilisateur"] . "\"\n";
	
	//ajout des coordonnées géographiques si l'utilisateur s'est géolocalisé
	$sto_conf_mamaison .= "coord_utilisateur = \"" . $_POST["latitude"]. "," . $_POST["longitude"]  . "\"\n";
	//reprend la numérotation des items dans le fichier de conf
	$i = 0;
	//parcours des POST trouvés
	foreach($_POST as $var => $val)
	{
		//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
		if (! item_valide($var)) continue;
		//prépare les variables
		$varon = $var . "_on";
		$varoff = $var . "_off";
		$varjours = $var . "_jours";
		$varitems = $var . "_items";
		$i++;
		//ajoute la ligne courante à la conf
		$sto_conf_mamaison .= "item" . $i . " = \"" . $_POST[$var] . "," . $_POST[$varitems] . "," . $_POST[$varon] . "," . $_POST[$varoff] . "," . $_POST[$varjours] . "\"" . PHP_EOL;
	}

	//écriture de la conf personnelle
	$pointeur_conf = @fopen(MA_CONF, "w");
	if ($pointeur_conf)
	{
		@fwrite($pointeur_conf, $sto_conf_mamaison);
		@fclose($pointeur_conf);
	} 
	else echo "ne peut ouvrir en écriture: " . MA_CONF . "<br>";

	//force le recalcul immédiat de la crontab
	require("cron.php");
	//message pour l'utilisateur 
	die("Configuration enregistrée. Vous allez être redirigé vers la page d'accueil.<script type=\"text/javascript\">setTimeout('document.location.href=\"./\"', 3000)</script>");
}
?>
<div class="floating-box">Mode absence&nbsp;&nbsp;</div> 
<div class="floating-box">
	<div class="onoffswitch">
        <input onclick="setTimeout(mode_vacances, 1000)" type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="mode_vacances" <?php if (est_en_mode_vacances()) echo "checked"; ?>>
        <label class="onoffswitch-label" for="mode_vacances">
            <span class="onoffswitch-inner"></span>
            <span class="onoffswitch-switch"></span>
        </label>
  	</div>
</div><br><br>
<?php
//formulaire
echo "\n<form name=mamaison method=post>";

//VILLE
//tri associatif des villes
ksort($villes);
//on doit demander la ville proche de l'utilisateur
echo "<b>Ma localisation</b><br>Choisissez la ville la plus proche :<br>";
echo "<form method=post><select onChange=\"afficheBouton()\" id='ville' name='ville_utilisateur'>\n";
foreach ($villes as $clef => $valeur) echo "<option" . marquer_champs($clef, $conf_mamaison["ville_utilisateur"]) . ">" . $clef . "</option>\n";
echo "</select> <button id=\"boutonloc\" type=button onclick=\"getLocation()\">Géolocalise-moi</button><p id=\"messageloc\"></p>\n";
$tab_c = explode(",", $conf_mamaison["coord_utilisateur"]);
$latitude = $tab_c[0];
$longitude = $tab_c[1];
echo "<input type=\"hidden\" name=\"latitude\" id=\"latitude\" value=\"$latitude\"> <input type=\"hidden\" name=\"longitude\" id=\"longitude\" value=\"$longitude\"> ";
?>

<!-- emplacement de chargement de la carte Google -->
<div id="mapGoogle"></div><br>
<!-- fin de l'emplacement -->

<b>Mes règles</b> <a href="#null" title="Dupliquer" onClick="javascript:ajoute();">[dupliquer une règle]</a>
<table id="programmation">
<tr id="pres"><td>Nom</td><td>Modules</td><td>Ouverture</td><td>Fermeture</td><td>Activation</td><td></td></td></tr>
<?php
//LISTE DES ITEMS
//on doit afficher les items connus
$i = 0;
//parcours des items connus
foreach($conf_mamaison as $var => $val)
{
	//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
	if (! item_valide($var)) continue;
	$item_cur = $conf_mamaison[$var];
	//nom
	echo "<tr><td><input type=text size=18 name=\"" . $var . "\" value=\""  . ucfirst(item_desc($item_cur)) . "\"></td>";
	//liste des items
	echo "<td>";
	echo "<input type=text size=10 name=\"" . $var . "_items" . "\" value=\""  . item_items($item_cur) . "\">";
	//allumer
	echo "</td><td>";
	creer_liste_horaire($var."_on", 6, 18, item_on($item_cur));
	//éteindre
	echo "</td><td>";
	creer_liste_horaire($var."_off", 13, 23, item_off($item_cur));
	echo "</td><td>";
	//jours d'activation
	creer_liste_jours($var."_jours", item_jours($item_cur));	
	//suppression
	$i++;
	echo "</td><td>";
	//on ne peut pas supprimer la première ligne de conf
	if ($i>1) echo "<a href=\"#null\" title=\"Supprimer\" onClick=\"javascript:supprime(" . $i . ");\">[suppr]</a>";
	//fin de la ligne
	echo "</td></tr>\n";
} 
echo "</table>\n";

//validation du formulaire
echo "<br><button type=submit>Enregistrer</button>";
//annulation
echo " <button type=button onClick=\"history.go(-1)\">Annuler</button>\n";
echo "</form>";
if (defined('GOOGLE_private_mapKey')) echo "<!-- appel au script de google -->\n<script src=\"https://maps.googleapis.com/maps/api/js?key=" . GOOGLE_private_mapKey . "\"></script>";
?>
</body>
</html>
