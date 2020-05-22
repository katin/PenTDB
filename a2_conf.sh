#!/bin/bash
# CONFIGURE APACHE2 for PENTDB
# Copy the apache config file for the PenTDB website into /etc/apache2/sites-available,
# configure it for the current user (e.g., kali), update ports.conf, and restart apache.
# All require sudo, but do not run this file as sudo -- that will put the wrong directory into the .conf file.
# 200518 KBI created
set -x
CONF_FILE="/etc/apache2/sites-available/pentdb.conf"
HOME_USER=$USER
# tell apache about our website
sudo cp a2_pentdb_template.conf $CONF_FILE
sudo sed -i "s/home_user/$USER/g" $CONF_FILE
# enable the site
sudo a2ensite pentdb
# set our site to localhost access only
sudo cp /etc/apache2/ports.conf /etc/apache2/ports.conf.bak
sudo sed -i 's/Listen 80/Listen 80\nListen 127.0.0.1:411/g' /etc/apache2/ports.conf
# restart apache for changes to take effect
sudo service apache2 restart
# add our hostname to local hosts file
sudo cp /etc/hosts /etc/hosts.bak
sed "/# The following/ i 127.0.0.1    pentdb.kali.local\n" /etc/hosts | sudo tee /etc/hosts
echo "apache and local host configuration completed."
