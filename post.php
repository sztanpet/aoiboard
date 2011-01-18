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

define('THREE_MEGS', 3*1024*1024);

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

$url     = rawurldecode($_REQUEST['url']);
$nick    = rawurldecode($_REQUEST['nick']);
$comment = rawurldecode(isset($_REQUEST['comment']) ? trim($_REQUEST['comment']) : '');

$tmp_path = tempnam(TMP_PATH, 'board_pic');

$header = curl_head($url);
preg_match('/Content-Length:\s?(?<size>\d+)/i', $header, $size);
$size = $size['size'];
preg_match('/Content-Type:\s?(?<type>\S+)/i', $header, $type);
$type = $type['type'];

if (!preg_match('!image/(jpeg|png|gif)!i', $type)) {
	save_link($url, $nick, $tmp_path);
	exit;
}

if ($size > THREE_MEGS) {
	error_log('too big image file, saving as link: '.htmlspecialchars($url));
	save_link($url, $nick, $tmp_path);
	exit;
}

if (curl_geturl($url, $tmp_path) === false) {
	error_log('cant download '.htmlspecialchars($url));
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
		exit;
		break;
}
save_pic($url, $nick, $comment, $tmp_path, $extension);
gc_pics();
build_rss_files();

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
		if ($_SERVER['HTTP_HOST'] !== 'netslum.ath.cx') {
			curl_geturl('http://netslum.ath.cx/board/post.php?'.http_build_query(array(
				'url' => $url,
				'nick' => $nick,
				'comment' => $comment,
			)), '/dev/null');
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

	if ($_SERVER['HTTP_HOST'] !== 'netslum.ath.cx') {
		curl_geturl('http://netslum.ath.cx/board/post.php?'.http_build_query(array(
			'url' => $url,
			'nick' => $nick,
		)), '/dev/null');
	}
}
