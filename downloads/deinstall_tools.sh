#!/bin/bash
printf "Welcome to the VSTENO tools deinstallation script
Note: This only works on DEBIAN-like systems with apt-get paket manager
Tools hunspell and git, installed by install_tools.sh, are not removed."

printf "\nremove webserver (apache2) ..."
sudo apt-get --assume-yes remove apache2

printf "\nremove php ..."
sudo apt-get --assume-yes remove php php-common libapache2-mod-php php-cli php-mysql php-mbstring

printf "\nremove server ..."
sudo apt-get --assume-yes remove mysql-server 

printf "\ninstall client ..."
sudo apt-get --assume-yes remove mysql-client

printf "\ninstall workbench ..."
sudo apt-get --assume-yes remove mysql-workbench

printf "\n
Have a look at all messages from the paket manager and consider to use
the 'sudo apt autoremove' to complete the deinstallation.  Note, this
script kept tools git and hunspell installed by install_tools.sh on
this system.  If there are no errors you are done ... :)\n"
