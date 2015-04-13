<?php
/**
 * 前端用户优惠model
 * 订单，单品，红包
 */
namespace Home\Model;
use Think\Model;
class OnSaleModel extends Model {
    
    //获取满足需求的优惠卷活动
    public function getOnsaleList($where=null){
    	$m = M('Onsale');
    	$where ['endTime'] = array (
					'egt',
					time() 
		);
    	$lists = $m->field('id, sale, name, money, use, sale_where, startTime, sale_startTime, sale_endTime,sale_use')->where($where)->select();
    	
    	$result = array();
    	foreach ($lists as $list) {
    	 	if(time() >= $list['startTime']) {
    	 		$result[] = $list;
    	 	}
    	}
    	
    	return $result;
    }
    
    //获取满足需求的优惠卷活动的一条数据
    public function getOnsaleOne($where){
    	if(is_array($where)) {
    		$m = M('Onsale');
    		$lists = $m->field('id, sale, name, money, use, sale_where, startTime, sale_startTime, sale_endTime,sale_use') -> where($where) -> find();
    		return $lists;
    	}
    	return false;
    
    }
    //增加用户优惠卷数据
    public function addUserOnsale($data){
    	$m = M('User_sale');
    	if($data) {
    		return $m->add($data);
    	}
    	return false;
    }
    
    //获取用户优惠卷
    public function getUserOnsale($where){
    	$m = M('User_sale');
		$where['state'] = 0;
		$where['is_use'] = 0;
    	$data = $m->where($where)->select();
		$result = array();
		foreach ($lists as $list) {
			if(time() > $list['startTime'] && time() < $list['endTime']) {
				$result[] = $list;
			}
		}
    	return $data;
    }
    
    //获取用户优惠卷一条
    public function getOneOnsale($where){
    	if(!$where) {
    		return false;
    	}
    	$m = M('User_sale');
    	$where['state'] = 0;
    	$where['is_use'] = 0;
    	$data = $m->field('id, sale_id, sale_money, startTime, endTime, fanwei')->where($where)->find();
    	if(time() >= $data['startTime'] && $data['endTime'] >= time())
    		return $data;
    	else 
    		return false;
    }
    
    //绑定用户优惠卷一条
    public function bindOneOnsale($where,$data){
    	if(!$where ) {
    		return false;
    	}
    	$m = M('User_sale');
    	$data['state']=1;
    	$result = $m->where($where)->save($data);
    	return $result;
    }
} 