#!/bin/bash
echo "download VSTENO:"
echo "change to apache directory /var/www/html ..."
cd /var/www/html
echo "clone VSTENO from https://github.com/marcelmaci/vsteno ..."
git clone https://github.com/marcelmaci/vsteno
# echo "change owner and user group ..."
# cd /var/www/html/vsteno
# chown -R user:user *
# chmod -R ugo+rw *
