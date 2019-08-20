# PenTDB
Pentesting Tracker application with HTML Interface

This is set of custom web forms and data displays that help track tests and results, IPs and ports under test, and vulns processed while pentesting. **Basically, this is a replacement for apps like KeepNote, and specialized for pentesting.**

PenTDB is for those people who don't want to spend brain cycles organizing and re-reading large amounts of detailed data about pentesting progress for multiple hosts under time pressure in **text files** and **free-form notes**... when tracking data bits quickly are what computers are for! Now you can apply that brain juice to figuring out the puzzles and problems instead. PenTDB may also be good for training purposes.

You can run PenTDB on your host machine, or on your Kali machine. If running on Kali, I recommend using an odd port, for example 411, to keep common web ports open for pentesting purposes. If you wish to use the handy WatchFiles feature, PenTDB must be running on your Kali machine.

### Requirements
This is designed to be run on Kali Linux. Web server (e.g. apache) and database (e.g. MariaDB) are required, as is PHP.

### Installation
**NOTE: DO NOT expose this web app to the Internet! It is insecure and for local use only. DO NOT INSTALL ON A WEB HOSTING SERVICE OR CLOUD SERVER.** *You have been warned.*
You can keep apache2 listening ONLY to the local host by changing the /etc/apache2/ports.conf file to have, for example:

```
Listen :80
Listen 127.0.0.1:411

```
You can find an example apache 2.4 site.conf file in the file sample_a2_conf.txt, included in this repo.

  1. Copy the files into the desired web root directory, and configure your webserver to serve them.
  2. mysql> CREATE DATABASE pentdb;
  2. mysql> GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES ON pentdb.* to '\<username\>'@'localhost' IDENTIFIED BY '\<password\>';
  2. mysql> FLUSH PRIVILEGES;
  2. $ sudo mysql -uroot pentdb < pentdb_db_init.sql
  2. $ cp dru_db_settings-default.php dru_db_settings.php    # and put the database credentials in your settings file
  3. [optional] $ sudo vim /etc/hosts     # and enter a line for your preferred URL to access, e.g.  127.0.0.1  pentdb.local
  6. Application is ready for use; browse to the site, e.g. http://pentdb.local:411
 
 ### Tuning
 For faster creation of PenTDB sessions, you can set the values in the pentdb-config.php file to match your personal directory structure. 
 
 ### Caveats
**Rapid hack**

This tool was thrown together quickly as an experiment, and is NOT a good example of PHP coding nor good database design. It has proven useful enough to justify development of a Version 2, which will include a clean DB design from scratch and will be written in Python as an exercise and learning project.

**Insecure**

It is also quite insecure. So insecure, in fact, that it might serve as the basis of an exercise for web app hacking practice and teaching secure (insecure?) code development. This is a tool for tracking tests; it isn't a releasable app. Don't run this on the cloud or anywhere that is exposed to the Internet... you will be pwnd, possibly by 8th graders that have watched a few YouTube videos.

**Length Limited Notes and a Solution**

Due to the use of GET mode in HTTP -- which can be handy in development and in "power user" mode to see what's going on and tweak field values and commands right in the URL -- the maximum length of any post can be as small as 2K depending on browser and server config. So keep your note field text concise; don't paste long results (like from LinuxPrivChecker) into the notes fields. 

Instead, **use the watch_file field:** save the long results as a text file in your testing data area, and then put the filename in the watch_file field of the test. You can then fold-open the entire file right on the web page.
