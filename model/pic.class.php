<?php
include_once(APPROOT.'/model/model.class.php');
include_once(APPROOT.'/model/orm.class.php');
include_once(APPROOT.'/model/modeliterator.class.php');

class Pic extends Model {

	protected $table     = 'pic';
	protected $id_column = 'id';

	protected $attr = array(
		'id' => array(
			'access'  => 'r', 
		),
		'ctime' => array(
			'access'  => 'r', 
			'default' => array('Pic', 'default_ctime'),
		),
		'original_url' => array(
			'access'  => 'r', 
		),
		'thumb' => array(
			'access'  => 'r', 
		),
		'nick' => array(
			'access'  => 'r', 
		),
		'path' => array(
			'access'  => 'r', 
		),
		'comment' => array(
			'access'  => 'r', 
			'default' => '',
		),
		'checksum' => array(
			'access'  => 'r',
			'read'    => 'get_checksum',
		),
		'deleted' => array(
			'access'  => 'r',
			'default' => false,
		),
	);

	public function validate() {
		if ($this->data['nick'] == '') {
			$this->errors['nick'] = 'no nick given';
		}

		if ($this->data['original_url'] == '') {
			$this->errors['original_url'] = 'no original_url given';
		}

		if (!$this->data['deleted']) {
			if (isset($this->data['path'])) {
				if (!is_file($this->data['path']) || !is_readable($this->data['path'])) {
					$this->errors['path'] = 'no such file path: '.$this->data['path'];
				}
			} else {
				$this->errors['path'] = 'no path given';
			}
		}

		if (isset($this->data['thumb'])) {
			if (!is_file($this->data['thumb']) || !is_readable($this->data['thumb'])) {
				$this->errors['thumb'] = 'no such file thumb: '.$this->data['thumb'];
			}
		} else {
			$this->errors['thumb'] = 'no thumb given';
		}
		
		if (!empty($errors)) {
			return false;
		}
		return true;
	}

	protected function get_checksum() {
		if (isset($this->data['path']) && !isset($this->data['checksum'])) {
			 $this->data['checksum'] = md5_file($this->data['path']);
		}

		return $this->data['checksum'];
	}

	protected static function default_ctime() {
		return date('Y-m-d H:m:s');
	}

	public function delete() {
		unlink($this->path);

		$dbcnx = parent::dbcnx();
		$q     = 'update '.$this->table.' set deleted = 1 where id = :id';
		$stmt  = $dbcnx->prepare($q);
		$stmt->execute(array(':'.$this->id_column => $this->data['id']));
	}
}
