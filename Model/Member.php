<?php

namespace Model;

use Service\Common\PasswordService;

class Member extends \Model\CommonModel
{
    public function __construct()
    {
        parent::__construct();
        $this->table_name = 'member';
    }

    //field email/mobile
    public function checkexist($field, $value)
    {
        if ($this->db->table($this->tbl_member)->where("$field = ?", array($value))->count()) return true;
        return false;
    }

    //添加会员
    public function addMember($data)
    {
        $res = $this->add($data);
        return $res ? $res : false;
    }

    public function addMemberCheck($data)
    {
        $res = $this->db->table($this->tbl_member_check)->create($data);
        return $res ? $res : false;
    }

    public function getMemberCheckByCode($code)
    {
        return $this->db->table($this->tbl_member_check)->where("`code` = ?", array($code))->fetch();
    }

    public function getMemberCheckByCodeMemberIdType($member_id, $type, $code)
    {
        $res = $this->db->table($this->tbl_member_check)->where("member_id = ? and type = ? and code = ?", array($member_id, $type, $code))->fetch();
        return $res ? $res : array();
    }

    public function delMemberCheck($where)
    {
        $res = $this->db->table($this->tbl_member_check)->del_where($where);

        return $res === false ? false : true;
    }

    public function getMemberById($id)
    {
        return $this->db->table($this->tbl_member)->where("id = ?", array($id))->fetch();
    }

    public function getMemberbyEmail($email, $field = '*')
    {
        $res = $this->db->table($this->tbl_member)->where("email = ?", array($email))->field($field)->fetch();

        return $res ? $res : array();
    }

    public function getMemberbyWhere($email,$mobile,$username = '', $field = '*')
    {
        $where = "email = ? ";
        $data[]  = $email;
        //当表单填入用户名时(PS：username也是不能重复的，如果邮箱不重复但是用户名重复则$this->add()不能执行.)
        if(!empty($username)){
            $where .= "or  username = ? ";
            $data[] = $username;
        }
        //当表单填入手机号时
        if(!empty($mobile)){
            $where .= "or  mobile = ? ";
            $data[] = $mobile;
        }

        $response = $this->db->table($this->tbl_member)->where($where, $data)->count();

        return $response;
    }

    public function activeMember($member_id)
    {
        $where = array(
            'id' => $member_id,
        );
        $data  = array(
            'status' => 1,
        );
        return $this->db->table($this->tbl_member)->update_where($where, $data);
    }

    public function delMemberCheckByCodeType($code, $type = 'register')
    {
        $where = array(
            'code' => $code,
            'type' => $type,
        );
        return $this->db->table($this->tbl_member_check)->del_where($where);
    }

    public function getMemberByEmailPassword($email, $password)
    {
        $member = $this->db->table($this->tbl_member)->where("email = ? and password = ? ", array($email, PasswordService::getEncryPassword($password)))->fetch();
        return $member ? $member : false;
    }

    //获取账户余额
    public function getMemberBalance($member_id)
    {
        $balance = $this->db->table($this->tbl_member)->field('balance')->where("id = ?", array($member_id))->fetchOne();
        return $balance ? (int)$balance : 0;
    }

    //获取账户余额
    public function getMemberBalanceFloat($member_id)
    {
        $balance = $this->db->table($this->tbl_member)->field('balance')->where("id = ?", array($member_id))->fetchOne();
        return $balance ? $balance : 0;
    }

    //加余额 返回余额
    public function setIncBalance($id, $price)
    {
        $row    = $this->db->update("update $this->tbl_member set balance=balance+? where id=?", array($price, $id));
        $member = $this->getMemberById($id);
        return $member['balance'] ? $member['balance'] : 0;
    }

    //减余额
    public function setDecBalance($id, $price)
    {
        $where = array(
            'id' => $id,
        );
        $data  = array(
            'balance' => $price,
        );

        $r = $this->db->table($this->tbl_member)->update_where($where, $data);

        return $r === false ? false : true;
    }

    //获取email根据会员的id
    public function getEmailById($id)
    {
        $email = $this->db->table($this->tbl_member)->field('email')->where("id = ?", array($id))->fetchOne();
        return $email ? $email : false;
    }


    //update password
    public function updatePassrod($member_id, $password)
    {
        $where = array(
            'id' => $member_id,
        );
        $data  = array(
            'password' => $password,
        );
        $r     = $this->db->table($this->tbl_member)->update_where($where, $data);

        return $r === false ? false : true;
    }

    //update tel
    public function updateMobile($member_id, $mobile)
    {
        $where = array(
            'id' => $member_id,
        );
        $data  = array(
            'mobile' => $mobile,
        );
        $r     = $this->db->table($this->tbl_member)->update_where($where, $data);

        return $r === false ? false : true;
    }


    //update member info
    public function updateMemberInfo($member_id, $data)
    {
        $where = array(
            'id' => $member_id,
        );

        $r = $this->db->table($this->tbl_member)->update_where($where, $data);

        return $r === false ? false : true;
    }

    //获取email和手机号
    public function getEmailPhone($member_id)
    {
        $res = $this->db->table($this->tbl_member)->where("id = ?", array($member_id))->field('email, mobile')->fetch();

        return $res ? $res : array();
    }

    public function getMemberListByIds($member_ids = array())
    {
        $members = array();
        if (is_array($member_ids) && !empty($member_ids)) {
            $where['id'] = array('in', $member_ids);
            $list        = $this->getListByWhere($where);
            if (is_array($list) && !empty($list)) {
                foreach ($list as $val) {
                    $members[$val['id']] = $val;
                }
            }
        }
        return $members;
    }

    public function getFieldsListByIds($member_ids = array(), $fields = array(), $limit = 999)
    {
        $members = array();
        if (is_array($member_ids) && !empty($member_ids)) {
            $where['id'] = array('in', $member_ids);
            $list        = $this->getListByWhere($where, 0, $limit, [], $fields);
            if (is_array($list) && !empty($list)) {
                foreach ($list as $val) {
                    $members[$val['id']] = $val;
                }
            }
        }
        return $members;
    }

    /**
     * 过滤用户信息中的密码字段
     * @param array $where
     * @param int $offset
     * @param int $limit
     * @param array $order
     * @param array $fields
     * @param string $group
     * @param string $sortKey
     * @return mixed
     */
    public function getListByWhere($where = array(), $offset = 0, $limit = 999999, $order = array(), $fields = array(), $group = '', $sortKey = '')
    {
        $list = parent::getListByWhere($where, $offset, $limit, $order, $fields, $group, $sortKey);
        foreach ((array)$list as $key => $val) {
            unset($list[$key]['password']);
        }
        return $list;
    }

    /**
     * 查询所有用户信息，如果不需要密码，不要调用此方法！！
     * @param array $where
     * @param array $fields
     * @param string $group
     * @return mixed
     */
    public function getAllInfoByWhere($where = array(), $fields = array(), $group = '')
    {
        $member_info = parent::getOneByWhere($where, $fields, $group);
        return $member_info;
    }

    /**
     * 过滤用户密码
     * @param array $where
     * @param array $fields
     * @param string $group
     * @return mixed
     */
    public function getOneByWhere($where = array(), $fields = array(), $group = '')
    {
        $member_info = parent::getOneByWhere($where, $fields, $group);
        if (is_array($member_info) && isset($member_info['password'])) {
            unset($member_info['password']);
        }
        return $member_info;
    }

    /**
     * @param int $member_id
     * @return array|mixed
     */
    public function getMemberInfoById($member_id = 0)
    {
        $member_info = array();
        if ($member_id) {
            $where['id'] = $member_id;
            $member_info = $this->getOneByWhere($where);
        }
        return $member_info;
    }

    //删除会员
    public function delMemberByUid($uid)
    {
        $info   = $this->getMemberById($uid);
        $return = array(
            'status' => 0,
            'info'   => '操作失败'
        );
        if (empty($info)) {
            $return['info'] = '不存在该用户';
        } else if ($this->getDomainAllByUid($uid, 'cname' . true) || $this->getDomainAllByUid($uid, 'ns', true)) {
            $return['info'] = '该用户尚有域名在使用';
        } else if ($this->_delMemberByUid($uid)) {
            $return['status'] = 1;
            $return['info']   = '操作成功';
        } else {
            $return['info'] = '操作失败';
        }
        return $return;

    }

    //获取用户的cname/ns域名
    public function getDomainAllByUid($uid, $domain_type = 'ns', $count = false)
    {
        if ($count) {
            return $this->db->table('member_domain_' . $domain_type)->where('member_id = ' . $uid)->count();
        } else {

        }
    }

    //删除member表中的用户
    public function _delMemberByUid($uid)
    {
        $array = array(
            'id' => $uid
        );
        $r     = $this->deleteByWhere($array);
        return $r === false ? false : true;
    }


    public function isAllowSend($field = "", $value = "")
    {
        trim($value);
        $where = "`{$field}` = '{$value}' AND `check` & 4";
        $info  = $this->getOneFieldByWhere($where, 'check');
        return empty($info) ? true : false;
    }


}