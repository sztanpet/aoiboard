<?php
mb_internal_encoding('utf-8');

include('./model/link.class.php');
include('./model/db.class.php');
include('./lib/constants.php');

$css_files = array(
	'./css/links.css',
	'./css/component/pager.css',
	'./css/component/menu.css',
);

$db  = new DB(array('db' => DB_LINK_PATH, 'item_class' => 'Link'));
$db->load();

$params = array();

if (isset($_REQUEST['limit']) && (int)$_REQUEST['limit'] > 0) {
	$limit = (int)$_REQUEST['limit'];
} else {
	$limit = PAGE_LIMIT_LINK;
}

if (isset($_GET['page']) && (int)$_GET['page'] >= 0) {
	$offset = $limit * (int)$_GET['page'];
	$page = (int)$_GET['page'];
}

if (isset($_GET['nick']) && trim($_GET['nick']) !== '') {
	$params['nick'] = rawurldecode($_GET['nick']);
	$nick = $params['nick'];
} else {
	$nick = null;
}

$items   = $db->get($params);
$maxpage = ceil(count($items) / $limit) - 1;
$offset  = isset($offset) ? (int)$offset : (int)$limit * $maxpage;
$items   = array_slice($items, $offset, $limit);
$page    = isset($page) ? $page : (int)$maxpage;

$urlparams = array('page' => $page, 'nick' => $nick, 'limit' => ($limit != PAGE_LIMIT && isset($_GET['limit'])) ? $limit : null);

include('./html/links.html.php');

