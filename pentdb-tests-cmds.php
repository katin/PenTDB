<?php
/* PenTDB process commands
 *
 * Process and call tree for the cmd parm on the Tests Maintenance page
 *
 * 190625 KBI created to support the new tests templates maintenance screen
 */


function ptdb_process_tests_cmd ( $mycmd ) {

// pentdb_top_msg('TESTS MAINTENANCE PROCESSED CMD '.$mycmd);

	switch( $mycmd ) {

		case 'new-test':
			$success = pentdb_add_test();			// uses $_GET parms
			break;


		case 'edit-test':
			if ( empty($_GET['tid']) ) {
				die('tid param is required for this action.');
			}
			display_tid_page( $_GET['tid'] );
			die();
			break;

		case 'update-test':
			$success = pentdb_update_test();	// uses $_GET for parms
			ptdb_process_tests_cmd ( 'edit-test' );
			break;

		default:
			echo "<div>Uknown command.</div>";
			die();

	}
}
