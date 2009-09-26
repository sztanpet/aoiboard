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
		foreach (array_keys($this->getAttr()) as $attr) {
			$this->data[$attr] = null;		
		}
	}

	public function match($params) {
		$match = true;

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

	public function __set($name, $value) {
		$attr = $this->getAttr();
		if (isset($attr[$name])) {
			if (in_array($attr[$name]['type'], array('wo', 'rw',))){
				if ($attr[$name]['write'] !== '') {
					call_user_func(array($this, $attr[$name]['write']), $value);
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
		$attr = $this->getAttr();
		if (isset($attr[$name])) {
			if (in_array($attr[$name]['type'], array('ro', 'rw',))){
				if ($attr[$name]['read'] !== '') {
					return call_user_func(array($this, $attr[$name]['read']), $value);
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
