<?php
//
// PenTracker Settings and Configuration
//

// 190621 KBI created

//
// some default values to save typing

$machine_id = gethostname();

switch ( $machine_id ) {
	
	case 'G16-D9':
		define("DEFAULT_DATA_PATH",'/home/katin/Documents/hackthebox/');
		define("DEFAULT_CMD_PATH",'/root/Documents/bin/');

		define("DEFAULT_HOST_CMD",'mktank $ip $hostname');
		define("DEFAULT_HOST_PRORESULT_CMD",'cd $ip && penscan');

		define("DEFAULT_HOST_WATCHFILE_1", 'banners.txt');
		define("DEFAULT_HOST_WATCHFILE_2", 'nmap-A-scan.txt');
		define("DEFAULT_HOST_WATCHFILE_3", 'nmap-deep-scan.txt');

		break;

	case 'kali':
		define("DEFAULT_DATA_PATH",'/root/Documents/PWK-Lab3/');
		define("DEFAULT_CMD_PATH",'/root/Documents/bin/');

		define("DEFAULT_HOST_CMD",'mktank $ip $hostname');
		define("DEFAULT_HOST_PRORESULT_CMD",'cd $ip && penscan');

		define("DEFAULT_HOST_WATCHFILE_1", 'banners.txt');
		define("DEFAULT_HOST_WATCHFILE_2", 'nmap-A-scan.txt');
		define("DEFAULT_HOST_WATCHFILE_3", 'nmap-deep-scan.txt');

		break;

	default:
		define("DEFAULT_DATA_PATH",'/data-dir/Documents/test-area/');
		define("DEFAULT_CMD_PATH",'/root/Documents/bin/');

		define("DEFAULT_HOST_CMD",'mktank $ip $hostname');
		define("DEFAULT_HOST_PRORESULT_CMD",'cd $ip && penscan');

		define("DEFAULT_HOST_WATCHFILE_1", 'banners.txt');
		define("DEFAULT_HOST_WATCHFILE_2", 'nmap-A-scan.txt');
		define("DEFAULT_HOST_WATCHFILE_3", 'nmap-deep-scan.txt');

		break;

}
