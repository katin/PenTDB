# PenTDB
Pentesting Tracker application with HTML Interface

Basically, a set of custom web forms and data displays that help track tests and results, IPs and ports under test, and vulns tried while pentesting.

Made for those who are wondering why we are spending gobs of brain cycles organizing and re-reading large amounts of detailed data about pentesting progress for multiple hosts in **text files** and **free-form notes**... when that's what computers are for! Now you can apply that brain juice to figuring out the puzzles and problems instead. May also be good for training purposes; we'll be exploring that.

### Requirements
This is designed to be run on Kali Linux. Web server (e.g. apache) and database (e.g. MariaDB) are required, as is PHP.

### Installation
**NOTE: DO NOT expose this web app to the Internet! It is insecure and for local use only. DO NOT INSTALL ON A WEB HOSTING SERVICE OR CLOUD SERVER.** *You have been warned.*
  1. Copy the files into the desired web root directory, and configure your webserver to serve them.
  2. mysql> CREATE DATABASE pentdb;
  2. mysql> GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES ON pentdb.* to '\<username\>'@'localhost' IDENTIFIED BY '\<password\>';
  2. $ cp dru_db_settings-default.php dru_db_settings.php    # and put the database credentials in your settings file
  3. [optional] $ sudo vim /etc/hosts     # and enter a line for your preferred URL to access, e.g.  127.0.0.1  pentdb.local
  3. Create the tables in the database by browsing to http://pentdb.local/pentdb_init.php
  4. $ cd <webroot> && chmod u+x load-templates.sh && ./load-templates.sh
  6. Application is ready for use; browse to the site, e.g. http://pentdb.local
 
 
