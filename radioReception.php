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
*/
require_once("fonctions.php");
//Récuperation des parametres du signal sous forme de variables
list($file,$sender,$group,$state,$interruptor) = $_SERVER['argv'];
//Affichages des valeurs dans la console a titre informatif
//echo "\nemetteur : $sender,\n Groupe :$group,\n on/off :$state,\n bouton :$interruptor";

//En fonction de la rangée de bouton sur laquelle on à appuyé, on effectue une action

//mon capteur crépusculaire DIO
if ($sender == "9841358" && $interruptor == 9)
{
	echo "mon capteur envoie $state\n";
	activer_module_radio(2, $state);
}

?>
