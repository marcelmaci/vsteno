#!/bin/bash
printf "Welcome to the VSTENO tools de-installation script.

Note: This only works on DEBIAN-like systems with apt-get package manager.

The installation of vsteno equally installed a number of tools.  This
script allows you to decide which of them to remove [0], or retain [1]
on your computer.  The following questions are mutually independent
from each other since some the tools (e.g., git) may be used for other
projects than vsteno, too.  Pressing 'Ctrl + C' will stop the script.

It is your responsibility to backup relevant data.\n"

# section apache2
printf "\nChoose either to remove [0], or to retain [1] apache2 webserver\n"
read APACHE

case $APACHE in
    0)
        printf "apache2 webserver will be removed."
        sudo apt-get --assume-yes remove apache2 ;;
    1)
        printf "You retain apache2 webserver as currently installed." ;;
esac


# section php
printf "\n\nChoose either to remove [0], or to retain [1] php\n"
read PHP
case $PHP in
    0)
        printf "php will be removed."
        sudo apt-get --assume-yes remove php php-common libapache2-mod-php php-cli php-mysql php-mbstring ;;
    1) printf "You retain php as currently installed." ;;
esac


# section hunspell in general
printf "\n\nChoose either to remove [0], or to retain [1] the basic
hunspell spell check engine. This renders all hunspell dictionaries
installed (see later questions) inaccessible.\n"
read HUNSPELL
case $HUNSPELL in
    0)
        printf "hunspell spell check engine will be removed."
        sudo apt-get --assume-yes remove hunspell ;;
    1)
        printf "You retain the hunspell spell check engine as installed." ;;
esac

# section hunspell, de_CH
printf "\n\nChoose either to remove [0], or to retain [1] hunspell's
de_ch / Swiss German dictionary.\n"
read DE_CH
case $DE_CH in
    0)
        printf "Hunspell dictionary de_CH / Swiss German will be removed."
        sudo apt-get --assume-yes remove hunspell-de-ch ;;
    1)
        printf "You retain the hunspell dictionary de_CH / Swiss German." ;;
esac

# section hunspell es
printf "\n\nChoose either to remove [0], or to retain [1] hunspell's
es / Spanish dictionary.\n"
read ES
case $ES in
    0)
        printf "Hunspell dictionary es / Spanish will be removed."
        sudo apt-get --assume-yes remove hunspell-es ;;
    1)
        printf "You retain the hunspell dictionary es / Spanish." ;;
esac

# section hunspell fr
printf "\n\nChoose either to remove [0], or to retain [1] hunspell's
fr / French dictionary.\n"
read FR
case $FR in
    0)
        printf "Hunspell dictionary fr / French will be removed."
        sudo apt-get --assume-yes remove hunspell-fr ;;
    1)
        printf "You retain the hunspell dictionary fr / French." ;;
esac

# section hunspell it
printf "\n\nChoose either to remove [0], or to retain [1] hunspell's
it / Italian dictionary.\n"
read IT
case $IT in
    0)
        printf "Hunspell dictionary it / Italian will be removed."
        sudo apt-get --assume-yes remove hunspell-fr ;;
    1)
        printf "You retain the hunspell dictionary it / Italian." ;;
esac

# section espeak
printf "\n\nChoose either to remove [0], or to retain [1] espeak.\n"
read ESPEAK
case $ESPEAK in
    0)
        printf "The espeak engine will be removed."
        sudo apt-get --assume-yes remove espeak ;;
    1)
        printf "You retain the espeack engine." ;;
esac


# section mysql
printf "\n\nChoose either to remove [0], or to retain [1] mysql-server,
mysql-client, and mysql-workbench.  An opt-out will affect all three
at once, thus ensure data relevant for you are already backed-up.\n"
read MYSQL
case $MYSQL in
    0)
        printf "mysql-server, mysql-client, and mysql-workbench will be removed."
        sudo apt-get --assume-yes remove mysql-server mysql-client mysql-workbench. ;;
    1)
        printf "You retain mysql-server, mysql-client, and mysql-workbench as installed." ;;
esac


# section git
printf "\n\nChoose either to remove [0], or to retain [1] git.  A remove
of git will not remove the .git directories in any project previously
monitored by git.\n"
read GIT
case $GIT in
    0)
        printf "git will be removed from your computer."
        sudo apt-get --assume-yes remove git ;;
    1)
        printf "You retain git as installed.\n" ;;
esac

# section git
printf "\n\nChoose either to remove [0], or to retain [1] VSTENO.  This will remove the
entire php-code in /var/www/html/vsteno\n"
read VSTENO
case $VSTENO in
    0)
        printf "VSTENO will be removed from your computer."
        sudo rm -r /var/www/html/vsteno ;;
    1)
        printf "You retain VSTENO as installed.\n" ;;
esac

# final word
#
printf "\nCheck all messages from the pakage manager.  Consider to use
the 'sudo apt autoremove' to complete the de-installation.  If there are
no errors you are done.\n"

printf "\nIf you want to be sure to delete ALL databases and data, you can use
the script ./remove_database.sh (be careful with that script: ALL MEANS ALL!!!)\n"
