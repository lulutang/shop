<?php
/**
 * 后台购物车Model
 *
 * @author tangll
 *
 */
namespace Admin\Model;
use Think\Model;

class UserOrderModel extends Model {

	/**
	 * 获取购物车信息
	 * @param int $page
	 * @param int $pageSize
	 * @param string $where
	 * @param int $sort
	 * @param int $sort_pay
	 * @param int $type
	 * @return array
	 */
	public function getUserOrderInfo($page, $pageSize, $where = null, $sort, $sort_pay,$type) {
		$result = M ( 'order' ); // 获得组合商品
		if ($sort == 1) {
			$sort = 'createtime DESC';
		} elseif ($sort == 0) {
			$sort = 'createtime ASC';
		}
		if ($sort_pay == 2) {
			$sort = 'pay_time DESC';
		} else if ($sort_pay == 3) {
			$sort = 'pay_time ASC';
		}
		if ($where) {
			if($type==1){
				$whereDay = array_merge ( $where, array (
						'pay_time > ' . strtotime ( date ( 'Y-m-d' ), time () )
				) );
			}else{
				$whereDay = array_merge ( $where, array (
						'createtime > ' . strtotime ( date ( 'Y-m-d' ), time () )
				) );
			}
		
			$dayCount = $result->where ( $whereDay )->count ();
			
			$cartInfo = $result->where ( $where )->order ( $sort )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $result->where ( $where )->count ();
			$sum = $result->where ( $where )->sum ( 'totalprice' );
			$goods_num = $result->where ( $where )->sum ( 'goods_number' );
		} else {
			if($type==1){
				$dayCount = $result->where ( 'pay_time > ' . strtotime ( date ( 'Y-m-d' ), time () ) )->count ();
			}else{
				$dayCount = $result->where ( 'createtime > ' . strtotime ( date ( 'Y-m-d' ), time () ) )->count ();
			}
			
			$cartInfo = $result->order ( $sort )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $result->count ();
			$sum = $result->sum ( 'totalprice' );
			$goods_num = $result->where ( $where )->sum ( 'goods_number' );
		}
		
		$pageSizeCount = ceil ( $count / $pageSize );
		if (empty ( $cartInfo )) {
			return array ();
		}
		
		$carts = array ();
		foreach ( $cartInfo as $key => $val ) {
			$cart = array ();
			$cart ['order_card'] = $val ['order_card'];
			$cart ['goods_number'] = $val ['goods_number'];
			$cart ['totalprice'] = $val ['totalprice'];
			$cart ['pay_type'] = $val ['pay_type'];
			$cart ['status'] = $val ['status'];
			$cart ['truename'] = $val ['user_name'];
			$cart ['pay_time'] = $val ['pay_time'];
			$cart ['createtime'] = $val ['createtime'];
			$cart ['id'] = $val ['order_id'];
			$cart ['user_id'] = $val ['user_id'];
			$cart ['phone'] = $val ['phone'];
			$carts [] = $cart;
		}
		
		return array (
				'orders' => $carts,
				'count' => $count,
				'dayCount' => $dayCount,
				'sum' => $sum ,
				'goods_number' => $goods_num
		);
	}
	
	/**
	 * 获取用户信息
	 * 
	 * @param int $userId        	
	 */
	private function getCartUserInfo($userId) {
		$cache = S ();
		$cacheInfo = $cache->dmin_user_info;
		
		if ($cacheInfo [$userId]) {
			$userInfo = $cacheInfo [$userId];
		} else {
			$model = M ( 'user' );
			$userInfo = $model->where ( 'uid  = ' . $userId )->limit ( 1 )->find ();
			$cacheInfo [$userId] = $userInfo;
			$cache->dmin_user_info = $cacheInfo;
		}
		
		return $userInfo;
	}
	/**
	 * 获取订单商品列表信息
	 * 
	 * @param int $page        	
	 * @param int $pageSize        	
	 * @param array $where        	
	 * @return array
	 */
	public function getOrdersGoods($page, $pageSize, $where, $sort) {
		$result = M ( 'order_goods' ); 
		$field = 'goods_id,order_id,order_code,goods_price,code,style,yiji,erji,addtime,status,id,user_id,user_name,phone,message'; // style 订单状态和CRM对接 1 中细软审核 2 已报商标局 3等待下发受理 4 等待注册公告 5 注册成功
		
		if ($sort ) {
			$sort = 'addtime DESC';
		} else {
			$sort = 'addtime ASC';
		}
		if ($where) {
			$orderInfo = $result->field ( $field )->where ( $where )->order ( $sort )->page ( $page, $pageSize )->select (); // 获取购物车商品
			
			$count = $result->where ( $where )->count ();
			$sum = $result->where ( $where )->sum ( 'goods_price' );
			$whereDay = array_merge ( $where, array (
					'addtime > ' . strtotime ( date ( 'Y-m-d' ), time () ) 
			) );
			$dayCount = $result->where ( $whereDay )->count ();
		} else {
			$orderInfo = $result->field ( $field )->order ( $sort )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $result->count ();
			$sum = $result->sum ( 'goods_price' );
			
			$whereDay = array (
					'addtime > ' . strtotime ( date ( 'Y-m-d' ), time () ) 
			);
			$dayCount = $result->where ( $whereDay )->count ();
		}
		
		if (empty ( $orderInfo )) {
			return array ();
		}
		
		$m= M('goods');
	
		//组装描述 
		foreach ($orderInfo as &$val) {
			if($val['message']) {
				$json = json_decode($val['message']);
				
				if($json -> short_title)
					$val['short_title']=$json -> short_title;
				else{
					$goods_info=$m ->field('short_title')->where('goods_id='.$val['goods_id'])->find();
					$val['short_title']=$goods_info['short_title'];
				}
			}
			if($val['goods_id']) {
			   $val['is_zeng'] = $this -> getIsZengping($val['user_id'], $val['order_code'], $val['goods_id']);
			
			}
		}
		return array (
				'goods' => $orderInfo,
				'count' => $count,
				'sum' => $sum,
				'dayCount' => $dayCount 
		);
	}
	
	/**
	 * 根据goodsID获取赠品
	 */
	private function getIsZengping($uid, $orderCode, $goodsId) {
		$m = M('user_act');
		$res = $m -> field('act_goods_id')->where(array('act_goods_id' => $goodsId, 'uid' => $uid, 'order_code' => $orderCode, 'act_type' => 1)) -> find();
		return $res;
	}
	
	/**
	 * 获取订单详情
	 * 
	 * @param int $yewuId        	
	 * @return array
	 */
	public function getOrderInfo($orderId) {
		$result = M ( 'order' ); 
		$res = $result->where ( array (
				'order_id' => $orderId 
		) )->find ();
		$model = M ( 'order_goods' ); 
	
		$res1 = $model->where ( 'order_id = ' . $orderId )->select ();
	
		
		return array (
				'order' => $res,
				'goods' => $res1 
		);
	}
	/**
	 * 获取二级业务分类
	 * 
	 * @param int $yewuId        	
	 * @return array
	 */
	public function getYewu($yewuId) {
		$result = M ( 'service' ); 
		$res = $result->where ( array (
				'parent_id' => $yewuId 
		) )->select ();
		return $res;
	}
	/**
	 * 通过user_id获取用户信息
	 * 
	 * @param unknown $userId        	
	 * @return array
	 */
	public function getUserInfoById($userId) {
		$data = $this->db(1,"DB_CONFIG1")->table("ecs_users")->field ( 'user_id,user_name,user_money,bind_mobile,photo_add,reg_time' )->where ( 'user_id = ' . $userId )->find ();
		if (! empty ( $data )) {
			return $data;
		}
		return array ();
	}
	/**
	 * 通过username获取用户信息
	 * 
	 * @param unknown $userName        	
	 * @return array
	 */
	public function getUserInfoByUserName($userName) {
		$data =$this->db(1,"DB_CONFIG1")->table("ecs_users")->field ( 'user_id,user_name,user_money,bind_mobile,photo_add,reg_time' )->where ( 'user_name = ' . $userName )->find ();
		if (! empty ( $data )) {
			return $data;
		}
		return array ();
	}
	
	/**
	 * 通过user_id获取用户信息
	 * 
	 * @param int $userId        	
	 * @param array $options
	 *        	保存的信息
	 * @return true or false
	 */
	public function updateUserInfoById($userId, $options) {
		if (! isset ( $options )) {
			return false;
		}		
		return $this->db(1,"DB_CONFIG1")->table("ecs_users")->where ( 'user_id = ' . $userId )->save ( $options );
	}
	
	/**
	 * 获取订单商品需求信息
	 * 
	 * @param int $yewuId        	
	 * @return array
	 */
	public function getOrderGoodsInfo($id) {
		$result = M ( 'order_goods' ); //
		$res = $result->field ( 'message, style' )->where ( array (
				'id' => $id 
		) )->find ();
		
		$message = json_decode ( $res ['message'] );
		
		isset ( $message->text ) ? $info ['text'] = $message->text : '';
		isset ( $message->style ) ? $info ['style'] = $message->style : '';
		isset ( $message->name ) ? $info ['name'] = $message->name : '';
		isset ( $message->j_info ) ? $info ['j_info'] = $message->j_info : '';
		isset ( $message->address ) ? $info ['address'] = $message->address : '';
		$info ['id'] = $id;
		$info ['type'] = $res['style'];
		return $info;
	}
	/**
	 * 增加后台操作日志
	 * 
	 * @param array $option        	
	 */
	public function add_admin_log($option) {
		$model = M ( 'admin_log' );
		if ($option) {
			$option ['createtime'] = time ();
			$model->add ( $option );
		}
	}
}
	