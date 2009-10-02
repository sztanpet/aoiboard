<?php
include_once(APPROOT.'/model/item.class.php');
include_once(APPROOT.'/model/itemiterator.class.php');

class Pic extends Item {

	const TABLE_NAME = 'pic';
	const ID_COLUMN  = 'id';

	protected static $attr = array(
		'id' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'ctime' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'original_url' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'thumb' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'nick' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'path' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'comment' => array(
			'type'  => 'r', 
			'read'  => '', 
			'write' => '',
		),
		'checksum' => array(
			'type'  => 'r',
			'read'  => '',
			'write' => '',
		),
		'deleted' => array(
			'type'  => 'r',
			'read'  => '',
			'write' => '',
		),
	);

	public function __construct($params) {

		parent::__construct($params);

		$errors = array();
		$params = array_map('trim', $params);

		if (isset($params['id'])) {
			$this->data['id'] = (int)$params['id'];
		}
		if (count(array_diff(array_keys($params), array('id'))) == 0) {
			$this->load();
		} else {

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

			if (isset($params['ctime']) && preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $params['ctime'])) {
				$this->data['ctime'] = $params['ctime'];
			} else {
				$errors['ctime'] = 'no ctime given';
			}
			
			if (isset($params['deleted'])) {
				$this->data['deleted'] = $params['deleted'] === 'N' ? false : (bool)$params['deleted'];
			} else {
				$this->data['deleted'] = false;
			}

			if (!$this->data['deleted']) {
				if (isset($params['path'])) {
					if (is_file($params['path']) && is_readable($params['path'])) {
						$this->data['path'] = $params['path'];
					} else {
						$errors['path'] = 'no suck file path: '.$params['path'];
					}
				} else {
					$errors['path'] = 'no path given';
				}
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
	}

	public function delete() {
		unlink($this->path);

		$dbcnx = parent::dbcnx();
		$q    = 'update '.self::TABLE_NAME.' set deleted = 1 where id = :id';
		$stmt = $dbcnx->prepare($q);
		$stmt->execute(array(':id' => $this->data['id']));
	}

	protected function getAttr() {
		return self::$attr;
	}

	protected function getId() {
		return self::ID_COLUMN;
	}

	protected function getTable() {
		return self::TABLE_NAME;
	}

	public static function get($params = array(), $order = '', $limit = '') {
		return parent::get(self::TABLE_NAME, 'Pic', self::$attr, self::ID_COLUMN, $params, $order, $limit);
	}
	
	public static function get_count($params = array()) {
		return parent::get_count(self::TABLE_NAME, 'Pic', self::$attr, self::ID_COLUMN, $params);
	}
}
