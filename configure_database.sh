#!/bin/bash
echo "configure database"
# gksudo edit /var/www/html/vsteno/php/dbpw.php
# use vi instead ...
vi /var/www/html/vsteno/php/dbpw.php
echo "open webbrowser with php-db_init script ..."
abrowser http://localhost/vsteno/php/init_db.php