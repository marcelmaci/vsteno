#!/bin/bash

# 	 download_vsteno.sh
# 	 Clones entire VSTENO project from github repository, sets head to selected
#    commit (latest by default) and copies it to /var/www/html for use with
#    apache (deleting an existing installation beforehand and replacing it by
#    the new one).
#
#    Copyright (c) 2018-2021  Marcel Maci (m.maci@gmx.ch)
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <https://www.gnu.org/licenses/>.

echo "download VSTENO:"
#echo "change to apache directory /var/www/html ..."
#sudo cd /var/www/html
echo "clone VSTENO from https://github.com/marcelmaci/vsteno ..."
sudo git clone https://github.com/marcelmaci/vsteno
# checkout necessary commit 
echo "checkout commit number: $commit"
cd vsteno
sudo git checkout $commit
cd ..
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
