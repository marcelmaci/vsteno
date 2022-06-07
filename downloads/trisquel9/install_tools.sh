#!/bin/bash

# 	 install_tools.sh
#	 Installs all tools needed by VSTENO using the apt package manager. 	 
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

echo "Welcome to the VSTENO tools installation script"
echo "Note: This only works on DEBIAN-like systems with apt-get paket manager"
echo "update paket manager ..."
sudo apt-get update
echo "install webserver (apache2) ..."
sudo apt-get --assume-yes install apache2
echo "install php ..."
sudo apt-get --assume-yes install php php-common libapache2-mod-php php-cli php-mysql php-mbstring
echo  "install hunspell ..."
sudo apt-get --assume-yes install hunspell
echo "install dictionaries:"
echo "german (de_CH) ..."
sudo apt-get --assume-yes install hunspell-de-ch
echo "spanisch (es)"
sudo apt-get --assume-yes install hunspell-es
echo "french (fr)"
sudo apt-get --assume-yes install hunspell-fr
echo "italian (it)"
sudo apt-get --assume-yes install hunspell-it
echo "install eSpeak ..."
sudo apt-get --assume-yes install espeak
echo "install database:"
echo "install server ..."
sudo apt-get --assume-yes install mysql-server 
echo "install client ..."
sudo apt-get --assume-yes install mysql-client
echo "install workbench ..."
sudo apt-get --assume-yes install mysql-workbench
echo "install git ..."
sudo apt-get --assume-yes install git
echo "restart apache ..."
sudo service apache2 restart
echo "Have a look at all messages from the paket manager"
echo "If there are no errors you are done ... :)"
