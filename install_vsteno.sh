#!/bin/bash
if [ $(whoami) == "root" ]; then
	echo "ERROR: run this script as normal user ./install_vsteno version"
	exit 1
fi
if [ -z $1 ]; then
	echo "ERROR: you must give a version as argument: ./install_vsteno version"
	echo "Valid versions are:"
	echo "Hephaistos: version 0.1"
	echo "latest: lastest commit (without garantees that it will work)"
	echo "commit: any commit number from https://github.com/marcelmaci/vsteno"	
	exit 1
fi

echo "Install VSTENO version: $1"
case "$1" in
        Hephaistos)
            commit=d958efb9ef3f353d71e265696c17f3383d83821a
            ;;
         
        oldstable)
            commit=6ddab137fde3b4c7208dd247445578944208c882
            ;;
         
        latest)
            commit=origin/master
            ;;
        *)
            commit=$1
esac
echo "Version $1 corresponds to commit: $commit"
# export variable to make it available for following scripts
export commit
./install_tools.sh
./download_vsteno.sh
./configure_database.sh
