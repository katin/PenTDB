<?php
/*
 * test_dru_db.php
 *
 * Test the dru_db pack: create database table, write, and read.
 *
*/
// 130903 KBI created
// 130904 KBI Tested complete and all functions working.


require_once './dru_db_settings.php';
require_once './dru_db_glue.php';
require_once './database.inc';
require_once './database.mysqli.inc';
require_once './dru_db_startup.php';

echo '<div>Executing test statements...</div>';

echo "<div>TEST 1: Create Table";



$test_create_stmt = "CREATE TABLE {pet} (
		name VARCHAR(20), 
		owner VARCHAR(20),
		species VARCHAR(20), 
		sex CHAR(1), 
		birth DATE, 
		death DATE
)";

$schema['watercooler'] = array(
  'description' => 'Test database for dru_db_pack.',
  'fields' => array(
    'tid' => array(
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'description' => 'Primary Key: Unique term ID.',
    ),
    'vid' => array(
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => TRUE,
      'default' => 0,
      'description' => 'The {vocabulary}.vid of the vocabulary to which the term is assigned.',
    ),
    'name' => array(
      'type' => 'varchar',
      'length' => 255,
      'not null' => TRUE,
      'default' => '',
      'description' => 'The term name.',
    ),
    'description' => array(
      'type' => 'text',
      'not null' => FALSE,
      'size' => 'big',
      'description' => 'A description of the term.',
    ),
    'weight' => array(
      'type' => 'int',
      'not null' => TRUE,
      'default' => 0,
      'size' => 'tiny',
      'description' => 'The weight of this term in relation to other terms. (127 max)',
    ),
  ),
  'primary key' => array('tid'),
  'indexes' => array(
    'taxonomy_tree' => array('vid', 'weight', 'name'),
    'vid_name' => array('vid', 'name'),
  ),
);


if (!db_table_exists('watercooler')) {
  db_create_table($my_result, 'watercooler', $schema['watercooler']);
  echo "<div>my_result:<br/><pre>".print_r($my_result,true)."</pre></div>\n";
  if ( $my_result[0]['success'] ) {
    echo '<div>Table created.</div>';
  } else {
    die ('<div>Huh. An issue.</div>');
  }
}


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
