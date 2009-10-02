<?php
define('APPROOT', dirname(__FILE__));

include(APPROOT.'/lib/constants.php');
include(APPROOT.'/lib/functions.php');
include(APPROOT.'/model/pic.class.php');

$url     = rawurldecode($_REQUEST['url']);
$nick    = rawurldecode($_REQUEST['nick']);
$comment = rawurldecode($_REQUEST['comment']);
$time    = $_SERVER['REQUEST_TIME'];

$file_name  = '/'.$time.rand(0,100);
$tmp_path   = tempnam(TMP_PATH, 'board_pic');

shell_exec("wget -U 'Opera/9.60 (X11; Linux i686; U; en)' -c '".escapeshellcmd($url)."' -o /dev/null -O ".$tmp_path);

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
	'ctime'         => date('Y-m-d H:m:s', $time),
));
if (Pic::get(array('checksum' => $pic->checksum))->count() === 0) {
	$pic->save();
} else {
	unlink($path);
	unlink($thumb_path);
}
