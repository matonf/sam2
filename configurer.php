<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width"/>
<title>SAM programme ma maison</title>
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
	recompte();
}

function afficheBouton()
{
	if (document.getElementById('ville').value == "Géolocalisée") document.getElementById('boutonloc').style.visibility="visible";
	else document.getElementById('boutonloc').style.visibility="hidden";
}

</script>
</head>
<body onload="afficheBouton()">

<?php 
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();
//récupère les nouveaux paramètres du formulaire et les stocke dans des fichiers sur disque
if (! empty($_POST))
{
	//si la ville a changé, on le log
	if ($_POST["ville_utilisateur"] != $conf_mamaison["ville_utilisateur"]) ecrire_log("a changé sa ville de référence : " . $_POST["ville_utilisateur"]);
	
	//stockage de la conf dans une variable
	$sto_conf_mamaison = "ville_utilisateur = \"" . $_POST["ville_utilisateur"] . "\"\n";
	
	//ajout des coordonnées géographiques si l'utilisateur s'est géolocalisé
	//if ($_POST["ville_utilisateur"] == "Géolocalisée") 
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
		fwrite($pointeur_conf, $sto_conf_mamaison);
		fclose($pointeur_conf);
	} else echo "ne peut ouvrir en écriture: " . MA_CONF . "<br>";

	//force le recalcul immédiat de la crontab
	require("cron.php");
	//message pour l'utilisateur 
	die("Configuration enregistrée. Vous allez être redirigé vers la page d'accueil.<script type=\"text/javascript\">setTimeout('document.location.href=\"./\"', 3000)</script>");
}

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
<div id="mapholder"></div>
<script>
var x = document.getElementById("messageloc");

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else { 
        x.innerHTML = "La géolocation ne fonctionne pas avec votre navigateur.";
    }
}

function showPosition(position) {
    x.innerHTML = "Vous avez été localisé ! Vous pouvez enregistrer.";
	document.getElementById("latitude").value = position.coords.latitude;
	document.getElementById("longitude").value = position.coords.longitude;
}
</script>
<?php
//LISTE DES ITEMS
//on doit afficher les items connus
echo "<br><b>Mes règles</b> <a href=\"#null\" title=\"Ajouter\" onClick=\"javascript:ajoute();\">+ajouter une règle</a>";
echo "<table id=\"programmation\">";
echo "<tr id=\"pres\"><td>Nom</td><td>Modules</td><td>Ouverture</td><td>Fermeture</td><td>Activation</td><td></td></td></tr>\n";
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
	creer_liste_horaire($var."_off", 14, 23, item_off($item_cur));
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
echo " <button type=button onClick=\"history.go(-1)\">Annuler</button>";
echo "</form>";
?>
</body>
</html>
