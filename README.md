# sam

Par Matthieu ONFRAY (http://www.onfray.info)
Licence : CC by sa

SAM pour Système Autonome de Maison est un projet d'amusement open-source combinant un peu de code, un peu d'électronique afin d'automatiser des équipements privés : lampes, volets, etc. branchés sur des modules à ondes (type Chacon DIO). Ce mini-projet de domotique use du PHP, du Javascript, un peu de shell...

1) Il vous faut un :
- ordinateur sous linux (PC/Raspberry pi/autre)
- serveur Web (Apache/Nginx/autre)
- PHP 5 et supérieur
- émetteur radio 433Mhz connecté sur l'ordinateur
- modules radio récepteurs DIO chacon pour piloter vos équipements (volets, lampes, etc.)
- SAM installé et configuré dans un répertoire de votre serveur web (/var/www/sam idéalement sinon il faut changer le chemin dans constantes.php et mamaison.sh)

2) Installation
Déposer le dossier sam dans votre /var/www
Donner les droits d'éxécution sur le binaire radioEmission (sudo chown root:www-data /var/www/sam/radioEmission
puis un sudo chmod 4777 radioEmission) sinon l'interface web ne fonctionnera pas !
Donner les droits d'éxécution sur ma_maison.sh à votre utilisateur de seveur web (www-data souvent)
Ajouter dans la crontab le lancement chaque nuit à 1h00 du script cron.php pour un utilisateur du système (www-data)
Exemple de ligne : 
0 1 * * * php /var/www/sam/cron.php #exécution nocturne de sam :)

3) Utilisation
Connexion : 
L'interface web se trouvera sur http://ip-de-votre-serveur/sam (ou autre selon la conf du serveur web).
Vous vous connectez avec votre utilisateur (à régler dans id.php avec un éditeur de texte) ou sans vous connecter (mettre sécurisation à false dans constant.php avec votre éditeur de texte). En cas de connexion sécurisée, un cookie est déposé pour un an sur votre client pour éviter de vous reloguer à chaque fois.
En cas de sécurisation votre serveur doit permettre les sessions pour le système captcha.

Page d'accueil :
La première page permet de :
- allumer/éteindre les lampes définies 
- fermer/ouvrir les volets définis
- accéder à la configuration

Page configuration :
Définissez autant de règles que vous voulez ! Entrez un nom de règle, puis les codes des modules (volets, lampes, etc), les périodes d'activation de la programmation (semaine, week-end, ou encore semaine et week-end). 

Un fichier de votre conf est créé par le programme (droits d'écriture nécessaire dans le répertoire sam).
Le lever et le coucher du soleil sont calculés selon la ville choisie dans la liste déroulante. Sélectionnez la plus proche de vous, j'ai retenu les nouvelles capitales de région (Bordeaux, Rouen, Lyon, etc). Par défaut c'est Rouen.
Si vous déménagez loin, il est logique de retourner choisir la nouvellle ville la plus proche.

4) Ressources et inspirations
http://blog.idleman.fr/raspberry-pi-12-allumer-des-prises-distance/
https://www.guillaume-leduc.fr/gestion-caches-nginx-php-fpm.html
http://legissa.ovh/internet-se-proteger-des-pirates-et-hackers.html

