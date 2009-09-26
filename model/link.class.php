<?php 
include('./lib/constants.php');
include_once('model/item.class.php');

class Link extends Item {
	
	const INDEX_NICK     = 0;
	const INDEX_TIME     = 1;
	const INDEX_TITLE    = 2;
	const INDEX_URL      = 3;

	protected static $attr = array(
		'nick' => array(
			'type'  => 'ro',
			'read'  => '',
			'write' => '',
		),
		'time' => array(
			'type'  => 'ro',
			'read'  => '',
			'write' => '',
		),
		'title' => array(
			'type'  => 'ro',
			'read'  => '',
			'write' => '',
		),
		'url' => array(
			'type'  => 'ro',
			'read'  => '',
			'write' => '',
		),
	);

	public function __construct($params) {
		parent::__construct($params);

		$errors = array();
		$params = array_map('trim', $params);
		
		
		if (isset($params['nick']) && $params['nick'] !== '') {
			$this->data['nick'] = $params['nick'];
		} else {
			$errors['nick'] = 'no nick given';
		}
		
		if (isset($params['url']) && $params['url'] !== '') {
			$this->data['url'] = $params['url'];
		} else {
			$errors['url'] = 'no url given';
		}
		
		if (isset($params['title']) && $params['title'] !== '') {
			$this->data['title'] = $params['title'];
		} else {
			$errors['title'] = 'no title given';
		}

		if (isset($params['time']) && preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $params['time'])) {
			$this->data['time'] = $params['time'];
		} else {
			$errors['time'] = 'no time given';
		}
		
		if (!empty($errors)) {
			throw new Exception(var_export($errors, true));
		}

	}
	
	public static function fromCSV($csv_array) {
		return new self(array(
			'nick'  => $csv_array[self::INDEX_NICK],
			'time'  => $csv_array[self::INDEX_TIME],
			'title' => $csv_array[self::INDEX_TITLE],
			'url'   => $csv_array[self::INDEX_URL],
		));
	}
	
	public function toCSVarray() {
		$re = array (
			self::INDEX_NICK  => str_replace(array(';', "\n", "\r"), '', $this->nick),
			self::INDEX_TIME  => $this->time,
			self::INDEX_TITLE => str_replace(array(';', "\n", "\r"), '', $this->title),
			self::INDEX_URL   => str_replace(array(';', "\n", "\r"), '', $this->url),
		);
		ksort($re);
		return $re;
	}

	public function toCSV() {
		return join(';', $this->toCSVarray())."\r\n";
	}
	
	public function match($params) {
		if ($params instanceof self) {
			return $params->url === $this->url ? true : false; 
		}

		return parent::match($params);
	}
	
	public function delete() {
	}

	public function getAttr() {
		return self::$attr;
	}

}
