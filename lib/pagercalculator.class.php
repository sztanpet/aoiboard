<?php

class PagerCalculator {

	private $item_count;
	private $page;
	private $limit;

	private $item_count_on_last_page;
	private $maxpage;
	private $offset;

	public function __construct($item_count, $limit) {
		$this->item_count = $item_count;
		$this->limit = $limit;
	}

	public function calculate($page) {
		$this->maxpage = self::calculate_maxpage($this->item_count, $this->limit);

		$this->item_count_on_last_page = self::calculate_item_count_on_last_page($this->item_count, $this->limit);
		if ($this->item_count_on_last_page === 0) {
			$this->maxpage -= 1;
		}

		$page = $page === null ? $this->maxpage : $page;
		$this->offset = max((($this->maxpage - $page) * $this->limit) + (($this->item_count_on_last_page != 0 ? $this->item_count_on_last_page : $this->limit) - $this->limit),0);
		if ($this->maxpage === $page && $this->item_count_on_last_page != 0) {
			$this->limit = $this->item_count_on_last_page;
		}

		return $page;
	}

	public static function calculate_maxpage($item_count, $limit) {
		$reminder = $item_count % $limit;
		if ($reminder === 0) {
			return (int)max(floor($item_count / $limit) - 1, 0);
		} else {
			return (int)max(floor($item_count / $limit), 0);
		}
	}

	public static function calculate_item_count_on_last_page($item_count, $limit) {
		$fullpage_count = floor($item_count / $limit);
		$item_count_on_full_pages = $fullpage_count * $limit;
		return $item_count_on_last_page = $item_count - $item_count_on_full_pages;
	}

	public function get_item_count() {
		return $this->item_count;
	}
	public function get_limit() {
		return $this->limit;
	}
	public function get_item_count_on_last_page() {
		return $this->item_count_on_last_page;
	}
	public function get_maxpage() {
		return $this->maxpage;
	}
	public function get_offset() {
		return $this->offset;
	}
}
