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


/* create statement:

CREATE TABLE `sessions` (
  `sid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `session_id` varchar(96) NOT NULL DEFAULT '',
  `data_path` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`sid`),
  KEY `main` (`session_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8;

*/

echo "<div>Creating Table: session";

$schema['session'] = array(
  'description' => 'PenTesting session',
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
  'primary key' => array('sid'),
  'indexes' => array(
    'main' => array('session_id'),
  ),
);

if (!db_table_exists('session')) {
  db_create_table($my_result, 'session', $schema['session']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    echo "<div>".print_r($my_result,true)."</div>";
    die ('<div>Huh. An issue.</div>');
  }
}


echo "<div>Creating Table: porttest";

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
}


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
}

echo "PenTDB Init Completed.";
die();

//----------------------------------------------


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

?>
