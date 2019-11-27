#!/bin/bash
if [ $(whoami) == "root" ]; then
	echo "ERROR: run this script as normal user ./install_vsteno version"
	exit 1
fi
if [ -z $1 ]; then
	echo "ERROR: you must give a version as argument: ./install_vsteno version"
	echo "Valid arguments for version are:"
	echo "0.2: version 0.2 (Ariadne)"
	echo "Ariadne: same as preceeding"
	echo "0.1: version 0.1 (Hephaistos)"
	echo "Hephaistos: same as preceeding"
	echo "lateststable: lastest stable version"
	echo "latest: latest version (might not work properly)"
	echo "commit: any commit number from https://github.com/marcelmaci/vsteno"	
	exit 1
fi

echo "Install VSTENO version: $1"
case "$1" in
        Hephaistos)
            version_number=0.1
	    version_name=Hephaistos
	    version_date="26/07/19"
            commit=4f09ae3ab48e06fb2357a7437a7fcf9321e3c6f6
            ;;
         
        0.1)
	    version_number=0.1
            version_name=Hephaistos
	    version_date="26/07/19"
	    commit=4f09ae3ab48e06fb2357a7437a7fcf9321e3c6f6
	    ;;
	Ariadne)
            version_number=0.2
            version_name=Ariadne
            version_date="25/11/19"
            commit=c7761a88d239bba280e334d38c73b66218cfb4c8
            ;;

        0.2)
            version_number=0.2
            version_name=Ariadne
            version_date="25/11/19"
            commit=c7761a88d239bba280e334d38c73b66218cfb4c8
            ;;
	lateststable)
            version_number=lateststable
            version_name=Ariadne
            version_date="25/11/19"
            commit=c7761a88d239bba280e334d38c73b66218cfb4c8
            ;;
         
        latest)
            version_number=latest
            version_name=Ariadne
            version_date="25/11/19"
	    commit=origin/master
            ;;
        *)
            commit=$1
esac
echo "Version $1 corresponds to commit: $commit"
# export variable to make it available for following scripts
export version_number
export version_name
export version_date
export commit
./install_tools.sh
./download_vsteno.sh
./configure_database.sh
