#!/bin/bash
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
