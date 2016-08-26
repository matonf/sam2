<?php
/*
Cette page récupere les informations du signal radio recu par le raspberry PI et effectue une action
en fonction de ces dernières.

NB : Cette page est appellée en parametre du programme C 'radioReception', vous pouvez tout à fait
appeller une autre page en renseignant le parametre lors de l'execution du programme C.

@author : Valentin CARRUESCO (idleman@idleman.fr)
@licence : CC by sa (http://creativecommons.org/licenses/by-sa/3.0/fr/)
RadioPi de Valentin CARRUESCO (Idleman) est mis à disposition selon les termes de la 
licence Creative Commons Attribution - Partage dans les Mêmes Conditions 3.0 France.
Les autorisations au-delà du champ de cette licence peuvent être obtenues à idleman@idleman.fr.
@modif : ONFRAY Matthieu http://onfray.info
*/

require_once("fonctions.php");
//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();
//récuperation des paramètres du signal sous forme de variables
list($file,$sender,$group,$state,$interruptor) = $_SERVER['argv'];

//mon capteur crépusculaire DIO a ces codes...
if ($sender == "9841358" && $interruptor == 9)
{
	$modules = null;
	//$state vaut "off" le soir, "on" le matin
	if ($state == "on") $moment = "l'aube";
	else $moment = "le crepuscule";
	//un peur d'horodatage pour les log
	echo "Le " . date("d/m/Y") . " , c'est " . $moment .  " a " . date("H:i") . "\n";
	//parcours des items connus
	foreach($conf_mamaison as $var => $val)
	{
		//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
		if (! isset($conf_mamaison[$var]) || ! item_valide($var)) continue;
		$item_cur = $conf_mamaison[$var];
		//si l'item se déclenche au capteur
		//ouverture à l'aube 
		if (item_on($item_cur) == AUBE && $state == "on") 
		{
			system("php " . CHEMIN . "activer.php on " . $var);
			$modules .= item_desc($item_cur) . " ";
		}
		//ouverture au crépuscule
		if (item_on($item_cur) == CREPUSCULE && $state == "off") 
		{
			system("php " . CHEMIN . "activer.php on " . $var);
			$modules .= item_desc($item_cur) . " ";
		}
		//fermeture à l'aube
		if (item_off($item_cur) == AUBE && $state == "on") 
		{
			system("php " . CHEMIN . "activer.php off " . $var);
			$modules .= item_desc($item_cur) . " ";
		}
		//fermeture au crépuscule
		if (item_off($item_cur) == CREPUSCULE && $state == "off") 
		{
			system("php " . CHEMIN . "activer.php off " . $var);
			$modules .= item_desc($item_cur) . " ";
		}
	} 
	//sortie pour les log
	if (! is_null($modules)) echo "Activation des modules suivants : " . $modules . "\n";
}
?>
