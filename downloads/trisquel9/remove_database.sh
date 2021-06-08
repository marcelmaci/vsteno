#!/bin/bash

# 	 remove_database.sh
# 	 This script deletes ALL (ALL means ALL) data related to databases
#    (mysql server, data, users, user groups - tutti quanti). This script
#    is NOT part of the official installation / deinstallation scripts for
#    VSTENO. Thus, it can only be called separately (manually). Use it ONLY 
# 	 if you not EXACTLY what you are doing. I am NOT responsible for any 
#	 loss of data or harm you might do to your system!
#
#    Copyright (c) 2021  Marcel Maci (m.maci@gmx.ch)
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

echo "THIS SCRIPTS DELETES ALL (AND ALL MEANS ALL!) YOUR DATABASES AND DATA!!!"
echo "PROCEED ONLY IF THIS IS WHAT YOU WANT TO DO! (HIT ENTER)"
echo "OTHERWHISE HIT CTRL-C NOW!"
echo "YOU'VE BEEN WARNED ... ! :)"
echo "Hit enter to continue ... "
read -s input
echo "... ok, starting bulldozer ... :)"
# stop server
sudo service mysql stop
# purge MySQL, databases, and configurations
sudo apt-get purge mysql-server mysql-common mysql-server-core-* mysql-client-core-*
# remove any additional database files:
sudo rm -rf /var/lib/mysql/
# remove configuration folder
sudo rm -rf /etc/mysql/
# remove logs
sudo rm -rf /var/log/mysql
# delete the user-generated during installation
sudo deluser --remove-home mysql
# delete usergroup
sudo delgroup mysql
