<?php
define('APPROOT', dirname(__FILE__)).'/';

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN, DB_USER, DB_PW, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
ORM::set_dbcnx($dbcnx);

$limit = PAGE_LIMIT_PIC;
list($params, $limit, $page) = build_iterator_where(PAGE_LIMIT_PIC);
$item_count = ORM::count('Pic', $params);
$maxpage = max(ceil($item_count / $limit) - 1, 0);
$item_count_on_last_page = $item_count - (floor($item_count / $limit) * $limit);
if ($item_count_on_last_page == 0) {
	$maxpage -= 1;
}
$urlparams = $_GET;

$visited_pages = cookie_list('visited_pages');

include(APPROOT.'/html/component/pager.html.php');
