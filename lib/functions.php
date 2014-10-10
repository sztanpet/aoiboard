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
	return array($t_wd, $t_ht);
}

function build_iterator_where($page_limit = array()) {
	$params = array();
	$page   = null;

	if (isset($_GET['limit']) && (int)$_GET['limit'] > 0) {
		$limit = (int)$_GET['limit'];
	} else {
		$limit = $page_limit;
	}

	if (isset($_GET['page']) && (int)$_GET['page'] >= 0) {
		$page = (int)$_GET['page'];
	}

	if (isset($_GET['nick']) && trim($_GET['nick']) !== '') {
		$params['nick'] = rawurldecode($_GET['nick']);
	}

	if (isset($_GET['from']) && (int)$_GET['from'] > 0) {
		$params['id'] = array(
			'cmp'   => '<',
			'value' => $_GET['from'],
		);
	}

	if (isset($_GET['url'])) {
		$params['original_url'] = array(
			'cmp'   => ' like ',
			'value' => '%'.$_GET['url'].'%',
		);
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

	return array($params, $limit, $page);
}

function render_iterator($class, $page_limit, $template) {

	list($params, $limit, $page) = build_iterator_where($page_limit);
	$item_count = ORM::count($class, $params);

	$pager_calculator = new PagerCalculator($item_count, $limit);
	$page = $pager_calculator->calculate($page);

	$offset = $pager_calculator->get_offset();
	$limit = $pager_calculator->get_limit();
	$maxpage = $pager_calculator->get_maxpage();

	$items = ORM::all($class, $params, array('ctime', 'desc'), array($offset, $limit));

	$urlparams = array(
		'page'  => $page,
		'nick'  => isset($params['nick']) && $params['nick'] !== null ? rawurlencode($params['nick']) : null,
		'day'   => (isset($params['ctime']) && isset($_GET['day']))   ? $_GET['day']  : null,
		'week'  => (isset($params['ctime']) && isset($_GET['week']))  ? $_GET['week'] : null,
		'url'   => (isset($params['original_url']) && isset($_GET['url'])) ? $_GET['url']  : null,
		'limit' => ($limit != $page_limit   && isset($_GET['limit'])) ? $limit        : null
	);

	extract($GLOBALS);

	// since this is an ajax query with only the data included, we can set expires header safely when not on the last page
	if (is_xhr_request() && $page !== $maxpage) {
		header('Expires: '.date('r', strtotime('+1 week', $_SERVER['REQUEST_TIME'])));
	}

	include(APPROOT.'/'.$template);
}

function curl_geturl($url, $filename, $referer = null, $options = array()) {
	if (($fd = fopen($filename, 'w')) === false) {
		return false;
	}

	$url = rebuild_url($url);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_HEADER        , false);
	curl_setopt($curl, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_1);
	curl_setopt($curl, CURLOPT_FILE          , $fd);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

	if (isset($options['size_limit'])) {
		$full_resp_length = 0;
		curl_setopt($curl, CURLOPT_WRITEFUNCTION, function($curl, $resp_chunk) use (&$full_resp_length, $fd, $options) {
			$chunk_size = strlen($resp_chunk);
			$full_resp_length += $chunk_size;
			if ($full_resp_length > $options['size_limit']) {
				return false;
			} else {
				return fwrite($fd, $resp_chunk);
			}
		});
	}

	if ($referer) {
		curl_setopt($curl, CURLOPT_REFERER, $referer);
	}

	$ret = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	fclose($fd);

	if ($ret === false) {
		throw new Exception($err);
	}

	return $ret;
}

function curl_head($url, $referer = null) {
	$url = rebuild_url($url);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_HEADER        , true);
	curl_setopt($curl, CURLOPT_NOBODY        , true);
	curl_setopt($curl, CURLOPT_HTTP_VERSION  , CURL_HTTP_VERSION_1_1);
	if ($referer) {
		curl_setopt($curl, CURLOPT_REFERER, $referer);
	}

	$ret = curl_exec($curl);
	curl_close($curl);

	return $ret;
}

function rebuild_url($url) {
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
	return $url;
}

function gc_pics() {
	$not_deleted_pics = ORM::all('pic', array('deleted' => array('value' =>  array('', '0', false, null))), array('ctime', 'asc'), array('0', '3000'));
	// glob() is disabled on some host :-(
	// just give up on open_basedir already and choort the php process damint (fpm is my hero)
	$files = array_diff(scandir(realpath(STORAGE_PATH)), array('.', '..'));
	$sum_size = 0;
	foreach ($files as $f) {
		$sum_size += (int)(filesize(realpath(STORAGE_PATH).'/'.$f) / 1024);
	}
	$bytes_to_free      = $sum_size - STORAGE_LIMIT;
	$bytes_freed_so_far = 0;

	if ($bytes_to_free < 0) {
		return;
	}

	file_put_contents(GC_LOG_FILE, '['.date('Y-m-d H:i:s').'] '.$bytes_to_free."kb to free\n", FILE_APPEND);

	foreach ($not_deleted_pics as $i => $pic) {
		if ($bytes_freed_so_far >= $bytes_to_free) {
			break;
		}
		$bytes_freed_so_far += (filesize(realpath($pic->path)) / 1024);
		file_put_contents(GC_LOG_FILE, '['.date('Y-m-d H:i:s').'] deleting: ('.$pic->id.') size: '.((int)(filesize(realpath($pic->path)) / 1024))."kb\n", FILE_APPEND);
		$pic->delete();
	}
	file_put_contents(GC_LOG_FILE, "\n", FILE_APPEND);
	return $bytes_freed_so_far;
}

function base_url() {
	return 'http://'.str_replace('\\', '/', $_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME'])).'/';
}

function base_path() {
	return str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
}

function build_rss_files(){
	$pics  = ORM::all('pic', array('deleted' => ''), array('id', 'desc'), array('0', '100'))->to_a();
	$links = ORM::all('link', array(), array('id', 'desc'), array(0, 100))->to_a();
	$combined = array_merge($pics, $links);
	usort($pics,     'cmp_model_by_ctime');
	usort($links,    'cmp_model_by_ctime');
	usort($combined, 'cmp_model_by_ctime');


	$rss_pic_thumbs = new RssBuilder(array(
		'title' => '#aoianime pictures',
		'link'  => base_url().RSS_PATH.'/'.PIC_THUMB_RSS_FILE,
	));
	$rss_pic_full = new RssBuilder(array(
		'title' => '#aoianime pictures (full size)',
		'link'  => base_url().RSS_PATH.'/'.PIC_FULL_RSS_FILE,
	));
	foreach ($pics as $pic) {
		$rss_pic_thumbs->add_item($pic->to_rss('thumb'));
		$rss_pic_full->add_item($pic->to_rss('full'));
	}
	$rss_pic_thumbs->build(RSS_PATH.PIC_THUMB_RSS_FILE);
	$rss_pic_full->build(RSS_PATH.PIC_FULL_RSS_FILE);



	$rss_links = new RssBuilder(array(
		'title' => '#aoianime links',
		'link'  => base_url().RSS_PATH.'/'.LINK_RSS_FILE,
	));
	foreach ($links as $link) {
		$rss_links->add_item($link->to_rss('full'));
	}
	$rss_links->build(RSS_PATH.LINK_RSS_FILE);


	$rss_combined_thumb = new RssBuilder(array(
		'title' => '#aoianime combined',
		'link'  => base_url().RSS_PATH.'/'.COMBINED_THUMB_RSS_FILE,
	));
	$rss_combined_full = new RssBuilder(array(
		'title' => '#aoianime combined (full size)',
		'link'  => base_url().RSS_PATH.'/'.COMBINED_FULL_RSS_FILE,
	));
	foreach ($combined as $item) {
		if ($item instanceof Pic) {
			$rss_combined_thumb->add_item($item->to_rss('thumb'));
			$rss_combined_full->add_item($item->to_rss('full'));
		} else {
			$rss_combined_thumb->add_item($item->to_rss());
			$rss_combined_full->add_item($item->to_rss());
		}
	}
	$rss_combined_thumb->build(RSS_PATH.COMBINED_THUMB_RSS_FILE);
	$rss_combined_full->build(RSS_PATH.COMBINED_FULL_RSS_FILE);
}

function cmp_model_by_ctime($lhs, $rhs){
	$l = strtotime($lhs->ctime);
	$r = strtotime($rhs->ctime);
	return $r - $l;
}

function setting_enabled($setting){
	return (isset($_COOKIE['setting_'.$setting]) && $_COOKIE['setting_'.$setting]) ? true : false;
}

function cookie_list($cookie_name) {
	return isset($_COOKIE[$cookie_name]) ? explode('|', $_COOKIE[$cookie_name]) : array();
}

function is_xhr_request() {
	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'], 'XMLHttpRequest') === 0);
}

function checksumize() {
	$pics = ORM::all('Pic', array(), array('ctime', 'asc'));

	foreach ($pics as $pic) {
		$ext = substr($pic->path, strrpos($pic->path, '.'), strlen($pic->path));
		print $pic->id."\n";
		if (is_readable($pic->path)) {
			rename($pic->path, STORAGE_PATH.'/'.$pic->checksum.$ext);
			$pic->path = STORAGE_PATH.$pic->checksum.$ext;
		}
		if (is_readable($pic->thumb)) {
			rename($pic->thumb, THUMB_PATH.'/'.$pic->checksum.'.jpg');
			$pic->thumb = THUMB_PATH.$pic->checksum.'.jpg';
		}
		$pic->save();
		flush();
	}
}

function measurize() {
	$pics = ORM::all('Pic');

	print "<pre>";
	foreach ($pics as $i => $pic) {
		list($w, $h) = getimagesize($pic->thumb);
		$pic->width = $w;
		$pic->height = $h;
		$pic->save();
		print ".";
		if ($i % 100 == 0 && $i != 0) {
			print "\n$i\n";
		}
		flush();
	}
}

function last_fetch_log_file() {
	return basename(end((glob(LOG_PATH.'/fetch-*.log'))));
}
