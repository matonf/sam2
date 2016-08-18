<?php
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/

//SECURITE 
//filtre des variables postées
foreach ($_REQUEST as $key => $val) 
{
	$val = trim(stripslashes(@htmlentities($val)));
	$_REQUEST[$key] = $val;
}

//on charge des constantes
require_once("constantes.php");

//si la sécurisation par login a été demandée (et qu'on a pas lancé via un script)
if (SECURISER == true && basename($_SERVER['PHP_SELF']) != "activer.php" && basename($_SERVER['PHP_SELF']) != "cron.php") 
{
	require_once("id.php");
	$autorise = false;
	//a-t-on le droit d'entrer ici ?
	for ($i=0; $i<count($utilisateurs); $i++) 
	{
		//vérification du couple login && mdp
		if (isset($_COOKIE[COOKIE_ID]))
		{ 
			if ($utilisateurs[$i][0] == $_COOKIE[COOKIE_ID] && md5($utilisateurs[$i][1]) == $_COOKIE[COOKIE_MDP])
			{
				$autorise = true;
				break;
			}
		}
	}
	
	if (! $autorise)
	{
		//non
		header("Location: login.php");
		exit();
	}
}

 
ecrire_log("a visité la page ". basename($_SERVER['PHP_SELF']));

//FONCTIONS

//est-on en mode vacances ?
function est_en_mode_vacances()
{
	return file_exists(FIC_VACANCES);
}

//active ou déasctive le mode vacances
function activer_mode_vacances($activer=true)
{
		if ($activer) 
		{
			//on crée un fichier vide
			$f = fopen(CHEMIN . FIC_VACANCES, "w");
			fclose($f);
		}
		else unlink(FIC_VACANCES);
}

//change la couleur du fond de page selon le mode vacances
function afficher_fond_page()
{
	if (est_en_mode_vacances()) echo "<body bgcolor=\"" . FOND_VACANCES . "\">";
	else "<body bgcolor=\"" . FOND_NORMAL . "\">";
}

//log les événements
function ecrire_log($texte)
{
	if (LOG === false) return false;
	//écriture de la conf personnelle
	$pointeur_log = fopen(CHEMIN . HISTO, "a");
	if (isset($_COOKIE["cookie_sam" . VERSION . "_id"])) $utilisateur = $_COOKIE[COOKIE_ID];
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
	//lever	automatique : calcul astronomique
	echo "<option" . marquer_champs("autol", $val_utilisateur)  . ">Au lever du soleil</option>\n";
	//lever réel : capteur crépusculaire
	if (AFFICHER_CAPTEUR) echo "<option" . marquer_champs("autol-capteur", $val_utilisateur)  . ">Au lever du soleil (capteur)</option>\n";
	//coucher automatique : calcul astronomique
	echo "<option" . marquer_champs("autoc", $val_utilisateur)  . ">Au coucher du soleil</option>\n";
	//lever réel : capteur crépusculaire
	if (AFFICHER_CAPTEUR) echo "<option" . marquer_champs("autoc-capteur", $val_utilisateur)  . ">Au coucher du soleil (capteur)</option>\n";
	//liste définie par paramètres : les heures
	for ($i=$min; $i<=$max; $i++) 
	{
		echo "<option" . marquer_champs($i, $val_utilisateur) . ">" .$i . "h00</option>\n";
		//option demi-heures
		if (AFFICHER30) echo "<option" . marquer_champs($i."30", $val_utilisateur) . ">" .$i . "h30</option>\n";
	}
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

//ouvrir ou fermer un objet par onde radio
function activer_module_radio($objet, $etat)
{
	$commande = CHEMIN . 'radioEmission ' . PIN . ' ' . SENDER . ' ' . $objet . ' ' . $etat;
	system($commande);
	ecrire_log("a passé l'objet $objet à $etat");
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

//des libellés dynamiques en fonction des noms de groupes
function texte_on($texte)
{
	if (stripos($texte, "volet") !== false || stripos($texte, "store") !== false) return "ouvrir";
	if (stripos($texte, "lampe") !== false) return "allumer";
	return "on";
}

function texte_off($texte)
{
	if (stripos($texte, "volet") !== false || stripos($texte, "store") !== false) return "fermer";
	if (stripos($texte, "lampe") !== false) return "éteindre";
	return "off";
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
