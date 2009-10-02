<?php
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/lib/functions.php');

$css_files = array(
	'./css/base.css',
	'./css/index.css',
	'./css/component/pager.css',
	'./css/component/menu.css',
);

render_iterator('Pic', PAGE_LIMIT_PIC, 'html/index.html.php', $css_files);
