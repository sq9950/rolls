<?php
/**
 * Desc: 左侧菜单服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2017-3-22 16:43:37
 */

namespace Service\Common;
use Model\Auth\NodeGroupModel;

class SlideService extends \Service\Service{

    private $nodeGroupModel;

    public function __construct(){
        parent::__construct();
        $this->initModels();
    }


    public function initModels(){
        $this->nodeGroupModel = new NodeGroupModel();
    }

    public function getNodeGroupList(){
        $where = [];
        $node_group = [];
        $list = $this->nodeGroupModel->getListByWhere($where);
        foreach($list as $key => $value){
            $node_group[$value['id']] = $value;
        }
        return $node_group;
    }

}