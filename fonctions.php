<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/

//SECURITE 
//contrôle des accès : selon le fichier constantes.php
//on charge des constantes
require_once("constantes.php");

//si pas de cookie déposé, c'est qu'on est nouveau ici !
if (! isset($_COOKIE["cookie_sam_id"]) && SECURISER == true) 
{
	header("Location: login.php");
	exit();
}

//filtre des variables postées
foreach ($_REQUEST as $key => $val) 
{
	$val = trim(stripslashes(@htmlentities($val)));
	$_REQUEST[$key] = $val;
}
 
ecrire_log("a visité la page ". basename($_SERVER['PHP_SELF']));


//FONCTIONS

//log les événements
function ecrire_log($texte)
{
	if (LOG === false) return false;
	//écriture de la conf personnelle
	$pointeur_log = fopen(HISTO, "a");
	if (isset($_COOKIE["cookie_sam_id"])) $utilisateur = $_COOKIE["cookie_sam_id"];
	else $utilisateur = "l'utilisateur";
	fwrite($pointeur_log, "Le " . date("d/m/Y à H:i") . ", " . $utilisateur . " " . $texte . PHP_EOL);
	fclose($pointeur_log);
}

//sélectionner un élement dans une liste déroulante
function marquer_champs($val, $choix_utilisateur)
{
	$msg = " value=\"" . $val . "\"";
	if ($choix_utilisateur == $val) $msg .= " selected";
	return $msg;
}

//crée une liste déroulante avec valeur numérique de min à max + choix automatique
function creer_liste_horaire($nom, $min, $max, $val_utilisateur)
{
	echo "<select name='$nom'>\n";
	//lever	
	echo "<option" . marquer_champs("autol", $val_utilisateur)  . ">Au lever du soleil</option>\n";
	//coucher
	echo "<option" . marquer_champs("autoc", $val_utilisateur)  . ">Au coucher du soleil</option>\n";
	//liste définie par paramètres
	for ($i=$min; $i<=$max; $i++) echo "<option" . marquer_champs($i, $val_utilisateur) . ">" .$i . "h00</option>\n";
	//option minuit
	echo "<option" . marquer_champs("24", $val_utilisateur)  . ">Minuit</option>\n";
	//possiblité de ne pas utiliser l'élément ou le groupe d'éléments
	echo "<option" . marquer_champs("25", $val_utilisateur)  . ">Ne rien faire</option>\n";
	echo "</select>\n";
}

//crée une liste déroulante avec choix des jours d'activation
function creer_liste_jours($nom, $val_utilisateur)
{
	echo "<select name='$nom'>\n";
	echo "<option" . marquer_champs("semaine_we", $val_utilisateur)  . ">Semaine et Week-end</option>\n";
	echo "<option" . marquer_champs("semaine", $val_utilisateur)  . ">Semaine</option>\n";
	echo "<option" . marquer_champs("week_end", $val_utilisateur)  . ">Week-end</option>\n";
	echo "</select>\n";
}

//ouvrir et fermer un objet
function activer($objet, $etat)
{
	$commande = './radioEmission ' . PIN . ' ' . SENDER . ' ' . $objet . ' ' . $etat;
	system($commande);
	ecrire_log("a passé à $etat l'objet $objet");
}

//fonctions de manipulations des champs de la conf


function item_valide($nom)
{
	//un nom valide d'item est itemX (tout court)
	if ((strpos($nom, "_") > 0) || (substr($nom, 0, 4) != "item")) return false;
	return true;
}

function item_expl($texte, $delimiteur=DELIMITEUR)
{
	
	return explode($delimiteur,$texte);
}

function item_desc($texte)
{
	return item_expl($texte)[0];
}

function item_items($texte)
{
	return item_expl($texte)[1];
}

function item_on($texte)
{
	return item_expl($texte)[2];
}

function item_off($texte)
{
	return item_expl($texte)[3];
}

function item_jours($texte)
{
	return item_expl($texte)[4];
}

//charge le fichier utilisateur et retourne un tableau associatif
function charger_conf()
{
	//charge le fichier
	$conf_fic = @parse_ini_file(MA_CONF);
	//en cas d'absence, charge des valeurs par défaut
	if ($conf_fic === FALSE) $conf_fic = [ "ville_utilisateur" => "Rouen" ];
	return $conf_fic;
}
?>
