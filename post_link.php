<?php

mb_internal_encoding('utf-8');
include('./lib/constants.php');
include('./model/link.class.php');
include('./model/db.class.php');

$url       = rawurldecode($_REQUEST['url']);
$nick      = rawurldecode($_REQUEST['nick']);
$time      = $_SERVER['REQUEST_TIME'];
$file_name = '/'.$time.rand(0,100);
$tmp_path  = TMP_PATH.$file_name;

shell_exec("wget -U 'Opera/9.24 (X11; Linux i686; U; en)' -c '".escapeshellcmd($url)."' -o /dev/null -O ".$tmp_path);

$title = $url;
if (preg_match('!<title>(?<title>.*?)</title>!sim', file_get_contents($tmp_path), $match)) {
	$title = html_entity_decode($match['title']);
}
unlink($tmp_path);

$item = new Link(array(
	'nick'  => $nick, 
	'title' => $title, 
	'url'   => $url, 
	'time'  => date('Y-m-d H:m:s', $_SERVER['REQUEST_TIME'])
));

$db = new DB(array('db' => DB_LINK_PATH, 'item_class' => 'Link'));

$db->lock();
$db->load();

if ($db->is_uniq($item)) {
	$db->add($item);
	$db->save();
}
$db->unlock();

