#!/bin/bash
# Configure and import the mysql database for PenTDB
# 200521 KBI created

# variables and dependencies
DBLIB_LOC="dru_dblib-v1.0/"
DB_SETTINGS_FILE="dru_db_settings"
DB_SETTINGS_FULL="html/$DBLIB_LOC$DB_SETTINGS_FILE"

# create our user, password, and db name
USER="u$RANDOM"
PWD=$(openssl rand -base64 8)
DBNAME="pentdb_$RANDOM"

# create and import the database
# (currently, there is no check to make sure mysql is running)
sudo mysql -e "create database $DBNAME;"
sudo mysql -e "GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES ON $DBNAME.* TO '$USER'@localhost IDENTIFIED BY '$PWD';"
if [ -e "./pentdb_preferred.sql" ]
then
  sudo mysql $DBNAME < ./pentdb_preferred.sql
else
  sudo mysql $DBNAME < pentdb_db_init.sql
fi

# update the settings file
# backup the current settings file if one exists
if [ -e "$DB_SETTINGS_FULL.php" ]
then
  NOW=$(date +"%m_%d_%Y")
  mv $DB_SETTINGS_FULL.php $DB_SETTINGS_FULL-$NOW.php
fi
# echo "cmd: $DB_SETTINGS_FULL-default.php $DB_SETTINGS_FULL.php"
cp $DB_SETTINGS_FULL-default.php $DB_SETTINGS_FULL.php
echo "\$db_url = 'mysqli://$USER:$PWD@localhost/$DBNAME';" >> $DB_SETTINGS_FULL.php

