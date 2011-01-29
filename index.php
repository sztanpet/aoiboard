<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0) {
	$css_files = array();
	$template = 'html/pager.html.php';
} else {
	$css_files = array(
		'./css/base.css',
		'./css/index.css',
		'./css/component/pager.css',
		'./css/component/menu.css',
	);
	$template = 'html/index.html.php';
}

$js_files = array(
	'js/settings.js',
);

if (setting_enabled('autofill')) {
	$js_files[] = 'js/pagescroller.js';
}

$autofillable = true;

render_iterator('Pic', PAGE_LIMIT_PIC, $template);
