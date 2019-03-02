<?php
/**
 * Desc: {{{dna_doc_title}}}
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2016-7-18 14:37:14
 */

namespace Service\Auth;

use Service\Common\AutoCommonService;
use Model\Auth\NodeGroupModel;

class NodeGroupService extends AutoCommonService {

    public function __construct() {
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
        $this->initListFieldMap();
        $this->initEditFieldMap();
        $this->initAddFieldMap();
    }

    public function initModels() {
        parent::initModels();
        $this->defaultModel = new NodeGroupModel();
    }

    /**
     * @node_name 配置新增页显示字段
     */
    public function initAddFieldMap() {
        $this->addFieldMap = array(
            0 =>
                array(
                    'field_name'  => 'id',
                    'field_type'  => 'int',
                    'field_title' => '自增ID',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'id',
                            'title' => '自增ID',
                            'type'  => 'hidden',
                        ),
                ),
            1 =>
                array(
                    'field_name'  => 'name',
                    'field_type'  => 'string',
                    'field_title' => '左侧菜单分组名',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'name',
                            'title' => '左侧菜单分组名',
                            'type'  => 'text',
                        ),
                ),
            2 =>
                array(
                    'field_name'  => 'status',
                    'field_type'  => 'select',
                    'field_title' => '状态',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'           => 'status',
                            'title'          => '状态',
                            'type'           => 'select',
                            'select_options' =>
                                array(
                                    0 =>
                                        array(
                                            'name'  => '禁用',
                                            'value' => '0',
                                        ),
                                    1 =>
                                        array(
                                            'name'  => '正常',
                                            'value' => '1',
                                        ),
                                ),
                        ),
                ),
            3 =>
                array(
                    'field_name'  => 'remark',
                    'field_type'  => 'string',
                    'field_title' => '备注',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'remark',
                            'title' => '备注',
                            'type'  => 'text',
                        ),
                ),
            4 =>
                array(
                    'field_name'  => 'sort',
                    'field_type'  => 'number',
                    'field_title' => '排序',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'sort',
                            'title' => '排序',
                            'type'  => 'text',
                        ),
                ),
            5 =>
                array(
                    'field_name'  => 'display',
                    'field_type'  => 'select',
                    'field_title' => '是否显示',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'           => 'display',
                            'title'          => '是否显示',
                            'type'           => 'select',
                            'select_options' =>
                                array(
                                    0 =>
                                        array(
                                            'name'  => '不显示',
                                            'value' => '0',
                                        ),
                                    1 =>
                                        array(
                                            'name'  => '显示（默认）',
                                            'value' => '1',
                                        ),
                                ),
                        ),
                ),
            6 =>
                array(
                    'field_name'  => 'created_at',
                    'field_type'  => 'datetime',
                    'field_title' => '新增时间',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'   => 'created_at',
                            'title'  => '新增时间',
                            'type'   => 'text',
                            'class'  => 'form-control lhgcalendar',
                            'output' => 'readonly',
                            'value'  => '0000-00-00 00:00:00',
                        ),
                ),
            7 =>
                array(
                    'field_name'  => 'updated_at',
                    'field_type'  => 'datetime',
                    'field_title' => '更新时间',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'   => 'updated_at',
                            'title'  => '更新时间',
                            'type'   => 'text',
                            'class'  => 'form-control lhgcalendar',
                            'output' => 'readonly',
                            'value'  => '2017-03-22 15:47:50',
                        ),
                ),
            8 =>
                array(
                    'field_name'  => '',
                    'field_type'  => '',
                    'field_title' => '操作',
                    'is_show'     => true,
                ),
        );
    }

    /**
     * @node_name 配置列表页显示字段
     */
    public function initListFieldMap() {
        $this->listFieldMap = array(
            0 =>
                array(
                    'field_name'  => 'id',
                    'field_type'  => 'int',
                    'field_title' => '自增ID',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'id',
                            'title'               => '自增ID',
                            'type'                => 'hidden',
                            'data-control-action' => 'filterEq',
                        ),
                ),
            1 =>
                array(
                    'field_name'  => 'name',
                    'field_type'  => 'string',
                    'field_title' => '左侧菜单分组名',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'name',
                            'title'               => '左侧菜单分组名',
                            'type'                => 'text',
                            'data-control-action' => 'filterEq',
                        ),
                ),
            2 =>
                array(
                    'field_name'  => 'status',
                    'field_type'  => 'select',
                    'field_title' => '状态',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'status',
                            'title'               => '状态',
                            'type'                => 'select',
                            'select_options'      =>
                                array(
                                    0 =>
                                        array(
                                            'name'  => '禁用',
                                            'value' => '0',
                                        ),
                                    1 =>
                                        array(
                                            'name'  => '正常',
                                            'value' => '1',
                                        ),
                                ),
                            'data-control-action' => 'filterEq',
                        ),
                ),
            3 =>
                array(
                    'field_name'  => 'remark',
                    'field_type'  => 'string',
                    'field_title' => '备注',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'remark',
                            'title'               => '备注',
                            'type'                => 'text',
                            'data-control-action' => 'filterEq',
                        ),
                ),
            4 =>
                array(
                    'field_name'  => 'sort',
                    'field_type'  => 'number',
                    'field_title' => '排序',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'sort',
                            'title'               => '排序',
                            'type'                => 'text',
                            'data-control-action' => 'filterEq',
                        ),
                ),
            5 =>
                array(
                    'field_name'  => 'display',
                    'field_type'  => 'select',
                    'field_title' => '是否显示',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'display',
                            'title'               => '是否显示',
                            'type'                => 'select',
                            'select_options'      =>
                                array(
                                    0 =>
                                        array(
                                            'name'  => '不显示',
                                            'value' => '0',
                                        ),
                                    1 =>
                                        array(
                                            'name'  => '显示（默认）',
                                            'value' => '1',
                                        ),
                                ),
                            'data-control-action' => 'filterEq',
                        ),
                ),
            6 =>
                array(
                    'field_name'  => 'created_at',
                    'field_type'  => 'datetime',
                    'field_title' => '新增时间',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'created_at',
                            'title'               => '新增时间',
                            'type'                => 'text',
                            'class'               => 'form-control lhgcalendar',
                            'output'              => 'readonly',
                            'value'               => '0000-00-00 00:00:00',
                            'data-control-action' => 'filterEq',
                        ),
                ),
            7 =>
                array(
                    'field_name'  => 'updated_at',
                    'field_type'  => 'datetime',
                    'field_title' => '更新时间',
                    'is_show'     => true,
                    'is_search'   => true,
                    'form_config' =>
                        array(
                            'name'                => 'updated_at',
                            'title'               => '更新时间',
                            'type'                => 'text',
                            'class'               => 'form-control lhgcalendar',
                            'output'              => 'readonly',
                            'value'               => '2017-03-22 15:47:50',
                            'data-control-action' => 'filterEq',
                        ),
                ),
            8 =>
                array(
                    'field_name'  => '操作',
                    'field_type'  => '',
                    'field_title' => '操作',
                    'is_show'     => true,
                ),
        );
    }

    /**
     * @node_name 配置编辑也显示字段
     */
    public function initEditFieldMap() {
        $this->editFieldMap = array(
            0 =>
                array(
                    'field_name'  => 'id',
                    'field_type'  => 'int',
                    'field_title' => '自增ID',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'id',
                            'title' => '自增ID',
                            'type'  => 'hidden',
                        ),
                ),
            1 =>
                array(
                    'field_name'  => 'name',
                    'field_type'  => 'string',
                    'field_title' => '左侧菜单分组名',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'name',
                            'title' => '左侧菜单分组名',
                            'type'  => 'text',
                        ),
                ),
            2 =>
                array(
                    'field_name'  => 'status',
                    'field_type'  => 'select',
                    'field_title' => '状态',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'           => 'status',
                            'title'          => '状态',
                            'type'           => 'select',
                            'select_options' =>
                                array(
                                    0 =>
                                        array(
                                            'name'  => '禁用',
                                            'value' => '0',
                                        ),
                                    1 =>
                                        array(
                                            'name'  => '正常',
                                            'value' => '1',
                                        ),
                                ),
                        ),
                ),
            3 =>
                array(
                    'field_name'  => 'remark',
                    'field_type'  => 'string',
                    'field_title' => '备注',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'remark',
                            'title' => '备注',
                            'type'  => 'text',
                        ),
                ),
            4 =>
                array(
                    'field_name'  => 'sort',
                    'field_type'  => 'number',
                    'field_title' => '排序',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'  => 'sort',
                            'title' => '排序',
                            'type'  => 'text',
                        ),
                ),
            5 =>
                array(
                    'field_name'  => 'display',
                    'field_type'  => 'select',
                    'field_title' => '是否显示',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'           => 'display',
                            'title'          => '是否显示',
                            'type'           => 'select',
                            'select_options' =>
                                array(
                                    0 =>
                                        array(
                                            'name'  => '不显示',
                                            'value' => '0',
                                        ),
                                    1 =>
                                        array(
                                            'name'  => '显示（默认）',
                                            'value' => '1',
                                        ),
                                ),
                        ),
                ),
            6 =>
                array(
                    'field_name'  => 'created_at',
                    'field_type'  => 'datetime',
                    'field_title' => '新增时间',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'   => 'created_at',
                            'title'  => '新增时间',
                            'type'   => 'text',
                            'class'  => 'form-control lhgcalendar',
                            'output' => 'readonly',
                            'value'  => '0000-00-00 00:00:00',
                        ),
                ),
            7 =>
                array(
                    'field_name'  => 'updated_at',
                    'field_type'  => 'datetime',
                    'field_title' => '更新时间',
                    'is_show'     => true,
                    'form_config' =>
                        array(
                            'name'   => 'updated_at',
                            'title'  => '更新时间',
                            'type'   => 'text',
                            'class'  => 'form-control lhgcalendar',
                            'output' => 'readonly',
                            'value'  => '2017-03-22 15:47:50',
                        ),
                ),
            8 =>
                array(
                    'field_name'  => '',
                    'field_type'  => '',
                    'field_title' => '操作',
                    'is_show'     => true,
                ),
        );
    }

    /**
     * @node_name 新增数据
     * @param array $params
     * @return array
     */
    public function addData($params = []) {

        $ret = parent::addData($params);

        return $ret;
    }

    /**
     * @node_name 编辑数据
     * @param array $params
     * @return array
     */
    public function editData($params = []) {

        $res = parent::editData($params);

        return $res;
    }

}
