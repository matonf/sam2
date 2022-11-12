# sam


SAM pour Système Autonome de Maison est un projet d'amusement open-source combinant un peu de code, un peu d'électronique afin d'automatiser des équipements privés : lampes, volets, etc. branchés sur des modules à ondes (type Chacon DIO). Ce mini-projet de domotique use du PHP, du Javascript, un peu de shell...

Capture d'écran de l'interface sur un téléphone portable :

![accueil](https://github.com/onfray/sam2/blob/master/index.png)

### Pré-requis
Il vous faut un :
- Raspberry pi, même une version très ancienne ou la moins puissante comme le Zero. Mon système configuré en serveur, n'utilise que 90 Mo de RAM tout compris (Debian, serveur web, autres...) et 0% du CPU de mon Raspberry Pi 0.
- serveur Web (Apache/Nginx/autre), j'utilise Nginx pour sa légèreté
- PHP 5 et supérieur (on est à la version 7 mais sur ma Debien Jessie j'en suis à la 5)
- émetteur radio 433Mhz connecté sur l'ordinateur 
- modules radio récepteurs DIO chacon pour piloter vos équipements (volets, lampes, etc.) qu'on peut acheter sur Internet ou en boutiques de bricolage comme Leroy Merluche
- SAM installé et configuré dans un répertoire de votre serveur web (/var/www/html par exemple)
- récepteur radio 433Mhz connecté sur l'ordinateur + capteur crépusculaire DIO (fonction prototypée, non opérationnelle à ce jour)
- un smartphone et PushBullet installé (optionnel) ou un abonnement chez Free pour recevoir gratuitement des notifications de l'application (ex : "SAM va ouvrir les volets du salon") 

### Installation  
Déposer le contenud du dossier sam dans votre /var/www/html 
Il vous faut préalablement installer et configurer nginx of course.

Donner les droits d'éxécution sur le binaire radioEmission (sudo chown root:www-data /var/www/html/radioEmission puis un sudo chmod 4777 radioEmission) sinon l'interface web ne fonctionnera pas !

Ajouter dans la crontab le lancement chaque nuit à 5h00 du script cron.php pour un utilisateur du système (www-data).

Exemple de ligne :   
0 5 * * * php /var/www/html/cron.php #exécution nocturne de sam :)

Pour sécuriser votre site, éditez le fichier id.php et mettez-y les couples utilisateur/mot de passe de votre choix. Le fichier en contient en exemple. Vous pouvez aussi définir votre couple clef publique/privée Google reCaptcha pour activer automatiquement cette technologie au login. Il faut mettre la constante SECURISER à true dans constantes.php pour activer la sécurisation (fait par défaut). Dans ce cas toutes les pages web demanderont une authentification, mais pour éviter de se loguer chaque fois, un cookie valable un an est positionné sur le client. Vous pouvez changer la durée de rétention du cookie dans constantes.php (constante COOKIE_EXPIRE)

Sécurisation : définissez votre couple clef publique/privée Google pour reCaptcha dans id.php et ainsi le login utilisera cette fonction anti robot par Google. Si les constantes sont à null, la sécurisation reCaptcha est ignorée.

Personnalisation : utilisez votre smartphone et installez PushBullet pour recevoir les notifications de SAM ! Créez depuis leur site votre clef privée ("credentials"). Ajoutez votre clef dans le script pushbullet.sh à la ligne 3 et vérifiez que la variable NOTIF_PORTABLE est à true dans constantes.php

Vous pouvez avoir une notification SMS par Free, si vous êtes chez eux aussi.

Si vous voulez pouvoir choisir les demi-heures dans les heures de programmation, dans constantes.php mettez à true la constante AFFICHER30

Si vous voulez pouvoir recevoir les ondes radio d'un capteur DIO, dans constantes.php mettez à true la constante AFFICHER_CAPTEUR <- pas encore opérationnel,ça m'est réservé, je dois finir le code.


### Utilisation  
Connexion :   
L'interface web se trouvera sur http://ip-de-votre-serveur (ou autre selon la conf du serveur web).
Vous vous connectez avec votre utilisateur (à régler dans id.php avec un éditeur de texte) ou sans vous connecter (SECURISER vaut false par défaut). En cas de connexion sécurisée, un cookie est déposé pour un an sur votre client pour éviter de vous reloguer à chaque fois.

Page d'accueil :  
La première page permet de :
- allumer/éteindre les lampes définies 
- fermer/ouvrir les volets définis

Page configuration :  

![configuration](https://github.com/onfray/sam2/blob/master/configurer.png)

Définissez autant de règles que vous voulez ! Entrez un nom de règle, puis les codes des modules (volets, lampes, etc), les périodes d'activation de la programmation (semaine, week-end, ou encore semaine et week-end). 

Un fichier de votre conf est créé par le programme (droits d'écriture nécessaires dans le répertoire sam).  
Le lever et le coucher du soleil sont calculés selon la ville choisie dans la liste déroulante. Sélectionnez la plus proche de vous, j'ai retenu les nouvelles capitales de région (Bordeaux, Rouen, Lyon, etc). Par défaut c'est Rouen. Si vous déménagez loin, il est logique de retourner choisir la nouvellle ville la plus proche.

Nouveauté : la géolocalisation. Déroulez la liste des villes, sélectionnez "Géolocalisée" puis cliquez sur le bouton "Géolocalise-moi". Acceptez la demande de géolocalisation : un message vous indique que vous avez été trouvé. Enrgistrez: vos coordonnées sont stockées, vous n'aurez plus jamais besoin de vous géolocaliser sauf si vous déménagez bin sûr.

Page Infos :

Donne des détails sur la météo locale et la configuration de SAM (notez le numéro d'émetteur sur un papier ou faites une capture d'écran, ça vous resservira si vous changez de carte Raspberry pi).

### Liste de courses
Pour ce projet, il faut :
* Un raspberry pi 0 W (10€) : https://shop.pimoroni.com/collections/raspberry-pi-zero
* Un émetteur radio (1€) : http://www.ebay.fr/itm/Module-emetteur-radio-433Mhz-Wireless-UHF-Transmitter-Arduino-Raspberry-HG-/201640979378?hash=item2ef2bd2bb2:g:eLgAAOSwqBJXWnOB
* Du fil électrique pour câbler tout ce petit monde et un spécifique de longueur 17.3 cm pour l'antenne (1€)
* Un transfo 220V->5V (5€ ou récupération de celui d'un vieux téléphone portable)
* Une carte mémoire 8Go microSD (5€) pour recevoir la distribution Linux
* Un boitier pour le pi (5€)
* Bonus : une LED pour clignoter quand on en a envie ! Quand le rasp est prêt ou qu'il émet une onde radio par exemple... (0.1€)
* Bonus 2 : un bouton poussoir pour éteindre proprement le rasp (0.1€)

L'ensemble coûtera entre 15 et 30 euros selon votre capacité à recycler boitier (en Lego ?), carte mémoire, chargeur et petits équipements électriques.

Et pour tous vos volets :
* Des modules de la marque DIO pour volets roulants (23€ pièce) : https://www.leroymerlin.fr/v3/p/produits/kit-de-3-modules-pour-volet-roulant-dio-e186621

### Ressources et inspirations
Radio et pi :
* http://blog.idleman.fr/raspberry-pi-08-jouer-avec-les-ondes-radio/
* http://blog.idleman.fr/raspberry-pi-10-commander-le-raspberry-pi-par-radio/
* http://blog.idleman.fr/raspberry-pi-12-allumer-des-prises-distance/
* http://wiringpi.com/download-and-install/
* http://playground.arduino.cc/Code/HomeEasy
* http://homeeasyhacking.wikia.com/wiki/Home_Easy_Hacking_Wiki
* https://learnraspi.com/2016/04/12/get-notifications-raspberry-pi-pushbullet/
* https://gladysproject.com/fr/article/connecter-un-arduino-au-raspberry-pi
* http://www.pihomeserver.fr/2013/10/25/raspberry-pi-home-server-ajouter-bouton-darret/

Serveur web Nginx :
* https://doc.ubuntu-fr.org/nginx
* https://wiki.deimos.fr/Nginx_:_Installation_et_configuration_d'une_alternative_d'Apache
* https://www.guillaume-leduc.fr/gestion-caches-nginx-php-fpm.html
* http://legissa.ovh/internet-se-proteger-des-pirates-et-hackers.html
