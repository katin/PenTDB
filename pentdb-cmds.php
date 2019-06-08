<?php
/* PenTDB process commands
 *
 * Process and call tree for the cmd parm
 *
 * 190525 KBI created as a part of refactoring index.php
 */

global $top_message;

function ptdb_process_cmd ( $mycmd ) {
global $top_message;

$top_message .= '<div>PROCESSED CMD</div>';

	switch( $mycmd ) {

		case 'create-session':
			$success = pentdb_create_session( $_GET['idname'] );
			if ( $success ) {
				$_GET['session_id'] = $success;
				break;
			} else {
				echo "<div>Create session failed.</div>";
				break;
			}


		case 'add-ip':
			$the_ip = pentdb_validate_ip($_GET['ipaddr'] );
			$the_session = $_GET['session_id'];		// TODO: sanitize session
				# run variables on hostname option
			$_GET['ip'] = $the_ip;
			$hostname = fill_varset( $_GET['hostname'] );	// TODO santize hostname (keep '$' chars!)
			$success = pentdb_add_ip( $the_ip, $the_session, $hostname );
			if ( $success ) {
				$_GET['ip'] = $success;
				if ( $_GET['mktank'] == 'mktank' ) {
					$data_path = pentdb_get_session_path();
					$cmd_path = pentdb_get_cmd_path();
					$my_cmd = $cmd_path.'mktank '.$the_ip.' '.$hostname.' '.$data_path;
					$cmd_result = shell_exec( $my_cmd );
					$top_message .= "CMD: $my_cmd -- RESULT: ".$cmd_result;
				}
				if ( $_GET['penscan'] == 'penscan' ) {
					$data_path = pentdb_get_session_path();
					$cmd_path = pentdb_get_cmd_path();
					$my_cmd = $cmd_path.'penscan '.$data_path.$the_ip;
					$cmd_result = exec( $my_cmd );
					$top_message .= "CMD: $my_cmd -- Launched.";
				}				break;
			} else {
				echo "<div>Add ip failed.</div>";
				break;
			}


		case 'add-service':
			if ( empty($_GET['altport'])) {
				die('You must specify a port number');
			}
			$the_ip = pentdb_validate_ip($_GET['ip'] );
			$the_session = $_GET['session_id'];		// TODO: sanitize session id
			// $service_select = pentdb_validate_service($_GET['service-select']);
			$port = pentdb_validate_port($_GET['altport']);
			
			// $service = substr($_GET['service'],0,strpos($_GET['service-select'], ' (' ));
			$service = $_GET['service'];

// die( print_r($_GET,true) );

			// [_] TODO: validate service selection
			$success = pentdb_add_service( $the_ip, $the_session, $port, $service );
			if ( $success ) {
				$_GET['service'] = $success;
				break;
			} else {
				echo "<div>Add service failed.</div>";
				break;
			}

		case 'new-vuln':
			$the_ip = pentdb_validate_ip($_GET['ip'] );
			$the_session = $_GET['session_id'];		// TODO: sanitize session id
			$port = pentdb_validate_port($_GET['port']);
			$service = $_GET['service'];
			$success = pentdb_new_vuln();		// uses $_GET for parms
			break;

		case 'update-banner':
			$up_q = "UPDATE {testinstance} set banner='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['banner'], $_GET['recid']);
			if ( !$up_result ) {
				"Banner update query failed. [ERR-881]";
				die();
			}
			return true;
			break;


		case 'update-flags':
			$up_q = "UPDATE {testinstance} set flags='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['flags'], $_GET['recid']);
			if ( !$up_result ) {
				"Flags update query failed. [ERR-881]";
				die();
			}
			return true;
			break;


		case 'update-notes':
			$up_q = "UPDATE {testinstance} set notes='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['notes'], $_GET['recid']);
			if ( !$up_result ) {
				"Notes update query failed. [ERR-881]";
				die();
			}
// echo "<div>rows:<pre>".print_r($up_result,true)."</pre></div>";

			return true;
			break;

		case 'set-pos':
			ptdb_set_binary_status( 'POS' );
			break;

		case 'set-neg':
			ptdb_set_binary_status( 'NEG' );
			break;

		case 'set-progress':
			ptdb_set_binary_status( 'IN-PROGRESS' );
			break;

		case 'new-port':
			create_port_record();
			break;

		case 'load_templates':
			ptdb_load_templates();
			break;

		case 'set-status':
			ptdb_set_depth_status();
			break;

		default:
			echo "<div>Uknown command.</div>";
			die();

	}

}

?>
