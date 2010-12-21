<?php
include_once(APPROOT.'/model/model.class.php');
include_once(APPROOT.'/model/orm.class.php');
include_once(APPROOT.'/model/modeliterator.class.php');

class Link extends Model {

	protected $table     = 'link';
	protected $id_column = 'id';

	protected $attr = array(
		'id' => array(
			'access'  => 'r',
		),
		'nick' => array(
			'access'  => 'r',
		),
		'ctime' => array(
			'access'  => 'r',
			'default' => array('Link', 'default_ctime'),
		),
		'title' => array(
			'access'  => 'r',
		),
		'url' => array(
			'access'  => 'r',
		),
	);

	public function validate() {
		if ($this->data['nick'] == '') {
			$this->errors['nick'] = 'no nick given';
		}

		if ($this->data['url'] == '') {
			$this->errors['url'] = 'no url given';
		}

		if ($this->data['title'] == '') {
			$this->errors['title'] = 'no title given';
		}

		if (!preg_match('/^\d{4}-\d\d-\d\d \d\d:\d\d:\d\d$/', $this->data['ctime'])) {
			$this->errors['ctime'] = 'no ctime given';
		}

		if (!empty($this->errors)) {
			return false;
		}
		return true;
	}

	public function to_rss() {
		return array(
			'title' => htmlspecialchars($this->title ? $this->title : $this->url, ENT_NOQUOTES, 'UTF-8'),
			'description' => htmlspecialchars($this->nick.' posted '.($this->title ? $this->title : $this->url).' at '.$this->ctime, ENT_NOQUOTES, 'UTF-8'),
			'link' => $this->url,
			'guid' => md5($this->url),
			'pubDate' => $this->ctime,
		);
	}

	protected static function default_ctime() {
		return date('Y-m-d H:m:s');
	}

}
