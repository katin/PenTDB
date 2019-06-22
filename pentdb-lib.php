<?php

// pentdb-lib.php
//
// Library of support functions for the PenTDB system.
//
// 190519 KBI - created

global $top_message;
global $webpages_cache_path;
global $base_path;

	$webpages_cache_path = "../exploit-db-pages/";
	$base_path = '/'.substr(__FILE__, 1, strrpos(__FILE__,'/'));


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

	// okay for service to be blank if we have a session_id and an fcmd
	$current_session = pentdb_clean( $_GET['session_id'] );
	$current_fcmd = pentdb_clean( $_GET['fcmd'] );
	if ( !empty($current_session) && !empty($current_fcmd) ) {
		return true;
	}

	return $service;

}

// fill_varset
//
// Fill in any variables that we know about:
//	$ip = ip address
//  $port = port
//  $i4p = forth octet of the current IP address

function fill_varset( $line, $ip = NULL, $port = NULL ) {
	// by this time, the $_GET parms have been validated
	// TO-DO: better global storage and passing of current page parms

	$my_ip = ($ip ? $ip : $_GET['ip']);
	$my_port = ($port ? $port : $_GET['port']);
	$my_i4p = substr( $my_ip, strrpos( $my_ip, '.')+1, 99);
	$my_hostname = pentdb_get_hostname( $my_ip );

// die('i4p='.$my_i4p);

	$line = str_replace ( '$i4p' , $my_i4p, $line );
	$line = str_replace ( '$ip' , $my_ip, $line );
	$line = str_replace ( '$port' , $my_port, $line );
	$line = str_replace ( '$hostname' , $my_hostname, $line );

	return $line;
}


function pentdb_get_hostname( $my_ip ) {
	$vars = pentdb_get_page_vars();
	$host_record = pentdb_get_host_record( $vars['session_id'], $my_ip );

	return $host_record['name'];
}


// get_session_path
//
// Read the session record and return the data path set

function pentdb_get_session_path() {
	$session_id = pentdb_clean( $_GET['session_id'] );
	$path_q = "SELECT * FROM {sessions} where session_id='%s'";
	$session_rec = db_fetch_array( db_query( $path_q, $session_id ) );
	if ( $session_rec ) {
		return $session_rec['data_path'];
	}

}

// get_cmd_path
//
// Read the session record and return the data path set

function pentdb_get_cmd_path() {
	$session_id = pentdb_clean( $_GET['session_id'] );
	$path_q = "SELECT * FROM {sessions} where session_id='%s'";
	$session_rec = db_fetch_array( db_query( $path_q, $session_id ) );
	if ( $session_rec ) {
		return $session_rec['cmd_path'];
	}

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
	$vars = pentdb_get_page_vars();
	$path = pentdb_get_session_path();
	$output = '';
	if ( isset($vars['session_id']) ) {
		$output .= '<span class="session-title">Session ID: <a class="hover-link" href="index.php?session_id='.$vars['session_id'].'">'.$vars['session_id'].'</a> </span><span class="spacer bold">data path: </span><span class="dir-path">'.$path.'</span>'."\n";
	}
	// build quick-links to other services on this IP address
	$services_list = get_service_list( $vars['session_id'], $vars['ip'] );
	$other_services_html = '';
	if ( isset($vars['ip']) ) {
		if ( !empty($vars['ip']) ) {
			$other_services_html .= '<span class="top-ip-addr">'.base_link($vars['session_id'], $vars['ip'], ' ', ' ','class="hover-link"').$vars['ip']."</a></span>\n";
		}
	}
// echo "<div><pre>".print_r($services_list,true)."</pre></div>";
	foreach ( $services_list as $service ) {
		$highlite = '';
		if ( $service['port'] == $vars['port'] && $service['service'] == $vars['service'] ) {
			$highlite = ' highlight';
		}
		$other_services_html .= '<span class="ip-service-link'.$highlite.'">'.base_link($vars['session_id'], $vars['ip'], $service['service'], $service['port']).'('.$service['port'].') '.$service['service']."</a></span>\n";
// echo "<div><pre>".print_r($service,true)."</pre></div>";

	}

// die('here!');
// $t1 = build_vuln_status_display( $vars['session_id'], $vars['ip'] );
// echo "<div>t1: <pre>".print_r($t1,true)."</pre></div>";
// echo "<div>session: <pre>".print_r($vars['session_id'],true)."</pre></div>";
// echo "<div>ip: <pre>".print_r($vars['ip'],true)."</pre></div>";

	$output .= '<div class="services-links-bar">' . $other_services_html . "</div>\n";

	$output .= '<div class="objectives-links-bar">'.build_objectives_status_display( $vars['session_id'], $vars['ip'] ). "</div>\n";

	$output .= '<div class="vulns-links-bar">'.build_vuln_status_display( $vars['session_id'], $vars['ip'] ). "</div>\n";
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

function wrapup_page() {
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

function display_html_footer() {

	// pentdb_log_error('','display');

}


// log error
//
// Simple log filer / displayer for PenTDB system.
//
// For now, this just displays to an area at the footer, but will write to a disk log someday.

function pentdb_log_error( $msg, $mode='log' ) {
static $error_log_html;

// $error_log_html = "ALARMZ!";

	if ( $mode == 'display' && !empty( $error_log_html ) ) {
		$output = '<div class="error-log display">'."\n";
		$output .= $error_log_html;
		$output .= '</div>'."\n";
		return $output;
	}

	$error_log_html .= '<div>'.$msg.'</div>'."\n";
}

// top msg
//
// Simple message logger and  displayer for PenTDB system.
//
// Collects messages. Displays to an area at the page top.

function pentdb_top_msg( $msg, $mode='log' ) {
static $top_msg_html;

// $top_msg_html = "GREETINGZ!";

	if ( $mode == 'display' && !empty( $top_msg_html ) ) {
		$output = '<div class="top-msg display">'."\n";
		$output .= $top_msg_html;
		$output .= '</div>'."\n";
		return $output;
	}

	$top_msg_html .= '<div>'.$msg.'</div>'."\n";
}


// create_session
//
// Create a test session - for now, just set the parameter to pass along

function pentdb_create_session( $name ) {
	$session = pentdb_clean( $name );
		// [_] *** TODO: sanitize data_path
	$data_path = $_GET['dir'];		// don't pentdb_clean() this; it removes the slashes!
	$cmd_path = $_GET['cmd_path'];		// don't pentdb_clean() this; it removes the slashes!
	$addsess_q = "INSERT into sessions (session_id,data_path,cmd_path) VALUES ('%s','%s','%s')";
	$addsess_result = db_query( $addsess_q, $session, $data_path, $cmd_path);
	if ( !$addsess_result ) {
		echo '<div>Query failed.</div>';
		echo "<div></pre>".print_r($addsess_result,true)."</pre></div>";
		return false;
	}

	return $name;
}


// add_ip
//
// add an IP address to the testing list for the specified session

function pentdb_add_ip( $ip, $session, $hostname ) {
	$addip_q = "INSERT into testinstance (session_id,ip_address,rectype,title) VALUES ('%s','%s','HOST','%s')";
	$addip_result = db_query( $addip_q, $session, $ip, 'HOST '.$hostname);
	if ( !$addip_result ) {
		echo '<div>Query failed.</div>';
		echo "<div></pre>".print_r($addip_result,true)."</pre></div>";
		return false;
	}
	return $ip;
}

// add_host
//
// add a new host to the database,
// uses the $_GET[] array for values

function pentdb_add_host() {

	$session_id = pentdb_clean($_GET['session_id']);

	// Create the host record
	$host_q = "INSERT into {host} (session_id, ip_address, name, platform, os_version, patch_version, cpu_arch, core_count, service_pack, status, cmd, process_result_cmd)"
		. " VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')";
	$result = db_query( $host_q,
		$session_id,
		$_GET['ip_address'],
		$_GET['name'],
		$_GET['platform'],
		$_GET['os_version'],
		$_GET['patch_version'],
		$_GET['cpu_arch'],
		$_GET['core_count'],
		$_GET['service_pack'],
		$_GET['status'],
		$_GET['cmd'],
		$_GET['process_result_cmd']
	);

	if ( !$result ) {
		pentdb_log_error("Error adding host record ".$_GET['ip_address']." [ERR-3021]");
		return;
	}

	echo '<div class="status">Host added.</div>'."\n";

	return;
}

function pentdb_update_host() {
	$vars = pentdb_get_page_vars();

	// validate fname ------

	if ( !isset($_GET['fname']) ) {
		pentdb_log_error("Feildname param required to update host. [MSG-4602]");
		return false;
	}
	$fname = pentdb_clean($_GET['fname']);

	$valid_host_fields = pentdb_get_valid_host_fields();
	if ( !in_array($fname, $valid_host_fields) ) {
		pentdb_log_error("Invalid fieldname '".$fname."' passed to update_host(). [MSG-4641]");
		return false;
	}

	// validate field data -----------

	if ( !isset($_GET[$fname]) ) {
		pentdb_log_error("Missing field data parm '".$fname."' in update_host(). [MSG-4607]");
		return false;
	}

	// update db
	$host_q = "UPDATE host SET ".$fname."='%s' WHERE session_id='%s' AND ip_address='%s'";
	$result = db_query( $host_q, $_GET[$fname], $vars['session_id'], $vars['ip'] );
	if ( !$result ) {
		pentdb_log_error("Host update failed. [MSG-4620]");
		return false;
	}

	return true;
}



// add_service
//
// add all the template records for the given port to the testinstance table

function pentdb_add_service( $the_ip, $the_session, $port, $service ) {

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
			. "service='%s' AND "
			. "port='%s' AND "
			. "title='%s' ";
		$dup_result = db_fetch_array(db_query( $dup_q, $session_id, $ip, $service, $port, $template['title']));
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
			pentdb_top_msg("Adding test ".$template['title']);
		}
	}
	pentdb_top_msg("Instance record set: $count tests added with $errcount errors.");

	return $service;
}


// new_vuln
//
// add a new vuln to the vuln database,
// uses the $_GET[] array for values

function pentdb_new_vuln() {

	$ip = pentdb_clean($_GET['ip']);
	$port = pentdb_clean($_GET['port']);
	$service = pentdb_clean($_GET['service']);
	$session_id = pentdb_clean($_GET['session_id']);


	// Create the vuln record
	$vuln_q = "INSERT into {vuln} (session_id, ip_address, title, port, service, url, code_language, status, order_weight)"
		. " VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s')";
	$result = db_query( $vuln_q,
		$session_id,
		$ip,
		$_GET['title'],
		$port,
		$service,
		$_GET['url'],
		$_GET['code_laguage'],
		$_GET['status'],
		$template['order_weight']
	);

	if ( !$result ) {
		echo '<div class="error">Error adding vuln record "'.$template['title'].' [ERR-1021]".</div>';
		// die();
	}

	echo '<div class="status">Vuln added.</div>'."\n";

	return;
}


function pentdb_update_vuln() {
	$vars = pentdb_get_page_vars();

	// validate fname ------

	if ( !isset($_GET['fname']) ) {
		pentdb_log_error("Feildname param required to update vuln. [MSG-4102]");
		return false;
	}
	$fname = pentdb_clean($_GET['fname']);

	$valid_vuln_fields = pentdb_get_valid_vuln_fields();
	if ( !in_array($fname, $valid_vuln_fields) ) {
		pentdb_log_error("Invalid fieldname '".$fname."' passed to update_vuln(). [MSG-4141]");
		return false;
	}

	// validate field data -----------

	if ( !isset($_GET[$fname]) ) {
		pentdb_log_error("Missing field data parm '".$fname."' in update_vuln(). [MSG-4107]");
		return false;
	}

	// update db

	$vuln_q = "UPDATE vuln SET ".$fname."='%s' WHERE vid=".$vars['vuln'];
	$result = db_query( $vuln_q, $_GET[$fname] );
	if ( !$result ) {
		pentdb_log_error("Vuln update failed. [MSG-4120]");
		return false;
	}

	if ($fname == 'url') {
		pentdb_auto_populate_vuln( $_GET[$fname], $vars['vuln'] );
	}

	return $status;
}

function pentdb_get_valid_host_fields() {
	$form_fields = array( 'name', 'platform',
		'os_version','patch_version',
		'cpu_arch', 'core_count','service_pack',
		'status','cmd','process_result_cmd',
		'watch_file','notes','wireshark','proof','loot','lessons_learned','flags'
	);

	return $form_fields;
}

function pentdb_get_valid_obj_fields() {
	$form_fields = array( 'title', 'objective', 'status', 'notes',
		'notes2', 'notes3', 'flags');

	return $form_fields;
}

function pentdb_get_valid_vuln_fields() {
	$form_fields = array( 'title', 'url', 'exploit_type', 'attack_type', 'platform',
		'edb_verified','target_version_match','tested_version_match','exploit_date',
		'exploit_engine','credentials_req','cpu_arch', 'core_count','service_pack_match',
		'has_code','is_poc','code_language','status','cmd','process_result_cmd',
		'watch_file','order_weight','raw_result','discovered','flags','notes',
	);

	return $form_fields;
}


// new_obj
//
// add a new objective to the vuln database,
// uses the $_GET[] array for values

function pentdb_new_objective() {

	$ip = pentdb_clean($_GET['ip']);
	$port = pentdb_clean($_GET['port']);
	$service = pentdb_clean($_GET['service']);
	$session_id = pentdb_clean($_GET['session_id']);


	// Create the vuln record
	$obj_q = "INSERT into {objective} (session_id, ip_address, title, port, service, objective, status)"
		. " VALUES ('%s','%s','%s','%s','%s','%s','%s')";
	$result = db_query( $obj_q,
		$session_id,
		$ip,
		$_GET['title'],
		$port,
		$service,
		$_GET['objective'],
		$_GET['status']
	);

	if ( !$result ) {
		pentdb_log_error("Error adding objective record ".$template['title']." [ERR-1084]");
	}

	pentdb_top_msg("Objective added.");

	return;
}

function pentdb_update_objective() {
	$vars = pentdb_get_page_vars();

	// validate fname ------

	if ( !isset($_GET['fname']) ) {
		pentdb_log_error("Feildname param required to update objective. [MSG-4103]");
		return false;
	}
	$fname = pentdb_clean($_GET['fname']);

	$valid_obj_fields = pentdb_get_valid_obj_fields();
	if ( !in_array($fname, $valid_obj_fields) ) {
		pentdb_log_error("Invalid fieldname '".$fname."' passed to update_objective(). [MSG-4142]");
		return false;
	}

	// validate field data -----------

	if ( !isset($_GET[$fname]) ) {
		pentdb_log_error("Missing field data parm '".$fname."' in update_objective(). [MSG-4108]");
		return false;
	}

	// update db

	$vuln_q = "UPDATE objective SET ".$fname."='%s' WHERE oid=".$vars['vuln'];
	$result = db_query( $vuln_q, $_GET[$fname] );
	if ( !$result ) {
		pentdb_log_error("Objective update failed. [MSG-4120]");
		return false;
	}

	return $status;
}


function pentdb_curl_fetch_page( $url ) {
	$ch=curl_init();
	$timeout=5;

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    curl_setopt($ch, CURLOPT_HEADER  ,1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    // the magic to get past the Securi WAFirewall is to specify a user agent (no more "bad bot")
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux i686; rv:52.0) Gecko/20100101 Firefox/52.0");


	// Get URL content
	$page_source=curl_exec($ch);
	$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

	// close handle to release resources
	curl_close($ch);

	if ( $httpcode > 399 ) {
		pentdb_log_error("Error fetching page ".$url." - HTTP CODE ".$httpcode." [MSG-4104]");
		return false;
	}

	return $page_source;
}



// auto_populate_vuln
//
// If the vuln url is at exploit-db.com, then attempt to 
//   auto-fill as many fields as possible from teh web page

function pentdb_auto_populate_vuln( $url, $vid ) {
global $top_message;
global $webpages_cache_path;

	$key_id = 'https://www.exploit-db.com/exploits/';
	if ( !substr($url, 1, strlen($key_id)) == $key_id ) {
		return false;
	}
	$top_message .= '<div>Processed exploit-db page.</div>'."\n";

	$page_source = pentdb_fetch_cached_page( $url );

	if ( empty($page_source) ) {
		$page_source = pentdb_curl_fetch_page( $url );
	}

	// if we get back false, we didn't get the page
	if ( !$page_source ) {
		return;
	}

	// save this page to cache so we don't have to re-read it later
	pentdb_cache_web_page( $url, $page_source );

// echo "<div><pre>".print_r($page_lines_array,true)."<pre><div>";

	//
	// process the page
	//

 	$dom = new domDocument('1.0', 'utf-8'); 
 	$dom->loadHTML($page_source); 
 	$dom->preserveWhiteSpace = false; 

	$data = array();

	// get exploit title
 	$elem = $dom->getElementsByTagName('title'); // here u use your desired tag
 	$data['title'] = $elem->item(0)->nodeValue;


// die('title:'.$title);

	// get verified status
	//
	$v_pos = strpos($page_source, 'EDB Verified');
	if ( $v_pos ) { 
		$v_text = substr($page_source, $v_pos, 300);	// 500 chars - could be lots of spaces
		$verified = strpos($page_source, 'mdi-check');
		if ( $verified ) {
			$data['edb_verified'] = 1;
		} else {
			$data['edb_verified'] = -1;
		}
	}

	// try to find the exploit type
	//
	$searchkey = '<a href="/?type=';
	$endmark = '"';
	$searchlength = 300;
	$type = pentdb_search_source( $page_source, $searchkey, $endmark, $searchlength );
	if ( $type ) {
		$data['exploit_type'] = $type;
		if ( strtolower($type) == 'dos' ) {
			$data['status'] = "ELIMINATED";
		}
	}

	// look for platform (linux, windows, PHP, Java, etc.)
	//
	$searchkey = '<a href="/?platform=';
	$endmark = '"';
	$searchlength = 300;
	$platform = pentdb_search_source( $page_source, $searchkey, $endmark, $searchlength );
	if ( $platform ) {
		$data['platform'] = $platform;
	}

	// extract exploit published date
	//
	$pubdate = '';
	$searchkey = 'Published:';
	$endmark = '</div>';
	$searchlength = 800;
	$section = pentdb_search_source( $page_source, $searchkey, $endmark, $searchlength );
	if ( $section ) {
		$searchkey = '<h6 class="stats-title">';
		$endmark = '</h6>';
		$searchlength = 400;
		$pubdate = pentdb_search_source( $section, $searchkey, $endmark, $searchlength );
	}
	if ( $pubdate ) {
		$data['exploit_date'] = $pubdate;
	}

	// make some guesses at exploit language
	//
	if ( $pos = strpos($page_source, '#include <stdio.h>') ) {
		$data['code_language'] = 'C';
	}
	if ( $pos = strpos($page_source, '#!/usr/bin/perl') ) {
		$data['code_language'] = 'perl';
	}
	if ( $pos = strpos($page_source, '#!/bin/perl') ) {
		$data['code_language'] = 'perl';
	}
	if ( $pos = strpos($page_source, '#!perl') ) {
		$data['code_language'] = 'perl';
	}
	if ( $pos = strpos($page_source, '#!/usr/bin/python') ) {
		$data['code_language'] = 'python';
	}
	if ( $pos = strpos($page_source, '#!/bin/python') ) {
		$data['code_language'] = 'python';
	}
	if ( $pos = strpos($page_source, '#!python') ) {
		$data['code_language'] = 'python';
	}

	// if we have a language identified, then we probably have code
	if ( $data['code_language'] ) {
		$data['has_code'] = 1;
	}

	// extract code segment from page -- and maybe compare it to what we have in Kali?

	// compare with:   <pre><code



// echo "<div><pre>".$page_source."<pre><div>";
// echo "<div><pre>".$vid."<pre><div>";
// die('passed');

	$sql_fields = '';
	$sql_values = '';
	$sql_series = '';
	foreach( $data as $key => $value ) {
		$sql_fields .= ($sql_fields ? ',' : '').$key;
		$sql_values .= ($sql_values ? ',' : '').$value;
		$sql_series .= ($sql_series ? ',' : '')."`$key`='".addslashes($value)."'";
	}
	if ( $sql_fields ) {
		$sql_fields = '('.$sql_fields.')';
		$sql_values = '('.$sql_fields.')';
		$vuln_q = "UPDATE vuln SET ".$sql_series." WHERE vid=%d";

// die($vuln_q);

		$result = db_query( $vuln_q, $vid );
		if ( !$result ) {
			pentdb_log_error("Vuln update failed: ".$vuln_q." [MSG-5188]");
			return false;
		}
	}

}


function pentdb_search_source( $page_source, $searchkey, $endmark, $searchlength, $debug = false ) {

	$s_pos = strpos($page_source, $searchkey);
	$found = '';

	if ( $s_pos ) { 
		$subtext = trim(substr($page_source, $s_pos+strlen($searchkey), $searchlength));
		$endpos = strpos($subtext, $endmark );
		$found = substr($subtext, 0, $endpos);
	}

	if ( $debug ) {
		die('z:'.$found);
	}

	return $found;
}



function pentdb_fetch_cached_page( $url ) {
global $webpages_cache_path;
global $base_path;

	$page_id = substr($url, strrpos($url, '/')+1).'.html';
	$filepath = $base_path.$webpages_cache_path.$page_id;

	if ( file_exists( $filepath ) ) {
// die('got the file');
		return file_get_contents($filepath);
	}

// die($filepath);

	return false;
}


function pentdb_cache_web_page( $url, $page_source, $overwrite = false ) {
global $webpages_cache_path;
global $base_path;

	// get the page / vuln id
	$page_id = substr($url, strrpos($url, '/')+1).'.html';
	$filepath = $base_path.$webpages_cache_path.$page_id;

	// check to see if it exists in our directory already
	if ( file_exists( $filepath ) && !$overwrite ) {
		// pentdb_log_error("File already exists - skipping caching of ".$filepath);
		return false;
	}

	// write the page out
	$status = file_put_contents ( $filepath, $page_source ); 
		pentdb_log_error("Cached file locally: ".$filepath);
// die("wrote out the file of length ".strlen($page_source).": ".$filepath." -- status: ".$status);
	return $status;
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
		echo '<div>Query failed.</div>';
		echo "<div></pre>".print_r($addip_result,true)."</pre></div>";
		return false;
	}
	return $status;
}


function ptdb_set_depth_status() {
	$vars = pentdb_get_page_vars();

	if ( !isset( $vars['rec_id']) ) {
		pentdb_log_error("Can't update status: missing rec_id. ERR-723.");
		return false;
	}

	$set_q = "UPDATE testinstance set status='%s' WHERE irid='%d'";
	$set_result = db_query( $set_q, $vars['status'], $vars['rec_id'] );
	if ( !$set_result ) {
		echo '<div>Query failed.</div>';
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
	if ( isset($_GET['fcmd']) ) {
		$vars['fcmd'] = pentdb_clean( $_GET['fcmd'] );
	}
	if ( isset($_GET['rec_id']) ) {
		$vars['rec_id'] = pentdb_clean( $_GET['rec_id'] );
	}
	if ( isset($_GET['service']) ) {
		$vars['service'] = pentdb_clean( $_GET['service'] );
	}
	if ( isset($_GET['status']) ) {
		$vars['status'] = pentdb_clean( $_GET['status'] );
	}
	if ( isset($_GET['vuln']) ) {
		$vars['vuln'] = pentdb_clean( $_GET['vuln'] );
	}
	return $vars;
}


function base_link($session_id, $ip, $service = NULL, $port = NULL, $extra = NULL, $spot = NULL, $vuln = NULL, $fcmd = NULL ) {
	return '<a '.$extra.' href="index.php'.'?'.pentdb_get_urlparms( array( 'session_id'=>$session_id,'ip'=>$ip,'service'=>$service,'port'=>$port,'vuln'=>$vuln) ).($fcmd ? '&fcmd='.$fcmd : '').($spot ? "#".$spot : '').'">';
}


// get_urlparms
//
// Format the core page parms for a URL parms string for forms action, links, etc.

function pentdb_get_urlparms( $parms = array() ) {

// echo '<div>parms: <pre>',print_r($parms,true).'</pre></div>';

	// all of these need to be runable thru pentdb_clean(),
	//   so don't add parms like URL, etc. here
	$check_parms = array( 	
		'port',
		'session_id',
		'ip',
		'service',
		'vuln',
	);

	// use assignment method that results in zero notices from undefined indexes
	// passed-in params override $_GET
	$data = array();
	foreach( $check_parms as $item ) {
		$data[$item] = '';
		if ( isset($_GET[$item]) ) {
			if ( !empty($_GET[$item])) {
				$data[$item] = pentdb_clean($_GET[$item]);
			}
		}
		if ( isset($parms[$item]) ) {
			if ( !empty($parms[$item]) ) {
				$data[$item] = pentdb_clean($parms[$item]);
			}
		}
	}

// echo '<div>pre-clean: <pre>',print_r($data,true).'</pre></div>';

	// remove any blank parms
	foreach ($data as $key => $value) {
		if ( empty($value) ) {
			unset( $data[$key] );
		}
	}
 // echo '<div>send back:<pre>',print_r($data,true).'</pre></div>';

	return http_build_query($data);
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
		$output .= build_service_status_display( $session_id, $ip, $service['service'], $service['port'] );
	}

	$output .= '</div>'."\n";

	return $output;
}


function pentdb_get_host_record( $session_id, $ip ) {

	// don't bother to query for an empty ip
	if ( empty($_GET['ip']) ) {
		return false;
	}
	$myip = pentdb_validate_ip( $ip );
	$my_session = pentdb_clean( $session_id );

	$host_q = "SELECT * FROM {host} WHERE session_id='%s' AND ip_address='%s'";

	$host_rec = db_query( $host_q, $my_session, $myip );
	if ( !$host_rec ) {
		pentdb_log_error("Host query failed. [Error-2715]");
		return false;
	}
	if ( $host_rec->num_rows == 0 ) {
		pentdb_log_error("No host record found. [Notice 2716]");
		return false;
	}

	$host_data = db_fetch_array( $host_rec );

	return $host_data;
}


function get_service_list( $session_id, $ip ) {
	$servicelist = array();

	// no need to error if we aren't on a page display with services
	if ( !isset($_GET['ip']) ) {
		return false;
	}

	$service_q = "SELECT service,port FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' GROUP BY port,service ORDER BY port";

// echo "<div><pre>".print_r($service_q,true)."</pre></div>";
// die("check");

	$service_recs = db_query( $service_q, $session_id, $ip );
	if ( !$service_recs ) {
		pentdb_log_error('<div>Services query failed. [Error-2711]</div>' );
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
		if ( $rec['port'] == 0) {
			continue;
		}
		$servicelist[] = array( 'service' => $rec['service'], 'port' => $rec['port'] );
	}

	return $servicelist;
}

// build_service_status_display
//
// Create HTML for a one-line, simple boxes-and-colors status display 
//  of the given service for the given ip address in the given session.
// This can grow more sophisticated over time, adding visual indicators
//  for flags, discoveries, and more.
// Hover-over for more details of the test.

function build_service_status_display( $session_id, $ip, $service, $port ) {

	$output = '<div class="service-test-status">'."\n";
	$rec_handle = read_service_records( $session_id, $ip, $service, $port );

// echo "<div>rows:<pre>".$rec_handle->num_rows."</pre></div>";

	$depth = 0;
	$title_found = false;
	while ( $rec = db_fetch_array($rec_handle) ) {

// echo "<div><pre>".print_r($rec,true)."</pre></div>";

		if ( !$title_found ) {
			if ( $rec['rectype'] == 'TITLE' ) {
				$output .= base_link($session_id,$ip,$service,$port,'class="hover-link"').'<div class="label">'.$rec['service'].' port '.$rec['port'].'</div></a>'."\n";
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
		$link = base_link($session_id,$ip,$service,$port,$title,"test-".$rec['irid']); 
		$flag_star = '';
		if ( $rec['statustype'] == 'DEPTH' && $rec['status'] > 0 ) {
			$flag_star = $rec['status'];
		}
		if ( !empty($rec['flags']) ) {
			// $flag_star = 'F';
			// $flag_star = '&#9679;';		// round dot
			// $flag_star = '&diams;';		// diamond
			$flag_star = '&oplus;';		// plus sign in a circle
		}

		$block = $link.'<div class="indicator '.$display_color.'">'.$flag_star.'</div></a>'."\n";
		$output .= $depth_mark . $block;

// echo "<div><pre>".print_r($rec,true)."</pre></div>";
// die("check");

	}

	$output .= '</div>'."\n";

// echo "<div><pre>".print_r($output,true)."</pre></div>";

	return $output;
}


function read_service_records( $session_id, $ip, $service, $port ) {

	$tests_q = "SELECT * FROM {testinstance} WHERE session_id='%s' AND ip_address='%s' AND service='%s' AND port='%s' ORDER BY pass_depth, order_weight, irid";

// echo "<div>port: <pre>".print_r($port,true)."</pre></div>";
// die("check");

	$tests_recs = db_query( $tests_q, $session_id, $ip, $service, $port );
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


// build_vuln_status_display
//
// Create HTML for a one-line, simple boxes-and-colors status display 
//  of the vulns for given service for the given ip address in the given session.
// This can grow more sophisticated over time, adding visual indicators
//  for flags, discoveries, and more.
// Hover-over for more details of the vuln.

function build_vuln_status_display( $session_id, $ip, $service = NULL, $port = NULL ) {

	$output = '<div class="service-vuln-status">'."\n";
	$rec_handle = read_vuln_records( $session_id, $ip );

	$depth = 0;
	while ( $rec = db_fetch_array($rec_handle) ) {
		$depth_mark = '';
		// if ( $rec['pass_depth'] > $depth ) {
		// 	$depth++;
		// 	$depth_mark = '<div class="depth-divider"></div>'."\n";
		// }
		$display_color = get_vuln_status_color( $rec['status'], $rec['flags'] );
		$title = 'title="'.($rec['status'] ? $rec['status'].' - ' : '').$rec['title'].($rec['flags'] ? ' - FLAGS: '.$rec['flags'] : '').'"';
		$link = base_link($session_id,$ip,$service,$port,$title,NULL,$rec['vid'],'display-vuln'); 
		$flag_star = '';
		if ( $rec['status'] == "OPEN" ) {
			$flag_star = '&#9679;';		// round dot
		}
		// if ( $rec['status'] == 'ELIMINATED' ) {
		// 	$flag_star = 'X';
		// }
		if ( !empty($rec['flags']) ) {
			// $flag_star = 'F';
			// $flag_star = '&#9679;';		// round dot
			$flag_star = '&diams;';		// diamond
			// $flag_star = '&oplus;';		// plus sign in a circle
		}

		$selected = '';
		if ( $_GET['fcmd'] == 'display-vuln' ) {
			if ( $rec['vid'] == $_GET['vuln'] ) {
				$selected = ' selected';
			}
		}

		$block = $link.'<div class="indicator '.$display_color.$selected.'">'.$flag_star.'</div></a>'."\n";
		$output .= $depth_mark . $block;
	}

	$output .= '</div>'."\n";

	return $output;
}


function read_vuln_records( $session_id, $ip, $service = NULL, $port = NULL ) {

	$where_service = '';
	if ( !empty($service) ) {
		$where_service = " AND service='%s'";
	}
	$where_port = '';
	if ( !empty($port) ) {
		$where_port = " AND port='%s'";
	}

	$vuln_q = "SELECT * FROM {vuln} WHERE session_id='%s' AND ip_address='%s'".$where_service.$where_port." ORDER BY order_weight";

// echo "<div>port: <pre>".print_r($port,true)."</pre></div>";
// echo "<div>session: <pre>".print_r($session_id,true)."</pre></div>";
// echo "<div>ip: <pre>".print_r($ip,true)."</pre></div>";
// echo "<div>service: <pre>".print_r($service,true)."</pre></div>";
// die("check");
	if ( empty($service) && empty($port) ) {
		$vuln_recs = db_query( $vuln_q, $session_id, $ip );
	} else {
		$vuln_recs = db_query( $vuln_q, $session_id, $ip, $service, $port );
	}

	if ( !$vuln_recs ) {
		pentdb_log_error( '<div>Query or DB error looking for vuln records. [Error 613]' );
		return false;
	}
	// if ( $vuln_recs->num_rows == 0 ) {
	// 	pentdb_log_error( '<div>No vuln records found for service '.$service.' session "'.$session_id.'" [MSG-2318]' );
	// 	return false;
	// }

// die('count: '.$vuln_recs->num_rows);

	return $vuln_recs;
}

//------------------------------------------------------

// build_objective_status_display
//
// Create HTML for a one-line, simple boxes-and-colors status display 
//  of the vulns for given service for the given ip address in the given session.
// This can grow more sophisticated over time, adding visual indicators
//  for flags, discoveries, and more.
// Hover-over for more details of the vuln.

function build_objectives_status_display( $session_id, $ip, $service = NULL, $port = NULL ) {

// return NULL;

	$output = '<div class="objective-status">'."\n";
	$rec_handle = read_objective_records( $session_id, $ip );

// echo "<div>rows:<pre>".$rec_handle->num_rows."</pre></div>";
// die('123');

	$depth = 0;
	while ( $rec = db_fetch_array($rec_handle) ) {

// echo "<div><pre>".print_r($rec,true)."</pre></div>";

		$display_color = '';
		// $display_color = get_objective_status_color( $rec['status'], $rec['flags'] );

		$title = 'title="'.($rec['status'] ? $rec['status'].' - ' : '').$rec['title'].')"';
		$link = base_link($session_id,$ip,$service,$port,$title,NULL,$rec['oid'],'display-obj'); 
		$flag_star = '&#9734;';		// hollow star
		if ( $rec['status'] == "ACCOMPLISHED" ) {
			$flag_star = '&#9733;';		// solid star
			$display_color = 'gold-star';
		}
		if ( $rec['status'] == "FAILED" ) {
			$flag_star = '&#9733;';		// solid star
			$display_color = 'red-star';
		}
		if ( $rec['status'] == "IN-PROGRESS" ) {
			$flag_star = '&#9733;';		// solid star
			$display_color = 'green-star';
		}

		$selected = '';
		if ( $_GET['fcmd'] == 'display-obj' ) {
			if ( $rec['oid'] == $_GET['vuln'] ) {
				$selected = ' selected';
			}
		}

		$block = $link.'<div class="obj-dot '.$display_color.$selected.'">'.$flag_star.'</div></a>'."\n";
		$output .= $block;

// echo "<div><pre>".print_r($output,true)."</pre></div>";
// die("check");

	}

	$output .= '</div>'."\n";

// echo "<div><pre>".print_r($output,true)."</pre></div>";

	return $output;
}


function read_objective_records( $session_id, $ip, $service = NULL, $port = NULL ) {

	$where_service = '';
	if ( !empty($service) ) {
		$where_service = " AND service='%s'";
	}
	$where_port = '';
	if ( !empty($port) ) {
		$where_port = " AND port='%s'";
	}

	$obj_q = "SELECT * FROM {objective} WHERE session_id='%s' AND ip_address='%s'".$where_service.$where_port;

// echo "<div>port: <pre>".print_r($port,true)."</pre></div>";
// echo "<div>session: <pre>".print_r($session_id,true)."</pre></div>";
// echo "<div>ip: <pre>".print_r($ip,true)."</pre></div>";
// echo "<div>service: <pre>".print_r($service,true)."</pre></div>";
// die("check");
	if ( empty($service) && empty($port) ) {
		$obj_recs = db_query( $obj_q, $session_id, $ip );
	} else {
		$obj_recs = db_query( $obj_q, $session_id, $ip, $service, $port );
	}

	if ( !$obj_recs ) {
		pentdb_log_error( '<div>Query or DB error looking for objective records. [Error 673]' );
		return false;
	}
	// if ( $vuln_recs->num_rows == 0 ) {
	// 	pentdb_log_error( '<div>No vuln records found for service '.$service.' session "'.$session_id.'" [MSG-2318]' );
	// 	return false;
	// }

// die('count: '.$vuln_recs->num_rows);

	return $obj_recs;
}

//-----------------------------------------------------



function get_status_color( $statustype, $status, $flags = NULL ) {
	$status_color = 'gray';

	// if ( !empty($flags) ) {
	// 	$status_color = 'yellow';
	// 	// return $status_color;
	// }

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
			if (is_numeric($status) && $status > 0) {
				$status_color = 'blue';
			}	
			break;

		case 'NONE':
			break;

		default:
			pentdb_log_error("NOTE: Unknown statustype: ".$status);
			break;
		}


	return $status_color;
}


function get_vuln_status_color( $status, $flags = NULL ) {
	$status_color = 'gray';

	// if ( !empty($flags) ) {
	// 	$status_color = 'yellow';
	// 	// return $status_color;
	// }


	switch ($status) {
		case 'POS':
			$status_color = 'green';
			break;

		case 'NEG':
			$status_color = 'red';
			break;

		case 'MATCH':
			$status_color = 'vuln-match';
			break;

		case 'POSSIBLE':
			$status_color = 'vuln-possible';
			break;

		case 'UNLIKELY':
			$status_color = 'vuln-unlikely';
			break;

		case 'ELIMINATED':
			$status_color = 'vuln-eliminated';
			break;

		default:
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
		<div><FORM class="statusform" action="index.php#test-'.$rec_id.'" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
			<INPUT type="hidden" name="fcmd" value="set-progress"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>
			<INPUT '.$progress_class.'type="submit" value="InProgress"></INPUT>
		</FORM></div>
			<div><FORM class="statusform" action="index.php#test-'.$rec_id.'" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
			<INPUT type="hidden" name="fcmd" value="set-pos"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>
			<INPUT '.$pos_class.'type="submit" value="POS"></INPUT>
		</FORM></div>
		<div><FORM class="statusform" action="index.php#test-'.$rec_id.'" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
			<INPUT type="hidden" name="fcmd" value="set-neg"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>		
			<INPUT '.$neg_class.'type="submit" value="NEG"></INPUT>
		</FORM></div>
	';

	$button_form .= pentdb_get_reset_status_form( $vars, $rec_id );

	return $button_form;
}


function get_depth_status_button( $status, $rec_id ) {
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

	$button_form = '';
	for ($x=1; $x<4; $x++) {
		$button_form .= '
			<div><FORM class="statusform" action="index.php#test-'.$rec_id.'" method="GET">
				<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
				<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
				<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
				<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
				<INPUT type="hidden" name="fcmd" value="set-status"></INPUT>
				<INPUT type="hidden" name="status" value="'.$x.'"></INPUT>
				<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>
				<INPUT '.($status == $x ? 'class="blue-button"' : '').'type="submit" value="'.$x.'"></INPUT>
			</FORM></div>
		';
	}

	$button_form .= pentdb_get_reset_status_form( $vars, $rec_id );

	return $button_form;
}


function create_port_record() {
	// YES IT'S INSECURE -- DON'T PROCESS FORMS LIKE THIS!

	$fields = ' (';
	$values = ' VALUES (';
	$comma = false;
	foreach( $_GET as $key => $value ) {
		if ( $key == 'fcmd' ) {
			continue;
		}
		if ( $key == 'ip' ) {
			$key = 'ip_address';
		}
		// [_] *** TODO having switched GET form cmd to fcmd, 
		//			this 'command' joggle can be fixed/removed
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


//////////////////////////////////////////////////////////
//														//
//   					 FORMS    						//	
//														//
//////////////////////////////////////////////////////////

function get_add_objective_form( $title = "Add an objective" ) {
	$vars = pentdb_get_page_vars();
	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">

		<LABEL for="title">Objective display title: </LABEL>
		<INPUT type="text" name="title" id = "title"></INPUT><br/>

		<LABEL for="objective">Objective description: </LABEL>
		<INPUT type="text" name="objective" id = "objective"></INPUT><br/>

		<LABEL for="status">Status: </LABEL>
		<SELECT name="status" id="status">
			<OPTION value="NEW">NEW</OPTION>
			<OPTION value="IN-PROGRESS">IN-PROGRESS</OPTION>
			<OPTION value="ACCOMPLISHED">ACCOMPLISHED</OPTION>
			<OPTION value="FAILED">FAILED</OPTION>
		</SELECT><br/>

		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="new-obj"></INPUT>
		<INPUT type="submit" value="Add objective"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

	return $bigform;
}

function pentdb_get_reset_status_form( $vars, $rec_id ) {


	$button_form .= '
		<div><FORM class="statusform" action="index.php#test-'.$rec_id.'" method="GET">
			<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
			<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
			<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
			<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
			<INPUT type="hidden" name="fcmd" value="set-status"></INPUT>
			<INPUT type="hidden" name="status" value="0"></INPUT>
			<INPUT type="hidden" name="rec_id" value="'.$rec_id.'"></INPUT>
			<INPUT type="submit" value="reset"></INPUT>
		</FORM></div>
	';

	return $button_form;
}


function get_add_service_form( $title = "Add a service" ) {
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
		<INPUT type="hidden" name="fcmd" value="new-port"></INPUT>
		<INPUT type="submit" value="Create service port"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

	return $bigform;
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
			<OPTION value="EXAMINE">EXAMINE</OPTION>
			<OPTION value="SCAN">SCAN</OPTION>
			<OPTION value="TOOL">TOOL</OPTION>
			<OPTION value="SCRIPT">SCRIPT</OPTION>
		</SELECT><br/>

		<LABEL for="pass_depth">Pass depth: </LABEL>
		<INPUT type="text" name="pass_depth" id="pass_depth" value="1"></INPUT><br/>

		<LABEL for="order_weight">Order Weight: </LABEL>
		<INPUT type="text" name="order_weight" id="order_weight" value="1"></INPUT><br/>

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
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="new-port"></INPUT>
		<INPUT type="submit" value="Add a test"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

		// <INPUT type="hidden" name="port" value="'.$port.'"></INPUT>

	return $bigform;
}


function get_session_form( $title = "Add a session") {
	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">
			<LABEL for="session_name">Session name: </LABEL>
			<INPUT type="text" name="idname" id="session_name"></INPUT><br/>
			<LABEL for="dir">Data (tanks) path: </LABEL>
			<INPUT type="text" name="dir" id="dir" value="'.DEFAULT_DATA_PATH.'"></INPUT> (Include trailing slash)<br/>
			<LABEL for="cmd_path">Scripts cmd path: </LABEL>
			<INPUT type="text" name="cmd_path" id="cmd_path" value="'.DEFAULT_CMD_PATH.'"></INPUT> (shell user has minimal path)<br/>
			<LABEL for="api_url">CmdSvr URL: </LABEL>
			<INPUT type="text" name="api_url" id="api_url" value="http://127.0.0.1:8888"></INPUT><br/>
			<INPUT type="hidden" name="fcmd" value="create-session"></INPUT><br/>
			<INPUT type="submit" value="Create session"></INPUT>
		</FORM></div>
	';

	$bigform = "<h2>".$title."</h2>\n" . $bigform;

	return $bigform;
}

/* -- defunct
function get_host_form( $title = "Add a host" ) {
	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">
			<LABEL for="ip_addr">IP address: </LABEL>
			<INPUT type="text" name="ipaddr" id="ip_addr"></INPUT><br/>
			<LABEL for="hostname">Host name: </LABEL>
			<INPUT type="text" name="hostname" id="hostname" value="box-$i4p"></INPUT><br/>
			'
			// <INPUT type="checkbox" name="mktank" value="mktank" id="mktank"><label for="mktank"> Run mktank command</label><br/>
			// <INPUT type="checkbox" name="penscan" value="penscan" id="penscan"><label for="penscan"> Launch penscan command</label><br/>
			.'<INPUT type="hidden" name="fcmd" value="add-ip"></INPUT>
			<INPUT type="hidden" name="session_id" value="'.$session_id.'"></INPUT>
			<INPUT type="submit" value="Add IP address"></INPUT>
		</FORM></div>
	';

	$bigform = "<h2>".$title."</h2>\n" . $bigform;

	return $bigform;
}
*/

function get_add_host_form( $title = "Add a host" ) {
	$vars = pentdb_get_page_vars();
	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">

		<LABEL for="ip_address">IP address: </LABEL>
		<INPUT type="text" name="ip_address" id = "ip_address"></INPUT><br/>

		<LABEL for="name">Name: </LABEL>
		<INPUT type="text" name="name" id = "name"></INPUT><br/>

		<LABEL for="status">Status: </LABEL>
		<SELECT name="status" id="status">
			<OPTION value="NEW">NEW</OPTION>
			<OPTION value="UNKNOWN">UNKNOWN</OPTION>
			<OPTION value="SCANNED">SCANNED</OPTION>
			<OPTION value="IN-PROGRESS">IN-PROGRESS</OPTION>
			<OPTION value="STANDBY">STANDBY</OPTION>
			<OPTION value="PWNED">PWNED</OPTION>
		</SELECT><br/>

		<LABEL for="platform">Platform: </LABEL>
		<INPUT type="text" name="platform" id = "platform"></INPUT><br/>

		<LABEL for="os_version">OS version: </LABEL>
		<INPUT type="text" name="os_version" id = "os_version"></INPUT><br/>

		<LABEL for="patch_version">Patch version: </LABEL>
		<INPUT type="text" name="patch_version" id = "patch_version"></INPUT><br/>

		<LABEL for="service_pack">Service pack (SP): </LABEL>
		<INPUT type="text" name="service_pack" id = "service_pack"></INPUT><br/>

		<LABEL for="cpu_arch">CPU architecture: </LABEL>
		<INPUT type="text" name="cpu_arch" id = "cpu_arch"></INPUT><br/>

		<LABEL for="core_count">Core count: </LABEL>
		<INPUT type="text" name="core_count" id = "core_count"></INPUT><br/>


		<LABEL for="cmd">Cmd: </LABEL>
		<INPUT type="text" name="cmd" id="cmd" value="'.DEFAULT_HOST_CMD.'"></INPUT><br/>

		<LABEL for="process_result_cmd">Process result cmd: </LABEL>
		<INPUT type="text" name="process_result_cmd" id="process_result_cmd" value="'.DEFAULT_HOST_PRORESULT_CMD.'"></INPUT><br/>


		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="add-host"></INPUT>
		<INPUT type="submit" value="Add host"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

	return $bigform;
}


function get_add_host_datum_form( $name, $value, $recid ) {

	// don't list internal fields
	// TODO: change this to get a list of valid fields, then we can control the order, too.
	if ( in_array($name, array("hid","created","session_id","ip_address")) ) {
		return NULL;
	}

	if ( in_array($name, array("cmd","process_result_cmd")) ) {
		$value = fill_varset( $value );
	}

	$vars = pentdb_get_page_vars();

	$data = '		<LABEL for="'.$name.'">'.$name.': </LABEL>
		<INPUT type="text" name="'.$name.'" id ="'.$name.'" value="'.$value.'"></INPUT>';

	$ta_form = '';
	if ( in_array($name, array("notes","wireshark","proof","loot","lessons_learned") )) {
		$data = '		<LABEL for="'.$name.'_form">'.$name.': </LABEL><br/>
		<textarea wrap="soft" cols="80" rows="8" name="'.$name.'" id ="'.$name.'">'.$value.'</textarea><br/>';
		$ta_form = ' taform';
	}

	if ( $name == 'status' ) {
		$data = '		<LABEL for="status">Status: </LABEL>
		<SELECT name="status" id="status">
			<OPTION '.($value=="NEW" ? 'SELECTED ' : '').'value="NEW">NEW</OPTION>
			<OPTION '.($value=="UNKNOWN" ? 'SELECTED ' : '').'value="UNKNOWN">UNKNOWN</OPTION>
			<OPTION '.($value=="SCANNED" ? 'SELECTED ' : '').'value="SCANNED">SCANNED</OPTION>
			<OPTION '.($value=="IN-PROGRESS" ? 'SELECTED ' : '').'value="IN-PROGRESS">IN-PROGRESS</OPTION>
			<OPTION '.($value=="STANDBY" ? 'SELECTED ' : '').'value="STANDBY">STANDBY</OPTION>
			<OPTION '.($value=="PWNED" ? 'SELECTED ' : '').'value="PWNED">PWNED</OPTION>
		</SELECT><br/>';
	}

	$myform = '
		<div class="inlineform host'.$ta_form.'"><FORM action="index.php" method="GET">
		'.$data.'
		<INPUT type="hidden" name="fname" value="'.$name.'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="update-host"></INPUT>
		<INPUT type="submit" value="Update"></INPUT>
		</FORM></div>
	';

	return $myform;
}



function get_add_vuln_form( $title = "Add a vuln" ) {
	$vars = pentdb_get_page_vars();
	$bigform = '
		<div class="bigform"><FORM action="index.php" method="GET">

		<LABEL for="title">Vuln display title: </LABEL>
		<INPUT type="text" name="title" id = "title"></INPUT><br/>

		<LABEL for="url">Vuln URL: </LABEL>
		<INPUT type="text" name="url" id = "url"></INPUT><br/>

		<LABEL for="status">Status: </LABEL>
		<SELECT name="status" id="status">
			<OPTION value="OPEN">OPEN</OPTION>
			<OPTION value="ELIMINATED">ELIMINATED</OPTION>
			<OPTION value="UNLIKELY">UNLIKELY</OPTION>
			<OPTION value="POSSIBLE">POSSIBLE</OPTION>
			<OPTION value="MATCH">MATCH</OPTION>
		</SELECT><br/>

		<LABEL for="code_language">Code language: </LABEL>
		<INPUT type="text" name="code_language" id="code_language"></INPUT><br/>

		<LABEL for="order_weight">Order Weight: </LABEL>
		<INPUT type="text" name="order_weight" id="order_weight" value="0"></INPUT><br/>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="fcmd" value="new-vuln"></INPUT>
		<INPUT type="submit" value="Add a vuln"></INPUT>
		</FORM></div>
	';
	$bigform = "<h2>".$title."</h2>\n" . $bigform;

		// <INPUT type="hidden" name="port" value="'.$port.'"></INPUT>

	return $bigform;
}


function get_add_vuln_datum_form( $name, $value, $recid ) {
	$vars = pentdb_get_page_vars();

	$data = '		<LABEL for="'.$name.'">'.$name.': </LABEL>
		<INPUT type="text" name="'.$name.'" id ="'.$name.'" value="'.$value.'"></INPUT>';

	if ( $name == 'status' ) {
		$data = '		<LABEL for="status">Status: </LABEL>
		<SELECT name="status" id="status">
			<OPTION '.($value=="OPEN" ? 'SELECTED ' : '').'value="OPEN">OPEN</OPTION>
			<OPTION '.($value=="ELIMINATED" ? 'SELECTED ' : '').'value="ELIMINATED">ELIMINATED</OPTION>
			<OPTION '.($value=="UNLIKELY" ? 'SELECTED ' : '').'value="UNLIKELY">UNLIKELY</OPTION>
			<OPTION '.($value=="POSSIBLE" ? 'SELECTED ' : '').'value="POSSIBLE">POSSIBLE</OPTION>
			<OPTION '.($value=="MATCH" ? 'SELECTED ' : '').'value="MATCH">MATCH</OPTION>
			<OPTION '.($value=="NEG" ? 'SELECTED ' : '').'value="NEG">FAILED! (NEG)</OPTION>
			<OPTION '.($value=="POS" ? 'SELECTED ' : '').'value="POS">WORKED! (POS)</OPTION>
		</SELECT><br/>';
	}

	$myform = '
		<div class="inlineform vuln"><FORM action="index.php" method="GET">

		'.$data.'
		<INPUT type="hidden" name="fname" value="'.$name.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="vuln" value="'.$vars['vuln'].'"></INPUT>

		<INPUT type="hidden" name="fcmd" value="update-vuln"></INPUT>
		<INPUT type="submit" value="Update"></INPUT>
		</FORM></div>
	';

	return $myform;
}

function get_add_obj_datum_form( $name, $value, $recid ) {
	$vars = pentdb_get_page_vars();

	$data = '		<LABEL for="'.$name.'">'.$name.': </LABEL>
		<INPUT type="text" name="'.$name.'" id ="'.$name.'" value="'.$value.'"></INPUT>';

	if ( $name == 'status' ) {
		$data = '		<LABEL for="status">Status: </LABEL>
		<SELECT name="status" id="status">
			<OPTION '.($value=="NEW" ? 'SELECTED ' : '').'value="NEW">NEW</OPTION>
			<OPTION '.($value=="IN-PROGRESS" ? 'SELECTED ' : '').'value="IN-PROGRESS">IN-PROGRESS</OPTION>
			<OPTION '.($value=="ACCOMPLISHED" ? 'SELECTED ' : '').'value="ACCOMPLISHED">ACCOMPLISHED</OPTION>
			<OPTION '.($value=="FAILED" ? 'SELECTED ' : '').'value="FAILED">FAILED</OPTION>
		</SELECT><br/>';
	}

	$myform = '
		<div class="inlineform objective"><FORM action="index.php" method="GET">

		'.$data.'
		<INPUT type="hidden" name="fname" value="'.$name.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="vuln" value="'.$vars['vuln'].'"></INPUT>

		<INPUT type="hidden" name="fcmd" value="update-obj"></INPUT>
		<INPUT type="submit" value="Update"></INPUT>
		</FORM></div>
	';

	return $myform;
}


function get_add_banner_form( $recid ) {
	$vars = pentdb_get_page_vars();
	$myform = '
		<div class="inlineform"><FORM action="index.php#test-'.$recid.'" method="GET">

		<LABEL for="banner">Banner: </LABEL>
		<INPUT type="text" name="banner" id = "banner"></INPUT>

		<INPUT type="hidden" name="recid" value="'.$recid.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>

		<INPUT type="hidden" name="fcmd" value="update-banner"></INPUT>
		<INPUT type="submit" value="Update Banner"></INPUT>
		</FORM></div>
	';

	return $myform;
}


function get_set_flags_form( $recid ) {
	$vars = pentdb_get_page_vars();
	$myform = '
		<div class="inlineform"><FORM action="index.php#test-'.$recid.'" method="GET">

		<LABEL for="flags">Flags: </LABEL>
		<INPUT type="text" name="flags" id = "flags"></INPUT>

		<INPUT type="hidden" name="recid" value="'.$recid.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>

		<INPUT type="hidden" name="fcmd" value="update-flags"></INPUT>
		<INPUT type="submit" value="Update Flags"></INPUT>
		</FORM></div>
	';

	return $myform;
}


function get_notes_form( $recid, $notes ) {
	$vars = pentdb_get_page_vars();
	$myform = '
		<div class="inlineform"><FORM class="notes-form" action="index.php#test-'.$recid.'" method="GET" id="notes-form-'.$recid.'">

		<LABEL for="notes_form">Notes: </LABEL><br/>
		<textarea wrap="soft" cols="80" rows="8" name="notes" id ="notes">'.$notes.'</textarea><br/>

		<INPUT type="hidden" name="recid" value="'.$recid.'"></INPUT>

		<INPUT type="hidden" name="service" value="'.$vars['service'].'"></INPUT>
		<INPUT type="hidden" name="session_id" value="'.$vars['session_id'].'"></INPUT>
		<INPUT type="hidden" name="ip" value="'.$vars['ip'].'"></INPUT>
		<INPUT type="hidden" name="port" value="'.$vars['port'].'"></INPUT>

		<INPUT type="hidden" name="fcmd" value="update-notes"></INPUT>
		<INPUT type="submit" value="Update Notes"></INPUT>
		</FORM></div>

	';

		// <textarea rows="4" cols="50" name="notes" form="notes-form">'.$notes.'</textarea>


	return $myform;
}

// load_templates
//
// CURRENTLY NOT WORKING - needs troubleshooting: the query works if run at the command line (via sudo),
//							but doesn't work from inside this program.
//
// Read in all the test pattern templates that we know about.

function ptdb_load_templates() {

	$templates_dir = "/home/katin/Workshop/PenTDB/public_html/";
	$db_name = "pentdb";

	$known_template_files = array(
		"port-22_chart.dat",
		"port-53_chart.dat",
		"port-80_chart.dat",
		"webapp_chart.dat",
	);

	foreach( $known_template_files as $file ) {
		$read_q = "LOAD DATA LOCAL INFILE '%s' INTO TABLE ".$db_name.".porttest FIELDS TERMINATED BY ',' ENCLOSED BY '\"' LINES TERMINATED BY '\\n' IGNORE 9 ROWS";
		$qresult = db_query( $read_q, $templates_dir.$file );
		if ( $qresult ) {
			echo '<div class="status-msg">'.$file.' read into database.</div>'."\n";
		} else {
			echo '<div class="status-msg">ERROR '.$file.' import failed. [MSG-12]</div>'."\n";
		}
	}

}

