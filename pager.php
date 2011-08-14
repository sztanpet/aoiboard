<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

list($params, $limit, $page) = build_iterator_where(PAGE_LIMIT_PIC);
$maxpage = max(ceil(ORM::count('Pic', $params) / $limit) - 1, 0);
$urlparams = $_GET;

$visited_pages = cookie_list('visited_pages');

include(APPROOT.'/html/component/pager.html.php');
