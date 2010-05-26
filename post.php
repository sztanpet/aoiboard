<?php
define('APPROOT', dirname(__FILE__));

ignore_user_abort(true);

// return as fast as we can, spare the bot
ob_end_clean();
header("Connection: close\r\n");
header("Content-Encoding: none\r\n");
ob_end_flush();     
flush();            



include(APPROOT.'/lib/constants.php');
include(APPROOT.'/lib/functions.php');
include(APPROOT.'/model/pic.class.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

$url     = rawurldecode($_REQUEST['url']);
$nick    = rawurldecode($_REQUEST['nick']);
$comment = rawurldecode(isset($_REQUEST['comment']) ? $_REQUEST['comment'] : '');
$time    = $_SERVER['REQUEST_TIME'];

$file_name  = '/'.$time.rand(0,100);
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
		unset($tmp_path);
		die('unknown image type: '.$image_info['mime']);
}
$path = STORAGE_PATH.$file_name.'.'.$extension;
rename($tmp_path, $path);

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
	'ctime'        => date('Y-m-d H:i:s', $time),
));

if (ORM::all('pic', array('checksum' => $pic->checksum))->count() === 0) {
	if (!$pic->save()) {
		var_dump($pic->errors());
	}
} else {
	unlink($path);
	unlink($thumb_path);
}
