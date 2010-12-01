<?php
mb_internal_encoding('utf-8');
error_reporting(E_ALL);

define('STORAGE_PATH'   , './pic/');
define('TMP_PATH'       , '/tmp/');
define('THUMB_PATH'     , './thumb/');
define('THUMB_WIDTH'    , 200);
define('THUMB_HEIGHT'   , 200);
define('PAGE_LIMIT_PIC' , 18);
define('PAGE_LIMIT_LINK', 50);
define('PAGER_LIMIT'    , 13);
define('STORAGE_LIMIT'  , (1024*1024)); //1G if files counted in kilobytes
define('DB_DSN'         , 'sqlite:'.APPROOT.'/db/board.s3db');
