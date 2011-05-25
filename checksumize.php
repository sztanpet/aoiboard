<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN, DB_USER, DB_PW, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
ORM::set_dbcnx($dbcnx);

$pics = ORM::all('Pic', array(), array('ctime', 'asc'));

foreach ($pics as $pic) {
	$ext = substr($pic->path, strrpos($pic->path, '.'), strlen($pic->path));
	print $pic->id."\n";
	if (is_readable($pic->path)) {
		rename($pic->path, STORAGE_PATH.'/'.$pic->checksum.$ext);
		$pic->path = STORAGE_PATH.$pic->checksum.$ext;
	}
	if (is_readable($pic->thumb)) {
		rename($pic->thumb, THUMB_PATH.'/'.$pic->checksum.'.jpg');
		$pic->thumb = THUMB_PATH.$pic->checksum.'.jpg';
	}
	$pic->save();
	flush();
}
