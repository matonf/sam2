#!/bin/bash

#Par Matthieu ONFRAY (http://www.onfray.info)
#Licence : CC by sa

#on va éviter le spam par mail
MAILTO=""

function impulser() {
	#Numéro WiringPi du pin raspberry branché a l'émetteur radio
	PIN=0
	#Code télécommande du raspberry (ne doit pas excéder les 2^26)
	PI=555
	#binaire qui émet l'onde radio
	FIC_RADIO="/var/www/sam/radioEmission"
	#émission ! flash ! powa ! beam !
	t=`$FIC_RADIO $PIN $PI $2 $1`
}

#lit un jngle ! et fait une pause de quelques secondes
l=`aplay /var/www/sam/jingle.wav;sleep 5`
#récupère l'état passé en paramètre
ETAT=$1
#émet pour chacun des modules radio passés en paramètre (de 2 à n)
while [ -n "$2" ]
do
  impulser $ETAT $2
shift
done

#sortie normale
exit 0
