<?php
include APPROOT.'lib/constants.php';
include APPROOT.'lib/functions.php';
include APPROOT.'lib/rss_builder.class.php';
include APPROOT.'model/pic.class.php';
include APPROOT.'model/link.class.php';

$dbcnx = new PDO(DB_DSN, DB_USER, DB_PW);
ORM::set_dbcnx($dbcnx);

$css_files = array(
	'css/bootstrap.min.css',
	'css/styles.css',
);

$js_files = array(
	'js/jquery-1.6.2.min.js',
	'js/scripts.js',
);

