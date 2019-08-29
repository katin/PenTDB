-- MySQL dump 10.16  Distrib 10.1.38-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: pentdb
-- ------------------------------------------------------
-- Server version	10.1.38-MariaDB-0+deb9u1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `host`
--

DROP TABLE IF EXISTS `host`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `host` (
  `hid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `name` varchar(127) NOT NULL DEFAULT '',
  `platform` varchar(127) NOT NULL DEFAULT '',
  `os_version` varchar(255) NOT NULL DEFAULT '',
  `patch_version` varchar(255) NOT NULL DEFAULT '',
  `cpu_arch` varchar(64) NOT NULL DEFAULT '',
  `core_count` tinyint(4) NOT NULL DEFAULT '0',
  `service_pack` varchar(64) NOT NULL DEFAULT '0',
  `status` varchar(16) NOT NULL DEFAULT '',
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
  `watch_file2` varchar(64) NOT NULL DEFAULT '',
  `watch_file3` varchar(64) NOT NULL DEFAULT '',
  `notes` longtext,
  `wireshark` longtext,
  `proof` longtext,
  `loot` longtext,
  `lessons_learned` longtext,
  `flags` varchar(255) NOT NULL DEFAULT '',
  `points` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`hid`),
  KEY `main` (`session_id`,`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `host`
--

LOCK TABLES `host` WRITE;
/*!40000 ALTER TABLE `host` DISABLE KEYS */;
/*!40000 ALTER TABLE `host` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objective`
--

DROP TABLE IF EXISTS `objective`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `objective` (
  `oid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `service` varchar(127) NOT NULL DEFAULT '',
  `status` varchar(16) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `objective` longtext,
  `notes` longtext,
  `notes2` longtext,
  `notes3` longtext,
  `flags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid`),
  KEY `main` (`ip_address`,`port`,`service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objective`
--

LOCK TABLES `objective` WRITE;
/*!40000 ALTER TABLE `objective` DISABLE KEYS */;
/*!40000 ALTER TABLE `objective` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `porttest`
--

DROP TABLE IF EXISTS `porttest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `porttest` (
  `pitid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `rectype` varchar(16) NOT NULL DEFAULT '',
  `statustype` varchar(16) NOT NULL DEFAULT '',
  `service` varchar(127) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `info` longtext,
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
  `pass_depth` tinyint(4) NOT NULL DEFAULT '0',
  `order_weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pitid`),
  KEY `main` (`port`,`service`),
  KEY `auxiliary` (`rectype`,`pass_depth`,`order_weight`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `porttest`
--

LOCK TABLES `porttest` WRITE;
/*!40000 ALTER TABLE `porttest` DISABLE KEYS */;
INSERT INTO `porttest` VALUES (1,22,'TITLE','DEPTH','ssh','ssh port $port','','','','',0,0),(2,22,'EXAMINE','BINARY','ssh','Level 1 Credentials attempts: defaults, related, common','hydra -U user-file.txt -P password-file.txt -v $ip ssh','Check for success','','1',1,0),(3,22,'EXAMINE','DEPTH','ssh','Version exploit search (SSH and SSL)','...','Exclude and test exploits','1','2',0,0),(4,22,'EXAMINE','BINARY','ssh','Level 2 Credentials attempts: cewl compiled password attack','(cewl tool to scrape their website, hydra or medusa to try passwords)','Check for success','','1',3,0),(5,22,'EXAMINE','BINARY','ssh','Find passwords: /etc/shadow, SAM file, or other source','Possible to view files via other services or vulns?','Check for success','','1',4,0),(6,22,'EXAMINE','BINARY','ssh','Level 3 Credentials attempts: brute force hydra/medusa with rockyou.txt','..add cmd..','Check for success','','2',1,0),(8,53,'TITLE','DEPTH','dns','dns port $port','','','','',0,0),(9,53,'SCRIPT','DEPTH','dns','Lookup DNS server name','host -t cname $ip $ip','Get local 2nd-level domain, store in $domain','','1',1,0),(10,53,'SCAN','BINARY','dns','Attempt zone transfer','dig $domain axfr @$ip','try other zone transfer tools if no results','','1',2,0),(11,53,'SCAN','DEPTH','dns','Scan with dnsrecon','dnsrecon $domain','try other domain scan tools if poor results','','1',3,0),(12,53,'SCRIPT','BINARY','dns','Brute force reverse-ip lookups','revip $subnet-addr','write this script','','1',4,0),(13,53,'SCRIPT','DEPTH','dns','Brute force subdomains checks -- see Fierce tool.','Custom word lists from cewl?','Analyze results.','','2',1,0),(14,53,'TOOL','DEPTH','dns','fierce tool','specific options?','analyze results','','2',2,0),(15,80,'TITLE','DEPTH','http','http port $port','nmap -sV $ip -p$port','nmap -sV $ip -p$port | grep open | rev | cut -d\" \" -f-2 | rev','','0',0,0),(16,80,'SCAN','BINARY','http','Nikto scan','nikto -host $ip -port $port | tee nikto-scan-$myport.txt','Examine results and check CVEs','','1',0,0),(17,80,'EXAMINE','DEPTH','http','vuln search','searchsploit $banner; google $banner exploit; http://exploit-db.com $service $progressive_versions','Human disqualification and ranking','','1',1,0),(18,80,'EXAMINE','BINARY','http','Examine index page','http://$ip','Check for app, version, and source for vulns','','1',2,0),(19,80,'EXAMINE','BINARY','http','Examine robots.txt file','wget http://$ip/robots.txt','Examine for subdirectories','','1',3,0),(20,80,'EXAMINE','BINARY','http','Load site with hostname instead of IP','DNS hostname; add it to /etc/hosts; pull up page','Anything different?','','1',4,0),(21,80,'EXAMINE','DEPTH','http','Check for file lists in subdirectories','Browse to /','Listing open?','','1',4,0),(22,80,'SCAN','DEPTH','http','gobuster subdir brute search','gobuster -u http://$ip -w /usr/share/wordlists/dirbuster/directory-list-2.3-medium.txt','Browse any subdirectories found','','1',5,0),(23,80,'SCAN','DEPTH','http','dirbuster (GUI)','dirbuster','Browse any subdirectories found','','2',1,0),(24,80,'SCAN','DEPTH','http','nmap scripts','select (all?) and run nmap http scripts','Analyze results.','','2',2,0),(25,80,'SCAN','DEPTH','http','Metasploit scripts','select and run msf scripts and scans','Analyze results','','2',3,0),(30,80,'TITLE','DEPTH','webapp','Generic webapp pentesting','http://$ip','','','0',0,0),(31,80,'EXAMINE','DEPTH','webapp','Examine page source code','source://$ip','Look for versions, github or downloadble source code, notes and comments, vulnerable JS libraries, etc.','','1',1,0),(32,80,'EXAMINE','DEPTH','webapp','SQL Injection authentication bypass','872394\' or 1=1 LIMIT 1;#','Paste into username field of login form','','1',2,0),(33,80,'EXAMINE','BINARY','webapp','Level 1 Credentials attempts: defaults, related, common','hydra -U user-file.txt -P password-file.txt -v $ip ssh','Check for success','','1',3,0),(34,80,'EXAMINE','DEPTH','webapp','Burpsuite forms intercept & manipulate','burpsuite #(gui)','Try various methods','','2',1,0),(35,80,'TOOL','BINARY','webapp','SQLmap (disallowed on OSCP Exam)','sqlmap -r /root/Documents/Exercises/timeclock-post.txt -p \"username\" --dbms=mysql --risk 3 --level 5 --os-shell','For post forms, use Burpsuite to intercept a sample post to the web page with the sql injection point and save it out (e.g., timeclock-post.txt','','3',0,0),(36,80,'EXAMINE','DEPTH','webapp','Finding vulerabilities in PHP webapps (paper)','https://www.exploit-db.com/papers/12871','Try various techniques in the paper','','3',1,0),(37,80,'EXAMINE','DEPTH','webapp','The OWASP Periodic Table fo Webapp Vulnerabilities','https://www.owasp.org/index.php/OWASP_Periodic_Table_of_Vulnerabilities#Periodic_Table_of_Vulnerabilities','Try various approaches, research tools, and techniques','','3',2,0),(45,80,'TITLE','DEPTH','login1','Generic Login pentesting','http://$ip','','','0',0,0),(46,80,'EXAMINE','DEPTH','login1','SQL Injection authentication bypass','872394\' or 1=1 LIMIT 1;#','Paste into username field of login form','','1',1,0),(47,80,'EXAMINE','BINARY','login1','Level 1 Credentials attempts: defaults, related, common','hydra -U user-file.txt -P password-file.txt -v $ip ssh','Check for success','','1',2,0),(48,80,'EXAMINE','DEPTH','login1','Examine page source code','source://$ip','Look for versions, github or downloadble source code, notes and comments, vulnerable JS libraries, etc.','','1',3,0),(49,80,'EXAMINE','BINARY','login1','Level 1 Credentials attempts: defaults, related, common','hydra -U user-file.txt -P password-file.txt -v $ip ssh','Check for success','','1',1,0),(50,80,'EXAMINE','BINARY','login1','Level 2 Credentials search: emails, sysfiles, note files, page text','hydra -U user-file.txt -P password-file.txt -v $ip ssh','Check for success','','2',1,0),(51,80,'EXAMINE','DEPTH','login1','Burpsuite forms intercept & manipulate','burpsuite #(gui)','Try various methods','','2',2,0),(52,80,'TOOL','BINARY','login1','Level 3 Credentials attempts: build wordlists','cewl (build list cmd) && hydra run list','Check for success','','2',3,0),(53,80,'TOOL','BINARY','login1','SQLmap (disallowed on OSCP Exam)','sqlmap -r /root/Documents/Exercises/timeclock-post.txt -p \"username\" --dbms=mysql --risk 3 --level 5 --os-shell','For post forms, use Burpsuite to intercept a sample post to the web page with the sql injection point and save it out (e.g., timeclock-post.txt','','3',1,0),(54,80,'EXAMINE','DEPTH','login1','Finding vulerabilities in PHP webapps (paper)','https://www.exploit-db.com/papers/12871','Try various techniques in the paper','','3',1,0),(55,80,'EXAMINE','DEPTH','login1','The OWASP Periodic Table fo Webapp Vulnerabilities','https://www.owasp.org/index.php/OWASP_Periodic_Table_of_Vulnerabilities#Periodic_Table_of_Vulnerabilities','Try various approaches, research tools, and techniques','','3',2,0);
/*!40000 ALTER TABLE `porttest` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `data_path` varchar(255) NOT NULL DEFAULT '',
  `cmd_path` varchar(255) NOT NULL DEFAULT '',
  `api_url` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY (`sid`),
  KEY `main` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `testinstance`
--

DROP TABLE IF EXISTS `testinstance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `testinstance` (
  `irid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `pass_depth` tinyint(4) NOT NULL DEFAULT '0',
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `service` varchar(127) NOT NULL DEFAULT '',
  `banner` varchar(127) NOT NULL DEFAULT '',
  `rectype` varchar(16) NOT NULL DEFAULT '',
  `statustype` varchar(16) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
  `info` longtext,
  `status` varchar(16) NOT NULL DEFAULT '',
  `order_weight` tinyint(4) NOT NULL DEFAULT '0',
  `raw_result` longtext,
  `discovered` longtext,
  `flags` varchar(255) NOT NULL DEFAULT '',
  `notes` longtext,
  PRIMARY KEY (`irid`),
  KEY `main` (`session_id`,`ip_address`,`service`),
  KEY `auxiliary` (`port`,`pass_depth`,`order_weight`,`status`,`flags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `testinstance`
--

LOCK TABLES `testinstance` WRITE;
/*!40000 ALTER TABLE `testinstance` DISABLE KEYS */;
/*!40000 ALTER TABLE `testinstance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vuln`
--

DROP TABLE IF EXISTS `vuln`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vuln` (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `service` varchar(127) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `exploit_type` varchar(32) NOT NULL DEFAULT '',
  `attack_type` varchar(32) NOT NULL DEFAULT '',
  `platform` varchar(127) NOT NULL DEFAULT '',
  `edb_verified` tinyint(4) NOT NULL DEFAULT '0',
  `target_version_match` tinyint(4) NOT NULL DEFAULT '0',
  `tested_version_match` tinyint(4) NOT NULL DEFAULT '0',
  `exploit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `exploit_engine` varchar(32) NOT NULL DEFAULT '',
  `credentials_req` tinyint(4) NOT NULL DEFAULT '0',
  `cpu_arch` varchar(64) NOT NULL DEFAULT '',
  `core_count` tinyint(4) NOT NULL DEFAULT '0',
  `service_pack_match` int(11) NOT NULL DEFAULT '0',
  `has_code` int(11) NOT NULL DEFAULT '0',
  `is_poc` int(11) NOT NULL DEFAULT '0',
  `code_language` varchar(16) NOT NULL DEFAULT '',
  `status` varchar(16) NOT NULL DEFAULT '',
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
  `order_weight` tinyint(4) NOT NULL DEFAULT '0',
  `raw_result` longtext,
  `discovered` longtext,
  `flags` varchar(255) NOT NULL DEFAULT '',
  `notes` longtext,
  PRIMARY KEY (`vid`),
  KEY `main` (`session_id`,`ip_address`,`service`),
  KEY `auxiliary` (`port`,`order_weight`,`status`,`flags`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vuln`
--

LOCK TABLES `vuln` WRITE;
/*!40000 ALTER TABLE `vuln` DISABLE KEYS */;
/*!40000 ALTER TABLE `vuln` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2019-08-20 15:56:31
