<?php
define('APPROOT', dirname(__FILE__).'/');
include APPROOT.'lib/bootstrap.php';

if (is_xhr_request()) {
	$css_files = array();
	$js_files  = array();
	$template = 'html/pager.html.php';
} else {
	$template = 'html/index.html.php';
}

$hidden_images = cookie_list('hideimages');
$visited_pages = cookie_list('visited_pages');
$autofillable = true;

render_iterator('Pic', PAGE_LIMIT_PIC, $template);
