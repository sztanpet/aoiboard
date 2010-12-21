<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
define('APPROOT', dirname(__FILE__));

include_once(APPROOT.'/lib/constants.php');
include_once(APPROOT.'/model/pic.class.php');
include_once(APPROOT.'/model/link.class.php');
include_once(APPROOT.'/lib/functions.php');

$dbcnx = new PDO(DB_DSN);
ORM::set_dbcnx($dbcnx);

build_rss_files();
