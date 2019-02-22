<?php
/*
Par Matthieu ONFRAY

*/
//CONSTANTES
//sécuriser l'accès par login
define('SECURISER', false);
//fichiers sans sécurisation
$NOLOGIN = [ "activer.php" , "cron.php", "radioReception.php", "dash-activer.php" ];
//Numéro WiringPi du pin raspberry branché a l'emetteur radio, si null = arduino
define('Pi_PIN', 0);
//Port COM de l'arduino
define('Arduino_COM', null);
//nom du fichier de conf
define('MA_CONF', 'mamaison2.conf');
//nos références françaises : villes => latitude, longitude
$villes = [ "Géolocalisée" => [ 0, 0 ] , "Lille" => [ 50.6329700, 3.0585800 ] , "Rouen" => [ 49.4431300, 1.0993200 ] , "Paris" => [ 48.8534100, 2.348800 ] , "Strasbourg" => [ 48.5839200,  7.7455300 ], "Rennes" => [ 48.1119800, -1.6742900 ], "Nantes" => [47.2172500, -1.5533600 ], "Orléans" => [ 47.9028900, 1.9038900 ], "Dijon" => [ 47.3166700, 5.0166700 ], "Lyon" => [ 45.7484600, 4.8467100 ], "Bordeaux" => [ 44.8404400, -0.5805000 ], "Toulouse" => [ 43.6042600, 1.4436700 ], "Marseille" => [ 43.2969500, 5.3810700 ], "Ajaccio" => [ 41.9272300, 8.7346200] ];
// RETENTION DU COOKIE : 1 an
define('COOKIE_EXPIRE', 365*24*3600);
//faut-il loguer
define('LOG', false);
//fichier de log
define('FIC_HISTO', 'historique.log');
//version du logiciel
define('VERSION', '3');
//délimiteur du fichier de conf
define('DELIMITEUR', ',');
//chemin du dossier web
define('CHEMIN', '/var/www/html/');
//chemin des fichiers switch
define('CHEMIN_SWITCH', CHEMIN . 'switch/');
//accepte les demi-heures dans la programmation
define('AFFICHER30', false);
//accepte un capteur crépusculaire dans la programmation
define('AFFICHER_CAPTEUR', false);
//fichier de LOCK du mode vacances
define('FIC_VACANCES', 'VACANCES.lock');
//nom du cookie de l'utilisateur
define('COOKIE_ID', "cookie_sam_id");
//nom du cookie du mot de passe
define('COOKIE_MDP', "cookie_sam_mdp");
//capteur : mot-clef pour l'aube
define('AUBE', 'capteur-aube');
//capteur : mot clef pour le crépuscule
define('CREPUSCULE', 'capteur-crepuscule');
//accepter notifications sur téléphone portable : FREE ou PUSHBULLET ou null
define('NOTIF_PORTABLE', null);
//numéro du pin de la LED
define('Pi_LED', null);
//fichier personnalisé de l'émetteur
define('FIC_SENDER', CHEMIN . 'sender.php');

//initialisation du code émetteur
if (! file_exists(FIC_SENDER)) 
{
	//détermine un numéro unique
	if (is_null(Pi_PIN))
	{
		//arduino : nombre aléatoire
		$numserienum = rand(1111, 999999);
	}
	else 
	{
		//raspberry pi : récupère le numéro de série du Pi
		$numserie = exec("grep Serial /proc/cpuinfo | cut -d ' ' -f 2");
		//ne garde que les numériques puis les 6 premiers chiffres
		$numserienum = (int) substr((int) preg_replace("#[^0-9]#", "", $numserie), 0, 6);
	}
	//code télécommande dans un fichier
	$pts = @fopen(FIC_SENDER, 'w');
	@fwrite($pts, "<?php\ndefine('SENDER', " . $numserienum . ");\n?>");
	@fclose($pts);
}
//charge le code émetteur
require_once(FIC_SENDER);
?>
