<?php
/**
 * Desc: 缓存管理服务类
 * Created by PhpStorm.
 * User: huangzhongxi@yundun.com
 * Date: 2016-11-24
 */

namespace Service\Cache;

class MonthlyCache extends \Service\Service
{

	private $monthly_cache_model;

	public function __construct()
	{
		parent::__construct();

		$this->initModels();
	}


	public function initModels()
	{
		$this->models = new \stdClass();

		$this->monthly_cache_model = new \Model\MonthlyCache();
	}


	// get list
	public function getMonthlyCacheList($params)
	{
		$result = array(
            'count'     =>  0,
            'data'      => array(),
        );
        extract($params);
        empty($where) && $where = array();
        empty($offset) && $offset = 0;
        empty($limit) && $limit = self::PAGE_SIZE;
        $order = array("id"=>"desc");
        $total=$this->monthly_cache_model->getListCountByWhere($where);

        if($total){
        	$list=$this->monthly_cache_model->getListByWhere($where,$offset,$limit,$order);

        	$result=array(
        		'count' => $total,
        		'data'  => $list
        	);
        }


        return $result;
	}

	// get one info 
	public function getOneMonthlyCacheInfo($where,$fields="*")
	{
		if(empty($where)) return false;

		$info=$this->monthly_cache_model->getOneByWhere($where,$fields);

		return $info;
	}


	// add monthly cache
	public function addMonthlyCache($data)
	{
		if(empty($data)) return false;

		$id=$this->monthly_cache_model->add($data);

		return $id===false?false:$id;
	}


	// delete monthly cache
	public function deleteMonthlyCache($where)
	{
		if(empty($where)) return false;

		$delete_id=$this->monthly_cache_model->deleteByWhere($where);

		return $delete_id===fasle?false:$delete_id;
	}

	// update monthly cache
	public function updateMonthlyCache($where,$data)
	{
		if(empty($where) || empty($data)) return false;

		$update_id=$this->monthly_cache_model->updateByWhere($where,$data);

		return $update_id===false?false:$update_id;
	}

	// update status
	public function setStatus($params=array())
	{
		$id=isset($params['id'])?intval($params['id']):0;
		$status=isset($params['status'])?intval($params['status']):1;

		$data=array(
			"status"=>$status,
			'update_time'=>date("Y-m-d H:i:s"),
		);

		$up_id=$this->monthly_cache_model->updateById($id,$data);

		if($up_id===false){
			$ret=["status"=>0,"info"=>"修改月报缓存状态失败"];
		}else{
			$ret=["status"=>1,"info"=>"修改月报缓存状态成功"];
		}

		return $ret;
	}
}