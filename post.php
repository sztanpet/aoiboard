<?php
define('APPROOT', dirname(__FILE__));

ignore_user_abort(true);

// return as fast as we can, spare the bot
header("Connection: close\r\n");
header("Content-Encoding: none\r\n");
header("Content-Length: 0");
flush();

include(APPROOT.'/lib/constants.php');
include(APPROOT.'/lib/functions.php');
include(APPROOT.'/model/pic.class.php');
include(APPROOT.'/model/link.class.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

$url     = rawurldecode($_REQUEST['url']);
$nick    = rawurldecode($_REQUEST['nick']);
$comment = rawurldecode(isset($_REQUEST['comment']) ? $_REQUEST['comment'] : '');

$tmp_path   = tempnam(TMP_PATH, 'board_pic');

if (curl_geturl($url, $tmp_path) === false) {
	return 'cant download '.htmlspecialchars($url);
}

$extension  = '';
$image_info = getimagesize($tmp_path);
switch ($image_info['mime']) {
	case 'image/gif':
		$extension = 'gif';
		break;
	case 'image/jpeg':
		$extension = 'jpg';
		break;
	case 'image/png':
		$extension = 'png';
		break;
	default:
		save_link($url, $nick, $tmp_path);
		break;
}
save_pic($url, $nick, $comment, $tmp_path, $extension);

function save_pic($url, $nick, $comment, $saved_file, $ext){
	$file_name  = '/'.$_SERVER['REQUEST_TIME'].rand(0,100);
	$path = STORAGE_PATH.$file_name.'.'.$ext;
	rename($saved_file, $path);

	$thumb_path = THUMB_PATH.$file_name.'.jpg';
	create_thumb($path, $thumb_path);

	chmod($path, 0664);
	chmod($thumb_path, 0664);

	$pic = new Pic(array(
		'nick'         => $nick,
		'original_url' => $url,
		'path'         => $path,
		'comment'      => $comment,
		'thumb'        => $thumb_path,
		'ctime'        => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
	));

	if (ORM::all('pic', array('checksum' => $pic->checksum))->count() === 0) {
		if (!$pic->save()) {
			var_dump($pic->errors());
		}
	} else {
		unlink($path);
		unlink($thumb_path);
	}
}

function save_link($url, $nick, $saved_file) {
	$title = $url;
	if (preg_match('!<title>(?<title>.*?)</title>!sim', file_get_contents($saved_file), $match)) {
		$title = html_entity_decode($match['title']);
	}
	unlink($saved_file);

	$link = new Link(array(
		'nick'  => $nick,
		'title' => $title,
		'url'   => $url,
		'ctime' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
	));

	$link->save();
}
