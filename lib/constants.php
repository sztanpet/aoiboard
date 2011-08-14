<?php
mb_internal_encoding('utf-8');
error_reporting(E_ALL);

define('STORAGE_PATH'   , './pic/');
define('RSS_PATH'       , APPROOT.'/rss/');
define('GC_LOG_FILE'    , './log/gc.log');
define('TMP_PATH'       , '/tmp/');
define('THUMB_PATH'     , './thumb/');
define('THUMB_WIDTH'    , 200);
define('THUMB_HEIGHT'   , 200);
define('PAGE_LIMIT_PIC' , 18);
define('PAGE_LIMIT_LINK', 50);
define('PAGER_LIMIT'    , 13);
define('STORAGE_LIMIT'  , (1024*1024)); //1G if files counted in kilobytes
define('DB_DSN'         , 'sqlite:'.APPROOT.'/db/board.s3db');
define('MAGIC_VERSION_NUMBER_AGAINST_CACHE_PROBLEMS', 9);

define('PIC_THUMB_RSS_FILE',      'pics_thumbs.xml');
define('PIC_FULL_RSS_FILE',       'pics_full.xml');
define('LINK_RSS_FILE',           'links.xml');
define('COMBINED_THUMB_RSS_FILE', 'combined_thumbs.xml');
define('COMBINED_FULL_RSS_FILE',  'combined_full.xml');
