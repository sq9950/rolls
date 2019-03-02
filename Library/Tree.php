<?php
/**
 * https://github.com/phunkei/php_mysql_tree
 */
class node {
	
	public $name;
	public $id;
	public $parent;
	public $children;
	
	
	public function __construct($parent, $id, $name) {
		$this->parent = $parent;
		$this->id = $id;
		$this->name = $name;
		$this->children = array();
	}
	
	public function addChild($node) {
		$this->children[] = $node;
	}
	
	public function deleteChild($i) {
		if(exists($this->children[$i])) {
			unset($this->children[$i]);
		}
	}
}

class Tree {
	
	public $topnode;
	private $dataSet;
	
	public function __construct($data, $field = array('parent' => 'parent_id', 'id' => 'id', 'name'=>'desc')) {
		$this->topnode = new node(null, 0, "topnode");
		$this->dataSet = $data;
		$this->createTree($this->topnode, $field);
	}
	
	private function createTree($node, $field) {
		foreach($this->dataSet as $key => $value) {
			if($value[$field['parent']] == $node->id) {
				$node->addChild(new node($value[$field['parent']], $value[$field['id']], $value[$field['name']]));
				unset($this->dataSet[$key]);
			}
		}
		foreach($node->children as &$c) {
			$this->createTree($c);
		}
	}
	
	public function getNodeById($id, $node = null) {
		$node = $node == null ? $this->topnode : $node;
		if($node->id == $id) {
			return $node;
		}
		else {
			foreach($node->children as $c) {
				$res = &$this->getNodeById($id, $c);
				if($res != null) {
					return $res;
				}
			}
		}
	}
	
	public function getTree() {
		return $this->topnode;
	}
}