#!/bin/bash

# 	 install_vsteno.sh
# 	 Main script that calls all the other scripts to install all tools
#    and data needed for VSTENO. Accepts one parameter that selects the
#    version that will be installed.
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

if [ $(whoami) == "root" ]; then
	echo "ERROR: run this script as normal user ./install_vsteno version"
	exit 1
fi
if [ -z $1 ]; then
	echo "ERROR: you must give a version as argument: ./install_vsteno version"
	echo "Valid arguments for version are:"
	echo "0.3: version 0.3 (Hyperion)"
	echo "Hyperion: same as preceeding"
	echo "0.2: version 0.2 (Ariadne)*"
	echo "Ariadne: same as preceeding*"
	echo "0.1: version 0.1 (Hephaistos)*"
	echo "Hephaistos: same as preceeding*"
	echo "lateststable: lastest stable version"
	echo "latest: latest version (might not work properly)"
	echo "commit: any commit number from https://github.com/marcelmaci/vsteno"	
	echo "(* obsolete version (only listed for historical reasons)"
	exit 1
fi

echo "DISCLAIMER: this install script will configure your database via the shell using clear text passwords."
echo "As a consequence, these passwords might show up in your shell history or log files."
echo "Hit enter if you are fine with that (or CTRL-C to stop the script)";
read -s disposable_variable
echo "Ok, here we go ... :-)"
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
            version_date="30/11/19"
            commit=d36ae5e9ac1e70720a07fac83156212e036d1d89
            ;;

        0.2)
            version_number=0.2
            version_name=Ariadne
            version_date="30/11/19"
            commit=d36ae5e9ac1e70720a07fac83156212e036d1d89
            ;;
	Hyperion)
            version_number=0.3
            version_name=Hyperion
            version_date="22/03/20"
            commit=c09d834dc133142612c85c598cdb45d19bbdce57
            ;;

        0.3)
            version_number=0.3
            version_name=Hyperion
            version_date="22/03/20"
            commit=c09d834dc133142612c85c598cdb45d19bbdce57
            ;;
	lateststable)
            version_number=lateststable
            version_name=Hyperion
            version_date="10/04/20"
            commit=d9df55086b0b4f177b83060198c01f50850e81d8
            ;;
         
        latest)
            version_number=latest
            version_name=Hyperion
            version_date="10/04/20"
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
