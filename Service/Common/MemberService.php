<?php
/**
 * Desc: 会员服务类
 * Created by PhpStorm.
 * User: 张鹏玄 | <zhangpengxuan@yundun.com>
 * Date: 2015/7/27
 */

namespace Service\Common;
class MemberService extends \Service\Service
{

    const DOMAIN_TYPE_NS = 'ns';
    const DOMAIN_TYPE_CNAME = 'cname';
    public $ret;

    public function __construct()
    {
        parent::__construct();
        $this->ret = array('status' => 0, 'info' => '操作失败');
        $this->initModels();
    }


    public function initModels()
    {
        $this->models                      = new \stdClass();
        $this->models->member              = new \Model\Member();
    }

    public function getMemberInfoById($id = 0)
    {
        $id = intval($id);
        if ($id) {
            $where['id'] = $id;
            $member_info = $this->models->member->getOneByWhere($where);
            $this->ret   = array(
                'status' => 1,
                'info' => "查询域名成功",
                'data' => $member_info
            );
        } else {
            $this->ret['info'] = '查询用户信息失败';
        }
        return $this->ret;
    }

    public function getLoginedMemberId()
    {
        return isset($_SESSION[$this->global_config['USER_AUTH_KEY']]['id']) ? $_SESSION[$this->global_config['USER_AUTH_KEY']]['id'] : null;
    }

    public function getLoginedMemberInfo()
    {
        return isset($_SESSION[$this->global_config['USER_AUTH_KEY']]) ? $_SESSION[$this->global_config['USER_AUTH_KEY']] : array();
    }

    /**
     * 判断用户是否是代理的下线
     * @param int $member_id
     * @return bool
     */
    public function isChildMember($member_id = 0)
    {
        $is_child = false;
        if ($member_id) {
            $where['id'] = intval($member_id);
            $pid         = $this->models->member->getOneFieldByWhere($where, 'pid');
            $pid && $is_child = true;
        }
        return $is_child;
    }

    /**
     * 判断用户是否是代理
     * @param int $member_id
     * @return bool
     */
    public function isAgentMember($member_id = 0)
    {
        $is_agent = false;
        if ($member_id) {
            $where['pid'] = intval($member_id);
            $count        = $this->models->member->getListCountByWhere($where);
            $count && $is_agent = true;
        }

        return $is_agent;
    }

    /**
     * 递归查询用户所属代理用户ID
     * @param int $member_id
     */
    public function getMemberAgentId($member_id = 0)
    {
        static $loop = 0;
        static $agent_list = array();
        if ($member_id && $loop <= 6) {
            $loop++;
            array_push($agent_list, $member_id);
            $pid = $this->models->member->getOneFieldByWhere(array('id' => $member_id), 'pid');
            $this->getMemberAgentId($pid);
        }
        return $agent_list;
    }

    /**
     * 查询代理用户的所有下线
     * @param int $agent_member_id
     * @return array
     */
    public function getAgentChildrenIdsList($agent_member_id = 0)
    {
        $children = array();
        if ($agent_member_id) {
            $where['pid'] = $agent_member_id;
            $children     = $this->models->member->getListByWhere($where);
        }
        return $children;
    }

    public function getMemberListByWhere($where = array(), $offset = 0, $limit = 500, $order = array(), $fields = array())
    {
        $count = $this->models->member->getListCountByWhere($where);
        $data  = $this->models->member->getListByWhere($where, $offset, $limit, $order);
        $this->unsetMemberPassword($data);
        $result = array(
            'count' => $count ? $count : 0,
            'data' => $count && !empty($data) ? $data : array(),
        );

        return $result;
    }

    public function getAgentMemberListByWhere($where = array(), $offset = 0, $limit = 500, $order = array(), $fields = array())
    {
        $agent_where['`pid`'] = $where['`pid`'];

        $res   = $this->models->member->selectBySql("select count(DISTINCT pid) as total from member where pid > 0");
        $count = isset($res[0]['total']) ? $res[0]['total'] : 0;
        $data  = array();
        if ($count) {
            $fields   = array('DISTINCT pid');
            $pid_list = $this->models->member->getListByWhere($agent_where, $offset, $limit, array('pid' => 'desc'), $fields);
            $pids     = array();
            foreach ($pid_list as $key => $val) {
                $pids[] = $val['pid'];
            }
            if (!empty($pids)) {
                $where['`id`'] = array('in', $pids);
                unset($where['`pid`']);
                $data = $this->models->member->getListByWhere($where, 0, 500, $order);
            }
        }

        $this->unsetMemberPassword($data);
        $result = array(
            'count' => $count,
            'data' => $count && !empty($data) ? $data : array(),
        );
        return $result;
    }

    public function unsetMemberPassword(&$members = array())
    {
        foreach ($members as $key => $val) {
            unset($members[$key]['password']);
        }
    }

    /**
     * 查询用户的代理关系
     * @param int $member_id
     * @return array
     */
    public function getMemberRelation($member_id = 0)
    {
        $agent_list = $this->getMemberAgentId($member_id);
        $agent_list = array_reverse($agent_list);
        $where      = array('id' => array('in', $agent_list));
        $list       = $this->models->member->getListByWhere($where);
        foreach ((array)$list as $key => $val) {
            $member_list[$val['id']] = $val;
        }
        foreach ((array)$agent_list as $key => $val) {
            $relation_member[] = $member_list[$val]['email'];
        }
        return $relation_member;
    }

    public function deleteMemberById($member_id = 0)
    {
        if ($member_id) {
            $where = array('pid' => $member_id);
            $count = $this->models->member->getListCountByWhere($where);
            if ($count) {
                $this->ret = array('status' => 0, 'info' => '用户有代理下线无法直接删除');
            } else {
                $where = array('id' => $member_id);
                $res   = $this->models->member->deleteByWhere($where);
                if ($res) {
                    $this->ret = array('status' => 1, 'info' => '删除用户成功');
                } else {
                    $this->ret = array('status' => 0, 'info' => '删除用户失败');
                }
            }
        } else {
            $this->ret = array('status' => 0, 'info' => '用户ID不能为空');
        }
        return $this->ret;
    }

    /**
     * 验证用户是否在代理关系层级树中（是否是代理或者下线）
     * @param array $member_ids
     * @return array
     */
    public function checkMemberIsInTree($member_ids = array())
    {
        $result = array();
        if (is_array($member_ids) && !empty($member_ids)) {
            foreach ($member_ids as $id) {
                $where['_string'] = " ( id = {$id} AND pid > 0 ) OR pid = {$id} ";
                $count            = $this->models->member->getListCountByWhere($where);
                $result[$id]      = $count ? true : false;
            }
        }
        return $result;
    }


    /**
     *验证用户是否有下线
     * @param array|int 用户pid
     * @return array
     */
    public function checkMemberHaveChildren($id)
    {
        if (is_array($id)) {
            $where = array("pid" => array("in", $id));
        } else {
            $where = array("pid" => $id);
        }

        $member_list = $this->models->member->getListByWhere($where);
        //处理成所需数据
        if ($member_list) {
            foreach ($member_list as $vo) {
                if (in_array($vo['pid'], $id)) {
                    $data[$vo['pid']] = $vo;
                } else {
                    $data[$vo['pid']] = array();
                }

            }
        } else {
            if (is_array($id)) {
                foreach ($id as $po) {
                    $data = array(
                        $po => array(),
                    );
                }
            } else {
                $data = array(
                    $id => array(),
                );
            }

        }

        return $data;

    }

    public function setMemberStatus($params = [])
    {
        if (!isset($params['remark']) || empty($params['remark'])) {
            return ['status' => 0, 'info' => '请设置操作备注！'];
        }
        if (!isset($params['id']) || empty($params['id'])) {
            return ['status' => 0, 'info' => '请指定用户！'];
        }
        $status = isset($params['status']) ? intval($params['status']) : 0;
        $status = $status == 1 ? 0 : 1;
        $data   = [
            'status' => $status,
        ];
        $res    = $this->models->member->updateById($params['id'], $data);
        if ($res) {
            $this->ret = [
                'status' => 1,
                'info' => $status == 1 ? '账户已恢复正常' : '账户已被禁用',
                'data' => [
                    'status' => $status,

                ]
            ];
        } else {
            $this->ret = ['status' => 0, 'info' => '操作失败'];
        }
        return $this->ret;
    }


//此方法用于csv导出
    public function getMemberList($params = array(), $no_page = false)
    {
        $result = array(
            'count' => 0,
            'data' => array(),
        );
        extract($params);
        empty($where) && $where = array();
        empty($offset) && $offset = 0;
        empty($limit) && $limit = 500;
        empty($order) && $order = array('id' => 'DESC');
        $total = $this->models->member->getListCountByWhere($where);

        if ($no_page) {
            $list = $this->models->member->getListByWhere($where, 0, 500, $order);
        } else {
            $list = $this->models->member->getListByWhere($where, $offset, $limit, $order);
        }
        foreach ((array)$list as $key => $val) {
            $list[$key]['status_desc'] = $this->global_config['DOMAIN_ALIAS_STATUS'][$val['status']];
        }
        $result['count'] = $total;
        $result['data']  = $list;
        return $result;
    }

    /**
     * @node_name 导出域名member列表格式化
     * @param array $data
     * @return array
     */
    public function formatExportData($data = [])
    {
        $formatted_data = [];
        if (is_array($data) && !empty($data)) {
            $export_fields = [
                'email' => '邮箱',
                'nickname' => '昵称',
                'mobile' => '手机号',
                'qq' => 'QQ',
                'addtime' => '注册日期',
                'status' => '状态',
                'baidu_source' => '来源',
                'baidu_search_word' => '搜索关键词',
                'pay_status' => '消费情况',
            ];
            array_push($formatted_data, array_values($export_fields));

            foreach ($data as $key => $one) {
                $need_data             = [
                    'email' => '',
                    'nickname' => '',
                    'mobile' => '',
                    'qq' => '',
                    'addtime' => '',
                    'status' => '',
                    'baidu_source' => '',
                    'baidu_search_word' => '',
                    'pay_status' => '',
                ];
                $need_data['email']    = $one['email'];
                $need_data['nickname'] = $one['nickname'];
                $need_data['mobile']   = $one['mobile'];
                $need_data['qq']       = $one['qq'];
                $need_data['addtime']  = $one['addtime'];
                switch ($one['status']) {
                    case '1':
                        $need_data['status'] = '正常';
                        break;
                    case '0':
                        $need_data['status'] = '禁用';
                        break;
                    case '2':
                        $need_data['status'] = '未激活';
                        break;

                }
                $need_data['baidu_source']      = $one['baidu_source'];
                $need_data['baidu_search_word'] = $one['baidu_search_word'];
                $need_data['pay_status'] = $one['pay_status'];

                foreach ($one['relaction'] as $k=>$v){
                    if(!empty($v['0']['roleName'])){
                        if(!in_array($v['0']['roleName'],$formatted_data['0'])){
                            $formatted_data['0'][] = $v['0']['roleName'];
                        }
                        $tmpVal = array_column($v,'memberName');
                        $need_data['memberName'.$k] = implode(',',$tmpVal);
                    }
                }

                array_push($formatted_data, $need_data);

            }
        }

        return $formatted_data;
    }


    //设置迁移状态
    public function setStatus($where = array(), $data = array())
    {
        $id = intval($where['id']);
        if ($id || !$data) {
            $res = $this->models->member->updateById($id, $data);
            if ($res) {
                $this->ret = array('status' => 1, 'info' => '修改成功');
            } else {
                $this->ret['info'] = '修改失败';
            }
        } else {
            $this->ret['info'] = '缺少参数!';
        }
        return $this->ret;
    }

    //设置用户迁移标注
    public function setMigrateTeam($where = array(), $data = array())
    {
        $id = intval($where['id']);
        if ($id || !$data) {
            $res = $this->models->member->updateById($id, $data);
            if ($res) {
                $this->ret = array('status' => 1, 'info' => '修改成功');
            } else {
                $this->ret['info'] = '修改失败';
            }
        } else {
            $this->ret['info'] = '缺少参数!';
        }
        return $this->ret;
    }

    //设置用户账户等级
    public function UpdateAccountLevelByWhere($where = array(), $data = array())
    {
        $id = intval($where['id']);
        if ($id || !$data) {
            $res = $this->models->member->updateById($id, $data);
            if ($res) {
                $this->ret = array('status' => 1, 'info' => '修改成功');
            } else {
                $this->ret['info'] = '修改失败';
            }
        } else {
            $this->ret['info'] = '缺少参数!';
        }
        return $this->ret;
    }


}
