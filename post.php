<?php
mb_internal_encoding('utf-8');
include('./lib/constants.php');
include('./lib/functions.php');
include('./model/db.class.php');
include('./model/pic.class.php');

$url     = rawurldecode($_REQUEST['url']);
$nick    = rawurldecode($_REQUEST['nick']);
$comment = rawurldecode($_REQUEST['comment']);

/*
$f    = 1; // 'follow rediredt'
$c    = 2;//1 for header, 2 for body, 3 for both 'content'
$r    = 'http://google.com/search'; 
$a    = "Opera/9.24 (X11; Linux i686; U; en)"; // 'user agent'
$cf   = NULL; // 'cookie file'
$pd   = NULL; 
$page = open_page($url, $f, $c, $r, $a, $cf, $pd);
*/

$time       = $_SERVER['REQUEST_TIME'];
$file_name  = '/'.$time.rand(0,100);
$tmp_path   = TMP_PATH.$file_name;

shell_exec("wget -U 'Opera/9.24 (X11; Linux i686; U; en)' -c '".escapeshellcmd($url)."' -o /dev/null -O ".$tmp_path);

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

$item = new Pic(array(
	'nick'         => $nick,
	'original_url' => $url,
	'path'         => $path,
	'comment'      => $comment,
	'thumb'        => $thumb_path,
	'time'         => date('Y-m-d H:m:s', $time),
));
$db = new DB(array('db' => DB_PIC_PATH, 'item_class' => 'Pic'));
$db->lock();
$db->load();
if ($db->is_uniq($item)) {
	$db->add($item);
	$db->save();
} else {
	unlink($path);
	unlink($thumb_path);
}
$db->unlock();
