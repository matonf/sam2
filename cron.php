<?php 
/*
Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa
*/
require_once("fonctions.php");

function creer_ligne_cron($etat, $items, $heure_activation, $periode_activation)
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
		//c'est l'option "ne rien faire"
		case 25 : 
			return; break;

		//minuit devient 23h59 pour le lancement le jour même
		case 24 : 
			$heure = 23; 
			$minutes = 59; 
			break;

		//lever ou coucher solaire calculé automatiquement
		case "autol" : case "autoc" :
			global $villes, $conf_mamaison;
			//jour du mois
			$jour = date("j");
			//mois de l'année
			$mois = date("n");			
			//récupération des coordonnées de la ville choisie
			$latitude = $villes[$conf_mamaison["ville_utilisateur"]][0];
			$longitude = $villes[$conf_mamaison["ville_utilisateur"]][1];
			//calcul de l'horaire solaire pour la France (GMT+2)
			if ($heure_activation == "autol") $slaire = date_sunrise(mktime(1,1,1, $mois, $jour) , SUNFUNCS_RET_STRING, $latitude, $longitude, 90, 2);
			if ($heure_activation == "autoc") $slaire = date_sunset(mktime(1,1,1, $mois, $jour), SUNFUNCS_RET_STRING, $latitude, $longitude, 90, 2);
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
	return "$minutes $heure * * * " . CHEMIN . "mamaison.sh $etat $items #cronSAM " . VERSION . PHP_EOL;
}

//charge la conf de l'utilisateur
$conf_mamaison = charger_conf();
$ligne_cron = null;
//parcours des items connus
foreach($conf_mamaison as $var => $val)
{
	//recherche le motif "itemX" : si on le trouve pas on passe au motif suivant
	if (! item_valide($var)) continue;
	$item_cur = $conf_mamaison[$var];
	//état on
	$ligne_cron .= creer_ligne_cron("on", item_items($item_cur), item_on($item_cur), item_jours($item_cur));
	//état off
	$ligne_cron .= creer_ligne_cron("off", item_items($item_cur), item_off($item_cur), item_jours($item_cur));
} 
//écrit le fichier crontab de ce jour 
$pointeur_cron = fopen(CHEMIN . "sam.crontab.aujourdhui", "w");
fwrite($pointeur_cron, $ligne_cron);
fclose($pointeur_cron);

//on écrit un peu de bash : on récupère la crontab courante, on retire les anciennes mentions #cronSAM
$ancienne_crontab = 'cd ' . CHEMIN . ';crontab -l 2>/dev/null|grep -v " #cronSAM">sam.crontab.hier';
system($ancienne_crontab);
//on ajoute celles du jour et on sauve
$nouvelle_crontab = '(cd ' . CHEMIN . ';cat sam.crontab.hier sam.crontab.aujourdhui) | crontab -';
system($nouvelle_crontab);
?>
