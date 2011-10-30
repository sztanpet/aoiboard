<?php
define('APPROOT', dirname(__FILE__).'/');
include APPROOT.'lib/bootstrap.php';

render_iterator('Link', PAGE_LIMIT_LINK, 'html/links.html.php');
