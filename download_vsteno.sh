#!/bin/bash
echo "download VSTENO:"
#echo "change to apache directory /var/www/html ..."
#sudo cd /var/www/html
echo "clone VSTENO from https://github.com/marcelmaci/vsteno ..."
sudo git clone https://github.com/marcelmaci/vsteno
# copy to webserver directory
echo "copy VSTENO to webserver"
# first delete existing vsteno intallation
sudo rm -R /var/www/html/vsteno
# copy cloned files to /var/www/html/
sudo mv vsteno /var/www/html
# echo "change owner and user group ..."
# cd /var/www/html/vsteno
# chown -R user:user *
# chmod -R ugo+rw *
