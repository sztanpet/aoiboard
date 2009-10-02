<?php
abstract class Item {

	protected $data         = array();
	protected static $dbcnx = null;
	private $id_column      = '';
	protected static $table = '';

	protected static $attr = array();
	abstract protected function getAttr();
	abstract protected function getId();

	public function delete() {
		$dbcnx = self::dbcnx();

		$q    = 'delete from '.self::$table.' where '.$this->id_column.' = :id';
		$stmt = $dbcnx->prepare($q);
		$stmt->execute(array(':id' => $this->data[$this->id_column]));
	}

	protected function load() {
		$dbcnx = self::dbcnx();

		$q = 'select * from '.self::$table.' where '.$this->id_column.' = :id';
		$stmt = $dbcnx->prepare($q);
		$stmt->execute(array(':id' => $this->data[$this->id_column]));
		$this->data = $stmt->fetch(PDO::FETCH_ASSOC);
	}

	protected static function dbcnx(){
		if (self::$dbcnx === null) {
			self::$dbcnx = new PDO(DB_DSN);
		}
		return self::$dbcnx;
	}
	
	public function save() {
		$dbcnx = self::dbcnx();

		$columns = array_diff(array_keys(self::$attr), array($this->id_column));
		foreach ($columns as $col) {
			$values[':'.$col] = $this->data[$col];
		}
		
		if ($this->data[$id_column] === null) {
			$q = 'insert into '.self::$table.' ('.join(', ', $columns).') VALUES ('.join(', ', array_keys($values)).')';
		} else {
			$values[':id'] = $this->data[$this->id_column];
			$q = 'update '.self::$table.' set ';
			foreach ($columns as $col) {
				$q .= $col.' = '.$values[':'.$col].',';
			}
			$q = substr($q, 0, -1).' where '.$this->id_column.' = :id';
		}
		$stmt = $dbcnx->prepare($q);
		$stmt->execute($values);
	}	

	protected static function get_count($table, $class, $attr, $id_column, $params) {
		$data      = array();
		$dbcnx     = self::dbcnx();

		$q_params = array(); 
		$q = 'select count('.$id_column.') count from '.$table;
		
		if (!empty($params)) {
			list($where, $q_params) = self::build_where($attr, $params);
			if ($where != '') {
				$q .= ' where '.$where;
			}
		}

		$stmt = $dbcnx->prepare($q);
		$stmt->execute($q_params);
		return reset(array_values($stmt->fetch(PDO::FETCH_ASSOC)));
	}

	protected static function get($table, $class, $attr, $id_column, $params, $order = '', $limit = '') {
		$data      = array();
		$dbcnx     = self::dbcnx();

		$q_params = array(); 
		$q = "select * from ".$table;
		if (!empty($params)) {
			list($where, $q_params) = self::build_where($attr, $params);
			if ($where != '') {
				$q .= ' where '.$where;
			}
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

		$stmt = $dbcnx->prepare($q);
		$stmt->execute($q_params);
		
		return new ItemIterator($stmt->fetchAll(PDO::FETCH_ASSOC), $class);
	}

	private static function build_where($attr, $params) {
		$q = '';
		$where_parts  = array();
		foreach (array_intersect(array_keys($params), array_keys($attr)) as $attr) {
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
					$i = 0;
					$part .= '(';
					foreach ($params[$attr]['value'] as $value) {
						$part .= ':'.$attr.'_'.$i.',';	
						$q_params[':'.$attr.'_'.$i] = $value;
						++$i;	
					}
					$part = substr($part, 0, -1).')';
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

		return array($q, $q_params);
	}

	public function __construct($params) {
		self::$attr  = $this->getAttr();
		self::$table = $this->getTable();
		$this->data  = array_fill_keys(array_keys(self::$attr), null);
		$this->id_column = $this->getId();
	}

	public function __set($name, $value) {
		if (isset(self::$attr[$name])) {
			if (in_array(self::$attr[$name]['type'], array('w', 'rw',))){
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
			if (in_array(self::$attr[$name]['type'], array('r', 'rw',))){
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
