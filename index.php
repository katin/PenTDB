<?php

// penTDB index.php
//
// Display a chart specified by the passed parms, "ip" and "service".
//
// 190519 KBI created

global $dru_db_version;

$dru_db_version = 'dru_dblib-v1.0';
require_once $dru_db_version.'/dru_db_settings.php';
require_once $dru_db_version.'/dru_db_glue.php';
require_once $dru_db_version.'/database.inc';
require_once $dru_db_version.'/database.mysqli.inc';
require_once $dru_db_version.'/dru_db_startup.php';

require_once 'pentdb-lib.php';
require_once 'pentdb-cmds.php';

date_default_timezone_set('America/Los_Angeles');


	//
	// param checks
	//

if ( isset($_GET['cmd']) ) {
	$mycmd = $_GET['cmd'];			// TODO: sanitize cmd
	ptdb_process_cmd( $mycmd );


}


if ( !isset($_GET['session_id']) ) {
	display_sessions();
}
if ( empty($_GET['session_id']) ) {
	echo '<div class="error"><h2>Missing parameter(s). Session ID required.</h2></div>';
	die();	
}
$session_id = pentdb_clean( $_GET['session_id'] );

if ( isset($_GET['service']) ) {
	if ( $vservice = pentdb_validate_service($_GET['service'] )) {
		if ( $vip = pentdb_validate_ip($_GET['ip'] )) {
			display_service_page( $session_id, $vip, $vservice );
		} else {
			echo '<div class="error">Invalid ip parameter. [Error 427]</div>';
		}
	} else {
		echo '<div class="error">Invalid service parameter. [Error 422]</div>';
	}
	die();
}

if ( isset($_GET['ip']) ) {
	if ( $vip = pentdb_validate_ip($_GET['ip'] )) {
		display_serviceslist_page( $session_id, $vip );
	} else {
		echo '<div class="error">Invalid ip parameter. [Error 425]</div>';
	}
	die();
}

	// No port or ip given, soooo...


display_iplist_page( $session_id );

display_html_footer();


//---------------------------------------------------------------
//           support functions 
//---------------------------------------------------------------

	//
	// display a list of IP addresses under test for this session
	//

function display_iplist_page( $session_id ) {

	$ip_q = "SELECT DISTINCT ip_address FROM {testinstance} WHERE session_id='%s'";
	$ip_recs = db_query( $ip_q, $session_id);
	if ( !$ip_recs ) {
		echo '<div>Query failed.';
		// *** TODO: Add link to add this as new session to db
		die();
	}
	$new_session = false;
	if ( isset($_GET['cmd']) ) {
		if ( $_GET['cmd'] == 'create-session' ) {
			$new_session = true;
		}
	}
	if ( $ip_recs->num_rows == 0 && !$new_session ) {
		echo '<div>Session id "'.$session_id.'"not found in database.';
		// *** TODO: Add link to add this as new session to db
		die();
	}

// echo "<div><pre>".print_r($ip_recs,true)."</pre></div>";

	// display a list of ip addresses available to test
	$ip_list = '';
	while ( $ip = db_fetch_array( $ip_recs ) ) {
		$ip_list .= '<div class="ip-link"><a href="index.php?session_id='.$session_id.'&ip='.$ip['ip_address'].'">'.$ip['ip_address'].'</a></div>'."\n";

		$ip_list .= build_ip_status_display( $session_id, $ip['ip_address'] );

	}
	if ( $ip_list ) {
		$ip_list = "<h2>Select IP address to test:</h2>\n" . $ip_list;
		$ip_list .= "\n".'<p class="clear">. </p>'."\n";
	}

$myform = '
	<div><FORM action="index.php" method="GET">
		<LABEL for="ip_addr">IP address: </LABEL>
		<INPUT type="text" name="ipaddr" id="ip_addr"></INPUT>
		<INPUT type="hidden" name="cmd" value="add-ip"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$session_id.'"></INPUT>
		<INPUT type="submit" value="Add IP address"></INPUT>
	</FORM></div>
';

	$mypage = $ip_list . $myform;
	display_page( $mypage );
}


	//
	// we have no parms. Display a list of sessions to select, or
	//	create a new session.

function display_sessions() {
	$sess_q = "SELECT DISTINCT session_id FROM {testinstance}";
	$sess_recs = db_query( $sess_q );
	if ( !$sess_recs ) {
		echo '<div>No sessions found in database.';
	}

	// display a list of sessions available to test
	$sess_list = '';
	while ( $session = db_fetch_array( $sess_recs ) ) {
		$sess_list .= '<div><a href="index.php?session_id='.$session['session_id'].'">'.$session['session_id'].'</a></div>'."\n";
	}
	if ( $sess_list ) {
		$sess_list = "<h2>Select a test session:</h2>\n" . $sess_list;
		$sess_list .= "\n<p></p>";
	}

	// display a create session form
$myform = '
	<div><FORM action="index.php" method="GET">
		<LABEL for="session_name">Session name: </LABEL>
		<INPUT type="text" name="idname" id="session_name"></INPUT>
		<INPUT type="hidden" name="cmd" value="create-session"></INPUT>
		<INPUT type="submit" value="Create session"></INPUT>
	</FORM></div>
';

// echo print_r($sess_list,true);
// die("check1");

	$mypage = $sess_list . $myform;
	display_page( $mypage );
}


	//
	// display a service test set page
	//

function display_service_page( $session_id, $ip, $service ) {

	$tests_q = "SELECT * FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' AND service='%s' ORDER BY pass_depth, order_weight";
	$tests_recs = db_query( $tests_q, $session_id, $ip, $service );
	if ( !$tests_recs ) {
		echo '<div>Session services "'.$session_id.'"not found in database. [Error 611]';
		die();
	}

	// display a list of tests for this service
	$test_list = '';
	$service = '';
	$passD = 0;
	while ( $test = db_fetch_array( $tests_recs ) ) {
		$status_bar = 'status-empty';
		switch ($test['statustype']) {
			case 'BINARY':
				switch ($test['status']) {
					case 'POS':
					case 'NEG':
						$status_bar = 'status-completed';
						break;

					default:
						break;
				}

			case 'DEPTH':
				break;

			case 'NONE':
				break;

			default:
				pentdb_log_error("NOTE: Unknown statustype on recid".$test['recid']);
				break;


		}
		if ( $test['pass_depth'] > $passD ) {
			$passD = $test['pass_depth'];
			$test_list .= '<div class="clear"></div>';
			$test_list .= '<div class="pass-title">Pass #'.$passD.'</div>'."\n";
		}


		$buttons = '<div class="button-block">'."\n";
		if ( $test['statustype'] == 'BINARY' ) {
			$buttons .= get_binary_status_button( $test['status'], $test['irid'] );
		}
		$buttons .= '</div>'."\n";

		$banner_form = '';
		if ( $test['rectype'] == 'TITLE' ) {
			$banner_form = get_add_banner_form( $test['irid'] );
		}
		$flags_form = get_set_flags_form( $test['irid'] );

		$lineid = "Tcmd".$test['irid'];
		$test_list .= '<div class="test" id="test-'.$test['irid'].'">'
			. '<div class="test-title '.$status_bar.'">'.strtoupper($test['rectype']).': &nbsp;&nbsp;'.$test['title'].($test['banner'] ? ' - '.$test['banner'] : '').'</div>'
			. $buttons
			. '<div class="flags-display">'.($test['flags'] ? 'Flags: ' : '').$test['flags'].'</div>'."\n"
			. '<div class="test-cmd">CMD: <input class="cmd-text" type="text" value="'.fill_varset($test['cmd']).'" id="'.$lineid.'"><button class="cmd-copy" onclick="ptdb_copytext(\''.$lineid.'\')">Copy</button></div>'
			. '<div class="test-process">PROCESS: <input class="cmd-text" type="text" value="'.addslashes(fill_varset($test['process_result_cmd'])).'" id="P'.$lineid.'"><button class="cmd-copy" onclick="ptdb_copytext(\'P'.$lineid.'\')">Copy</button></div>'
			. "</div>\n";

		$test_list .= $banner_form . $flags_form;

		$service = ($service ? $service : $test['service']);
	}
	if ( $test_list ) {
		$test_list = '<h2>Test Tracker, <a class="hover-link" href="index.php?session_id='.$session_id.'&ip='.$ip.'">IP '.$ip.'</a>, service '.$service.' / '.$service.':</h2>'."
		\n" . $test_list;
		$test_list .= "\n".'<p class="clear"></p>'."\n";
	} else {
		$test_list .= "<h2>No tests found.</h2>";
		$test_list .= "<div>ip ".$ip.", service ".$service."</div>\n";
	}

	$mypage = $test_list . get_add_test_form();
	display_page( $mypage );
}


	//
	// display a services list page
	//

function display_serviceslist_page( $session_id, $ip ) {

	$service_q = "SELECT * FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' AND rectype='TITLE' ORDER BY service";
	$service_recs = db_query( $service_q, $session_id, $ip);
	if ( !$service_recs ) {
		echo '<div>Session id "'.$session_id.'"not found in database.';
		// *** TODO: Add link to add this as new session to db
		die();
	}

	// display a list of services available to test
	$service_list = '';
	while ( $service = db_fetch_array( $service_recs ) ) {

			// port zero is the HOST record; skip that
		// if ( $service['port'] == 0 ) {
		// 	continue;
		// }
		$service_list .= '<div><a class="hover-link" href="index.php?session_id='.$session_id.'&ip='.$service['ip_address'].'&service='.$service['service'].'">'.$service['title'].': '.$service['service'].'</a></div>'."\n";

// echo "<div><pre>".print_r($service,true)."</pre></div>";

	}
	if ( $service_list ) {
		$service_list = "<h2>Select service to test:</h2>\n" . $service_list;
		$service_list .= "\n<p></p>\n";
	}


	// display a create test form
	$tests_q = "SELECT service,title,port FROM {porttest} WHERE rectype='TITLE'";
	$tests_recs = db_query( $tests_q );
	if ( !$tests_recs ) {
		echo '<div>Error - failed query of servicetest table.</div>';
		die();
	}
	if ( $tests_recs->num_rows == 0 ) {
		echo '<div>No service test templates found in database.</div>';
		die();
	}

	$test_list = '';
	while ( $test = db_fetch_array( $tests_recs ) ) {
		$test_list .= '<OPTION value="'.$test['service'].'">'.$test['service'].' (service '.$test['service'].') </OPTION>'."\n";
	}

	$myform = '
		<div><FORM action="index.php" method="GET">
		<SELECT name="service">
	'.$test_list.'
		</SELECT>
		<LABEL for="altport">port:</LABEL>
		<INPUT type="text" name="altport" id="altport"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$session_id.'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$ip.'"></INPUT>
		<INPUT type="hidden" name="cmd" value="add-service"></INPUT>
		<INPUT type="submit" value="Add service set"></INPUT>
		</FORM></div>
	';


	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">

		<LABEL for="port">Port number: </LABEL>
		<INPUT type="text" name="port" id = "port"></INPUT><br/>

		<LABEL for="title">Port display title: </LABEL>
		<INPUT type="text" name="title" id = "title"></INPUT><br/>

		<LABEL for="service">Service label: </LABEL>
		<INPUT type="text" name="service" id = "service"></INPUT><br/>

		<LABEL for="banner">Banner: </LABEL>
		<INPUT type="text" name="banner" id = "banner"></INPUT><br/>

		<LABEL for="statustype">Status type: </LABEL>
		<SELECT name="statustype" id="statustype">
			<OPTION value="BINARY">BINARY</OPTION>
			<OPTION value="DEPTH">DEPTH</OPTION>
			<OPTION value="NONE">NONE</OPTION>
		</SELECT><br/>

		<LABEL for="command">Command: </LABEL>
		<INPUT type="text" name="command" id = "command"></INPUT><br/>

		<LABEL for="process_result_cmd">Process result cmd: </LABEL>
		<INPUT type="text" name="process_result_cmd" id = "process_result_cmd"></INPUT><br/>

		<INPUT type="hidden" name="pass_depth" value="0"></INPUT>
		<INPUT type="hidden" name="order_weight" value="0"></INPUT>
		<INPUT type="hidden" name="rectype" value="TITLE"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$session_id.'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$ip.'"></INPUT>
		<INPUT type="hidden" name="cmd" value="new-port"></INPUT>
		<INPUT type="submit" value="Create port test"></INPUT>
		</FORM></div>
	';

	$mypage = $service_list . $myform . $bigform;
	display_page( $mypage );
}



/* --- holding tank

		<LABEL for="rectype">Record/Test type: </LABEL>
		<SELECT name="rectype" id="rectype">
			<OPTION value="TITLE">TITLE</OPTION>
			<OPTION value="SCAN">SCAN</OPTION>
			<OPTION value="TOOL">TOOL</OPTION>
			<OPTION value="SCRIPT">SCRIPT</OPTION>
			<OPTION value="HOST">HOST</OPTION>
			<OPTION value="EXAMINE">EXAMINE</OPTION>
		</SELECT><br/>

*/


