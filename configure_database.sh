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

# main
echo "configure database"
# gksudo edit /var/www/html/vsteno/php/dbpw.php
# use vi instead ...
vi /var/www/html/vsteno/php/dbpw.php
echo "open webbrowser with php-db_init script ..."
# get local installed browser
local_browser=`GetBrowser`
#$local_browser http://localhost/vsteno/php/introduction.php
$local_browser http://localhost/vsteno/php/init_db.php
# echo "call php database init script ..."
# php -f /var/www/html/vsteno/php/init_db_cli.php
