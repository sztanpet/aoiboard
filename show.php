<?php
define('APPROOT', dirname(__FILE__).'/');
include APPROOT.'lib/bootstrap.php';

$pic = ORM::first('pic', array('id' => $_GET['p']));

include(APPROOT.'/html/show.html.php');
