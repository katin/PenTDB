<?php 

// api.php - PenTDB API
//
// 190519 KBI - created

global $dru_db_version;

$dru_db_version = 'dru_dblib-v1.0';
require_once $dru_db_version.'/dru_db_settings.php';
require_once $dru_db_version.'/dru_db_glue.php';
require_once $dru_db_version.'/database.inc';
require_once $dru_db_version.'/database.mysql.inc';
require_once $dru_db_version.'/dru_db_startup.php';

require_once "pentdb-lib.php";

date_default_timezone_set('America/Los_Angeles');


	//
	// param checks
	//

if ( !isset($_GET['cmd']) || !isset($_GET['ticket']) ) {
	echo '<div class="error"><h2>Missing parameter(s). Ticket and cmd parms are required.</h2></div>';
	die();
}
if ( empty($_GET['cmd']) || empty($_GET['ticket']) ) {
	echo '<div class="error"><h2>Missing parameter(s). Ticket and cmd parms are required.</h2></div>';
	die();	
}

	//
	// sanitize parms
	//

	// TODO

	$cmd = $_GET['cmd'];
	$ticket = $_GET['ticket'];


	//
	// verify ticket
	//

	// TODO



	// 
	// run cmd
	//

switch ($cmd) {
	case 'create-instance':
		$ip = pentdb_clean($_GET['ip']);
		$port = pentdb_clean($_GET['port']);
		$session_id = pentdb_clean($_GET['session_id']);

		// fetch the port template
		$template_q = "SELECT * from {porttest} WHERE port='%s'";
		$template_recs = db_query( $template_q, $port );
		if ( !$template_recs ) {
			echo '<div class="error">Template for port '.$port.' not found in database.</div>';
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
				$template['service'],
				$template['rectype'],
				$template['statustype'],
				$template['title'],
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
		break;

	default:
		echo "Command ".$cmd." not found.";
		break;
}




