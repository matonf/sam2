# sam

Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa

SAM pour Système Autonome de Maison est un projet d'amusement open-source combinant un peu de code, un peu d'électronique afin d'automatiser des équipements privés : lampes, volets, etc. branchés sur des modules à ondes (type Chacon DIO). Ce mini-projet de domotique use du PHP, du Javascript, un peu de shell...

1) Il vous faut un :
- ordinateur sous linux (PC/Raspberry pi/autre), le pi est idéal pour ce projet, même une version très ancienne ou la moins puissante le Zero. Mon système configuré en serveur, n'utilise que 90 Mo de ram tout compris (Debian, serveur web, autres...) et 0% du CPU de mon Raspberry Pi 2 (quad core certes).
- serveur Web (Apache/Nginx/autre), j'utilise Nginx pour sa légèreté
- PHP 5 et supérieur (on est 7 mais sur ma Debien Jessie j'en suis au 5)
- émetteur radio 433Mhz connecté sur l'ordinateur (1 euro environ sur Amazon)
- modules radio récepteurs DIO chacon pour piloter vos équipements (volets, lampes, etc.) qu'on peut acheter sur Internet ou en boutiques de bricolage comme Leroy Merluche
- SAM installé et configuré dans un répertoire de votre serveur web (/var/www/sam idéalement sinon il faut changer le chemin dans constantes.php)
- récepteur radio 433Mhz connecté sur l'ordinateur + capteur crépusculaire DIO (fonction protoypée, non opérationnelle à ce jour)
- un smartphone et PushBullet installé (optionnel) pour recevoir gratuitement des notifications de l'application (ex : "SAM va ouvrir les volets du salon") 

2) Installation
Déposer le dossier sam dans votre /var/www
Il vous faut préalablement installer et configurer nginx of course.

Donner les droits d'éxécution sur le binaire radioEmission (sudo chown root:www-data /var/www/sam/radioEmission
puis un sudo chmod 4777 radioEmission) sinon l'interface web ne fonctionnera pas !

Ajouter dans la crontab le lancement chaque nuit à 1h00 du script cron.php pour un utilisateur du système (www-data)

Exemple de ligne : 
0 1 * * * php /var/www/sam/cron.php #exécution nocturne de sam :)

Pour sécuriser votre site, éditez le fichier id.php et mettez-y les couples utilisateur/mot de passe de votre choix. Le fichier en contient en exemple. Vous pouvez aussi définir votre couple clef publique/privée Google reCaptcha pour activer automatiquement cette technologie au login. Il faut mettre la constante SECURISER à true dans constantes.php pour activer la sécurisation (fait par défaut). Dans ce cas toutes les pages web demanderont une authentification, mais pour éviter de se loguer chaque fois, un cookie valable un an est positionné sur le client. Vous pouvez changer la durée de rétention du cookie dans constantes.php (constante COOKIE_EXPIRE)

Sécurisation : définissez votre couple clef publique/privée Google pour reCaptcha dans id.php et ainsi le login utilisera cette fonction anti robot par Google. Si les constantes sont à null, la sécurisation reCaptcha est ingorée.

Personnalisation : utilisez votre smartphone et installez PushBullet pour recevoir les notifications de SAM ! Créez depuis leur site votre clef privée ("credentials"). Ajoutez votre clef dans le script pushbullet.sh à la ligne 3 et vérifiez que la variable NOTIF_PORTABLE est à true dans constantes.php

Si vous voulez pouvoir choisir les demi-heures dans les heures de programmation, dans constantes.php mettez à true la constante AFFICHER30

Si vous voulez pouvoir recevoir les ondes radio d'un capteur DIO, dans constantes.php mettez à true la constante AFFICHER_CAPTEUR <- pas encore opérationnel,ça m'est réservé, je dois finir le code.


3) Utilisation
Connexion : 
L'interface web se trouvera sur http://ip-de-votre-serveur/sam (ou autre selon la conf du serveur web).
Vous vous connectez avec votre utilisateur (à régler dans id.php avec un éditeur de texte) ou sans vous connecter (mettre sécurisation à false dans constantes.php avec votre éditeur de texte). En cas de connexion sécurisée, un cookie est déposé pour un an sur votre client pour éviter de vous reloguer à chaque fois.

Page d'accueil :
La première page permet de :
- allumer/éteindre les lampes définies 
- fermer/ouvrir les volets définis
- accéder à la configuration

Page configuration :
Définissez autant de règles que vous voulez ! Entrez un nom de règle, puis les codes des modules (volets, lampes, etc), les périodes d'activation de la programmation (semaine, week-end, ou encore semaine et week-end). 

Un fichier de votre conf est créé par le programme (droits d'écriture nécessaires dans le répertoire sam).
Le lever et le coucher du soleil sont calculés selon la ville choisie dans la liste déroulante. Sélectionnez la plus proche de vous, j'ai retenu les nouvelles capitales de région (Bordeaux, Rouen, Lyon, etc). Par défaut c'est Rouen.
Si vous déménagez loin, il est logique de retourner choisir la nouvellle ville la plus proche.


4) Ressources et inspirations

Radio et pi :
http://blog.idleman.fr/raspberry-pi-08-jouer-avec-les-ondes-radio/
http://blog.idleman.fr/raspberry-pi-10-commander-le-raspberry-pi-par-radio/
http://blog.idleman.fr/raspberry-pi-12-allumer-des-prises-distance/
http://wiringpi.com/download-and-install/
http://playground.arduino.cc/Code/HomeEasy
http://homeeasyhacking.wikia.com/wiki/Home_Easy_Hacking_Wiki
https://learnraspi.com/2016/04/12/get-notifications-raspberry-pi-pushbullet/
https://gladysproject.com/fr/article/connecter-un-arduino-au-raspberry-pi

Serveur web Nginx :
https://doc.ubuntu-fr.org/nginx
https://wiki.deimos.fr/Nginx_:_Installation_et_configuration_d'une_alternative_d'Apache
https://www.guillaume-leduc.fr/gestion-caches-nginx-php-fpm.html
http://legissa.ovh/internet-se-proteger-des-pirates-et-hackers.html

