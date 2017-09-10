<?php

class Topic {
	private $filename = '';
	private $description = '';
	private $parent = '';
	private $keywords = array();
	
	private static $list_topics = array();
	
	public function inizalise() {
		require_once( str_replace ( '\\', '/', dirname(__FILE__)) . '/topics.list.php' );
	}
	
	
	function __construct($filename, $description, $parent, $keywords=array()) {
		$this->filename = $filename;
		$this->description = $description;
		$this->parent = $parent;
		$this->keywords = $keywords;
	} // class.Topic.func.__construct
	
	
	public static function get_all_topics() {
		Topic::inizalise();
		return Topic::$list_topics;
	}
	
	
	public function get_filename() {
		return $this->filename;
	}
	
	
	public function get_description() {
		return $this->description;
	}
	
	
	public function get_parent() {
		return $this->parent;
	}
	
	
	public function get_keywords() {
		return $this->keywords;
	}
	
} // class.Topic

?>