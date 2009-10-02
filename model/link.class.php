<?php 
include_once(APPROOT.'/model/item.class.php');
include_once(APPROOT.'/model/itemiterator.class.php');

class Link extends Item {
	
	const TABLE_NAME = 'link';
	const ID_COLUMN  = 'id';

	protected static $attr = array(
		'id' => array(
			'type'  => 'r',
			'read'  => '',
			'write' => '',
		),
		'nick' => array(
			'type'  => 'r',
			'read'  => '',
			'write' => '',
		),
		'ctime' => array(
			'type'  => 'r',
			'read'  => '',
			'write' => '',
		),
		'title' => array(
			'type'  => 'r',
			'read'  => '',
			'write' => '',
		),
		'url' => array(
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
			$this->load();
		} else {

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

			if (isset($params['ctime']) && preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $params['ctime'])) {
				$this->data['ctime'] = $params['ctime'];
			} else {
				$errors['ctime'] = 'no ctime given';
			}

			if (!empty($errors)) {
				throw new Exception(var_export($errors, true));
			}
		}
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
		return parent::get(self::TABLE_NAME, 'Link', self::$attr, self::ID_COLUMN, $params, $order, $limit);
	}
	
	public static function get_count($params = array()) {
		return parent::get_count(self::TABLE_NAME, 'Link', self::$attr, self::ID_COLUMN, $params);
	}
}
