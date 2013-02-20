<?php
define('APPROOT', dirname(__FILE__).'/');
include APPROOT.'lib/bootstrap.php';

ignore_user_abort(true);

// return as fast as we can, spare the bot
header("Connection: close\r\n");
header("Content-Encoding: none\r\n");
header("Content-Length: 0");
flush();

define('FIVE_MEGS', 5*1024*1024);

$url     = rawurldecode($_REQUEST['url']);
$nick    = rawurldecode($_REQUEST['nick']);
$comment = rawurldecode(isset($_REQUEST['comment']) ? trim($_REQUEST['comment']) : '');

$tmp_path = tempnam(TMP_PATH, 'board_pic');

$referer = null;

$referer_map = array(
	'yande.re' => 'http://yande.re/post/',
	'sankakustatic.com' => 'http://chan.sankakucomplex.com/post/show/42',
        'cs.sankakucomplex.com' => 'http://chan.sankakucomplex.com/post/show/42',
	'is.sankakucomplex.com' => 'http://idol.sankakucomplex.com/post/show/42',
);

foreach ($referer_map as $needle => $ref) {
	if (stristr($url, $needle) !== false) {
		$referer = $ref;
	}
}

$header = curl_head($url, $referer);
preg_match('/Content-Length:\s?(?<size>\d+)/i', $header, $size);
$size = isset($size['size']) ? $size['size'] : false;
preg_match('/Content-Type:\s?(?<type>\S+)/i', $header, $type);
$type = isset($type['type']) ? $type['type'] : false;

if ($size === false) {
	save_link($type, $size, $url, $nick, $tmp_path);
	exit;
}

if ($size > FIVE_MEGS) {
	error_log('too big image file, saving as link: '.htmlspecialchars($url));
	save_link($type, $size, $url, $nick, $tmp_path);
	exit;
}

if (curl_geturl($url, $tmp_path, $referer) === false) {
	error_log('cant download '.htmlspecialchars($url));
	save_link($type, $size, $url, $nick, $tmp_path);
	exit;
}

if (!preg_match('!image/(jpeg|png|gif)!i', $type)) {
	save_link($type, $size, $url, $nick, $tmp_path);
	exit;
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
		save_link($type, $size, $url, $nick, $tmp_path);
		exit;
		break;
}
save_pic($url, $nick, $comment, $tmp_path, $extension);

build_rss_files();

function save_pic($url, $nick, $comment, $saved_file, $ext){
	$file_name  = md5_file($saved_file);
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
	}
}

function save_link($type, $size, $url, $nick, $saved_file) {
	$title = $url;
	if (stristr($type, 'text/html') !== false && $size < FIVE_MEGS) {
		if (preg_match('!<title>(?<title>.*?)</title>!sim', file_get_contents($saved_file), $match)) {
			$title = html_entity_decode($match['title']);
		}
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
