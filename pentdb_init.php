#!/usr/bin/php
<?php
/*
 * Pen-db_init.php
 *
 * Create the database tables needed for PenTracker
 *
*/
// 190512 KBI copied office_test01.php and customized for PenTDB.

global $dru_db_version;

$dru_db_version = 'dru_dblib-v1.0';
require_once $dru_db_version.'/dru_db_settings.php';
require_once $dru_db_version.'/dru_db_glue.php';
require_once $dru_db_version.'/database.inc';
require_once $dru_db_version.'/database.mysqli.inc';
require_once $dru_db_version.'/dru_db_startup.php';

echo '<div>PenTracker Database Initialization</div>';


/* LIST OF TABLES

   Version 1 of the PenTracker has these tables:

   > host
   > objective
   > porttest
   > sessions
   > testinstance
   > vuln

*/


echo "<div>Creating Table: host";

/* SQL statement version:

CREATE TABLE `host` (
  `hid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `name` varchar(127) NOT NULL DEFAULT '',
  `platform` varchar(32) NOT NULL DEFAULT '',
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
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`hid`),
  KEY `main` (`session_id`,`ip_address`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8 

*/

$schema['host'] = array(
  'description' => 'PenTesting Host (machine/ip)',
  'fields' => array(
    'hid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique host record ID.',
    ),
    'created' => array(
      'mysql_type' => 'timestamp',
      'not null' => TRUE,
      'description' => 'Record creation time',
    ),
    'session_id' => array(
      'type' => 'varchar',
      'length' => 96,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Testing session ID',
    ),
    'ip_address' => array(
      'type' => 'varchar',
      'length' => 39,
      'not null' => TRUE,
      'default' => '',
      'description' => 'IP address of host (IPv4 15 chars or IPv6 39 chars)',
    ),
    'name' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Host name',
    ),
    'platform' => array(
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Platform or OS, e.g. Windows 7, Ununtu 16.04, etc.',
    ),
    'os_version' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Operating system version',
    ),
    'patch_version' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Patch version(s)',
    ),
    'cpu_arch' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'CPU architecture, e.g. 32-bit, 64-bit.',
    ),
    'core_count' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
      'description' => 'The specific number of cpu cores',
    ),
    'service_pack' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '0',
      'description' => 'Service pack version or ID',
    ),
    'status' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Current status of this host: NEW, UNKNOWN, SCANNED, IN-PROGRESS, STANDBY, or PWNED',
    ),
    'cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'process_result_cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or script to run to process the raw result',
    ),
    'watch_file' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Log or status file to display if present.',
    ),
    'watch_file2' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Log or status file to display if present.',
    ),
    'watch_file3' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Log or status file to display if present.',
    ),
    'notes' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Notes on this host',
    ),
    'wireshark' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Seen by wireshark',
    ),
    'proof' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'ifconfig, flags, etc.',
    ),
    'loot' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => '/etc/passwd, files, passwords, keys, emails, info, etc.',
    ),
     'lessons_learned' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Summary of new discernment, method, watch-for, etc.',
    ),
   'flags' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'port' => array(
      'type' => 'int',
      'unsigned' => FALSE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The (common) port number to be tested',
    ),
  ),
  'primary key' => array('hid'),
  'indexes' => array(
    'main' => array('session_id','ip_address'),
  ),
);


if (!db_table_exists('host')) {
  db_create_table($my_result, 'host', $schema['host']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue. (host)</div>');
  }
} else {
  echo "<div>Table host already exists; no action performed.</div>\n";
}



/* mysql create statement for sessions:

CREATE TABLE `sessions` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `data_path` varchar(255) NOT NULL DEFAULT '',
  `api_url` varchar(127) NOT NULL DEFAULT '',
  PRIMARY KEY (`sid`),
  KEY `main` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

*/

echo "<div>Creating Table: sessions";

$schema['sessions'] = array(
  'description' => 'PenTesting sessions',
  'fields' => array(
    'sid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique item ID.',
    ),
    'created' => array(
      'mysql_type' => 'timestamp',
      'not null' => TRUE,
      'description' => 'Record creation time',
    ),
    'session_id' => array(
      'type' => 'varchar',
      'length' => 96,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Session ID or handle',
    ),
    'data_path' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Data path for tanks and data on local machine',
    ),
    'cmd_path' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'URL for local command server API (commonly http://127.0.0.1:8888)',
    ),
    'api_url' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'URL for local command server API (commonly http://127.0.0.1:8888)',
    ),
  ),
  'primary key' => array('sid'),
  'indexes' => array(
    'main' => array('session_id'),
  ),
);

if (!db_table_exists('sessions')) {
  db_create_table($my_result, 'sessions', $schema['sessions']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue.</div>');
  }
} else {
  echo "<div>Table sessions already exists; no action performed.</div>\n";
}



echo "<div>Creating Table: porttest";

/* SQL statement version:

CREATE TABLE `porttest` (
  `pitid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `rectype` varchar(16) NOT NULL DEFAULT '',
  `statustype` varchar(16) NOT NULL DEFAULT '',
  `service` varchar(127) NOT NULL DEFAULT '',
  `title` varchar(127) NOT NULL DEFAULT '',
  `info` longtext,
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
  `pass_depth` tinyint(4) NOT NULL DEFAULT '0',
  `order_weight` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pitid`),
  KEY `main` (`port`,`service`),
  KEY `auxiliary` (`rectype`,`pass_depth`,`order_weight`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

*/

$schema['porttest'] = array(
  'description' => 'PenTesting item template',
  'fields' => array(
    'pitid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique item ID.',
    ),
    'port' => array(
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The (common) port number to be tested',
    ),
    'rectype' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item type: title, scan, tool, script, or examine',
    ),
    'statustype' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Status type: binary, depth, or none',
    ),
    'service' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The (common) service name.',
    ),
    'title' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'info' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Information, description, or tips for the test, tool, or process.',
    ),
    'cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'process_result_cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'watch_file' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Log or status file to display if present.',
    ),
    'pass_depth' => array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'size' => 'tiny',
      'description' => 'Pass depth for this item: generally 1, 2, or 3.',
    ),
    'order_weight' => array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'size' => 'tiny',
      'description' => 'Order (weight) in which to perform this test relative to others in this pass depth.',
    ),
  ),
  'primary key' => array('pitid'),
  'indexes' => array(
    'main' => array('port','service'),
    'auxiliary' => array('rectype','pass_depth',"order_weight"),
  ),
);


if (!db_table_exists('porttest')) {
  db_create_table($my_result, 'porttest', $schema['porttest']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue.</div>');
  }
} else {
  echo "<div>Table porttest already exists; no action performed.</div>\n";
}



echo "<div>Creating Table: testinstance";

/* SQL statement version:

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
  `title` varchar(127) NOT NULL DEFAULT '',
  `cmd` longtext,
  `process_result_cmd` longtext,
  `watch_file` varchar(64) NOT NULL DEFAULT '',
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

*/

$schema['testinstance'] = array(
  'description' => 'PenTesting item instance',
  'fields' => array(
    'irid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique item ID.',
    ),
    'created' => array(
      'mysql_type' => 'timestamp',
      'not null' => TRUE,
      'description' => 'Record creation time',
    ),
    'session_id' => array(
      'type' => 'varchar',
      'length' => 96,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Testing session ID',
    ),
    'ip_address' => array(
      'type' => 'varchar',
      'length' => 39,
      'not null' => TRUE,
      'default' => '',
      'description' => 'IP address of target (IPv4 15 chars or IPv6 39 chars)',
    ),
    'pass_depth' => array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'size' => 'tiny',
      'description' => 'Pass depth for this item: generally 1, 2, or 3.',
    ),
   'port' => array(
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The (common) port number to be tested',
    ),
    'service' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The (common) service name.',
    ),
     'banner' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The (common) service name.',
    ),
    'rectype' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item type: title, scan, tool, script, or examine',
    ),
    'statustype' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Status type: binary, depth, or none',
    ),
    'title' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'process_result_cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or script to run to process the raw result',
    ),
    'watch_file' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Log or status file to display if present.',
    ),
    'status' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Current status of this test: binary, depth, or blank for untested',
    ),
    'order_weight' => array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'size' => 'tiny',
      'description' => 'Order (weight) in which to perform this test relative to others in this pass depth.',
    ),
    'raw_result' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Raw output from the test script or command',
    ),
    'discovered' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Discovery results (processed) that chain to another test or exploit',
    ),
    'flags' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'notes' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Notes (human or processing script generated).',
    ),
  ),
  'primary key' => array('irid'),
  'indexes' => array(
    'main' => array('session_id','ip_address','service'),
    'auxiliary' => array('port','pass_depth','order_weight','status','flags'),
  ),
);


if (!db_table_exists('testinstance')) {
  db_create_table($my_result, 'testinstance', $schema['testinstance']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue.</div>');
  }
} else {
  echo "<div>Table testinstance already exists; no action performed.</div>\n";
}



echo "<div>Creating Table: vuln";

/* SQL statement version:

CREATE TABLE `vuln` (
  `vid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `service` varchar(127) NOT NULL DEFAULT '',
  `title` varchar(127) NOT NULL DEFAULT '',
  `url` varchar(255) NOT NULL DEFAULT '',
  `exploit_type` varchar(32) NOT NULL DEFAULT '',
  `attack_type` varchar(32) NOT NULL DEFAULT '',
  `platform` varchar(32) NOT NULL DEFAULT '',
  `edb_verified` tinyint(4) NOT NULL DEFAULT '0',
  `target_version_match` tinyint(4) NOT NULL DEFAULT '0',
  `tested_version_match` tinyint(4) NOT NULL DEFAULT '0',
  `exploit_date` datetime NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 

*/


$schema['vuln'] = array(
  'description' => 'PenTesting vulnerability-exploit test',
  'fields' => array(
    'vid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique item ID.',
    ),
    'created' => array(
      'mysql_type' => 'timestamp',
      'not null' => TRUE,
      'description' => 'Record creation time',
    ),
    'session_id' => array(
      'type' => 'varchar',
      'length' => 96,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Testing session ID',
    ),
    'ip_address' => array(
      'type' => 'varchar',
      'length' => 39,
      'not null' => TRUE,
      'default' => '',
      'description' => 'IP address of target (IPv4 15 chars or IPv6 39 chars)',
    ),
   'port' => array(
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The (common) port number to be tested',
    ),
    'service' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The (common) service name.',
    ),
    'title' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'url' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'URL of the vuln or exploit',
    ),
    'exploit_type' => array(
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Exploit type, e.g. local or remote.',
    ),
    'attack_type' => array(
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Attack type, e.g. DoS, or from the ATT&CK matrix',
    ),
    'platform' => array(
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Platform, e.g. Windows 7, Ununtu 16.04, etc.',
    ),
    'edb_verified' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
      'description' => 'Has the exploit been verified on exploit-db.com?',
    ),
    'target_version_match' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
      'description' => 'Does the exploit target version match our target version?',
    ),
    'tested_version_match' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
      'description' => 'Has the exploit been tested on our target version?',
    ),
    'exploit_date' => array(
      'mysql_type' => 'datetime',
      'not null' => TRUE,
      'description' => 'Date of the exploit - does it match out target sw era?',
    ),
    'exploit_engine' => array(
      'type' => 'varchar',
      'length' => 32,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Engine of exploit, e.g. Metasploit, manual, or other tool.',
    ),
    'credentials_req' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
      'description' => 'Does this exploit require login credentials?',
    ),
    'cpu_arch' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'CPU architecture, e.g. 32-bit, 64-bit.',
    ),
    'core_count' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'size' => 'tiny',
      'default' => '0',
      'description' => 'The specific number of cores (if required) for the exploit to work',
    ),
    'service_pack_match' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'default' => '0',
      'description' => 'Does the service pack version match the target?',
    ),
    'has_code' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'default' => '0',
      'description' => 'Does the exploit have code? (vs. text explaination)',
    ),
    'is_poc' => array(
      'type' => 'int',
      'length' => 4,
      'not null' => TRUE,
      'default' => '0',
      'description' => 'Is this a proof-of-concept? (Thus requiring code modification.)',
    ),
    'code_language' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'In what language is the exploit written? E.g., python, bash, perl.',
    ),
    'status' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Current status of this test: binary, depth, or blank for untested',
    ),
    'cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'process_result_cmd' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or script to run to process the raw result',
    ),
    'watch_file' => array(
      'type' => 'varchar',
      'length' => 64,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Log or status file to display if present.',
    ),
    'order_weight' => array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'size' => 'tiny',
      'description' => 'Order (weight) in which to perform this test relative to others in this pass depth.',
    ),
    'raw_result' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Raw output from the test script or command',
    ),
    'discovered' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Discovery results (processed) that chain to another test or exploit',
    ),
    'flags' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'notes' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'Notes (human or processing script generated).',
    ),
  ),
  'primary key' => array('vid'),
  'indexes' => array(
    'main' => array('session_id','ip_address','service'),
    'auxiliary' => array('port','order_weight','status','flags'),
  ),
);


if (!db_table_exists('vuln')) {
  db_create_table($my_result, 'vuln', $schema['vuln']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue.</div>');
  }
} else {
  echo "<div>Table vuln already exists; no action performed.</div>\n";
}






echo "<div>Creating Table: objective";


/* SQL statement version:

CREATE TABLE `objective` (
  `oid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `ip_address` varchar(39) NOT NULL DEFAULT '',
  `port` int(10) unsigned NOT NULL DEFAULT '0',
  `service` varchar(127) NOT NULL DEFAULT '',
  `status` varchar(16) NOT NULL DEFAULT '',
  `title` varchar(127) NOT NULL DEFAULT '',
  `objective` longtext,
  `notes` longtext,
  `notes2` longtext,
  `notes3` longtext,
  `flags` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`oid`),
  KEY `main` (`ip`,`port`,`service`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

*/

$schema['objective'] = array(
  'description' => 'PenTesting objective',
  'fields' => array(
    'oid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique item ID.',
    ),
    'created' => array(
      'mysql_type' => 'timestamp',
      'not null' => TRUE,
      'description' => 'Record creation time',
    ),
    'session_id' => array(
      'type' => 'varchar',
      'length' => 96,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Testing session ID',
    ),
    'ip_address' => array(
      'type' => 'varchar',
      'length' => 39,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The associated IP address',
    ),
    'port' => array(
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The associated port number',
    ),
    'service' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The (common) service name.',
    ),
    'status' => array(
      'type' => 'varchar',
      'length' => 16,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Current status of this objective: new, in-progress, achieved, or failed.',
    ),
    'title' => array(
      'type' => 'varchar',
      'length' => 127,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
    'objective' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'notes' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'notes2' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'notes3' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'The cmd or example cmd that can be used.',
    ),
    'flags' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'Item title.',
    ),
  ),
  'primary key' => array('oid'),
  'indexes' => array(
    'main' => array('ip_address','port','service'),
  ),
);


if (!db_table_exists('objective')) {
  db_create_table($my_result, 'objective', $schema['objective']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue.</div>');
  }
} else {
  echo "<div>Table objective already exists; no action performed.</div>\n";
}




echo "PenTDB database initialization completed.";
die();


//----------------------------------------------
// tests, leftover

// add a table entry
echo "<div>TEST 2: Add records";

$x = 1;
while ( $x < 12 ) {
  $test_q = "INSERT into {watercooler} (vid,name,description,weight) VALUES (".($x+222)
    . ",'Norman','Animation Character',".($x+42).")";
  $result = db_query($test_q);
  if ( $result != 1 ) {
    echo "<div>result:<pre>".print_r($result,true)."</pre></div>\n";
  }
  $x++;
}

// read and list records
echo "<div>TEST 3: Read records";

$n = 0;
$read_q = "SELECT * FROM {watercooler}";
$result_q = db_query( $read_q );
while ( $data = db_fetch_array($result_q) ) {
  $n++;
  echo "<div>record ".$n.":<br/><pre>".print_r($data,true)."</pre></div>\n";
}

echo "<div>TEST 4: Drop table";

db_drop_table($my_result, 'watercooler');
echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
if ( $my_result[0]['success'] ) {
  echo '<div>Table deleted.</div>';
} else {
  die ('<div>Huh. An issue.</div>');
}

