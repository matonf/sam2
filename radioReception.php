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

//Récuperation des parametres du signal sous forme de variables
list($file,$sender,$group,$state,$interruptor) = $_SERVER['argv'];
//Affichages des valeurs dans la console a titre informatif
echo "\nemetteur : $sender,\n Groupe :$group,\n on/off :$state,\n boutton :$interruptor";

//En fonction de la rangé de bouton sur laquelle on à appuyé, on effectue une action
switch($interruptor){

	//Rangée d'interrupteur 1 
	case '0':
		//system('gpio mode 3 out');
		//Bouton On appuyé
		//if($state=='on') $i=1;
		//else $i=0;
		//echo 'Mise à $1 du PIN 3 (15 Pin physique)';
		//system('gpio write 3 $i');
		//Bouton off appuyé
		/*}else{
			echo 'Mise à 0 du PIN 3 (15 Pin physique)';
			system('gpio write 3 0');
		}*/
	break;
	
	//Rangée d'interrupteur 2
	case '1':
		//Bouton On appuyé
		if($state=='on'){
			//mettre quelque chose ici
		//Bouton off appuyé
		}else{
			//mettre quelque chose ici
		}
	break;
	
	///Rangée d'interrupteur 3
	case '2':
		if($state=='on'){
			//mettre quelque chose ici	
		}else{
			//mettre quelque chose ici
		}
	break;
}

?>
