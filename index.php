<?php

// penTDB index.php
//
// Display a tracking chart specified by the passed parms, "session", ip" and "service".
//
// 190519 KBI created

global $dru_db_version;

$dru_db_version = 'dru_dblib-v1.0';
require_once $dru_db_version.'/dru_db_settings.php';
require_once $dru_db_version.'/dru_db_glue.php';
require_once $dru_db_version.'/database.inc';
require_once $dru_db_version.'/database.mysqli.inc';
require_once $dru_db_version.'/dru_db_startup.php';

require_once 'pentdb-config.php';
require_once 'pentdb-lib.php';
require_once 'pentdb-cmds.php';

date_default_timezone_set('America/Los_Angeles');


	//
	// param checks
	//

if ( isset($_GET['fcmd']) ) {
	$mycmd = $_GET['fcmd'];			// TODO: sanitize cmd
	ptdb_process_cmd( $mycmd );
}

// if we don't have a session going, 
//   then display the choose/new session page

if ( !isset($_GET['session_id']) ) {
	display_sessions();
}

if ( empty($_GET['session_id']) ) {
	pentdb_log_error("Missing parameter(s). Session ID required.");
	display_page();
	die();	
}

$session_id = pentdb_clean( $_GET['session_id'] );

if ( isset($_GET['service']) ) {
	if ( !isset($_GET['port']) ) {
		die('Port param is required for this display.');
	}
	if ( $vservice = pentdb_validate_service($_GET['service'] ) ) {
		if ( $vip = pentdb_validate_ip($_GET['ip'] )) {
			if ( $vport = $_GET['port'] ) {
				display_service_page( $session_id, $vip, $vservice, $vport );
			}
		} else {
			echo '<div class="error">Invalid ip parameter. [Error 427]</div>';
		}
	} 
	// else {
	// 	echo '<div class="error">Invalid service parameter. [Error 422]</div>';
	// }
	// die();
}

if ( isset($_GET['ip']) ) {
	if ( $vip = pentdb_validate_ip($_GET['ip'] )) {
		display_serviceslist_page( $session_id, $vip );
	} else {
		echo '<div class="error">Invalid ip parameter. [Error 425]</div>';
	}
	die();
}

	// No service or ip given, soooo...


display_iplist_page( $session_id );

display_html_footer();


//---------------------------------------------------------------
//           support functions 
//---------------------------------------------------------------

	//
	// display a vuln page
	//

function display_vuln_page( $session_id, $ip, $vuln_id ) {

	$vars = pentdb_get_urlparms( array( 'session_id'=>$session_id, 'ip'=>$ip) );

	$vuln_q = "SELECT * FROM {vuln} WHERE vid='%s'";
	$vuln_recs = db_query( $vuln_q, $vuln_id);
	if ( !$vuln_recs ) {
		echo '<div>Query failed. [MSG-841]';
		// *** TODO: Add link to add this as new session to db
		die();
	}

	$display_fields = pentdb_get_valid_vuln_fields();

	// display the vuln info
	$output = '';
	$visit_button = '';
	while ( $vuln = db_fetch_array( $vuln_recs ) ) {
		foreach ($display_fields as $field) {
			$output .= get_add_vuln_datum_form( $field, $vuln[$field], $vuln['vid'] );
		}
		if ( !empty($vuln['url']) ) {
			$visit_button = '<a class="link-button" href="'.$vuln['url'].'">Visit exploit page</a>'."\n";
		}
	}
	if ( $output ) {
		$output = "<h2>Vuln info:</h2>\n" . $visit_button . $output;
		$output .= "\n".'<p class="clear">. </p>'."\n";
	}

	$quick_add_form = get_add_vuln_form();
	$output .= "<hr>\n" . $quick_add_form;

	display_page( $output );
}


	//
	// display a objective page
	//

function display_objective_page( $session_id, $ip, $oid ) {

	$vars = pentdb_get_urlparms( array( 'session_id'=>$session_id, 'ip'=>$ip) );

	$obj_q = "SELECT * FROM {objective} WHERE oid='%s'";
	$obj_recs = db_query( $obj_q, $oid);
	if ( !$obj_recs ) {
		echo '<div>Query failed. [MSG-842]';
		// *** TODO: Add link to add this as new session to db
		die();
	}

	$display_fields = pentdb_get_valid_obj_fields();

	// display the obj info
	$output = '';
	while ( $obj = db_fetch_array( $obj_recs ) ) {
		foreach ($display_fields as $field) {
			$output .= get_add_obj_datum_form( $field, $obj[$field], $obj['vid'] );
		}
	}
	if ( $output ) {
		$output = "<h2>obj info:</h2>\n" . $visit_button . $output;
		$output .= "\n".'<p class="clear">. </p>'."\n";
	}

	display_page( $output );
}




	//
	// display a list of IP addresses under test for this session
	//

function display_iplist_page( $session_id ) {

	$ip_q = "SELECT * FROM {host} WHERE session_id='%s'";

	$ip_recs = db_query( $ip_q, $session_id);
	if ( !$ip_recs ) {
		echo '<div>Host query failed. [MSG-2116]';
		// *** TODO: Add link to add this as new session to db
		die();
	}
	$new_session = false;
	if ( isset($_GET['fcmd']) ) {
		if ( $_GET['fcmd'] == 'create-session' ) {
			$new_session = true;
		}
	}
	// if ( $ip_recs->num_rows == 0 && !$new_session ) {
	// 	echo '<div>Session id "'.$session_id.'" not found in database. [MSG-2116]';
	// 	// *** TODO: Add link to add this as new session to db
	// 	die();
	// }

	// display a list of ip addresses available to test
	$ip_list = '';
	while ( $ip = db_fetch_array( $ip_recs ) ) {

		$status_color = '';
		if ( $ip['status'] == 'PWNED' ) {
			$status_color = ' green';
		}
		if ( $ip['status'] == 'LLSHELL' ) {
			$status_color = ' blue';
		}

		$host_link = '<a class="hover-link" href="index.php?'.pentdb_get_urlparms( array( 'ip'=>$ip['ip_address']) ).'">';

		$ip_list .= '<details><summary class="ip-link"><span class="host-status'.$status_color.'">['.$ip['status'].'] &nbsp; </span>'
			. ' <span class="host-points">'.$ip['points'].' pts &nbsp;</span>'
			. ' <span class="host-ip">'.$host_link.$ip['ip_address'].'</a></span>'
			. ' <span class="host-name">'.$host_link.$ip['name'].'</a></span>'
			. ' <span class="host-spots">'.ptdb_build_host_spots($session_id,$ip['ip_address']).'</span>'
			. '</summary>'."\n";

		$ip_list .= build_ip_status_display( $session_id, $ip['ip_address'] )
			.'<div class="bottom-space"></div></details>'."\n";

	}
	if ( $ip_list ) {
		$ip_list = "<h2>Select IP address to test:</h2>\n" . $ip_list;
		$ip_list .= "\n".'<p class="clear">. </p>'."\n";
	}

	$mypage = $ip_list . get_add_host_form();
	display_page( $mypage, "session-page" );
}


	//
	// we have no parms. Display a list of sessions to select, or
	//	create a new session.

function display_sessions() {
	$sess_q = "SELECT * FROM {sessions} GROUP BY session_id ORDER BY created DESC,session_id";
	try {
		$sess_recs = db_query( $sess_q );
		if ( !$sess_recs ) {
			echo '<div>No sessions found in database. [MSG-11]</div>'."\n";
			echo '<div><em>Do you need to <a href="Pen-db_init.php">intialize the database</a>?</em></div>'."\n";
			// echo '<div>Error info: <pre>'.print_r($sess_recs,true).'</pre></div>';
		}
	}
	catch ( Exception $e ) {
		echo '<div>Error info: <pre>'.print_r($e,true).'</pre></div>';
	}

	// display a list of sessions available to test
	$sess_list = '';
	while ( $session = db_fetch_array( $sess_recs ) ) {
		$date = date("Y-M-d", strtotime($session['created']) );
		$sess_list .= '<div><a href="index.php?session_id='.$session['session_id'].'">'.$session['session_id'].'</a> &nbsp; <span class="session-date">('.$date.')</span></div>'."\n";
	}
	if ( $sess_list ) {
		$sess_list = "<h2>Select a test session:</h2>\n" 
		. '<div class="session-list">'
		. $sess_list
		. "</div>\n";
		$sess_list .= "\n<p></p>";
	}

	// display a create session form
	$myform = get_session_form();

	$mypage = $sess_list . $myform;
	display_page( $mypage );
}



	//
	// display a service test set page
	//

function display_service_page( $session_id, $ip, $service, $port ) {

	unset($_GET['vuln']);		// [_] *** TODO: Figure out a better way to do vuln display page

	$tests_recs = ptdb_get_test_set( $session_id, $ip, $service, $port );
	$org_service = $service;		// this is a hack for the MakeDepth/MakeBinary button-forms

	// display a list of tests for this service
	$test_list = '';
	$service = '';
	$passD = 0;
	while ( $test = db_fetch_array( $tests_recs ) ) {
		$auto_expand = false;
		$status_bar = 'status-empty';
		if ( !empty($test['flags']) ) {
			$status_bar = 'status-flagged';
		}
		switch ($test['statustype']) {
			case 'BINARY':
				switch ($test['status']) {
					case 'POS':
						$status_bar = 'status-completed';
						$auto_expand = true;
						break;
					case 'NEG':
						$status_bar = 'status-negative';
						break;

					case 'IN-PROGRESS':
						$status_bar = 'status-in-progress';
						$auto_expand = true;
						break;

					default:
						break;
				}

			case 'DEPTH':
				switch ($test['status']) {
					case 'POS':
						$status_bar = 'status-completed';
						$auto_expand = true;
						break;
					case 'NEG':
						$status_bar = 'status-negative';
						break;

					case 'IN-PROGRESS':
						$status_bar = 'status-in-progress';
						$auto_expand = true;
						break;

					default:
						break;
				}
				if ( is_numeric($test['status']) && $test['status'] > 0 ) {
					$status_bar = 'status-in-depth';
				}
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
		if ( $test['statustype'] == 'DEPTH' ) {
			$buttons .= get_binary_status_button( $test['status'], $test['irid'] )
					. get_depth_status_button( $test['status'], $test['irid'] );
		}

		// add a button to swtich from BINARY to DEPTH statustypes
		if ( $test['statustype'] == 'DEPTH' ) {
			$fcmd = 'set-stype-binary';
			$button_name = 'MakeBinary';
		} else {
			$fcmd = 'set-stype-depth';
			$button_name = 'MakeDepth';	
		}
		// $button_name = 'pasta';
		$org_service = ($service ? $service : $test['service']);
		$buttons .= '
		<div><FORM class="statusform" action="index.php#test-'.$test['irid'].'" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$session_id.'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$ip.'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$org_service.'"></INPUT>
			<INPUT type="hidden" name="port" value="'.$port.'"></INPUT>
			<INPUT type="hidden" name="fcmd" value="'.$fcmd.'"></INPUT>
			<INPUT type="hidden" name="recid" value="'.$test['irid'].'"></INPUT>
			<INPUT type="submit" value="'.$button_name.'"></INPUT>
			<INPUT type="hidden" name="safe" value="check"></INPUT>
		</FORM></div>';

		// add a simple copy-cmd button with irid displayed and a mysql mod record cmd
		$qmysqlid = "qsql-".$test['irid'];
		$buttons .= '<INPUT class="hideme" type="text" id="'.$qmysqlid.'" value="UPDATE testinstance set title=\'newtitle\' WHERE irid='.$test['irid'].';"></INPUT><button class="sql-copy" onclick="ptdb_copytext(\''.$qmysqlid.'\')">rid:'.$test['irid'].'</button>';

		$buttons .= '</div>'."\n";

		$fieldguide_form = get_fieldguide_form( $test['irid'], $test['fieldguide_file'] );
		$fieldguide_display = get_fieldguide_display( $ip, $test['fieldguide_file'] );

		$info_form = get_info_form( $test['irid'], $test['info'] );
		$notes_form = get_notes_form( $test['irid'], $test['notes'] );
		$discovered_form = get_discovered_form( $test['irid'], $test['discovered'] );
		$raw_result_form = get_raw_result_form( $test['irid'], $test['raw_result'] );

		$banner_form = '';
		if ( $test['rectype'] == 'TITLE' ) {
			$banner_form = get_add_banner_form( $test['irid'] );
		}
		$flags_form = get_set_flags_form( $test['irid'] );
		$watchfile_form = get_watchfile_form( $test['irid'], $test['watch_file'] );
		$watchfile_display = get_watchfile_display( $ip, $test['watch_file'] );

		// if the last form submit operation was about this test, then keep this test open
		// [_] *** TODO reconcile $_GET['rec_id'] and $_GET['recid']... can they all be the same?
		if ( $_GET['recid'] == $test['irid'] || $_GET['rec_id'] == $test['irid'] ) {
			$auto_expand = true;
		}
		if ( isset($_GET['expand']) ) {
			if ( $_GET['expand'] == $test['irid'] ) {
				$auto_expand = true;
			}
		}

		$summary = 'summary';
		$details = 'details';
		if ( $auto_expand ) { 
			$summary = 'div';
			$details = 'div';
		}
		$lineid = "Tcmd".$test['irid'];
		$test_list .= '<div class="test-wrapper" id="test-'.$test['irid'].'">'."\n";
		$test_list .= '<'.$details.'>'."\n";
		$test_list .= '<'.$summary.' class="test-title '.$status_bar.'">'.strtoupper($test['rectype']).': &nbsp;&nbsp;'.$test['title'].($test['banner'] ? ' - '.$test['banner'] : '').'</'.$summary.'>'
			. $buttons
			. '<div class="flags-display">'.($test['flags'] ? 'Flags: ' : '').$test['flags'].'</div>'."\n"
			. get_add_cmd_form( $test['irid'], fill_varset(str_replace('"', '&quot;',$test['cmd'])) )
			// . '<div class="test-cmd">CMD: <input class="cmd-text" type="text" value="'.addslashes(fill_varset(str_replace('"', '&quot;',$test['cmd']))).'" id="'.$lineid.'"><button class="cmd-copy" onclick="ptdb_copytext(\''.$lineid.'\')">Copy</button></div>'
			.  get_add_processcmd_form( $test['irid'], fill_varset(str_replace('"', '&quot;',$test['process_result_cmd'])) )
			// . '<div class="test-process">PROCESS: <input class="cmd-text" type="text" value="'.addslashes(fill_varset(str_replace('"', '&quot;',$test['process_result_cmd']))).'" id="P'.$lineid.'"><button class="cmd-copy" onclick="ptdb_copytext(\'P'.$lineid.'\')">Copy</button></div>'
			. "\n";

		$test_list .= $fieldguide_form . $fieldguide_display;

		$test_list .= $info_form . $notes_form . $banner_form . $flags_form . $watchfile_form . $watchfile_display . $discovered_form . $raw_result_form;
		$test_list .= '</'.$details.'>'."\n";
		$test_list .= '</div>'."\n";	// close test-wrapper


		$service = ($service ? $service : $test['service']);
	}

	if ( $test_list ) {
			// build page title
		$quicklinks = '<div class="quicklink"><a href="#add-test-form">Add a test</a> <a href="#add-objective-form">Add an objective</a> <a href="#add-vuln-form">Add a vuln</a> <a href="#save-template-form">Save this test set as a Template</a></div>'."\n";
		$test_list = $quicklinks . '<h2>Test Set, <a class="hover-link" href="index.php?session_id='.$session_id.'&ip='.$ip.'">IP '.$ip.'</a>, service '.$service.' / '.$service.':</h2>'."
		\n" 
		  . build_service_status_display( $session_id, $ip, $service, $port )
		  . "\n<p>&nbsp;</p>\n" . $test_list;

		$test_list .= "\n".'<p class="clear"></p>'."\n";
	} else {
		$test_list .= "<h2>No tests found.</h2>";
		$test_list .= "<div>ip ".$ip.", service ".$service."</div>\n";
	}

	$mypage = $test_list . get_add_test_form() . get_add_objective_form() . get_add_vuln_form() . get_save_template_form();

	display_page( $mypage );
}


	//
	// display a services list page
	//

function display_serviceslist_page( $session_id, $ip ) {

	unset($_GET['vuln']);		// [_] *** TODO: Figure out a better way to do vuln display page

	// display host information & update form
	$host_form = '';
	$host_rec = pentdb_get_host_record( $session_id, $ip );
	foreach ($host_rec as $name => $value) {
		$host_form .= get_add_host_datum_form( $name, $value, $host_rec['hid'] );
	}
	if ( $host_form ) {
		$host_form = "<h2>Host info:</h2>\n" . $host_form;
		$host_form .= "\n".'<p class="clear">. </p>'."\n";
	}



	$service_q = "SELECT * FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' AND rectype='TITLE' GROUP BY port,service ORDER BY service";
	$service_recs = db_query( $service_q, $session_id, $ip);
	if ( !$service_recs ) {
		echo '<div>Session id "'.$session_id.'"not found in database.';
		// *** TODO: Add link to add this as new session to db
		die();
	}

	// display a list of services available to test
	$service_list = '';
	$discoveries = '';
	while ( $service = db_fetch_array( $service_recs ) ) {

			// port zero is the HOST record; skip that
		// if ( $service['port'] == 0 ) {
		// 	continue;
		// }

		$panel = build_service_status_display( $session_id, $ip, $service['service'], $service['port'] );
		$service_list .= $panel."\n";

		// get any discoveries and add them to our list
		$discoveries .= read_discoveries( $session_id, $ip, $service['service'], $service['port'] );

	}

	if ( $service_list ) {
		$points_display = '';
		if ( !empty($host_rec['points']) ) {
			$points_display = ' &nbsp;<em>('. $host_rec['points'].' pts)</em>';
		}
		$host_info = '<div class="host-info">'
			. $host_rec['platform'].' '
			. $host_rec['cpu_arch'].' '
			. $host_rec['os_version'].' ' 
			. $host_rec['patch_version'].' '
			. ($host_rec['service_pack'] ? "SERVICE PACK " . $host_rec['service_pack'] : '') . '</div>';
		$service_list = "<h2>Host: ".$host_rec['name'].' / '
			. $host_rec['ip_address'] 
			. $points_display
			. "</h2>\n" . $host_info . $service_list;
		$service_list .= "\n".'<p class="clear"></p><p>&nbsp;</p>'."\n";
	}

	if ( $discoveries ) {
		$discoveries = 
			'<div class="discoveries-section"><h3>Discoveries:</h3>'. $discoveries . "</div>";
		$discoveries .= "\n".'<p class="clear"></p><p>&nbsp;</p>'."\n";
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
		$test_list .= '<OPTION value="'.$test['service'].'">'.$test['port'].' ('.$test['service'].') </OPTION>'."\n";
	}

	$myform = '
		<div><FORM id="quick-add-service" action="index.php" method="GET">
		<SELECT name="service">
	'.$test_list.'
		</SELECT>
		<LABEL for="altport">port:</LABEL>
		<INPUT type="text" name="altport" id="altport"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$session_id.'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$ip.'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="add-service"></INPUT>
		<INPUT type="submit" value="Add service set"></INPUT>
		</FORM></div>
	';

	$quicklink_add_service = '<div class="quicklink"><a href="#quick-add-service">Add a service</a> <a href="#add-objective-form">Add an objective</a> <a href="#add-vuln-form">Add a vuln</a></div>'."\n";

	$mypage = $quicklink_add_service 
		. $service_list 
		. $discoveries
		. $host_form 
		. $myform 
		. get_add_service_form() 
		. get_add_objective_form() 
		. get_add_vuln_form();

	display_page( $mypage );
}


