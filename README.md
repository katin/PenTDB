# PenTDB
Pentesting Tracker application with HTML Interface

Basically, a set of custom web forms and data displays that help track tests and results, IPs and ports under test, and vulns tried while pentesting.

Made for those who are wondering why we are spending gobs of brain cycles writing and re-reading testing progress in **text files** and **free-form notes**... when that's what computers are for! May also be good for training purposes; we'll be exploring that.

### Requirements
This is designed to be run on Kali Linux. Web server (e.g. apache) and database (e.g. MariaDB) are required, as is PHP.

### Installation
  1. Copy the files into the desired web root directory, and configure your apache to serve them.
  2. mysql> CREATE DATABASE pentdb;
  2. mysql> GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES ON pentdb.* to '<username>'@'localhost' IDENTIFIED BY '<password>';
  2. $ cp dru_db_settings-default.php dru_db_settings.php    # and put the database credentials in your settings file
  3. [optional] $ sudo vim /etc/hosts     # and enter a line for your preferred URL to access, e.g.  127.0.0.1  pentdb.local
  4. Browse to the site, e.g. http://pentdb.local  --  you should see the page with an error about missing data
  5. Create the tables in the database by browsing to http://pentdb.local/pentdb_init.php
  6. Application is ready for use.
 
 
