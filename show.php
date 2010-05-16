<?php
define('APPROOT', dirname(__FILE__));

include(APPROOT.'/lib/constants.php');
include(APPROOT.'/model/pic.class.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

$pic = ORM::first('pic', array('id' => $_GET['p']));

$css_files = array(
	'./css/base.css',
	'./css/index.css',
);


include(APPROOT.'/html/show.html.php');
