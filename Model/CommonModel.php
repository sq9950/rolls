<?php

namespace Model;

class CommonModel extends Model
{
	//config
	protected $configall;
	
	protected $database_cp = 'lsls';

	//yundun_cp database
	protected $tbl_member_domain_ns; //ns域名表
	protected $tbl_member_domain_cname;//cname域名表
	protected $tbl_member_domain_alias;
	protected $tbl_domain; //控制台域名表
	protected $tbl_member; //会员表
	protected $tbl_member_log;
	protected $tbl_member_check;
	protected $tbl_domain_interim;
	protected $tbl_member_domain_record_remark;
	protected $tbl_member_domain_ns_record_dns;//ns dns记录表
	protected $tbl_member_domain_ns_record_dns_log;
	protected $tbl_domain_group;
	protected $tbl_domain_group_domain;
	protected $tbl_domain_setting;
	protected $tbl_member_domain_ns_record_web;//ns web记录表
	protected $tbl_domain_balance;//负载均衡ip
	protected $tbl_balance_group;//负载均衡
	protected $tbl_agency_info;//代理
	protected $tbl_agency_serverip;
	protected $tbl_agency_group_account;
	protected $tbl_agency_group_domain;
	protected $tbl_agency_info_att;
	protected $tbl_agency_diy_plan_price;
	protected $tbl_server_ip;
    protected $tbl_links;
	protected $tbl_attribute ;
	protected $tbl_server_ip_check_log;
	protected $tbl_ip2fw;
	protected $tbl_black_list;
	protected $tbl_index_cate;
	protected $tbl_index_ctype;
	protected $tbl_index_article;
	protected $tbl_index_config;
	protected $tbl_index_configuration;
	protected $tbl_domain_level;
	protected $tbl_jgb_member;//监管账户表
	protected $tbl_jgb_domain;//监管域名表
	//protected $tbl_domain_level_v2;
	protected $tbl_domain_action;
	protected $tbl_balance_order;
	protected $tbl_index_coupon_customer;
	protected $tbl_index_coupon;
	protected $tbl_index_coupon_history;
	protected $tbl_index_feedback;
	protected $tbl_order_manage;
	protected $tbl_order_product;
	protected $tbl_member_domain_extra;
	protected $tbl_index_affiliate_link;
	protected $tbl_index_affiliate_transaction;
	protected $tbl_order_invoice;
	protected $tbl_member_invoice;
	protected $tbl_member_setting;
	//monitor
	protected $tbl_domain_monitor;
	protected $tbl_domain_monitor_node;
	protected $tbl_domain_monitor_subdomain;
	protected $tbl_domain_monitor_alarm_info;
	protected $tbl_domain_monitor_switch_log;

	//test
	protected $tbl_views_gdnsd_depth;
	
	public function __construct(){
		parent::__construct();
		$this->configall = $this->config->getAll();
		//yundun_cp table
        $this->tbl_node = 'rbac_node';
		$this->tbl_member_domain_ns = 'member_domain_ns';
		$this->tbl_member_domain_cname = 'member_domain_cname';
		$this->tbl_member_domain_alias = 'member_domain_alias';
		$this->tbl_domain = 'domain';
		$this->tbl_member = 'member';
		$this->tbl_member_log = 'member_log';
		$this->tbl_member_check = 'member_check';
		$this->tbl_domain_interim = 'domain_interim';
		$this->tbl_member_domain_record_remark = 'member_domain_record_remark';
		$this->tbl_member_domain_ns_record_dns = 'member_domain_ns_record_dns';
		$this->tbl_member_domain_ns_record_dns_log = 'member_domain_ns_record_dns_log';
		$this->tbl_domain_group = 'domain_group';
		$this->tbl_domain_group_domain = 'domain_group_domain';
		$this->tbl_domain_setting = 'domain_setting';
		$this->tbl_member_domain_ns_record_web = 'member_domain_ns_record_web';
		$this->tbl_domain_balance = 'domain_balance';
		$this->tbl_balance_group = 'balance_group';
		$this->tbl_agency_info = 'agency_info';
		$this->tbl_agency_serverip = 'agency_serverip';
		$this->tbl_agency_group_account = 'agency_group_account';
		$this->tbl_agency_group_domain = 'agency_group_domain';
		$this->tbl_agency_info_att = 'agency_info_att';
		$this->tbl_agency_diy_plan_price = 'agency_diy_plan_price';
		$this->tbl_server_ip = 'server_ip';
        $this->tbl_links = 'link';
		$this->tbl_attribute = 'attribute';
		$this->tbl_server_ip_check_log = 'server_ip_check_log';
		$this->tbl_ip2fw = 'ip2fw';
		$this->tbl_black_list = 'black_list';
		$this->tbl_index_cate = 'index_cate';
		$this->tbl_index_ctype = 'index_ctype';
		$this->tbl_index_article = 'index_article';
		$this->tbl_index_config = 'index_config';
		$this->tbl_index_configuration = 'index_configuration';
		$this->tbl_index_feedback = 'index_feedback';
		$this->tbl_domain_level = 'domain_level';
		$this->tbl_domain_level_v2 = 'domain_level_v2';
		$this->tbl_domain_action = 'domain_action';
		$this->tbl_balance_order = 'balance_order';
		$this->tbl_index_coupon_customer = 'index_coupon_customer';
		$this->tbl_index_coupon = 'index_coupon';
		$this->tbl_index_coupon_history = 'index_coupon_history';
		$this->tbl_order_manage = 'order_manage';
		$this->tbl_order_product = 'order_product';
		$this->tbl_member_domain_extra = 'member_domain_extra';
		$this->tbl_index_affiliate_link = 'index_affiliate_link';
		$this->tbl_index_affiliate_transaction = 'index_affiliate_transaction';
		$this->tbl_order_invoice = 'order_invoice';
		$this->tbl_member_invoice = 'member_invoice';
		$this->tbl_member_setting = 'member_setting';
		$this->tbl_jgb_member = 'jgb_member';
		$this->tbl_jgb_domain = 'jgb_domain';
		
		//monitor
		$this->tbl_domain_monitor = 'domain_monitor';
		$this->tbl_domain_monitor_node = 'domain_monitor_node';
		$this->tbl_domain_monitor_subdomain = 'domain_monitor_subdomain';
		$this->tbl_domain_monitor_alarm_info = 'domain_monitor_alarm_info';
		$this->tbl_domain_monitor_switch_log = 'domain_monitor_switch_log';
	}
	//方法未定义或不可访问时调用
	public function __call($method, $args){
		die("Model function ( $method ) not exist ");
	}
	//php 5.3.0 version later to call
	public static function __callStatic($method, $args){
		die("Model static function ( $method ) not exist ");
	}
	
	//save member setting
	public function saveMemberSetting($member_id, $data, $delGroup = true){
		if(!$data) return;
		 foreach($data as $group => $values){
			if($delGroup){
				$this->db->table($this->tbl_member_setting)->del_where(array('member_id'=>$member_id,'group'=>$group));
			}
			foreach($values as $k => $v){
				$d = array(
					'member_id' => $member_id,
					'group' => $group,
					'key' => $k,
					'value' => $v,
				);
				$ds = $this->db->table($this->tbl_member_setting)->where("member_id = ? and `group` = ? and `key` = ?",array($d['member_id'],$d['group'],$d['key']))->fetch();
				//如果存在先删除
				if($ds) $this->db->table($this->tbl_member_setting)->del_where(array('id'=>$ds['id']));
				$this->db->table($this->tbl_member_setting)->save($d);
			}
		 }
	}
	
	//get member setting
	public function getMemberSetting($member_id, $group='',$key=''){
		$res = array();
		if(is_null($member_id)) return false;
		if(empty($group)){
			//获取当前域名控制台所有配置 return array(group =>array(key=>val),group =>array(key=>val)) 二维数组
			$result = $this->db->table($this->tbl_member_setting)->where("member_id=?",array($member_id))->select();
			if($result){
				foreach($result as $r){
					$res[$r['group']][$r['key']] = $r['value'];
				}
			}
		}else if(!empty($group) && empty($key)){
			//获取当前域名某个组配置  return array('key'=>val) 一维数组
			$result = $this->db->table($this->tbl_member_setting)->where("member_id=? and `group` = ?",array($member_id, $group))->select();
			if($result){
				foreach($result as $r){
					$res[$r['key']] = $r['value'];
				}
			}
		}else if(!empty($group) && !empty($key)){
			//获取当前域名某个组某个key的配置 return string value值
			$result = $this->db->table($this->tbl_member_setting)->where("member_id=? and `group` =? and `key` = ?",array($member_id, $group, $key))->fetch();
			if($result){
				$res = $result['value']?$result['value']:'';
			}
		}
		
		return $res;
	}

	//控制台开关操作
	public function switch01($control_id, $group, $key, $value=1){
		$sql = "INSERT INTO `domain_setting` SET domain_id='{$control_id}',`group`='{$group}',`key`='{$key}',`value`='$value' ON DUPLICATE KEY UPDATE `value`=value^1";
        $this->db->insert($sql);
	}
	
	//设置domain setting
	public function _setDefaultSettingCommon($control_id, $group, $key){
		$data = array(
			'domain_id' => $control_id,
			'group' => $group,
			'key' => $key,
			'value' => 0,
		);
		$id = $this->db->table($this->tbl_domain_setting)->save($data); //create has problem
		return $id?$id:false;
	}
	
	//获取domain setting
	public function _getDefaultSettingCommon($control_id, $group, $key){
		$ds = $this->db->table($this->tbl_domain_setting)->where("domain_id = ? and `group` = ? and `key` = ?",array($control_id, $group, $key))->fetch();
		return $ds?$ds:false;
	}
	
	/**
	 * 获取域名控制台id
	 * 支持域名id和域名
	 */
	public	function getDomainControId($domain_id, $type='cname') {
		//is_numeric 数字或数字字符串
		$type = in_array(strtolower($type), array('cname','ns')) ? strtolower($type) : 'cname';
		if(!is_numeric($domain_id)){
			$table = $type == 'ns' ? $this->tbl_member_domain_ns : $this->tbl_member_domain_cname;
			$domain_id = $this->db->table($table)->field('id')->where("domain = ?",array($domain_id))->fetchOne();
		}
		$domain_id = (int)$domain_id;
		$res = $this->db->table('domain')->where("domain_id=? and type=?",array($domain_id,$type))->fetch();
		if($res)return $res['id'];
		return 0;
	}
	
	//获取域名id type control_id
	public function getDomainIdTypeControlIdByDomain($domain){
		$ns = $this->db->table($this->tbl_member_domain_ns)->field('id')->where("domain = ?", array($domain))->fetchOne();
		if(isset($ns) && $ns){
			$domain_id = $ns;
			$domain_type = 'ns';
			$control_id = $this->db->table($this->tbl_domain)->field('id')->where("domain_id = ? and type=?", array($domain_id, $domain_type))->fetchOne();
			$control_id = isset($control_id)&&$control_id?$control_id:null;
		}else{
			$cname = $this->db->table($this->tbl_member_domain_cname)->field('id')->where("domain = ? and parent_id = 0", array($domain))->fetchOne();
			if(isset($cname) && $cname){
				$domain_id = $cname;
				$domain_type = 'cname';
				$control_id = $this->db->table($this->tbl_domain)->field('id')->where("domain_id = ? and type=?", array($domain_id, $domain_type))->fetchOne();
				$control_id = isset($control_id)&&$control_id?$control_id:null;
			}
		}
		return array(
			'domain_id' => isset($domain_id)?$domain_id:null,
			'domain_type' => isset($domain_type)?$domain_type:null,
			'control_id' => isset($control_id)?$control_id:null
		);
	}
	
	//get table by domain_type
	public function getTablesByDomainType($domain_type){
		$table = '';
		switch($domain_type){
			case 'cname':
				$table = $this->tbl_member_domain_cname;
			break;
			case 'ns':
				$table = $this->tbl_member_domain_ns;
			break;
		}
		return $table;
	}
	
	//get domain views
	public function getDomainViews($domain_id){
		//根据域名套餐等级获取线路
		
		//高防dns等级线路
		
		//配置
		return $this->configall['Anti_Ns_Record_Line'];
	}
	
	//get domain id type by control_id
	public function getDomainIdTypeByControlId($control_id){
	    $res = $this->db->table($this->tbl_domain)->where("id=?",array($control_id))->fetch();
		if(!$res) return false;
		$result = array();
		$result = array(
			'domain_id' => $res['domain_id'],
			'domain_type' => $res['type'],
		);
		return $result;
	}
	
	//get domain info by Control_id
	public function getDomainInfobyControlId($control_id, $uid){
		$domain = $this->getDomainIdTypeByControlId($control_id);
		if(!$domain) return false;
		$tbl = $domain['domain_type'] == 'ns'?$this->tbl_member_domain_ns:$this->tbl_member_domain_cname;
		$domain_info = $this->db->table($tbl)->where("id = ? and member_id = ? ",array($domain['domain_id'], $uid))->fetch();
		if($domain_info){
			$domain_info['domain_type'] = $domain['domain_type'];
		}
		return $domain_info?$domain_info:false;
	}
	
	//get member status
	public function getMemberStatus($member_id){
		$res = $this->db->table($this->tbl_member)->where('id = ?', array($member_id))->fetch();
        return (bool)$res['status'];
	}
	
	/**
	 *同步域名
	 *domain_type cname/ns
	 */
	public function update_domain_rsync($domain_type , $domain_id){
		$this->db->update("update `member_domain_{$domain_type}` set rsync=rsync|2 where id=?",array($domain_id));		
	}
	
	//触发域名同步
    public function triggerUpdate($control_id) {
        $domain = $this->db->table($this->tbl_domain)->where("id=?",array($control_id))->fetch();
        $where['id'] = $domain['domain_id'];
        $type = $domain['type'];
        $this->updateFromSqlByWhere("update `member_domain_{$type}` set rsync=rsync|2 ", $where);
    }
	

	public function get_domain_table($domain_type){
		switch ($domain_type) {
			case 'cname':
				return $this->tbl_member_domain_cname;
				break;
			case 'ns':
				return $this->tbl_member_domain_ns;
				break;
			default:
				return false;
				break;
		}
	}


	public function getAllViews($field = '`name`,`desc`', $retarr1d = true){
		$res = $this->db_dns->table($this->tbl_yundun_dns_views)->field($field)->select();
		if(!$retarr1d) return $res?$res:array();
		$views_name_desc = array();
		foreach($res as $r){
			$views_name_desc[$r['name']] = $r['desc'];
		}
		
		return $views_name_desc;
	}

	public function getAllViewsDescName(){
		$res = $this->getAllViews();
		if(isset($res) && is_array($res)){
			return array_flip($res);
		}
	}
	
	public function getHttpsDomainList($control_id){
		$sql = 'SELECT `value` from '.$this->tbl_domain_setting.' where `domain_id`=:did and `group`="sslhttps" and `key`="domains"';
		$array = array(
				':did'=>$control_id
		);
		$result = $this->db->select($sql,$array);
		return $result[0]?$result[0]:null;
	}
	
	public function getModelName(){
		return $this->table_name;
	}

	public function updateDomainCheck($check, $where){
		$sql = "update {$this->table_name} set `check`= `check`^`check`|$check";
		$res = $this->updateFromSqlByWhere($sql, $where);

		return $res;
	}
	
}
