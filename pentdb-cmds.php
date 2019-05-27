<?php
/* PenTDB process commands
 *
 * Process and call tree for the cmd parm
 *
 * 190525 KBI created as a part of refactoring index.php
 */

function ptdb_process_cmd ( $mycmd ) {

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
			$success = pentdb_add_ip( $the_ip, $the_session );
			if ( $success ) {
				$_GET['ip'] = $success;
				break;
			} else {
				echo "<div>Add ip failed.</div>";
				break;
			}


		case 'add-port':
			if ( empty($_GET['altport'])) {
				die('You must specify a port number');
			}
			$the_ip = pentdb_validate_ip($_GET['ip'] );
			$the_session = $_GET['session_id'];		// TODO: sanitize session id
			// $port_select = pentdb_validate_port($_GET['port-select']);
			$port = pentdb_validate_port($_GET['altport']);
			
			// $service = substr($_GET['service'],0,strpos($_GET['service-select'], ' (' ));
			$service = $_GET['service'];

// die( print_r($_GET,true) );

			// [_] TODO: validate service selection
			$success = pentdb_add_port( $the_ip, $the_session, $port, $service );
			if ( $success ) {
				$_GET['port'] = $success;
				break;
			} else {
				echo "<div>Add port failed.</div>";
				break;
			}

		case 'update-banner':
			$up_q = "UPDATE {testinstance} set banner='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['banner'], $_GET['recid']);
			if ( !$up_result ) {
				"Banner update query failed. [ERR-881]";
				die();
			}
			return true;
			break;


		case 'set-pos':
			ptdb_set_binary_status( 'POS' );
			break;


		case 'set-neg':
			ptdb_set_binary_status( 'NEG' );
			break;

		case 'new-port':
			create_port_record();
			break;

		default:
			echo "<div>Uknown command.</div>";
			die();

	}

}

?>
