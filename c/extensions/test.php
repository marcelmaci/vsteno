<html>
<head>
</head>
<body>

 <?php

// Add the following line to php.ini (e.g. in /etc/php/7.0/apache2):
//
// extension=/var/www/html/git/c/extensions/vsteno_native.so
//
// (Adapt path if necessary.)
// 
// Compile source in /var/www/html/git/c/extensions/:
//
// make clean
// make
//
// Restart apache2 webserver:
//
// sudo service apache2 restart
//
// The extension is ready to be used from php like in the following example


$hello = HelloWorld("FROM PHP");
echo "$hello<br>";		    

?>

</body>
