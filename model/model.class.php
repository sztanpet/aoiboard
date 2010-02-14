<?php
abstract class Model {

	protected $data         = array();
	protected static $dbcnx = null;
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
		$q    = 'delete from '.$this->table.' where '.$this->id_column.' = :id';
		$stmt = self::$dbcnx->prepare($q);
		$stmt->execute(array(':'.$this->id_column => $this->data[$this->id_column]));
	}

	public static function set_dbcnx($dbcnx) {
		self::$dbcnx = $dbcnx;
	}
	
	public function save($do_validate = true) {
		if ($do_validate && !$this->validate()) {
			return false;
		}

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
		$stmt = self::$dbcnx->prepare($q);
		$stmt->execute($values);
		return true;
	}

	public static function get($attr, $table, $class, $id_column, $params, $order = '', $limit = '') {
		$q_params = array(); 
		$q = "select * from ".$table;
		
		list($where, $q_params) = self::build_where($attr, $id_column, $params);
		
		if ($where != '') {
			$q .= ' where '.$where;
		}

		if ($order !== '') {
			if (is_array($order) && in_array($order[0], array_keys($attr)) && in_array(strtolower($order[1]), array('asc', 'desc'))) {
				$q .= ' order by '.$order[0].' '.$order[1];
			} elseif (in_array($order, array_keys($attr))) {
				$q .= ' order by '.$order;
			}
		}

		if ($limit !== '') {
			if (is_array($limit) && count($limit) == 2) {
				$q .= ' limit '.(int)$limit[0].', '.(int)$limit[1];
			} else {
				$q .= ' limit '.(int)$limit;
			}
		}

		$stmt = self::$dbcnx->prepare($q);
		$stmt->execute($q_params);

		return new ModelIterator($stmt->fetchAll(PDO::FETCH_ASSOC), $class);
	}

	public static function get_count($attr, $table, $class, $id_column, $params) {
		$data      = array();
 
		$q_params = array(); 
		$q = 'select count('.$id_column.') count from '.$table;
 
		if (!empty($params)) {
			list($where, $q_params) = self::build_where($attr, $id_column, $params);
			if ($where != '') {
				$q .= ' where '.$where;
			}
		}
		
		$stmt = self::$dbcnx->prepare($q);
		$stmt->execute($q_params);
		return reset(array_values($stmt->fetch(PDO::FETCH_ASSOC)));
	}

	private static function build_where($attrs, $id_column, $params) {
		$q           = '';
		$q_params    = array();
		$where_parts = array();

		if (is_array($params)) {
			foreach (array_intersect(array_keys($params), array_keys($attrs)) as $attr) {
				if (is_array($params[$attr])) {
					$part = '';
					if (isset($params[$attr]['apply'])) {
						$part .= str_replace('@@'.$attr.'@@', $attr, $params[$attr]['apply']);
					} else {
						$part .= $attr;
					}

					if (is_array($params[$attr]['value'])) {
						$part .= ' in ';
					} elseif (isset($params[$attr]['cmp'])) {
						$part .= $params[$attr]['cmp'];
					} else {
						$part .= ' = ';
					}

					if (is_array($params[$attr]['value'])) {
						if (!empty($params[$attr]['value'])) {
							$i = 0;
							$part .= '(';
							foreach ($params[$attr]['value'] as $value) {
								$part .= ':'.$attr.'_'.$i.',';	
								$q_params[':'.$attr.'_'.$i] = $value;
								++$i;	
							}
							$part = substr($part, 0, -1).')';
						} else {
							$part = '()';
						}
					} else {
						$part .= ':'.$attr;
						$q_params[':'.$attr] = $params[$attr]['value'];
					}

					$where_parts[] = $part;					
				} else {
					$where_parts[] = $attr.' = :'.$attr;
					$q_params[':'.$attr] = $params[$attr];
				}
			}
			if (!empty($where_parts)) {
				$q = join(' and ', array_values($where_parts));
			}
		} elseif (is_numeric($params)) {
			$q        = $id_column.' = :id';
			$q_params = array('id' => $params);
		}

		return array($q, $q_params);
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
