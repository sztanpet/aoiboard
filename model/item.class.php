<?php
include('./lib/constants.php');

abstract class Item {

	protected $data;

	protected static $attr = array();
	abstract protected function getAttr();

	abstract public function delete();

	abstract public function toCSV();
	abstract public static function fromCSV($csv_array);

	public function __construct($params) {
		self::$attr = $this->getAttr();
		$this->data = array_fill_keys(array_keys(self::$attr), null);
	}

	public function match($params) {
		$match = true;

		foreach (array_keys(array_intersect_key($this->data, $params)) as $k) {
			$match = $params[$k] === $this->data[$k] ? true : false;
			if (!$match) {
				return false;
			}
		}
		return true;
	}

	public function __set($name, $value) {
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
	
	public function __get($name) {
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
