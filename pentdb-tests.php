<?php

// pentdb-tests.php
//
// Record creation and maintenance for PenTDB Tests Templates.
//
// 190625 KBI created

global $dru_db_version;

$dru_db_version = 'dru_dblib-v1.0';
require_once $dru_db_version.'/dru_db_settings.php';
require_once $dru_db_version.'/dru_db_glue.php';
require_once $dru_db_version.'/database.inc';
require_once $dru_db_version.'/database.mysqli.inc';
require_once $dru_db_version.'/dru_db_startup.php';

require_once 'pentdb-config.php';
require_once 'pentdb-lib.php';
require_once 'pentdb-tests-cmds.php';

date_default_timezone_set('America/Los_Angeles');


	//
	// param checks
	//

if ( isset($_GET['fcmd']) ) {
	$mycmd = $_GET['fcmd'];			// TODO: sanitize cmd
	ptdb_process_tests_cmd( $mycmd );
}


	//
	// display our page
	//

$orderby = 'service,port';
if ( isset($_GET['sort']) ) {
	if ( in_array($_GET['sort'], array("port","rectype","statustype","title",
			"service-rev","port-rev","rectype-rev","statustype-rev","title-rev")) ) {
		$orderby = $_GET['sort'];
	}
	if ( in_array($_GET['sort'], array("port-rev","rectype-rev","statustype-rev","title-rev")) ) {
		$orderby = substr($_GET['sort'], 0, strpos($_GET['sort'], '-'))." DESC";
// die( substr($_GET['sort'], 0, strpos($_GET['sort'], '-')) );
	}
	if ( $_GET['sort'] == 'service-rev') {
		$orderby = 'service DESC,port';
	}
}

$where = '';
$include_parms = '';
$service = '';
if ( isset($_GET['service']) ) {
	if ( empty($_GET['service']) ) {
		unset($_GET['service']);
	} else {
		$service = pentdb_clean( $_GET['service'] );
		$where = " WHERE service='%s'";
		$include_parms = 'SERVICE';
	}
}
$port = '';
if ( isset($_GET['port']) ) {
	if ( empty($_GET['port']) ) {
		unset($_GET['port']);
	} else {
		$port = pentdb_validate_port( $_GET['port'] );
		$where = " WHERE port='%s'";
		$include_parms = 'PORT';
	}
}

$alltests_q = "SELECT * FROM {porttest}".$where." ORDER BY ".$orderby.",service,pass_depth,order_weight";
switch ($include_parms) {
	case 'SERVICE':
		$alltests_recs = db_query( $alltests_q, $service );
		break;

	case 'PORT':
		$alltests_recs = db_query( $alltests_q, $port );
		break;

	default:
		$alltests_recs = db_query( $alltests_q );
		break;
}

// diebug($alltests_q,true);

if ( !$alltests_recs ) {
	pentdb_top_msg( "No porttest records found in database." );
}

	// set up our sorting columns
if (!isset($_GET['sort']) ) {
	$_GET['sort'] = 'service';		// the default sort
}
$service_sort_arrow = '';
$service_sort = '?sort=service';
$port_sort_arrow = '';
$port_sort = '?sort=port';
$rectype_sort_arrow = '';
$rectype_sort = '?sort=rectype';
$statustype_sort_arrow = '';
$statustype_sort = '?sort=statustype';
$title_sort_arrow = '';
$title_sort = '?sort=title';

switch ($_GET['sort']) {
	case 'service':
		$service_sort_arrow = ' &#8615;';
		$service_sort = '?sort=service-rev';
		break;

	case 'service-rev':
		$service_sort_arrow = ' &#8613;';
		break;


	case 'port':
		$port_sort_arrow = ' &#8615;';
		$port_sort = '?sort=port-rev';
		break;

	case 'port-rev':
		$port_sort_arrow = ' &#8613;';
		break;

	case 'rectype':
		$rectype_sort_arrow = ' &#8615;';
		$rectype_sort = '?sort=rectype-rev';
		break;

	case 'rectype-rev':
		$rectype_sort_arrow = ' &#8613;';
		break;

	case 'statustype':
		$statustype_sort_arrow = ' &#8615;';
		$statustype_sort = '?sort=statustype-rev';
		break;

	case 'statustype-rev':
		$statustype_sort_arrow = ' &#8613;';
		break;

	case 'title':
		$title_sort_arrow = ' &#8615;';
		$title_sort = '?sort=title-rev';
		break;

	case 'title-rev':
		$title_sort_arrow = ' &#8613;';
		break;

}

	// display all portests with edit & delete links

$test_list .= "<h2>Tests on file</h2>\n";
$test_list .= display_data_filter_form();

// $test_list .= "<em>Click on a column title to sort by that column.</em>\n";
$test_list .= '<table class="tests-table">'."\n";
$test_list .= "<tr>" 
	. '<th><a href="pentdb-tests.php'.$service_sort.'">service</a> '.$service_sort_arrow.'</th>'
	. '<th><a href="pentdb-tests.php'.$port_sort.'">port</a> '.$port_sort_arrow.'</th>'
	. '<th>pass</th>'
	. '<th>weight</th>'
	. '<th><a href="pentdb-tests.php'.$rectype_sort.'">rectype</a> '.$rectype_sort_arrow.'</th>'
	. '<th><a href="pentdb-tests.php'.$statustype_sort.'">statustype</a> '.$statustype_sort_arrow.'</th>'
	. "<th>actions</th>"
	. '<th><a href="pentdb-tests.php'.$title_sort.'">title</a> '.$title_sort_arrow.'</th>'
	. "</tr>\n";

$even = false;
while ( $test = db_fetch_array( $alltests_recs ) ) {
	if ( $even ) {
		$row_class = " even";
		$even = false;
	} else {
		$row_class = " odd";
		$even = true;
	}
	$test_list .= '<tr class="'.$row_class.'">'."\n";
	$test_list .= "<td>".$test['service']."</td>\n";
	$test_list .= "<td>".$test['port']."</td>\n";
	$test_list .= "<td>".$test['pass_depth']."</td>\n";
	$test_list .= "<td>".$test['order_weight']."</td>\n";
	$test_list .= "<td>".$test['rectype']."</td>\n";
	$test_list .= "<td>".$test['statustype']."</td>\n";
	$test_list .= '<td><a href="pentdb-tests.php?fcmd=edit-test&tid='.$test["pitid"].'">edit</a> | <a href="pentdb-tests.php?fcmd=delete-test&tid='.$test["pitid"].'">delete</a></td>'."\n";
	$test_list .= "<td>".$test['title']."</td>\n";
	$test_list .= "</tr>\n";

}
$test_list .= '</table>'."\n";


// display a create test form
$myform = get_new_test_form();

$quicklink_add_service = '<div class="quicklink"><a href="#add-test-form">Add a new test template</a></div>'."\n";

$mypage = $quicklink_add_service . $test_list . $myform;

display_tests_page( $mypage );


// display filter form & indicators
//

function display_data_filter_form() {

	$myform = '
	<div class="filter-data-form">
	<FORM action="pentdb-tests.php" method="GET">
	<LABEL for="service-filter">service:</LABEL>
	<SELECT name="service" id="service-filter">
	'.implode(tests_get_service_options()).'
	</SELECT>
	<INPUT type="submit" value="Show"></INPUT>
	</FORM></div>
	';

	$myform .= '
	<div class="filter-data-form">
	<FORM action="pentdb-tests.php" method="GET">
	<LABEL for="port-filter"> &nbsp; &nbsp; port:</LABEL>
	<SELECT name="port" id="port-filter">
	'.implode(tests_get_port_options()).'
	</SELECT>
	<INPUT type="submit" value="Show"></INPUT>
	</FORM></div>
	';

	if ( isset($_GET['service']) || isset($_GET['port']) ) {
		$myform .= '<div class="quicklink"><a href="pentdb-tests.php">SHOW ALL RECORDS</a></div>'."\n";
	}

	return $myform;
}




function tests_get_service_options() {

	$service_selected = NULL;
	if ( isset($_GET['service']) ) {
		$service_selected = pentdb_clean($_GET['service']);
	}
	$servicelist = array();

	$service_q = "SELECT service FROM {porttest} GROUP BY service ORDER BY service";

	$service_recs = db_query( $service_q );
	if ( !$service_recs ) {
		pentdb_log_error('Services query failed. [ERR-9711]');
		return false;
	}
	if ( $service_recs->num_rows == 0 ) {
		pentdb_top_msg('[Notice 233] No service records found.' );
		return false;
	}

	$servicelist[] = '<OPTION value="">-</OPTION>';
	while ( $rec = db_fetch_array( $service_recs) ) {
		$servicelist[] = '<OPTION '
			.($rec['service']==$service_selected ? "SELECTED " : '')
			.'value="'.$rec['service'].'">'.$rec['service'].'</OPTION>';
	}

	return $servicelist;
}


function tests_get_port_options() {

	$port_selected = NULL;
	if ( isset($_GET['port']) ) {
		$port_selected = pentdb_clean($_GET['port']);
	}
	$portlist = array();

	$port_q = "SELECT port FROM {porttest} GROUP BY port ORDER BY port";

	$port_recs = db_query( $port_q );
	if ( !$port_recs ) {
		pentdb_log_error('Ports query failed. [ERR-9712]');
		return false;
	}
	if ( $port_recs->num_rows == 0 ) {
		pentdb_top_msg('[Notice 233] No port records found.' );
		return false;
	}

	$portlist[] = '<OPTION value="">-</OPTION>';
	while ( $rec = db_fetch_array( $port_recs) ) {
		$portlist[] = '<OPTION '
			.($rec['port']==$port_selected ? "SELECTED " : '')
			.'value="'.$rec['port'].'">'.$rec['port'].'</OPTION>';
	}

	return $portlist;
}


// display test detail and edit form
//

function display_delete_page( $tid ) {

	$tid = pentdb_clean( $tid );

	$test_q = "SELECT * FROM {porttest} WHERE pitid='%s'";
	$test_rec = db_query( $test_q, $tid );
	if ( !$test_rec ) {
		pentdb_log_error( "Error reading porttest tid=".$tid." [ERR-5141]" );
	}

	$test = db_fetch_array( $test_rec );

	// display the test info

	$test_list .= "<h2>DELETE Test</h2>\n";
	$test_list .= '<table class="tests-table">'."\n";
	$test_list .= "<tr>" 
		. '<th><a href="pentdb-tests.php'.$service_sort.'">service</a> '.$service_sort_arrow.'</th>'
		. '<th><a href="pentdb-tests.php'.$port_sort.'">port</a> '.$port_sort_arrow.'</th>'
		. '<th><a href="pentdb-tests.php'.$rectype_sort.'">rectype</a> '.$rectype_sort_arrow.'</th>'
		. '<th><a href="pentdb-tests.php'.$statustype_sort.'">statustype</a> '.$statustype_sort_arrow.'</th>'
		. '<th><a href="pentdb-tests.php'.$title_sort.'">title</a> '.$title_sort_arrow.'</th>'
		. "</tr>\n";
	$test_list .= '<tr class="'.$row_class.'">'."\n";
	$test_list .= "<td>".$test['service']."</td>\n";
	$test_list .= "<td>".$test['port']."</td>\n";
	$test_list .= "<td>".$test['rectype']."</td>\n";
	$test_list .= "<td>".$test['statustype']."</td>\n";
	$test_list .= "<td>".$test['title']."</td>\n";
	$test_list .= "</tr>\n";
	$test_list .= '</table>'."\n";

	// build the confirmation form
	$myform = '
		<div class="delete-confirm-form"><FORM action="pentdb-tests.php" method="GET">
		<div class="alert-text">ARE YOU SURE?</div>
		<div>There is no undo for this action.</div>
		<INPUT type="hidden" name="tid" value="'.$tid.'"></INPUT>
		<INPUT type="hidden" name="delete" value="CONFIRM"></INPUT>
		<INPUT type="hidden" name="fcmd" value="delete-test"></INPUT>
		<INPUT type="submit" value="DELETE"></INPUT>
		</FORM></div>

	';


	display_tests_page( $test_list . $myform );

}

function display_tid_page( $tid ) {

	$tid = pentdb_clean( $tid );

	$test_q = "SELECT * FROM {porttest} WHERE pitid='%s'";
	$test_rec = db_query( $test_q, $tid );
	if ( !$test_rec ) {
		pentdb_log_error( "Error reading porttest tid=".$tid." [ERR-5141]" );
	}

	$test = db_fetch_array( $test_rec );

	// display the test info
	// $output = '<div class="bigform">'."\n";
	foreach ($test as $fieldname => $value) {
		$output .= get_test_datum_form( $fieldname, $value, $test['pitid'] );
	}
	if ( $output ) {
		$output = "<h2>Test info:</h2>\n" . $output;
		$output .= "\n".'<p class="clear">. </p>'."\n";
	}
	// $output .= "</div>\n";

	display_tests_page( $output );

}

function get_new_test_form( $title = "Add a test template" ) {
	$vars = pentdb_get_tests_vars();
	$bigform = '
		<div class="bigform"><FORM action="pentdb-tests.php" method="GET" id="add-test-form">

		<LABEL for="title">Test display title: </LABEL>
		<INPUT type="text" name="title" id = "title"></INPUT><br/>

		<LABEL for="service">Service: </LABEL>
		<INPUT type="text" name="service" id = "service"></INPUT><br/>

		<LABEL for="port">Port: </LABEL>
		<INPUT type="text" name="port" id = "port"></INPUT><br/>

		<LABEL for="rectype">Record/Test type: </LABEL>
		<SELECT name="rectype" id="rectype">
			<OPTION value="EXAMINE">EXAMINE</OPTION>
			<OPTION value="SCAN">SCAN</OPTION>
			<OPTION value="TOOL">TOOL</OPTION>
			<OPTION value="SCRIPT">SCRIPT</OPTION>
			<OPTION value="TITLE">TITLE</OPTION>
		</SELECT><br/>

		<LABEL for="statustype">Status type: </LABEL>
		<SELECT name="statustype" id="statustype">
			<OPTION value="BINARY">BINARY</OPTION>
			<OPTION value="DEPTH">DEPTH</OPTION>
			<OPTION value="NONE">NONE</OPTION>
		</SELECT><br/>

		<LABEL for="command">Info: </LABEL>
		<INPUT type="text" name="info" id = "info"></INPUT><br/>

		<LABEL for="command">Cmd: </LABEL>
		<INPUT type="text" name="cmd" id = "cmd"></INPUT><br/>

		<LABEL for="process_result_cmd">Process result cmd: </LABEL>
		<INPUT type="text" name="process_result_cmd" id = "process_result_cmd"></INPUT><br/>

		<LABEL for="watch_file">Watch file: </LABEL>
		<INPUT type="text" name="watch_file" id="watch_file"></INPUT><br/>

		<LABEL for="pass_depth">Pass depth: </LABEL>
		<INPUT type="text" name="pass_depth" id="pass_depth" value="1"></INPUT><br/>

		<LABEL for="order_weight">Order Weight: </LABEL>
		<INPUT type="text" name="order_weight" id="order_weight" value="1"></INPUT><br/>

		<INPUT type="hidden" name="fcmd" value="new-test"></INPUT>
		<INPUT type="submit" value="Add a test"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

		// <INPUT type="hidden" name="port" value="'.$port.'"></INPUT>

	return $bigform;
}


function get_test_datum_form( $fieldname, $value, $pitid ) {
	if ( $fieldname == 'pitid' ) {
		return NULL;
	}

	$data = '		<LABEL for="'.$fieldname.'">'.$fieldname.': </LABEL>
		<INPUT type="text" name="'.$fieldname.'" id ="'.$fieldname.'" value="'.$value.'"></INPUT>';

	if ( $fieldname == 'rectype' ) {
		$data = '		<LABEL for="rectype">rectype: </LABEL>
		<SELECT name="rectype" id="rectype">
			<OPTION '.($value=="TITLE" ? 'SELECTED ' : '').'value="TITLE">TITLE</OPTION>
			<OPTION '.($value=="SCAN" ? 'SELECTED ' : '').'value="SCAN">SCAN</OPTION>
			<OPTION '.($value=="TOOL" ? 'SELECTED ' : '').'value="TOOL">TOOL</OPTION>
			<OPTION '.($value=="SCRIPT" ? 'SELECTED ' : '').'value="SCRIPT">SCRIPT</OPTION>
			<OPTION '.($value=="EXAMINE" ? 'SELECTED ' : '').'value="EXAMINE">EXAMINE</OPTION>
		</SELECT><br/>';
	}
	if ( $fieldname == 'statustype' ) {
		$data = '		<LABEL for="statustype">statustype: </LABEL>
		<SELECT name="statustype" id="statustype">
			<OPTION '.($value=="BINARY" ? 'SELECTED ' : '').'value="BINARY">BINARY</OPTION>
			<OPTION '.($value=="DEPTH" ? 'SELECTED ' : '').'value="DEPTH">DEPTH</OPTION>
			<OPTION '.($value=="NONE" ? 'SELECTED ' : '').'value="NONE">TOOL</OPTION>
		</SELECT><br/>';
	}
	$myform = '
		<div class="inlineform test"><FORM action="pentdb-tests.php" method="GET">

		'.$data.'
		<INPUT type="hidden" name="fname" value="'.$fieldname.'"></INPUT>
		<INPUT type="hidden" name="tid" value="'.$pitid.'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="update-test"></INPUT>
		<INPUT type="submit" value="Update"></INPUT>
		</FORM></div><div class="clear"></div>
	';

	return $myform;
}


function pentdb_add_test() {
	$vars = pentdb_get_tests_vars();

	// pre-emptively fix rapid-entered TITLE records
	if ( $_GET['rectype'] == 'TITLE' ) {
		$_GET['pass_depth'] = 0;
		$_GET['order_weight'] = 0;
	}

	// Create the instance record
	$test_q = "INSERT into {porttest} (port, service, rectype, statustype, title, info, cmd, process_result_cmd, watch_file, pass_depth, order_weight)"
		. " VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')";
	$result = db_query( $test_q,
		$_GET['port'],
		$_GET['service'],
		$_GET['rectype'],
		$_GET['statustype'],
		$_GET['title'],
		$_GET['info'],
		$_GET['cmd'],
		$_GET['process_result_cmd'],
		$_GET['watch_file'],
		$_GET['pass_depth'],
		$_GET['order_weight']
	);

	if ( !$result ) {
		$errcount++;
		pentb_log_error("Error adding instance record ".$_GET['title']);
		// die();
	} else {
		pentdb_top_msg("Added test ".$_GET['title']);
	}

	return $result;
}


function pentdb_update_test() {	

	$vars = pentdb_get_tests_vars();

	// validate fname ------

	if ( !isset($_GET['fname']) ) {
		pentdb_log_error("Feildname param required to update test. [MSG-5102]");
		return false;
	}
	$fname = pentdb_clean($_GET['fname']);

	$valid_test_fields = pentdb_get_valid_test_fields();
	if ( !in_array($fname, $valid_test_fields) ) {
		pentdb_log_error("Invalid fieldname '".$fname."' passed to update_vuln(). [MSG-5141]");
		return false;
	}

	// validate field data -----------

	if ( !isset($_GET[$fname]) ) {
		pentdb_log_error("Missing field data parm '".$fname."' in update_test(). [MSG-5107]");
		return false;
	}

	// update db

	$test_q = "UPDATE porttest SET ".$fname."='%s' WHERE pitid=".$vars['tid'];
	$result = db_query( $test_q, $_GET[$fname] );
	if ( !$result ) {
		pentdb_log_error("Test update failed. [MSG-5120]");
		return false;
	}

	return $status;

}


function pentdb_get_valid_test_fields() {
	$form_fields = array( 'port', 'rectype', 'statustype', 'service', 'title', 'info',
		'cmd', 'process_result_cmd', 'watch_file', 'order_weight', 'pass_depth'
	);

	return $form_fields;
}



// html header
//
// Issue an HTML header with styles, etc.

function tests_display_html_header() {
global $top_message;

?>
<HTML>
<HEAD>
  <link rel="stylesheet" type = "text/css" href = "pentdb-styles.css" />
  <script src="pentdb.js"></script>
</HEAD>

<BODY>
	<div id="top">
	<span class="titlespan"><a class="hover-link" href="index.php">PenTDB Tool by K10</a></span>
<?php
	// $vars = pentdb_get_tests_vars();

	$output = '';
	$output .= '<span class="session-title"><a class="hover-link" href="pentdb-tests.php">Tests Templates Maintenance</a></span>'."\n";


	$output .= '</div>'."\n";				// close #top
	$output .= '<div id="page">'."\n";		// open page

	if ( $top_message ) {
		// $output .= '<div class="top-message">'.$top_message.'</div>'."\n";
	}
	$output .= pentdb_log_error('','display');
	$output .= pentdb_top_msg('','display' );


	echo $output;
}


// wrapup_page
//
// Display footer and end-of-page code

function tests_wrapup_page() {
	echo '</div>'."\n";		// close #page
	display_html_footer();
?>
</BODY>
</HTML>
<?php
}


// html footer
//
// Issue our HTML footer with close tags, links, etc.

function tests_display_html_footer() {

	// pentdb_log_error('','display');

}

// display_tests_page
//
// Display page top, footer, and page tail around the given content
// and end execution

function display_tests_page( $content ) {
	tests_display_html_header();
	echo $content;
	tests_wrapup_page();
	die();
}


function pentdb_get_tests_vars() {
	$vars = array();
	// if ( isset($_GET['session_id']) ) {
	// 	$vars['session_id'] = pentdb_clean( $_GET['session_id'] );
	// }
	// if ( isset($_GET['port']) ) {
	// 	$vars['port'] = pentdb_clean( $_GET['port'] );
	// }
	// if ( isset($_GET['ip']) ) {
	// 	$vars['ip'] = pentdb_clean( $_GET['ip'] );
	// }
	// if ( isset($_GET['fcmd']) ) {
	// 	$vars['fcmd'] = pentdb_clean( $_GET['fcmd'] );
	// }
	// if ( isset($_GET['rec_id']) ) {
	// 	$vars['rec_id'] = pentdb_clean( $_GET['rec_id'] );
	// }
	// if ( isset($_GET['service']) ) {
	// 	$vars['service'] = pentdb_clean( $_GET['service'] );
	// }
	// if ( isset($_GET['status']) ) {
	// 	$vars['status'] = pentdb_clean( $_GET['status'] );
	// }
	if ( isset($_GET['tid']) ) {
		$vars['tid'] = pentdb_clean( $_GET['tid'] );
	}
	return $vars;
}


