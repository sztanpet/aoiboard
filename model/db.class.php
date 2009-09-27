<?php
include('./lib/constants.php');
include_once('./model/item.class.php');

class DB{
	private $db   = '';
	private $list = array();
	private $fd   = null;
	private $item_class = '';
	
	public function __construct($params) {
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

		$this->item_class = $params['item_class'];
		$this->db         = $params['db'];
	}

	public function load() {
		$lines = file($this->db, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			try {
				$this->list[] = call_user_func(array($this->item_class, 'fromCSV'), explode(";", $line));
			} catch (Exception $e) {} // skip missing files
		}
		return true;
	}

	public function lock($path = '') {
		if ($path === '' && $this->fd !== null) {
			return true;
		}
		if ($path !== '' && $this->fd !== null) {
			$this->unlock();
		}
		$path = ($path === '') ? $this->db : $path;
		$this->fd = fopen($path, 'r');
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
		$tmp_name = TMP_PATH.$_SERVER['REQUEST_TIME'].rand(0, 100).'.csv'; 
		$lines = array();
		foreach ($this->list as $item) {
			$lines[] = $item->toCSV();
		}
		file_put_contents($tmp_name, $lines);
		$is_locked = $this->fd === null ? true : false;
		if ($is_locked) {
			$this->unlock();
			$this->lock($tmp_name);
		}
		rename($tmp_name, $this->db);
		chmod($this->db, 0664);
	}
	
	public function get($params, $limit = null, $offset = null) {
		$list = $this->list;
		$re   = array();

		if (empty($params)) {
			$re = $this->list;
		} else {
			foreach ($this->list as $item) {
				if ($item->match($params)) {
					$re[] = $item;
				}
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
		return $this->check_has($item) === false ? true : false;
	}
}
