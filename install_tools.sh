#!/bin/bash
echo "Welcome to the VSTENO tools installation script"
echo "Note: This only works on DEBIAN-like systems with apt-get paket manager"
if [ $(whoami) != "root"]; then
	echo "ERROR: you must be root to install the tools"
	echo "Run this script with: sudo ./install_tools.sh"
	exit 1
fi
echo "update paket manager ..."
apt-get update
echo "install webserver (apache2) ..."
apt-get install apache2
echo "install php ..."
apt-get install php php-common libapache2-mod-php php-cli php-mysql php-mbstring
echo  "install hunspell ..."
apt-get install hunspell
echo "install dictionaries:"
echo "german (de_CH) ..."
apt-get install hunspell-de-ch
echo "spanisch (es)"
apt-get install hunspell-es
echo "french (fr)"
apt-get install hunspell-fr
echo "install eSpeak ..."
apt-get install espeak
echo "install database:"
echo "install server ..."
apt-get install mysql-server 
echo "install client ..."
apt-get install mysql-client
echo "install workbench ..."
apt-get install mysql-workbench
echo "install git ..."
apt-get install git
echo "Install gedit ..."
sudo apt-get install gedit
echo "Have a look at all messages from the paket manager"
echo "If there are no errors you are done ... :)"
