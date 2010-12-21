<?php


class RssBuilder {

	private $items;
	private $feed;

	public function __construct($params){
		foreach (array('title', 'description', 'link',) as $v) {
			$this->feed[$v] = isset($params[$v]) ? $params[$v] : '';
		}
	}

	public function add_item($item) {
		$this->items[] = $item;

		if (!isset($this->feed['pubDate']) || strtotime($this->feed['pubDate'] < strtotime($item['pubDate']))) {
			$this->feed['pubDate'] = date('r', strtotime($item['pubDate']));
		}
	}

	public function build($out_path){
		if (!is_dir(dirname($out_path))) {
			mkdir(dirname($out_path), 0777, true);
		}
		$this->feed['lastBuildDate'] = date('r');

		$xml = '<?xml version="1.0" encoding="UTF-8" ?>
	<rss version="2.0">
		<channel>
			<title><![CDATA['.$this->feed['title'].']]></title>
			<description><![CDATA['.$this->feed['description'].']]></description>
			<link><![CDATA['.$this->feed['link'].']]></link>
			<lastBuildDate>'.$this->feed['lastBuildDate'].'</lastBuildDate>
			<pubDate>'.$this->feed['pubDate'].'</pubDate>

		'.join("\n\n", array_map(array(__CLASS__, 'to_item'), $this->items)).'
		</channel>
	</rss>';

		file_put_contents($out_path, $xml);
		chmod($out_path, 0777);
	}

	private static function to_item($data){
		return '
			<item>
				<title><![CDATA['.$data['title'].']]></title>
				<description><![CDATA['.$data['description'].']]></description>
				<link><![CDATA['.$data['link'].']]></link>
				<guid><![CDATA['.$data['guid'].']]></guid>
				<pubDate>'.date('r', strtotime($data['pubDate'])).'</pubDate>
			</item>';
	}
}

