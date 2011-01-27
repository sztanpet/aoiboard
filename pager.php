<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

list($params, $limit, $offset, $page) = build_iterator_where(PAGE_LIMIT_PIC);
$maxpage = max(ceil(ORM::count('Pic', $params) / $limit) - 1, 0);
$urlparams = $_GET;

$css_files = array(
	'./css/component/pager.css',
);

include(APPROOT.'/html/component/pager.html.php');