<?php 
/*
Par Matthieu ONFRAY

*/

require_once("fonctions.php");


function creer_ligne_cron($etat, $item, $heure_activation, $periode_activation)
{
	//Jour de la semaine : 1 (pour Lundi) à 7 (pour Dimanche)
	$numjour_semaine = date("N");
	//on filtre les jours inactifs en sortant prématurément
	switch ($periode_activation)
	{
		case "semaine" : if ($numjour_semaine >= 6) return; break;
		case "week_end" : if ($numjour_semaine <= 5) return; break;
	}
	switch ($heure_activation)
	{
		//c'est l'option "ne rien faire" ou les commande extérieures via capteur crépusculaire
		case 25 : 
		case CREPUSCULE : 
		case AUBE :
			return; break;

		//minuit devient 23h59 pour le lancement le jour même
		case 24 : 
			$heure = 23; 
			$minutes = 59; 
			break;

		//lever ou coucher solaire calculé automatiquement
		case "autol" : 
		case "autoc" :
			global $villes, $conf_mamaison;
			//jour du mois
			$jour = date("j");
			//mois de l'année
			$mois = date("n");		
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

			//calcul de l'horaire solaire pour la France (GMT+1)
			if ($heure_activation == "autol") $slaire = lever_solaire($mois, $jour, $latitude, $longitude);
			if ($heure_activation == "autoc") $slaire = coucher_solaire($mois, $jour, $latitude, $longitude);
			$heure_tab = explode(":", $slaire);
			//formatage des données pour la cron
			$heure = $heure_tab[0];
			$minutes = $heure_tab[1];
			break;	
		
		default : 
			//cas des heures fixes 
			if (strlen($heure_activation) <= 2)
			{		
				$heure = $heure_activation; 
				$minutes = 0;
			}
			else
			{
				//cas des heures intermédiaires (les minutes sont par 2 : 15, 30, 45)
				$heure = substr($heure_activation, 0, strlen($heure_activation)-2);
				$minutes = substr($heure_activation, strlen($heure_activation)-2);
			}
			break;
	}
	return "$minutes $heure";
}
//FIN DE LA FONCTION


//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();
$actions_cron = array();
$ligne_cron = null;
//détection du mode vacances
if (! est_en_mode_vacances())
{
	//parcours des items connus et stockage dans un tableau associatif
	foreach ($conf_mamaison as $item => $val)
	{
		//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
		if (! item_valide($item)) continue;

		//état on/off parcourus
		foreach ($tab_actions = array("on", "off") as $tab_action)
		{
			$fonction_etat = "item_" . $tab_action;
			$ret_cron = creer_ligne_cron($tab_action, $item, $fonction_etat($val), item_jours($val));
			if ($ret_cron) 
				//concatène le résultat des ordres
				if (isset($actions_cron[$ret_cron])) $actions_cron[$ret_cron] = $actions_cron[$ret_cron] . ' ' . $item . '/' . $tab_action;
				else $actions_cron[$ret_cron] = $item . '/' . $tab_action;
		}
	} 
	//stocke le parcours du tableau dans une chaîne de caractères
	foreach ($actions_cron as $horaire => $ordre) $ligne_cron .= "$horaire * * * php " . CHEMIN . "activer.php $ordre #cronSAM " . VERSION . PHP_EOL;
}

//écrit le fichier crontab de ce jour 
$pointeur_cron = @fopen(CHEMIN . "sam.crontab.aujourdhui", "w");
if ($pointeur_cron)
{
	@fwrite($pointeur_cron, $ligne_cron);
	@fclose($pointeur_cron);
} else echo "ne peut ouvrir en écriture: " . CHEMIN . "sam.crontab.aujourdhui" . "<br>";

//on écrit un peu de bash : on récupère la crontab courante, on retire les anciennes mentions #cronSAM
$ancienne_crontab = 'cd ' . CHEMIN . ';crontab -l 2>/dev/null|grep -v " #cronSAM">sam.crontab.hier';
system($ancienne_crontab);
//on ajoute celles du jour et on sauve
$nouvelle_crontab = '(cd ' . CHEMIN . ';cat sam.crontab.hier sam.crontab.aujourdhui) | crontab -';
system($nouvelle_crontab);
ecrire_log("a reconfiguré la programmation");
?>
