<?php

class ItemIterator implements Iterator {
	private $data    = array();
	private $class   = '';
	private $index   = -1;
	private $current = null;
	private $count;

	public function __construct($data, $class) {
		$this->data  = $data;	
		$this->count = count($data);
		if (!class_exists($class)) {
			throw Exception('invalid class given: '.$class);
		}
		$this->class = $class;
	}

	public function current() {
		return $this->current;
	}

	public function key() {
		return $this->index;
	}

	public function rewind() {
		$this->index = -1;
		$this->next();
	}

	public function next() {
		++$this->index;
		if ($this->valid()) {
			try {
				$this->current = new $this->class($this->data[$this->index]);
			} catch (Exception $e) {
				$this->next();
			}
		}
	}

	public function valid() {
		return (bool)($this->index < $this->count);
	}

	public function count() {
		return $this->count;
	}
	
	public function reverse() {
		$this->data = array_reverse($this->data);
	}
}
