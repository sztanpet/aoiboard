<?php
include('./lib/constants.php');
include_once('model/item.class.php');

class Pic extends Item {
	
	const INDEX_CHECKSUM     = 0;
	const INDEX_TIME         = 1;
	const INDEX_NICK         = 2;
	const INDEX_PATH         = 3;
	const INDEX_THUMB        = 4;
	const INDEX_ORIGINAL_URL = 5;
	const INDEX_COMMENT      = 6;

	protected static $attr = array(
		'time' => array(
			'type'  => 'ro', 
			'read'  => '', 
			'write' => '',
		),
		'original_url' => array(
			'type'  => 'ro', 
			'read'  => '', 
			'write' => '',
		),
		'thumb' => array(
			'type'  => 'ro', 
			'read'  => '', 
			'write' => '',
		),
		'nick' => array(
			'type'  => 'ro', 
			'read'  => '', 
			'write' => '',
		),
		'path' => array(
			'type'  => 'ro', 
			'read'  => '', 
			'write' => '',
		),
		'comment' => array(
			'type'  => 'ro', 
			'read'  => '', 
			'write' => '',
		),
		'checksum' => array(
			'type' => 'ro',
			'read' => '',
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
		
		if (isset($params['original_url']) && $params['original_url'] !== '') {
			$this->data['original_url'] = $params['original_url'];
		} else {
			$errors['original_url'] = 'no original_url given';
		}

		if (isset($params['comment'])) {
			$this->data['comment'] = $params['comment'];
		} else {
			$errors['comment'] = 'no comment given';
		}
		
		if (isset($params['time']) && preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $params['time'])) {
			$this->data['time'] = $params['time'];
		} else {
			$errors['time'] = 'no time given';
		}
		 
		if (isset($params['path'])) {
			if (is_file($params['path']) && is_readable($params['path'])) {
				$this->data['path'] = $params['path'];
			} else {
				$errors['path'] = 'no suck file path: '.$params['path'];
			}
		} else {
			$errors['path'] = 'no path given';
		}

		if (isset($params['thumb'])) {
			if (is_file($params['thumb']) && is_readable($params['thumb'])) {
				$this->data['thumb'] = $params['thumb'];
			} else {
				$errors['thumb'] = 'no suck file: '.$params['thumb'];
			}
		} else {
			$errors['thumb'] = 'no thumb given';
		}

		if (!empty($errors)) {
			throw new Exception(var_export($errors, true));
		}

		if (!isset($params['checksum'])) {
			$this->data['checksum'] = md5_file($params['path']);
		} else {
			$this->data['checksum'] = $params['checksum'];
		}
	}

	public static function fromCSV($csv_array) {
		return new self(array(
			'checksum'     => $csv_array[self::INDEX_CHECKSUM],
			'time'         => $csv_array[self::INDEX_TIME],
			'nick'         => $csv_array[self::INDEX_NICK],
			'path'         => $csv_array[self::INDEX_PATH],
			'thumb'        => $csv_array[self::INDEX_THUMB],
			'comment'      => $csv_array[self::INDEX_COMMENT],
			'original_url' => $csv_array[self::INDEX_ORIGINAL_URL],
		));
	}

	public function toCSVarray() {
		$re = array (
			self::INDEX_CHECKSUM     => $this->checksum,
			self::INDEX_TIME         => $this->time,
			self::INDEX_NICK         => str_replace(array(';', "\n", "\r"), '', $this->nick),
			self::INDEX_PATH         => str_replace(array(';', "\n", "\r"), '', $this->path),
			self::INDEX_THUMB        => str_replace(array(';', "\n", "\r"), '', $this->thumb),
			self::INDEX_ORIGINAL_URL => str_replace(array(';', "\n", "\r"), '', $this->original_url),
			self::INDEX_COMMENT      => str_replace(array(';', "\n", "\r"), '', $this->comment),
		);
		ksort($re);
		return $re;
	}

	public function toCSV() {
		return join(';', $this->toCSVarray())."\r\n";
	}

	public function delete() {
		unlink($this->path);
		unlink($this->thumb);
	}

	public function match($params) {
		if ($params instanceof self) {
			return $params->checksum === $this->checksum ? true : false; 
		}

		return parent::match($params);
	}

	public function getAttr() {
		return self::$attr;
	}
}

