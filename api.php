<?php 

// api.php - PenTDB API
//
// Serve this file locally only:   php -S 127.0.0.1:8888 api.php
//									(this command serves IP4 only)
//
// 190601 KBI - switch to router file for use with PHP dev web server; runs commands only (no db)
// 190519 KBI - created

date_default_timezone_set('America/Los_Angeles');




//
// show requests on console
// 

$path = $_SERVER["SCRIPT_FILENAME"];
file_put_contents("php://stdout", "\nRequested: $path");



//
// get parms
//

$parms = array();
parse_str( $_SERVER['QUERY_STRING'], $parms );



//
// param checks
//


//
// sanitize parms
//




// 
// run cmds
//

echo "<div>SERVER: <pre>".print_r($_SERVER,true)."</pre></div>";

echo "<div>parms: <pre>".print_r($parms,true)."</pre></div>";

$result = shell_exec('echo $PATH');
echo "<div>PATH: <pre>".print_r($result,true)."</pre></div>";


switch ( $parms['cmd'] ) {
	case 'penscan':
		$datapath = $parms['datapath'];
		$ip = $parms['ip'];
		$cmdpath = $parms['cmdpath'];
		$execcmd = $cmdpath.'penscan '.$datapath.$ip;
		file_put_contents("php://stdout", "\nExecuting command: $execcmd");
		exec($execcmd);
	break;

}



/* example output of $_SERVER upon run:
Array
(
    [DOCUMENT_ROOT] => /home/katin/Workshop/PenTDB/public_html
    [REMOTE_ADDR] => 127.0.0.1
    [REMOTE_PORT] => 47754
    [SERVER_SOFTWARE] => PHP 7.2.19-1+0~20190531112637.22+stretch~1.gbp75765b Development Server
    [SERVER_PROTOCOL] => HTTP/1.1
    [SERVER_NAME] => 127.0.0.1
    [SERVER_PORT] => 8888
    [REQUEST_URI] => /?cmd=mktank&path=/home/katin/Documents/bin
    [REQUEST_METHOD] => GET
    [SCRIPT_NAME] => /index.php
    [SCRIPT_FILENAME] => /home/katin/Workshop/PenTDB/public_html/index.php
    [PHP_SELF] => /index.php
    [QUERY_STRING] => cmd=mktank&path=/home/katin/Documents/bin
    [HTTP_HOST] => 127.0.0.1:8888
    [HTTP_CONNECTION] => keep-alive
    [HTTP_UPGRADE_INSECURE_REQUESTS] => 1
    [HTTP_USER_AGENT] => Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36
    [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,* /*;q=0.8,application/signed-exchange;v=b3
    [HTTP_ACCEPT_ENCODING] => gzip, deflate, br
    [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.9
    [REQUEST_TIME_FLOAT] => 1559391652.7855
    [REQUEST_TIME] => 1559391652
)
*/


