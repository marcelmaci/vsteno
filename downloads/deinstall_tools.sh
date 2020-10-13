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
        printf "apache2 webserver, data and utilities will be removed."
        sudo apt-get --assume-yes remove apache2 apache2-data apache2-utils;;
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


# section hunspell checker in general
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
printf "\n\nChoose either to remove [0], or to retain [1]
hunspell-de-ch, the Swiss German dictionary.\n"
read DE_CH
case $DE_CH in
    0)
        printf "hunspell-de_ch, the Swiss German dictionary will be removed."
        sudo apt-get --assume-yes remove hunspell-de-ch ;;
    1)
        printf "You retain hunspell-de_ch, the Swiss German dictionary." ;;
esac

# section hunspell es
printf "\n\nChoose either to remove [0], or to retain [1]
hunspell-es, the Spanish dictionary.\n"
read ES
case $ES in
    0)
        printf "hunspell-es, the Spanish dictionary will be removed."
        sudo apt-get --assume-yes remove hunspell-es ;;
    1)
        printf "You retain hunspell-es, the Spanish dictionary." ;;
esac

# section hunspell fr
printf "\n\nChoose either to remove [0], or to retain [1]
hunspell-fr, the French French dictionary.\n"
read FR
case $FR in
    0)
        printf "hunspell-fr, the French French dictionary will be removed."
        sudo apt-get --assume-yes remove hunspell-fr ;;
    1)
        printf "You retain hunspell-fr, the French French dictionary." ;;
esac


# section espeak
printf "\n\nChoose either to remove [0], or to retain [1] espeak and its data.\n"
read ESPEAK
case $ESPEAK in
    0)
        printf "The espeak engine will be removed."
        sudo apt-get --assume-yes remove espeak espeak-data ;;
    1)
        printf "You retain the espeack engine as installed." ;;
esac


# section mysql
printf "\n\nChoose either to remove [0], or to retain [1] mysql-server,
mysql-client, and mysql-workbench.  An opt-out will affect all three
at once, thus ensure data relevant for you are already backed-up.\n"
read MYSQL
case $MYSQL in
    0)
        printf "mysql-server, mysql-client, and mysql-workbench will be removed."
        sudo apt-get --assume-yes remove mysql-common mysql-server* mysql-client* mysql-workbench*. ;;
    1)
        printf "You retain mysql-server, mysql-client, and mysql-workbench as installed." ;;
esac


# section mecab
printf "\n\nChoose either to remove [0], or to retain [1] Mecab, a
Japanese morphological analysis system, and its data.\n"
read MECAB
case $MECAB in
    0)
        printf "Mecab and its components will be removed."
        sudo apt-get --assume-yes remove mecab* libmecab* ;;
    1)
        printf "You retain Mecab and its components as installed." ;;
esac


# section git
printf "\n\nChoose either to remove [0], or to retain [1] git.  Note,
removing git will not remove the .git directories in any project 
previously managed with git.\n"
read GIT
case $GIT in
    0)
        printf "git will be removed from your computer."
        sudo apt-get --assume-yes remove git git-man ;;
    1)
        printf "You retain git as installed.\n" ;;
esac

# final word
#
printf "\nCheck all messages from the pakage manager.  Consider to use
the 'sudo apt autoremove' to complete the de-installation.  If there are
no errors you are done.\n"
