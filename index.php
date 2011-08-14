<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

if (is_xhr_request()) {
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
	'js/shortcuts.js',
);

if (setting_enabled('autofill')) {
	$js_files[] = 'js/autofiller.js';
}

$hidden_images = cookie_list('hideimages');
$visited_pages = cookie_list('visited_pages');
$autofillable = true;

render_iterator('Pic', PAGE_LIMIT_PIC, $template);
