<?php
define('APPROOT', dirname(__FILE__));


// return as fast as we can, spare the bot
ob_end_clean();
header("Connection: close\r\n");
header("Content-Encoding: none\r\n");
ob_end_flush();     
flush();            


include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/link.class.php');

$dbcnx = new PDO(DB_DSN);
Model::set_dbcnx($dbcnx);

$url       = rawurldecode($_REQUEST['url']);
$nick      = rawurldecode($_REQUEST['nick']);
$time      = $_SERVER['REQUEST_TIME'];
$file_name = '/'.$time.rand(0,100);
$tmp_path  = tempnam(TMP_PATH, 'board_link');

shell_exec("wget -U 'Opera/9.60 (X11; Linux i686; U; en)' -c '".escapeshellcmd($url)."' -o /dev/null -O ".$tmp_path);

$title = $url;
if (preg_match('!<title>(?<title>.*?)</title>!sim', file_get_contents($tmp_path), $match)) {
	$title = html_entity_decode($match['title']);
}
unlink($tmp_path);

$link = new Link(array(
	'nick'  => $nick, 
	'title' => $title, 
	'url'   => $url, 
	'ctime' => date('Y-m-d H:m:s', $_SERVER['REQUEST_TIME']),
));
$link->save();
