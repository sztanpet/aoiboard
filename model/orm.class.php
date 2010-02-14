<?php

class ORM {
	private static $models = array();

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

		return call_user_func(array($model, 'get'), $attr, $model, $table, $id_column, $params, $order, $limit);
	}

	public static function count($model, $params = array(), $order = '') {
		list($attr, $table, $id_column) = self::get_model_info($model);

		return call_user_func(array($model, 'get_count'), $attr, $model, $table, $id_column, $params, $order);
	}

	public function first($model, $params = array(), $order = '') {
		list($attr, $table, $id_column) = self::get_model_info($model);

		$dbre = call_user_func(array($model, 'get'), $attr, $model, $table, $id_column, $params, $order, '1');
		$dbre->rewind();
		return $dbre->current();
	}
}

