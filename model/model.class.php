<?php
abstract class Model {

	protected $data         = array();
	private static $dsn     = '';

	protected $id_column    = '';
	protected $table        = '';
	protected $attr         = array();
	protected $errors       = array();

	public abstract function validate();

	public function is_valid() {
		return $this->valid();
	}

	public function errors() {
		return $this->errors;
	}

	public function set_error($attr, $value) {
		$this->errors[$attr] = $value;
		return true;
	}

	/*
	 * lack of static binding workaround
	 */
	public function get_attr() {
		return $this->attr;
	}

	public function get_table() {
		return $this->table;
	}

	public function get_id_column() {
		return $this->id_column;
	}
	/*
	 * end of workaround of lacking late static binding
	 */

	public function delete() {
		$dbcnx = ORM::get_dbcnx();
		$q     = 'delete from '.$this->table.' where '.$this->id_column.' = :id';
		$stmt  = $dbcnx->prepare($q);
		$stmt->execute(array(':'.$this->id_column => $this->data[$this->id_column]));
	}

	public function save($do_validate = true) {
		if ($do_validate && !$this->validate()) {
			return false;
		}

		$dbcnx = ORM::get_dbcnx();
		$columns = array_diff(array_keys($this->attr), array($this->id_column));
		foreach ($columns as $col) {
			$values[':'.$col] = $this->data[$col];
		}

		if ($this->data[$this->id_column] === null) {
			$q = 'insert into '.$this->table.' ('.join(', ', $columns).') VALUES ('.join(', ', array_keys($values)).')';
		} else {
			$values[':'.$this->id_column] = $this->data[$this->id_column];
			$q = 'update '.$this->table.' set ';
			foreach ($columns as $col) {
				$q .= $col.' = :'.$col.',';
			}
			$q = substr($q, 0, -1).' where '.$this->id_column.' = :'.$this->id_column;
		}
		$stmt = $dbcnx->prepare($q);
		$stmt->execute($values);
		return true;
	}

	public function __construct($params = array()) {
		foreach ($this->attr as $name => $attr) {
			if (isset($attr['default'])) {
				if (is_callable($attr['default'])) {
					$this->data[$name] = call_user_func($attr['default']);
				} else {
					$this->data[$name] = $attr['default'];
				}
			} else {
				$this->data[$name] = null;
			}
		}
		if (is_array($params)) {
			$this->data = array_merge($this->data, $params);
		}
	}

	public function __set($name, $value) {
		if (isset($this->attr[$name])) {
			if ($this->attr[$name]['access'] === 'w' || $this->attr[$name]['access'] === 'rw'){
				if (isset($this->attr[$name]['write']) && $this->attr[$name]['write'] !== '') {
					$this->data[$name] = call_user_func($this->attr[$name]['write'], $value);
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
		if (isset($this->attr[$name])) {
			if ($this->attr[$name]['access'] === 'r' || $this->attr[$name]['access'] === 'rw'){
				if (isset($this->attr[$name]['read']) && $this->attr[$name]['read'] !== '') {
					return call_user_func(array($this, $this->attr[$name]['read']));
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
