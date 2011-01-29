<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/link.class.php');
include_once(APPROOT.'/lib/functions.php');

$css_files = array(
	'./css/base.css',
	'./css/links.css',
	'./css/component/pager.css',
	'./css/component/menu.css',
);

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

render_iterator('Link', PAGE_LIMIT_LINK, 'html/links.html.php');
