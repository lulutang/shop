<?php
/**
 * 用户优惠model
 * 订单，单品，红包
 */
namespace Admin\Model;
use Think\Model;
class OnSaleModel extends Model {
    //获取所有订单流程列表信息
    public function getList() {
        $m = M('Onsale');
        $lists = $m->select();
        if( $lists ) return $lists;
    }
    
    //获取满足需求的优惠卷活动
    public function getOnsaleList(){
    	$m = M('Onsale');
    	$where ['endTime'] = array (
					'egt',
					time() 
		);
    	$lists = $m->field('id, sale, money, use, sale_where, startTime, sale_startTime, sale_endTime,sale_use')->where($where)->select();
    	$result = array();
    	foreach ($lists as $list) {
    	 	if(time() >= $list['startTime']) {
    	 		$result[] = $list;
    	 	}
    	}
    	return $result;
    }
    
    //获取满足需求的用户优惠
    public function getUserOnsaleList($where, $page, $pageSize){
    	$m = M('User_sale');
    	if ($where) {
			$info = $m->where ( $where )->order ( 'createTime desc' )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $m->where ( $where )->count ();
		} else {
			$info = $m->order ( 'createTime desc' )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $m->count ();
		}
		$result =array('info' => $info, 'count' => $count);
    	return $result;
    }
    
    //获取所有订单流程列表信息
    public function getOnsaleUserInfo($where) {
    	$m = M('User_sale');
    	$lists = $m -> field('count(*) as num ,sum(sale_money) as money') -> where($where) -> find();
  
    	if( $lists ) return $lists;
    }
} 