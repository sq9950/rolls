<?php
/**
 *
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016-7-18 14:55:29
 */

namespace Model\Auth;
class NodeGroupModel extends \Model\ExtendModel{

    public function __construct(){
        $this->db_conf_name = 'cp';
        $this->table_name = 'rbac_node_group';
        parent::__construct();
    }

    public function getAutoDevFields(){
        return array (
  0 => 
  array (
    'name' => 'id',
    'type' => 'int unsigned',
    'form_type' => 'number',
    'title' => '自增ID',
    'extra' => 'auto_increment',
    'key' => 'PRI',
    'null' => 'NO',
    'default' => NULL,
  ),
  1 => 
  array (
    'name' => 'name',
    'type' => 'varchar',
    'form_type' => 'string',
    'title' => '左侧菜单分组名',
    'extra' => '',
    'key' => 'MUL',
    'null' => 'NO',
    'default' => NULL,
  ),
  2 => 
  array (
    'name' => 'status',
    'type' => 'tinyint',
    'form_type' => 'select',
    'title' => '状态',
    'extra' => '',
    'key' => 'MUL',
    'null' => 'YES',
    'default' => '0',
  ),
  3 => 
  array (
    'name' => 'remark',
    'type' => 'varchar',
    'form_type' => 'string',
    'title' => '备注',
    'extra' => '',
    'key' => '',
    'null' => 'YES',
    'default' => NULL,
  ),
  4 => 
  array (
    'name' => 'sort',
    'type' => 'smallint unsigned',
    'form_type' => 'number',
    'title' => '排序',
    'extra' => '',
    'key' => '',
    'null' => 'YES',
    'default' => '9999',
  ),
  5 => 
  array (
    'name' => 'display',
    'type' => 'tinyint',
    'form_type' => 'select',
    'title' => '是否显示',
    'extra' => '',
    'key' => '',
    'null' => 'NO',
    'default' => '1',
  ),
  6 => 
  array (
    'name' => 'created_at',
    'type' => 'datetime',
    'form_type' => 'date',
    'title' => '新增时间',
    'extra' => '',
    'key' => '',
    'null' => 'YES',
    'default' => '0000-00-00 00:00:00',
  ),
  7 => 
  array (
    'name' => 'updated_at',
    'type' => 'timestamp',
    'form_type' => 'date',
    'title' => '更新时间',
    'extra' => 'on update CURRENT_TIMESTAMP',
    'key' => '',
    'null' => 'YES',
    'default' => NULL,
  ),
);
    }

}

