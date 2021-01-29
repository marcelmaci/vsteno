echo "THIS SCRIPTS DELETES ALL (AND ALL MEANS ALL!) YOUR DATABASES AND DATA!!!"
echo "PROCEED ONLY IF THIS IS WHAT YOU WANT TO DO! (HIT ENTER)"
echo "OTHERWHISE HIT CTRL-C NOW!"
echo "YOU'VE BEEN WARNED ... ! :)"
echo "Hit enter to continue ... "
read -s input
echo "... ok, starting bulldozer ... :)"
# stop server
sudo service mysql stop
# purge MySQL, databases, and configurations
sudo apt-get purge mysql-server mysql-common mysql-server-core-* mysql-client-core-*
# remove any additional database files:
sudo rm -rf /var/lib/mysql/
# remove configuration folder
sudo rm -rf /etc/mysql/
# remove logs
sudo rm -rf /var/log/mysql
# delete the user-generated during installation
sudo deluser --remove-home mysql
# delete usergroup
sudo delgroup mysql
