<?php
include('constants.php');
class DB{
	private $db   = '';
	private $list = array();
	private $fd   = null;
	private $item_class = '';
	
	public function __construct($params) {
		$params = array_merge(array('db' => DB_PATH), $params);
		if (!isset($params['db'])) {
			throw new Exception('give "db" param as a valid database file');
		}
		
		if (!is_file($params['db'])) {
			file_put_contents($params['db'], '');
			chmod($params['db'], 0664);
		}
		
		if (!is_readable($params['db']) || !is_writeable($params['db'])) {
			throw new Exception('set both read and write premissions on: '.$params['db']);
		}

		if (!isset($params['item_class']) || !class_exists($params['item_class'])) {
			throw new Exception('set item_class to valid class name, given: '.$params['item_class']);
		}

		
		$this->db = $params['db'];

		$this->load($this->db);
	}

	private function load($db) {
		$lines = file($this->db, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		$tmp   = array();
		foreach ($lines as $line) {
			$tmp[] = explode(";", $line);
		}
		$this->list = array_map(array('Item', 'fromCSV'), $tmp);
	}

	public function lock() {
		if ($this->fd !== null) {
			return true;
		}
		$this->fd = fopen($this->db, 'r');
		flock($this->fd, LOCK_EX);
		return true;
	}
	
	public function unlock() {
		if ($this->fd === null) {
			return true;
		}
		flock($this->fd, LOCK_UN);
		$this->fd = null;
		return true;
	}

	public function count() {
		return count($this->list);
	}

	public function save() {
		$tmp_name = TMP_PATH.$_SERVER['REQUEST_TIME'].'.csv'; 
		$lines = array();
		foreach ($this->list as $item) {
			$lines[] = $item->toCSV();
		}
		file_put_contents($tmp_name, $lines);
		$is_locked = is_resource($this->fd) ? true : false;
		if ($is_locked) {
			$this->unlock();
		}
		rename($tmp_name, $this->db);
		chmod($this->db, 0664);
		if ($is_locked) {
			$this->lock();
		}
	}
	
	public function get($params, $limit = null, $offset = null) {
		$list = $this->list;
		$re   = array();

		foreach ($this->list as $item) {
			if ($item->match($params)) {
				$re[] = $item;
			}
		}

		if ($limit !== null && $offset !== null) {
			$re = array_slice($re, $offset, $limit);
		}

		return $re;
	}

	public function check_has($item) {
		foreach ($this->list as $k => $v) {
			if ($item->match($v)) {
				return $k;
			}
		}
		return false;
	}

	public function add($item) {
		$this->list[] = $item;
	}

	public function delete($item) {
		if ($key = $this->check_has($item)) {
			$this->list[$key]->delete();
			unset($this->list[$key]);
			$this->save();
			return true;
	
		}
		return false;
	}

	public function is_uniq($item) {
		return $this->check_has($item) ? false : true;
	}
}

class Item{
	
	const INDEX_CHECKSUM     = 0;
	const INDEX_TIME         = 1;
	const INDEX_NICK         = 2;
	const INDEX_PATH         = 3;
	const INDEX_THUMB        = 4;
	const INDEX_ORIGINAL_URL = 5;
	const INDEX_COMMENT      = 6;

	private static $attr = array(
		'time' => array(
			'type' => 'ro', 
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

	private $data = array();

	public function __construct($params) {
		foreach (array_keys(self::$attr) as $attr) {
			$this->data[$attr] = null;		
		}

		$errors = array();
		if (isset($params['nick'])) {
			$this->data['nick'] = $params['nick'];
		} else {
			$errors['nick'] = 'no nick given';
		}
		
		if (isset($params['original_url'])) {
			$this->data['original_url'] = $params['original_url'];
		} else {
			$errors['original_url'] = 'no original_url given';
		}

		if (isset($params['comment'])) {
			$this->data['comment'] = $params['comment'];
		} else {
			$errors['comment'] = 'no comment given';
		}
		
		if (isset($params['time'])) {
			$this->data['time'] = $params['time'];
		} else {
			$errors['time'] = 'no time given';
		}
		 
		if (isset($params['path'])) {
			if (is_file($params['path'])) {
				$this->data['path'] = $params['path'];
			} else {
				$errors['path'] = 'no suck file path: '.$params['path'];
			}
		} else {
			$errors['path'] = 'no path given';
		}

		if (isset($params['thumb'])) {
			if (is_file($params['thumb'])) {
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
		return new Item(array(
			'checksum'     => $csv_array[Item::INDEX_CHECKSUM],
			'time'         => $csv_array[Item::INDEX_TIME],
			'nick'         => $csv_array[Item::INDEX_NICK],
			'path'         => $csv_array[Item::INDEX_PATH],
			'thumb'        => $csv_array[Item::INDEX_THUMB],
			'comment'      => $csv_array[Item::INDEX_COMMENT],
			'original_url' => $csv_array[Item::INDEX_ORIGINAL_URL],
		));
	}

	public function toCSVarray() {
		$re = array (
			Item::INDEX_CHECKSUM     => $this->data['checksum'],
			Item::INDEX_TIME         => $this->data['time'],
			Item::INDEX_NICK         => str_replace(array(';', "\n", "\r"), '', $this->data['nick']),
			Item::INDEX_PATH         => str_replace(array(';', "\n", "\r"), '', $this->data['path']),
			Item::INDEX_THUMB        => str_replace(array(';', "\n", "\r"), '', $this->data['thumb']),
			Item::INDEX_ORIGINAL_URL => str_replace(array(';', "\n", "\r"), '', $this->data['original_url']),
			Item::INDEX_COMMENT      => str_replace(array(';', "\n", "\r"), '', $this->data['comment']),
		);
		ksort($re);
		return $re;
	}

	public function toCSV() {
		return join(';', $this->toCSVarray())."\r\n";
	}

	public function delete() {
		unlink($this->data['path']);
		unlink($this->data['thumb']);
	}

	public function match($params) {
		$match = true;

		if ($params instanceof Item) {
			return $params->checksum === $this->data['checksum'] ? true : false; 
		}

		foreach ($this->data as $k => $v) {
			if (isset($params[$k])) {
				$match = $params[$k] === $this->data[$k] ? true : false;
				if (!$match) {
					return false;
				}
			}
		}
		return true;

	}

	public function __set($name, $value){
		if (isset(self::$attr[$name])) {
			if (in_array(self::$attr[$name]['type'], array('wo', 'rw',))){
				if (self::$attr[$name]['write'] !== '') {
					call_user_func(array($this, self::$attr[$name]['write']), $value);
				} else {
					$this->data[$name] = $value;
				}
			} else {
				throw new Exception('not writeable (wo or rw) attr : '.$name);
			}
		} else {
			throw new Exception('no such attr: '.$name);
		}
	}
	
	public function __get($name){
		if (isset(self::$attr[$name])) {
			if (in_array(self::$attr[$name]['type'], array('ro', 'rw',))){
				if (self::$attr[$name]['read'] !== '') {
					return call_user_func(array($this, self::$attr[$name]['read']), $value);
				} else {
					return $this->data[$name];
				}
			} else {
				throw new Exception('no such readable (ro or rw) attr : '.$name);
			}
		} else {
			throw new Exception('no such attr: '.$name);
		}
	}
}
