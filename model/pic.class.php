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
			'access'  => 'rw',
		),
		'nick' => array(
			'access'  => 'r',
		),
		'path' => array(
			'access'  => 'rw',
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
			'access'  => 'rw',
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

	public function to_rss($pic_size = 'thumb') {
		return array(
			'title' => $this->original_url,
			'description' =>'
				<a href="'.base_url().'/'.trim($this->path, './').'"><img src="'.base_url().'/'.trim(($pic_size == 'thumb') ? $this->thumb : $this->path, './').'"></a><br>
				<div>'.
				$this->nick.' posted @ '.$this->ctime.'</div><br>'.
				(trim($this->comment) ? '<div class="comment">comment: '.$this->comment.'</div><br>' : '').'
				',
			'link' => base_url().'/show.php?id='.$this->id,
			'guid' => $this->checksum,
			'pubDate' => $this->ctime,
		);
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
		unlink(realpath($this->path));

		$dbcnx = ORM::get_dbcnx();
		$q     = 'update '.$this->table.' set deleted = 1 where id = :'.$this->id_column;
		$stmt  = $dbcnx->prepare($q);
		$stmt->execute(array(':'.$this->id_column => $this->data['id']));
	}

	public function html_thumb() {
		return str_replace('//', '/', $this->thumb);
	}
}
