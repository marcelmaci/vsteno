#!/bin/bash
function GetBrowser {
    # returns first browser from list installed on target system 
	# or empty string if no browser is found
	local list="abrowser icecat chromium firefox opera chrome konqueror midori qupzilla"
	local browser; local result; local installed;	
	for browser in $list
	do
  		#echo Test $browser ...
  		result=`which $browser`
  		if [ -z ${result} ]
  		then
        	#echo $browser ist nicht installiert.
			continue
  		else
        	#echo Browser $browser ist installiert.
        	installed=$browser
			break;
  		fi
	done
	if  [ -n ${installed} ] 
	then
  		#echo "Es wurde ein Browser gefunden: $installed"
  		#$installed http://localhost/vsteno/php/introduction.php
		echo ${installed}
	else
		echo ""  		
		#echo "Es wurde kein Browser gefunden."
	fi
}

function WriteDatabaseVariables {
    # set these variables as global with default values    
    dbserver="localhost:3306"
    dbuser="root"
    dbpwd="test"
    dbname="vsteno"
    # get values from user
    echo "Enter database configuration: "
    echo "Server (default: $dbserver): "
    read input
    if [ -n "$input" ]
    then
        dbserver=$input
    fi
    echo "User (default: $dbuser): "
    read input
    if [ -n "$input" ]
    then
        dbuser=$input
    fi
    echo "Password (default: $dbpwd): "
    read -s input
    if [ -n "$input" ]
    then
        dbpwd=$input
    fi
    echo "Database name (default: $dbname): "
    read -s input
    if [ -n "$input" ]
    then
        dbname=$input
    fi    
    # now write variables to dbpw.php via sed
    sudo sed -i 's/db_servername = ".*\?";/db_servername = "'"$dbserver"'";/g' /var/www/html/vsteno/php/dbpw.php
    sudo sed -i 's/db_username = ".*\?";/db_username = "'"$dbuser"'";/g' /var/www/html/vsteno/php/dbpw.php
    sudo sed -i 's/db_password = ".*\?";/db_password = "'"$dbpwd"'";/g' /var/www/html/vsteno/php/dbpw.php
    sudo sed -i 's/db_dbname = ".*\?";/db_dbname = "'"$dbname"'";/g' /var/www/html/vsteno/php/dbpw.php
    # write version variables to constants.php via sed
    sudo sed -i 's/version_name = ".*\?";/version_name = "'"$version_name"'";/g' /var/www/html/vsteno/php/constants.php
    # version number not inside "" !
    sudo sed -i 's/version_number = .*\?;/version_number = '"$version_number"';/g' /var/www/html/vsteno/php/constants.php
    sudo sed -i 's/version_commit_id = ".*\?";/version_commit_id = "'"$commit"'";/g' /var/www/html/vsteno/php/constants.php
    sudo sed -i 's/version_date = ".*\?";/version_date = "'"$version_date"'";/g' /var/www/html/vsteno/php/constants.php
}

# main
echo "configure database"
# gksudo edit /var/www/html/vsteno/php/dbpw.php
# use vi instead ...
# sudo vi /var/www/html/vsteno/php/dbpw.php
# write it directly to dbpw.php via sed
WriteDatabaseVariables
echo "open webbrowser with php-db_init script ..."
# get local installed browser
local_browser=`GetBrowser`
#$local_browser http://localhost/vsteno/php/introduction.php
$local_browser http://localhost/vsteno/php/init_db.php
# echo "call php database init script ..."
# php -f /var/www/html/vsteno/php/init_db_cli.php
