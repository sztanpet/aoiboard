<?php
function create_thumb($path, $thumb_path, $width = THUMB_WIDTH, $height = THUMB_HEIGHT) {
	$image_info = getImageSize($path); // see EXIF for faster way
	switch ($image_info['mime']) {
		case 'image/gif':
			if(imagetypes() & IMG_GIF){ // not the same as IMAGETYPE
				$o_im = @imageCreateFromGIF($path) ;
			}else{
				throw new Exception('GIF images are not supported');
			}
			break;
		case 'image/jpeg':
			if (imagetypes() & IMG_JPG){
				$o_im = @imageCreateFromJPEG($path) ;
			}else{
				throw new Exception('JPEG images are not supported');
			}
			break;
		case 'image/png':
			if(imagetypes() & IMG_PNG){
				$o_im = @imageCreateFromPNG($path) ;
			}else{
				throw new Exception('PNG images are not supported');
			}
			break;
		case 'image/wbmp':
			if(imagetypes() & IMG_WBMP){
				$o_im = @imageCreateFromWBMP($path) ;
			}else{
				throw new Exception('WBMP images are not supported');
			}
			break;
		default:
			throw new Exception($image_info['mime'].' images are not supported');
			break;
	}

	list($o_wd, $o_ht, $html_dimension_string) = $image_info;

	$ratio = $o_wd / $o_ht;
	$t_ht  = $width;
	$t_wd  = $height;

	if (1 > $ratio) {
		$t_wd = round($o_wd * $t_wd / $o_ht);
	} else {
		$t_ht = round($o_ht * $t_ht / $o_wd);
	}


	$t_wd = ($t_wd < 1) ? 1 : $t_wd;
	$t_ht = ($t_ht < 1) ? 1 : $t_ht;

	$t_im = imageCreateTrueColor($t_wd, $t_ht);

	imageCopyResampled($t_im, $o_im, 0, 0, 0, 0, $t_wd, $t_ht, $o_wd, $o_ht);

	imagejpeg($t_im, $thumb_path, 85);

	chmod($thumb_path, 0664);
	imageDestroy($o_im);
	imageDestroy($t_im);
	return true;
}

function render_iterator($class, $page_limit, $template, $css_files) {
	$params = array();

	if (isset($_REQUEST['limit']) && (int)$_REQUEST['limit'] > 0) {
		$limit = (int)$_REQUEST['limit'];
	} else {
		$limit = $page_limit;
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

	if (isset($_GET['day'])) {
		if ($_GET['day'] == 'last' || $_GET['day'] == 'tomorrow') {
			$params['ctime'] = array(
				'apply' => 'DATE(@@ctime@@)',
				'value' => date("Y-m-d", strtotime('-1 day')),
			);
		}

		if ($_GET['day'] == 'today') {
			$params['ctime'] = array(
				'apply' => 'DATE(@@ctime@@)',
				'value' => date("Y-m-d", $_SERVER['REQUEST_TIME']),
			);
		}

		if (preg_match('/^\d{4}-\d\d-\d\d$/', $_GET['day'])) {
			$params['ctime'] = array(
				'apply' => 'DATE(@@ctime@@)',
				'value' => $_GET['day'],
			);
		}

		if (preg_match('/^-\d+$/', $_GET['day'])) {
			$params['ctime'] = array(
				'apply' => 'DATE(@@ctime@@)',
				'value' => date('Y-m-d', strtotime($_GET['day'].' day')),
			);
		}

		if (preg_match('/^(?<type>from|to)_(?<date>\d{4}-\d\d-\d\d|-\d+)$/', $_GET['day'], $tmp)) {
			$params['ctime'] = array(
				'apply' => 'DATE(@@ctime@@)',
			);

			if (substr($tmp['date'], 0, 1) == '-') {
				$params['ctime']['value'] = date('Y-m-d', strtotime($tmp['date'].' day'));
			} else {
				$params['ctime']['value'] = $tmp['date'];
			}
			if ($tmp['type'] == 'from') {
				$params['ctime']['cmp'] = '>=';
			} elseif ($tmp['type'] == 'to') {
				$params['ctime']['cmp'] = '<=';
			}
		}
	}

	if (isset($_GET['week']) && (in_array($_GET['week'], array('last', 'this')) || preg_match('/^-\d+$/', $_GET['week']))) {
		if ($_GET['week'] == 'last') {
			$start = strtotime('-1 week', strtotime('last Monday'));
		} elseif ($_GET['week'] == 'this') {
			$start = strtotime('last Monday');
		} else {
			$start = strtotime($_GET['week'].' week', strtotime('last Monday'));
		}
		$days = array();
		for ($i = 0; $i < 7; ++$i) {
			$days[] = date('Y-m-d', strtotime('+'.$i.' day', $start));
		}
		$params['ctime'] = array(
			'apply' => 'DATE(@@ctime@@)',
			'value' => $days,
		);
	}

	$maxpage = max(ceil(ORM::count($class, $params) / $limit) - 1, 0);
	$offset  = isset($offset) ? (int)$offset : (int)$limit * $maxpage;
	$page    = isset($page) ? $page : (int)$maxpage;

	$items   = ORM::all($class, $params, '', array($offset, $limit));
	$items->reverse();

	$urlparams = array(
		'page'  => $page, 
		'nick'  => $nick, 
		'day'   => (isset($params['ctime']) && isset($_GET['day']))   ? $_GET['day']  : null,
		'week'  => (isset($params['ctime']) && isset($_GET['week']))  ? $_GET['week'] : null,
		'limit' => ($limit != $page_limit && isset($_GET['limit']))   ? $limit        : null
	);

	include(APPROOT.'/'.$template);
}

function curl_geturl( $url, $filename ) {
	if (($fd = fopen($filename, 'w')) === false) {
		return false;
	}

	$parts = parse_url($url);

	if (!isset($parts['scheme']) || !isset($parts['host'])) {
		return false;
	}


	$url  = $parts['scheme'].'://';
	$url .= isset($parts['user']) ?     $parts['user'] : '';
	$url .= isset($parts['pass']) ? ':'.$parts['pass'] : '';
	$url .= isset($parts['pass']) || isset($parts['user']) ? '@'.$parts['host'] : $parts['host'];
	$url .= isset($parts['path']) ? join('/', array_map('rawurlencode', explode('/', $parts['path']))) : '';

	if (isset($parts['query'])) {
		parse_str($parts['query'], $query_parts);
		$url .= '?'.http_build_query(array_map('rawurlencode', $query_parts));
	}

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_REFERER       , $url);
	curl_setopt($curl, CURLOPT_USERAGENT     , 'Mozilla/5.0 (Windows; U; Windows NT 5.1; hu; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 ( .NET CLR 3.5.30729)');
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_HEADER        , false);
	curl_setopt($curl, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_1);
	curl_setopt($curl, CURLOPT_FILE          , $fd);

	$ret = curl_exec($curl);
	curl_close($curl);
	fclose($fd);

	return $ret;
}
