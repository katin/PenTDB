# PenTDB
Pentesting Tracker application with HTML Interface

**NEW:** Installation Walk-thru video: https://www.youtube.com/watch?v=N9m67Qk8Af4

This is set of custom web forms and data displays that help track tests and results, IPs and ports under test, and vulns processed while pentesting. **Basically, this is a replacement for apps like KeepNote, and specialized for pentesting.**

PenTDB is for those people who don't want to spend brain cycles organizing and re-reading large amounts of detailed data about pentesting progress for multiple hosts under time pressure in **text files** and **free-form notes**... when tracking data bits quickly are what computers are for! Now you can apply that brain juice to figuring out the puzzles and problems instead. PenTDB may also be good for training purposes.

You can run PenTDB on your host machine, or on your Kali machine. If running on Kali, I recommend using an odd port, for example 411, to keep common web ports open for pentesting purposes. If you wish to use the handy WatchFiles feature, PenTDB **must** be running on your Kali machine.

### Requirements
This is designed to be run on Kali Linux. Web server (e.g. apache) and database (e.g. MariaDB) are required, as is PHP.
If you want to use the exploit-auto-populate feature, you'll need curl for PHP, e.g., something like:
   ```
   $ apt install php7.3-curl
   ```

### Installation
**NOTE: DO NOT expose this web app to the Internet! It is insecure and for local use only. DO NOT INSTALL ON A WEB HOSTING SERVICE OR CLOUD SERVER.** *You have been warned.*
You can keep apache2 listening ONLY to the local host by changing the /etc/apache2/ports.conf file to have, for example:

```
Listen :80
Listen 127.0.0.1:411
```
You can find an example apache 2.4 site.conf file in the file sample_a2_conf.txt, included in this repo.

1. Create a set of directories:
```
$ mkdir PenTDB
$ mkdir PenTDB/logs
$ mkdir PenTDB/exploit-db-pages
```
2. Clone the PenTDB repository files into the PenTDB directory and rename it to "html":
```
$ cd PenTDB
$ git clone https://github.com/katin/PenTDB.git
$ mv PenTDB html
```
3. Configure your webserver to serve the files at PenTDB/html
  * there is an example apache 2.4 .conf file included: sample_a2_conf.txt
```
$ cd /etc/apache2/sites-available
$ cp /your-path/PenTDB/html/sample_a2_conf.txt pentdb.conf
```
  * edit /etc/apache2/sites-available/pentdb.conf, and set the paths to match your paths
```
$ a2ensite pentdb.conf
```
  * edit /etc/apache2/ports.conf   to include   Listen 127.0.0.1:411
  * edit /etc/hosts to include:      127.0.0.1    pentdb.kali.local
```
  * $ service apache2 start
```
or:  service apache2 restart     # if it was already running

4. Create, configure, and load the database
  * Launch mysql if needed and log in 
```
$ service mysql start      # no need to restart if already running
$ mysql -uroot
mysql> CREATE DATABASE pentdb;
mysql> GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, LOCK TABLES, CREATE TEMPORARY TABLES ON pentdb.* to '<username>'@'localhost' IDENTIFIED BY '<password>';
mysql> FLUSH PRIVILEGES;
mysql> exit
$ mysql -uroot pentdb < /your-path/PenTDB/html/pentdb_db_init.sql
```
5. Create and customize settings file
```
$ cd /root/.../PenTDB/html/dru_dblib-v1.0
$ cp dru_db_settings-default.php dru_db_settings.php 
```
  *edit dru_db_settings.php and add your database credentials and db name
  * Optional: set your preferred data path in PenTDB/html/pentdb-config.php
6. Application is ready for use; browse to the site, e.g. http://pentdb.kali.local:411
 
### Tuning
 For faster creation of PenTDB sessions, you can set the values in the pentdb-config.php file to match your personal directory structure. 
 
### Troubleshooting
**Symptom: WATCH FILES aren't working**
Make sure the directory holding your data files is accessible by the web server (typically user www-data), e.g.:
```
    $ chown -R :www-data /root/Documents/hackthebox
```
### Missing Features

Be advised that this version has no method of taking screenshots, storing screenshots, or viewing screenshots. You'll probably want to figure out how you are going to manage and organize screenshots to make them easy for yourself.

### Road Map
Adding these features is on the road map for continued development:
* export/import of test templates and service test sets, so the community can share testing sets
* creds tracking and easy creation of files for using found credentials with tools like hydra
* vuln scoring to rank found exploits from best-match to least

### Caveats
**Rapid hack**

This tool was thrown together quickly as an experiment, and is NOT a good example of PHP coding nor good database design. It has proven useful enough to justify development of a Version 2, which will include a clean DB design from scratch and will be written in Python as an exercise and learning project.

**Insecure**

It is also quite insecure. So insecure, in fact, that it might serve as the basis of an exercise for web app hacking practice and teaching secure (insecure?) code development. This is a tool for tracking tests; it isn't a releasable app. Don't run this on the cloud or anywhere that is exposed to the Internet... you will be pwnd, possibly by 8th graders that have watched a few YouTube videos.

**Length Limited Notes and a Solution**

Due to the use of GET mode in HTTP -- which can be handy in development and in "power user" mode to see what's going on and tweak field values and commands right in the URL -- the maximum length of any post can be as small as 2K depending on browser and server config. So keep your note field text concise; don't paste long results (like from LinuxPrivChecker) into the notes fields. 

Instead, **use the watch_file field:** save the long results as a text file in your testing data area, and then put the filename in the watch_file field of the test. You can then fold-open the entire file right on the web page.
