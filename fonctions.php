<?php
/*
Par Matthieu ONFRAY 
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
require_once("id.php");

//si la sécurisation par login a été demandée (et qu'on a pas lancé via un script)
if (SECURISER == true && in_array(basename($_SERVER['PHP_SELF']), $NOLOGIN) == false)
{
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
	return file_exists(CHEMIN . FIC_VACANCES);
}

//active ou désactive le mode vacances
function activer_mode_vacances($activer=true)
{
	if ($activer) 
	{
		//on crée un fichier vide
		$f = @fopen(CHEMIN . FIC_VACANCES, "w");
		@fclose($f);
	}
	else @unlink(CHEMIN . FIC_VACANCES);
}

//log les événements
function ecrire_log($texte)
{
	if (LOG === false) return false;
	//écriture de la conf personnelle
	$pointeur_log = @fopen(CHEMIN . FIC_HISTO, "a");
	if ($pointeur_log)
	{
		if (isset($_COOKIE["cookie_sam" . VERSION . "_id"])) $utilisateur = $_COOKIE[COOKIE_ID];
		else $utilisateur = "le système";
		@fwrite($pointeur_log, "Le " . date("d/m/Y à H:i:s") . ", " . $utilisateur . " " . $texte . PHP_EOL);
		@fclose($pointeur_log);
	} else echo "ne peut ouvrir en lecture: " . CHEMIN . FIC_HISTO . "<br>";
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
	if (AFFICHER_CAPTEUR) echo "<option" . marquer_champs(AUBE, $val_utilisateur)  . ">A l'aube (capteur)</option>\n";
	//coucher automatique : calcul astronomique
	echo "<option" . marquer_champs("autoc", $val_utilisateur)  . ">Au coucher du soleil</option>\n";
	//coucher réel : capteur crépusculaire
	if (AFFICHER_CAPTEUR) echo "<option" . marquer_champs(CREPUSCULE, $val_utilisateur)  . ">Au crépuscule (capteur)</option>\n";
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

//prépare l'activation d'un ensemble d'éléments radio
function activer_item_radio($item, $etat)
{
	global $conf_mamaison;
	//dépose un fichier marqueur
	if ($etat == "on") 
	{
		//on crée un fichier vide
		$f = @fopen(CHEMIN_SWITCH . $item, "w");
		@fclose($f);
	}
	else @unlink(CHEMIN_SWITCH . $item);
	//récupère l'élément concerné par l'action
	$items = item_expl(item_items($conf_mamaison[$item]), " ");
	//activation des objets en mode manuel : "on" pour les ouvrir et "off" pour les fermer
	for ($i=0; $i<count($items); $i++) activer_module_radio($items[$i], $etat);
}

//ouvrir ou fermer un objet par onde radio
function activer_module_radio($objet, $etat)
{
	//vérifications : les états "on" ou "off"
	if ($etat != "on" && $etat != "off") 
	{
		ecrire_log("a tenté de passer l'objet $objet à un état incorrect : $etat");
		return ;
	}	

	//prépare la commande radio arduino
	if (is_null(Pi_PIN)) 
	{
		//sur 2 caractères, l'état de l'objet
		if ($etat == "on") $etat_dec = "01";
		else $etat_dec = "00";
		//sur 4 caractères le numéro d'objet
		$objet_dec = sprintf("%'.04d", decbin($objet));
		//envoi en décimal (émetteur + état + objet) suivi d'un code fin de ligne
		$commande = bindec(decbin(SENDER) . $etat_dec . $objet_dec) . "\n";
		echo $commande;
		//DTR=off : éviter le reset à chaque envoi
		exec("mode " . Arduino_COM . ": BAUD=9600 PARITY=N data=8 stop=1 xon=off dtr=off");
		$fp = fopen(Arduino_COM, "w");
		//envoi du signal
		fwrite($fp, $commande);
		sleep(1);
		$content = fgets($fp, 2096);
		//gestion des retours
		echo "Return = $content\n";
		fclose($fp);
	}
	else 
	{		
		//allumage de la LED
		if (! is_null(Pi_LED))
		{
			//passe le pin en sortie 
			exec('gpio -g mode ' . Pi_LED . ' out');
			//allume la LED
			exec('gpio -g write ' . Pi_LED . ' 1');
		}
		
		$commande = CHEMIN . 'radioEmission ' . Pi_PIN . ' ' . SENDER . ' ' . $objet . ' ' . $etat;
		//rejoue la commande pour augmenter les chances
		for ($i=0; $i<3; $i++)
		{
			//on lance la commande radio
			system($commande);
			//attente entre les deux envois
			usleep(100);
		}
		
		//extinction de la LED
		if (! is_null(Pi_LED)) exec('gpio -g write ' . Pi_LED . ' 0');

	}
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
	return explode($delimiteur,trim($texte));
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

function texte($etat, $texte, $lang="fr")
{
	$volets = [ "off" => [ "en" => "close" , "fr" => "fermer"], "on" => [ "en" => "open" , "fr" => "ouvrir" ] ];
	$lampes = [ "off" => [ "en" => "light off" , "fr" => "éteindre" ], "on" => [ "en" => "light on", "fr" => "allumer" ] ];
	//fermeture volet
	if (stripos($texte, "volet") !== false || stripos($texte, "store") !== false) return $volets[$etat][$lang];
	//extinction lampe
	if (stripos($texte, "lampe") !== false) return $lampes[$etat][$lang];
	//pas de libellé trouvé
	return $etat;
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

//fonction astronomiques
function coucher_solaire($mois, $jour, $latitude, $longitude)
{
	return date_sunset(mktime(1,1,1, $mois, $jour), SUNFUNCS_RET_STRING, $latitude, $longitude, 90, 1+date("I"));
}

function lever_solaire($mois, $jour, $latitude, $longitude)
{
	return date_sunrise(mktime(1,1,1, $mois, $jour) , SUNFUNCS_RET_STRING, $latitude, $longitude, 90, 1 + date("I"));
}

?>
