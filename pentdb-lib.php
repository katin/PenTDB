<?php

// pentdb-lib.php
//
// Library of support functions for the PenTDB system.
//
// 190519 KBI - created


// clean()
//
// Santize the parameter; assumed passed in a GET or POST form.

function pentdb_clean( $item ) {

	// return only letters, numbers, underscores, periods or dashes
	return( preg_replace('/[^-a-zA-Z0-9_.]/', '', $item) );

}


// validate ip
//
// Validate an ip address that has been passed in

function pentdb_validate_ip( $ip ) {
	return filter_var($ip, FILTER_VALIDATE_IP);
}

function pentdb_validate_port( $port ) {
	if ( is_numeric($port) && $port > 0 && $port < 65536 ) {
		return $port;
	} else {
		return false;
	}
}

function pentdb_validate_service( $service ) {
	// [_] TODO: write service validator
	// can't just use db query, since new services can be added per host
	return $service;

}

// fill_varset
//
// Fill in any variables that we know about:
//	$ip = ip address
//  $port = port

function fill_varset( $line, $ip = NULL, $port = NULL ) {
	// by this time, the $_GET parms have been validated
	// TO-DO: better global storage and passing of current page parms

	$my_ip = ($ip ? $ip : $_GET['ip']);
	$my_port = ($port ? $port : $_GET['port']);

	$line = str_replace ( '$ip' , $my_ip , $line );
	$line = str_replace ( '$port' , $my_port , $line );

	return $line;
}



// display_page
//
// Display page top, footer, and page tail around the given content
// and end execution

function display_page( $content ) {
	display_html_header();
	echo $content;
	wrapup_page();
	die();
}


// html header
//
// Issue an HTML header with styles, etc.

function display_html_header() {
?>
<HTML>
<HEAD>
  <link rel="stylesheet" type = "text/css" href = "pentdb-styles.css" />
  <script src="pentdb.js"></script>
</HEAD>

<BODY>
	<H1><a class="hover-link" href="index.php">PenTDB Tool by K10</a></H1>
<?php
	if ( isset($_GET['session_id']) ) {
		$sid = pentdb_clean( $_GET['session_id'] );
		echo '<h2>Session ID: <a class="hover-link" href="index.php?session_id='.$sid.'">'.$sid.'</a></h2>'."\n";
	}
}


// wrapup_page
//
// Display footer and end-of-page code

function wrapup_page() {
	display_html_footer();
?>
</BODY>
</HTML>
<?php
}


// html footer
//
// Issue our HTML footer with close tags, links, etc.

function display_html_footer() {

	pentdb_log_error('','display');

}


// log error
//
// Simple log filer / displayer for PenTDB system.
//
// For now, this just displays to an area at the footer, but will write to a disk log someday.

function pentdb_log_error( $msg, $mode='log' ) {
static $error_log_html;

	if ( $mode == 'display' ) {
		echo "<hr>\n".$error_log_html;
		return;
	}

	$error_log_html .= '<div>'.$msg.'</div>'."\n";
}


// create_session
//
// Create a test session - for now, just set the parameter to pass along

function pentdb_create_session( $name ) {
	return $name;
}


// add_ip
//
// add an IP address to the testing list for the specified session

function pentdb_add_ip( $ip, $session ) {
	$addip_q = "INSERT into testinstance (session_id,ip_address,rectype,title) VALUES ('%s','%s','HOST','%s')";
	$addip_result = db_query( $addip_q, $session, $ip, 'HOST '.$ip);
	if ( !$addip_result ) {
		echo '<div>Query failed.';
		echo "<div></pre>".print_r($addip_result,true)."</pre></div>";
		return false;
	}
	return $ip;
}


// add_port
//
// add all the template records for the given port to the testinstance table

function pentdb_add_port( $the_ip, $the_session, $port, $service ) {

	$ip = pentdb_clean($_GET['ip']);
	// $port = pentdb_clean($_GET['port']);
	$session_id = pentdb_clean($_GET['session_id']);

	// fetch the port template
	$template_q = "SELECT * from {porttest} WHERE service='%s'";
	$template_recs = db_query( $template_q, $service );
	if ( !$template_recs ) {
		echo '<div class="error">Template for service '.$service.' not found in database.</div>';
		die();
	}

	$count = 0;
	$errcount = 0;
	while ( $template = db_fetch_array($template_recs) ) {

		//echo "<div></pre>".print_r($template,true)."</pre></div>";


		// check to see if record aready exists
		$dup_q = "SELECT irid FROM {testinstance} WHERE "
			. "session_id='%s' AND "
			. "ip_address='%s' AND "
			. "port='%s' AND "
			. "title='%s' ";
		$dup_result = db_fetch_array(db_query( $dup_q, $session_id, $ip, $port, $template['title']));
		if ( $dup_result ) {
			echo '<div>Session "'.$session_id.'", record port '.$port.': "'.$template['title'].'" already on file - skipping insert.';
			continue;
		}

		// Create the instance record
		$instance_q = "INSERT into {testinstance} (session_id, ip_address, pass_depth, port, service, rectype, statustype, title, cmd, process_result_cmd, order_weight)"
			. " VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')";
		$result = db_query( $instance_q,
			$session_id,
			$ip,
			$template['pass_depth'],
			$port,
			$service,
			$template['rectype'],
			$template['statustype'],
			fill_varset( $template['title'], $ip, $port),
			$template['cmd'],
			$template['process_result_cmd'],
			$template['order_weight']
		);

		if ( !$result ) {
			$errcount++;
			echo '<div class="error">Error adding instance record "'.$template['title'].'".</div>';
			// die();
		} else {
			$count++;
			echo '<div>Adding test "'.$template['title'].'"</div>';
		}
	}
	echo '<div class="status">Instance record set: '.$count.' tests added with '.$errcount.' errors.</div>';

	return $port;
}


function ptdb_set_binary_status( $status ) {
	$vars = pentdb_get_page_vars();

	if ( !isset( $vars['rec_id']) ) {
		pentdb_log_error("Can't update status: missing rec_id. ERR-721.");
		return false;
	}

	$set_q = "UPDATE testinstance set status='%s' WHERE irid='%d'";
	$set_result = db_query( $set_q, $status, $vars['rec_id'] );
	if ( !$set_result ) {
		echo '<div>Query failed.';
		echo "<div></pre>".print_r($addip_result,true)."</pre></div>";
		return false;
	}
	return $status;
}


function pentdb_get_page_vars() {
	$vars = array();
	if ( isset($_GET['session_id']) ) {
		$vars['session_id'] = pentdb_clean( $_GET['session_id'] );
	}
	if ( isset($_GET['port']) ) {
		$vars['port'] = pentdb_clean( $_GET['port'] );
	}
	if ( isset($_GET['ip']) ) {
		$vars['ip'] = pentdb_clean( $_GET['ip'] );
	}
	if ( isset($_GET['cmd']) ) {
		$vars['cmd'] = pentdb_clean( $_GET['cmd'] );
	}
	if ( isset($_GET['rec_id']) ) {
		$vars['rec_id'] = pentdb_clean( $_GET['rec_id'] );
	}
	if ( isset($_GET['service']) ) {
		$vars['service'] = pentdb_clean( $_GET['service'] );
	}

	return $vars;
}


// build_ip_status_display
//
// Builds a simple, boxes-and-colors status display for the
//  given session and ip.
// Hover-over for more details of each test.

function build_ip_status_display( $session_id, $ip ) {

	$output = '<div class="ip-test-status">'."\n";

	$servicelist = get_service_list( $session_id, $ip );

// echo "<div><pre>".print_r($servicelist,true)."</pre></div>";
// die("check");

	foreach( $servicelist as $service ) {
		$output .= build_service_status_display( $session_id, $ip, $service );
	}

	$output .= '</div>'."\n";

	return $output;
}


function get_service_list( $session_id, $ip ) {
	$servicelist = array();

	$service_q = "SELECT DISTINCT service FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' ORDER BY port";

// echo "<div><pre>".print_r($service_q,true)."</pre></div>";
// die("check");

	$service_recs = db_query( $service_q, $session_id, $ip );
	if ( !$service_recs ) {
		pentdb_log_error('<div>Services query failed. [Error-2111]</div>' );
		return false;
	}
	if ( $service_recs->num_rows == 0 ) {
		pentdb_log_error('<div>No service records found. [Notice 211]</div>' );
		return false;
	}

	while ( $rec = db_fetch_array( $service_recs) ) {
		// for now, dont' show service zero 
		if ( empty($rec['service'])) {
			continue;
		}
		$servicelist[] = $rec['service'];
	}

	return $servicelist;
}


function base_link($session_id, $ip, $service, $extra = NULL, $spot = NULL) {
	return '<a '.$extra.' href="index.php?session_id='.$session_id.'&ip='.$ip.'&service='.$service.($spot ? "#".$spot : '').'">';
}

// build_service_status_display
//
// Create HTML for a one-line, simple boxes-and-colors status display 
//  of the given service for the given ip address in the given session.
// This can grow more sophisticated over time, adding visual indicators
//  for flags, discoveries, and more.
// Hover-over for more details of the test.

function build_service_status_display( $session_id, $ip, $service ) {

	$output = '<div class="service-test-status">'."\n";
	$rec_handle = read_service_records( $session_id, $ip, $service );

// echo "<div>rows:<pre>".$rec_handle->num_rows."</pre></div>";

	$depth = 0;
	$title_found = false;
	while ( $rec = db_fetch_array($rec_handle) ) {
		if ( !$title_found ) {
			if ( $rec['rectype'] == 'TITLE' ) {
				$output .= base_link($session_id,$ip,$service,'class="hover-link"').'<div class="label">'.$rec['service'].' port '.$rec['port'].'</div></a>'."\n";
				$title_found = true;
			}
		}
		$depth_mark = '';
		if ( $rec['pass_depth'] > $depth ) {
			$depth++;
			$depth_mark = '<div class="depth-divider"></div>'."\n";
		}
		$display_color = get_status_color( $rec['statustype'], $rec['status'], $rec['flags'] );
		$title = 'title="'.$rec['title'].($rec['flags'] ? ' - FLAGS: '.$rec['flags'] : '').'"';
		$link = base_link($session_id,$ip,$service,$title,"test-".$rec['irid']); 
		$block = $link.'<div class="indicator '.$display_color.'"></div></a>'."\n";
		$output .= $depth_mark . $block;

// echo "<div><pre>".print_r($rec,true)."</pre></div>";
// die("check");

	}

	$output .= '</div>'."\n";

// echo "<div><pre>".print_r($output,true)."</pre></div>";

	return $output;
}


function read_service_records( $session_id, $ip, $service ) {

	$tests_q = "SELECT * FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' AND service='%s' ORDER BY pass_depth, order_weight";

	$tests_recs = db_query( $tests_q, $session_id, $ip, $service );
	if ( !$tests_recs ) {
		pentdb_log_error( '<div>Query or DB error. [Error 612]' );
		return false;
	}
	if ( $tests_recs->num_rows == 0 ) {
		pentdb_log_error( '<div>No records found for service '.$service.' session "'.$session_id.'" [MSG-2118]' );
		return false;
	}

	return $tests_recs;
}


function get_status_color( $statustype, $status, $flags = NULL ) {
	$status_color = 'gray';

	if ( !empty($flags) ) {
		$status_color = 'yellow';
		return $status_color;
	}

	switch ($statustype) {
		case 'BINARY':
			switch ($status) {
				case 'POS':
					$status_color = 'green';
					break;

				case 'NEG':
					$status_color = 'red';
					break;

				case 'IN-PROGRESS':
					$status_color = 'orange';
					break;

				default:
					break;
			}

		case 'DEPTH':
			break;

		case 'NONE':
			break;

		default:
			pentdb_log_error("NOTE: Unknown statustype: ".$status);
			break;
		}
	return $status_color;
}


function get_binary_status_button( $status, $rec_id ) {
	$vars = pentdb_get_page_vars();

	$pos_class = '';
	if ($status == 'POS') {
		$pos_class = 'class="green-button" ';
	}

	$neg_class = '';
	if ($status == 'NEG') {
		$neg_class = 'class="red-button" ';
	}

	$progress_class = '';
	if ($status == 'IN-PROGRESS') {
		$progress_class = 'class="orange-button" ';
	}

	$button_form = '
		<div><FORM action="index.php" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="cmd" value="set-progress"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>
			<INPUT '.$progress_class.'type="submit" value="InProgress"></INPUT>
		</FORM></div>
			<div><FORM action="index.php" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="cmd" value="set-pos"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>
			<INPUT '.$pos_class.'type="submit" value="POS"></INPUT>
		</FORM></div>
		<div><FORM action="index.php" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="cmd" value="set-neg"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>		
			<INPUT '.$neg_class.'type="submit" value="NEG"></INPUT>
		</FORM></div>
	';

	return $button_form;
}


function create_port_record() {
	// YES IT'S INSECURE -- DON'T PROCESS FORMS LIKE THIS!

	$fields = ' (';
	$values = ' VALUES (';
	$comma = false;
	foreach( $_GET as $key => $value ) {
		if ( $key == 'cmd' ) {
			continue;
		}
		if ( $key == 'ip' ) {
			$key = 'ip_address';
		}
		if ( $key == 'command' ) {
			$key = 'cmd';
		}
		echo '<div>'.$key." / ".$value.'</div>'."\n";
		$fields .= ($comma ? ',' : '').$key;
		$values .= ($comma ? ',' : '').'"'.$value.'"';
		$comma = true;
	}
	$fields .= ') ';
	$values .= ') ';

	$newp_q = "INSERT into {testinstance}".$fields.$values;

echo "<div>".$newp_q."</div>\n";

	$newp_result = db_query( $newp_q );
	if ( !$newp_result ) {
		pentdb_log_error ('<div>Insert failed. [ERR-486]</div>');
		return false;
	}
	return true;

}

function get_add_test_form( $title = "Add a test" ) {
	$vars = pentdb_get_page_vars();
	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">

		<LABEL for="title">Test display title: </LABEL>
		<INPUT type="text" name="title" id = "title"></INPUT><br/>

		<LABEL for="banner">Banner: </LABEL>
		<INPUT type="text" name="banner" id = "banner"></INPUT><br/>

		<LABEL for="rectype">Record/Test type: </LABEL>
		<SELECT name="rectype" id="rectype">
			// <OPTION value="TITLE">TITLE</OPTION>
			<OPTION value="SCAN">SCAN</OPTION>
			<OPTION value="TOOL">TOOL</OPTION>
			<OPTION value="SCRIPT">SCRIPT</OPTION>
			<OPTION value="HOST">HOST</OPTION>
			<OPTION value="EXAMINE">EXAMINE</OPTION>
		</SELECT><br/>

		<LABEL for="pass_depth">Pass depth: </LABEL>
		<INPUT type="text" name="pass_depth" id="pass_depth" value="0"></INPUT><br/>

		<LABEL for="order_weight">Order Weight: </LABEL>
		<INPUT type="text" name="order_weight" id="order_weight" value="0"></INPUT><br/>

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

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="cmd" value="new-port"></INPUT>
		<INPUT type="submit" value="Add a test"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

		// <INPUT type="hidden" name="port" value="'.$port.'"></INPUT>

	return $bigform;
}

function get_add_banner_form( $recid ) {
	$vars = pentdb_get_page_vars();
	$myform = '
		<div class="inlineform"><FORM action="index.php" method="GET">

		<LABEL for="banner">Banner: </LABEL>
		<INPUT type="text" name="banner" id = "banner"></INPUT>

		<INPUT type="hidden" name="recid" value="'.$recid.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>

		<INPUT type="hidden" name="cmd" value="update-banner"></INPUT>
		<INPUT type="submit" value="Update Banner"></INPUT>
		</FORM></div>
	';

	return $myform;
}


function get_set_flags_form( $recid ) {
	$vars = pentdb_get_page_vars();
	$myform = '
		<div class="inlineform"><FORM action="index.php" method="GET">

		<LABEL for="flags">Flags: </LABEL>
		<INPUT type="text" name="flags" id = "flags"></INPUT>

		<INPUT type="hidden" name="recid" value="'.$recid.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>

		<INPUT type="hidden" name="cmd" value="update-flags"></INPUT>
		<INPUT type="submit" value="Update Flags"></INPUT>
		</FORM></div>
	';

	return $myform;
}


function get_notes_form( $recid, $notes ) {
	$vars = pentdb_get_page_vars();
	$myform = '
		<div class="inlineform"><FORM class="notes-form" action="index.php" method="GET" id="notes-form-'.$recid.'">

		<LABEL for="notes_form">Notes: </LABEL>
		<textarea wrap="soft" cols="80" rows="8" name="notes" id ="notes">'.$notes.'</textarea><br/>

		<INPUT type="hidden" name="recid" value="'.$recid.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>

		<INPUT type="hidden" name="cmd" value="update-notes"></INPUT>
		<INPUT type="submit" value="Update Notes"></INPUT>
		</FORM></div>

	';

		// <textarea rows="4" cols="50" name="notes" form="notes-form">'.$notes.'</textarea>


	return $myform;
}
