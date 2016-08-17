rm radioReception
g++ radioReception.cpp -o radioReception -lwiringPi
sudo chown root:www-data /var/www/sam/radioReception
sudo chmod 4777 radioReception
sudo chmod +x radioReception
sudo /var/www/sam/radioReception /var/www/sam/radioReception.php 2
