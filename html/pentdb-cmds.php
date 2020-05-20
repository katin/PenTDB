<?php
/* PenTDB process commands
 *
 * Process and call tree for the cmd parm
 *
 * 190525 KBI created as a part of refactoring index.php
 */


function ptdb_process_cmd ( $mycmd ) {

// pentdb_top_msg('PROCESSED CMD '.$mycmd);

	switch( $mycmd ) {

		case 'create-session':
			$success = pentdb_create_session( $_GET['idname'] );
			if ( $success ) {
				$_GET['session_id'] = $success;
			}
			break;

		case 'add-host':
			$the_session = $_GET['session_id'];		// TODO: sanitize session
			$success = pentdb_add_host( $the_session );
			break;

		case 'update-host':
			$success = pentdb_update_host();	// uses $_GET for parms
			// ptdb_process_cmd ( 'display-vuln' );
			break;

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

			// [_] TODO: validate service selection
			$success = pentdb_add_service( $the_ip, $the_session, $port, $service );
			if ( $success ) {
				$_GET['service'] = $success;
				$_GET['port'] = $port;
				break;
			} else {
				echo "<div>Add service failed.</div>";
				break;
			}

		case 'display-vuln':
			if ( empty($_GET['vuln']) ) {
				die('Vuln param is required for this display.');
			}
			$vuln = pentdb_clean( $_GET['vuln'] );

			if ( !isset($_GET['ip']) ) {
				die('IP param is required for this display.');
			}
			if ( $vip = pentdb_validate_ip($_GET['ip'] )) {
				// clear port so it doesn't light up on the top bar
				unset($_GET['port']);
				display_vuln_page( $session_id, $vip, $vuln );
				die();
			}
			break;

		case 'new-vuln':
			$the_ip = pentdb_validate_ip($_GET['ip'] );
			$the_session = $_GET['session_id'];		// TODO: sanitize session id
			$port = pentdb_validate_port($_GET['port']);
			$service = $_GET['service'];
			$vuln_id = pentdb_new_vuln();			// uses $_GET for parms
			$_GET['vuln'] = $vuln_id;
			if ( $vuln_id ) {
				ptdb_process_cmd("display-vuln");
			}
			break;

		case 'update-vuln':
			$success = pentdb_update_vuln();	// uses $_GET for parms
			ptdb_process_cmd ( 'display-vuln' );
			break;

		case 'display-obj':
			// yeah, we're using the 'vuln' parm for oid until we convert it to 'recid'
			if ( empty($_GET['vuln']) ) {
				die('Vuln param (oid) is required for this display.');
			}
			$oid = pentdb_clean( $_GET['vuln'] );

			if ( !isset($_GET['ip']) ) {
				die('IP param is required for this display.');
			}
			if ( $vip = pentdb_validate_ip($_GET['ip'] )) {
				display_objective_page( $session_id, $vip, $oid );
				die();
			}
			break;

		case 'new-obj':
			$the_ip = pentdb_validate_ip($_GET['ip'] );
			$oid = pentdb_new_objective();		// uses $_GET for parms
			$_GET['vuln'] = $oid;
			if ( $oid ) {
				ptdb_process_cmd("display-obj");
			}
			break;

		case 'update-obj':
			$success = pentdb_update_objective();	// uses $_GET for parms
			ptdb_process_cmd ( 'display-obj' );
			break;

		case 'update-cmd':
			$up_q = "UPDATE {testinstance} set cmd='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['cmd'], $_GET['recid']);
			if ( !$up_result ) {
				"Cmd field update query failed. [ERR-891]";
				die();
			}
			return true;
			break;

		case 'update-processcmd':
			$up_q = "UPDATE {testinstance} set process_result_cmd='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['processcmd'], $_GET['recid']);
			if ( !$up_result ) {
				"Process cmd update query failed. [ERR-892]";
				die();
			}
			return true;
			break;


		case 'update-banner':
			$up_q = "UPDATE {testinstance} set banner='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['banner'], $_GET['recid']);
			if ( !$up_result ) {
				"Banner update query failed. [ERR-886]";
				die();
			}
			return true;
			break;


		case 'update-flags':
			$up_q = "UPDATE {testinstance} set flags='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['flags'], $_GET['recid']);
			if ( !$up_result ) {
				"Flags update query failed. [ERR-887]";
				die();
			}
			return true;
			break;

		case 'update-watchfile':
			$up_q = "UPDATE {testinstance} set watch_file='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['watch_file'], $_GET['recid']);
			if ( !$up_result ) {
				"Watch file update query failed. [ERR-889]";
				die();
			}
			return true;
			break;

		case 'update-fieldguidefile':
			$up_q = "UPDATE {testinstance} set fieldguide_file='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['fieldguide_file'], $_GET['recid']);
			if ( !$up_result ) {
				"Watch file update query failed. [ERR-890]";
				die();
			}
			return true;
			break;

		case 'update-info':
			$up_q = "UPDATE {testinstance} set info='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['info'], $_GET['recid']);
			if ( !$up_result ) {
				"Info update query failed. [ERR-870]";
				die();
			}
			return true;
			break;

		case 'update-notes':
			$up_q = "UPDATE {testinstance} set notes='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['notes'], $_GET['recid']);
			if ( !$up_result ) {
				"Notes update query failed. [ERR-888]";
				die();
			}
			return true;
			break;

		case 'update-discovered':
			$up_q = "UPDATE {testinstance} set discovered='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['discovered'], $_GET['recid']);
			if ( !$up_result ) {
				"Discovered update query failed. [ERR-871]";
				die();
			}
			return true;
			break;

		case 'update-raw-result':
			$up_q = "UPDATE {testinstance} set raw_result='%s' WHERE irid='%d'";
			$up_result = db_query($up_q, $_GET['raw-result'], $_GET['recid']);
			if ( !$up_result ) {
				"Raw-result update query failed. [ERR-872]";
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

		case 'set-progress':
			ptdb_set_binary_status( 'IN-PROGRESS' );
			break;

		case 'new-port':	// aka  add-test  or  new-test
			if ( !isset($_GET['service']) ) {
				pentdb_log_error('"service" param cannot be empty when adding a new service');
				unset($_GET['fcmd']);
				break;
			}
			if ( empty($_GET['port']) ) {
				pentdb_log_error('"port" param cannot be empty when adding a new service');
				unset($_GET['fcmd']);
				break;
			}
			create_port_record();
			jump_to_latest_test();
			break;

		case 'copy-port':
			if ( !isset($_GET['service']) ) {
				pentdb_log_error('"service" param cannot be empty when adding a new service');
				unset($_GET['fcmd']);
				break;
			}
			if ( empty($_GET['port']) ) {
				pentdb_log_error('"port" param cannot be empty when adding a new service');
				unset($_GET['fcmd']);
				break;
			}
			copy_port_record();
			jump_to_latest_test();
			break;

		case 'load_templates':
			ptdb_load_templates();
			break;

		case 'set-status':
			ptdb_set_depth_status();
			break;

		case 'save-as-template':
			ptdb_testset_to_template();
			break;

		case 'set-stype-binary':
			$up_q = "UPDATE {testinstance} set statustype='%s',status='' WHERE irid='%d'";
			$up_result = db_query($up_q, "BINARY", $_GET['recid']);
			if ( !$up_result ) {
				"Raw-result update query failed. [ERR-861]";
				die();
			}
			return true;
			break;

		case 'set-stype-depth':
			$up_q = "UPDATE {testinstance} set statustype='%s',status='' WHERE irid='%d'";
			$up_result = db_query($up_q, "DEPTH", $_GET['recid']);
			if ( !$up_result ) {
				"Raw-result update query failed. [ERR-862]";
				die();
			}
			return true;
			break;

		default:
			echo "<div>Uknown command.</div>";
			die();

	}

}

?>
