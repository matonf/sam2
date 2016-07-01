<html>
<head>
<meta charset="utf-8">
<title>SAM programme ma maison</title>
<script type="text/javascript">
function recompte() 
{
	//Recomptage des lignes...
	var i;
	// modification numéro de la ligne
	for (i=1; i < document.getElementById('programmation').rows.length; i++)
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
</script>
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
//récupère les nouveaux paramètres du formulaire et les stocke dans des fichiers sur disque
if (! empty($_POST))
{
	//si la ville a changé, on le log
	if ($_POST["ville_utilisateur"] != $conf_mamaison["ville_utilisateur"]) ecrire_log("a changé sa ville de référence : " . $_POST["ville_utilisateur"]);
	
	//stockage de la conf dans une variable
	$sto_conf_mamaison = "ville_utilisateur=" . $_POST["ville_utilisateur"] . "\n";
	//reprend la numérotation des items dans le fichier de conf
	$i = 0;
	//parcours des POST trouvés
	foreach($_POST as $var => $val)
	{
		//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
		if (! item_valide($var)) continue;
		//prépare les vaiables
		$varon = $var . "_on";
		$varoff = $var . "_off";
		$varjours = $var . "_jours";
		$varitems = $var . "_items";
		$i++;
		//ajoute la ligne courante à la conf
		$sto_conf_mamaison .= "item" . $i . "=" . $_POST[$var] . "," . $_POST[$varitems] . "," . $_POST[$varon] . "," . $_POST[$varoff] . "," . $_POST[$varjours] . PHP_EOL;
	}

	//écriture de la conf personnelle
	$pointeur_conf = fopen(MA_CONF, "w");
	fwrite($pointeur_conf, $sto_conf_mamaison);
	fclose($pointeur_conf);
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
echo "<form method=post><select name='ville_utilisateur'>\n";
foreach ($villes as $clef => $valeur) echo "<option" . marquer_champs($clef, $conf_mamaison["ville_utilisateur"]) . ">" . $clef . "</option>\n";
echo "</select>";

//LISTE DES ITEMS
//on doit afficher les items connus
echo "<br><br><b>Mes règles</b> <a href=\"#null\" title=\"Ajouter\" onClick=\"javascript:ajoute();\">+ajouter une règle</a>";
echo "<table id=\"programmation\">";
echo "<tr id=\"pres\"><td>Nom de la règle</td><td>Numéros des modules</td><td>Heure d'ouverture</td><td>Heure de fermeture</td><td>Jours d'activation</td><td></td></td></tr>\n";
$i = 0;
//parcours des items connus
foreach($conf_mamaison as $var => $val)
{
	//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
	if (! item_valide($var)) continue;
	$item_cur = $conf_mamaison[$var];
	//nom
	echo "<tr><td><input type=text name=\"" . $var . "\" value=\""  . item_desc($item_cur) . "\"></td>";
	//liste des items
	echo "<td>";
	echo "<input type=text name=\"" . $var . "_items" . "\" value=\""  . item_items($item_cur) . "\">";
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
	echo "</td><td><a href=\"#null\" title=\"Supprimer\" onClick=\"javascript:supprime(" . $i . ");\">[suppr]</a></td>";
	//fin de la ligne
	echo "</tr>\n";
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
