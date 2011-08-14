<?php

class ORM {

	public static $debug = false;

	private static $models = array();
	private static $dbcnx  = null;

	private static function get_model_info($model) {
		if (!isset(self::$models[$model])) {
			// no late static binding made me do this
			$tmp_model = new $model;

			self::$models[$model] = array(
				$tmp_model->get_attr(),
				$tmp_model->get_table(),
				$tmp_model->get_id_column(),
			);

			unset($tmp_model);

		}
		return self::$models[$model];
	}

	public static function all($model, $params = array(), $order = '', $limit = '') {
		list($attr, $table, $id_column) = self::get_model_info($model);

		return self::get($attr, $model, $table, $id_column, $params, $order, $limit);
	}

	public static function count($model, $params = array(), $order = '') {
		list($attr, $table, $id_column) = self::get_model_info($model);

		return self::get_count($attr, $table, $id_column, $params, $order);
	}

	public static function first($model, $params = array(), $order = '') {
		list($attr, $table, $id_column) = self::get_model_info($model);

		$dbre = self::get($attr, $model, $table, $id_column, $params, $order, '1');
		$dbre->rewind();
		return $dbre->current();
	}


	public static function set_dbcnx($dbcnx) {
		self::$dbcnx = $dbcnx;
	}

	public static function get_dbcnx() {
		return self::$dbcnx;
	}


	private static function get($attr, $table, $class, $id_column, $params, $order = '', $limit = '') {
		$q_params = array();
		$q = 'select '.join(',', array_keys($attr)).' from '.$table;

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

		if (self::$debug) {
			var_dump($q, $q_params);
		}
		$stmt = self::$dbcnx->prepare($q);
		$stmt->execute($q_params);

		return new ModelIterator($stmt->fetchAll(PDO::FETCH_ASSOC), $class);
	}

	public static function get_count($attr, $table, $id_column, $params) {
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
}
