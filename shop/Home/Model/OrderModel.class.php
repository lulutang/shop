<?php
namespace Home\Model;
use Think\Model;
/**
 * 订单model类
 * @author tangll
 *
 */
class OrderModel extends Model {
	
	/**
	 * 删除order
	 */
	public function deleteOrder($id){
		$m = M('Order');
		$data = array('status' => 2);
		$this->cancelOrderBind($id);
		return $m ->where('order_id = '. $id)->save($data);
	}
	
	/**
	 * 取消用户绑定order的优惠卷
	 */
	public function cancelOrderBind($id){
		$id = intval($id);
		$m = M('Order');
		if($id > 0) {
			$result = $m -> field('onsale_id, onsale_money, totalprice') -> where('order_id = '. $id.' AND status = 0')->find();
			$onsale_id = $result['onsale_id'];
			
			if($onsale_id) {
				$data = array('onsale_money' => 0, 
							  'onsale_id' => 0, 
							  'totalprice'=> $result['totalprice'] + $result['onsale_money']);
				
				 $m ->where('order_id = '. $id)->save($data);
				 
				 $onsaleMoedl = M('User_sale');
				 $onsale =array('state' => 0, 'orderId' => 0);
				 $onsaleMoedl ->where('id = '. $onsale_id)->save($onsale);
				 return true;
			}	
		}
		return false;
	}
	
	/**
	 * 通过购物车添加订单
	 * @param array $get get数组
	 */
	public function makeOrderByCart($get) {
		$cartIds = I ( 'get.cartIds' );
 		$cartModel = new CartModel();
 		$session = session('user');
 		$user_id = $session['user_id'];
 		$user_name = $session['user_name'];
 		$phone = $session['bind_mobile'];
 		
 		$cartInfo = $cartModel->getCart('user_id = '. $user_id .' AND id IN ('.$cartIds.')');
 		
 		$is_package = 0;
 		$goodsPrice = 0;
 		$goodsIds = '';
 		$goodsIdsArray = array();
 		$packageIds = '';
 		$packageIdsArray = array();
 		$creator = array();//需要写注册信息的
 		foreach ($cartInfo as $info) {
 			$goodsPrice += $info['now_price'];
 			//如果是需要填写注册信息的
 			if($info['creator']) {
 				$creator[] = $info['id'];
 			}else{
 				$goodsIdsString .= $info['goods_id'].',';
 				if($info['package_id']) {
 					$is_package = 1;
 					$packageIds .= $info['package_id'].',';
 					$packageIdsArray[] = $info['package_id'];
 				}
 			}
 			
 		}
 		$goodsIdsString = substr($goodsIdsString, 0, strlen($goodsIdsString)-1);
 		$goodsIdsArray = explode ( ',', $goodsIdsString );
 		if ($packageIdsArray) {
 			foreach ($packageIdsArray as $package_id ) {
 				$options = array (
 						'package_id' => $package_id
 				); // 组合查询条件
 				$result = getPackageOne ( $options ); // 获得某一个组合商品
 				$goods = reset ( $result );
 				if(empty($goods)) {
 					return array('fail'=>array('message' => '所选的商品不存在'));
 				}
 				if($goods['status'] != 1) {
 					return array('fail'=>array('message' => '所选的优惠套餐里有已过期的商品'));
 				}
 				$goodsInfo = array (
 						'package_id' => $goods ['package_id'],
 						'package_code' => $goods ['package_code'],
 						'short_title' => $goods ['short_title'],
 						'title' => $goods ['title'],
 						'description' => $goods ['description'],
 						'now_price' => $goods ['now_price'],
 						'old_price' => $goods ['old_price'],
 						'thumb' => $goods ['thumb'] ,
 						'order_code' => $goods ['order_card']
 							
 				);
 			}
 		}
 		//截取掉，
 		$message = array (
 				'text' => '',
 				'short_title' => '购物车组合商品'
 		);
 			
 		$packageIds = substr($packageIds,0,strlen($packageIds)-1);
 		$orderInfo = array (
 				'order_card' => 'ZXRCART' . makeOrderCardId (), // 订单编号
 				'goods_number' => count($goodsIdsArray),
 				'totalprice' => $goodsPrice, // 订单总额
 				'status' => 0, // 订单状态 关联ordermanage表id
 				'createtime' => time (),
 				'user_id' => $user_id, // 下订单人关联前台用户
 				'user_name' => $user_name, // 下订单人关联前台用户
 				'goods_id' => $goodsIdsString, // 商品id
 				'pay_type' => '', // 支付方式
 				'is_invoile' => '', // 是否要发票
 				'nvoile_title' => '', // 发票抬头
 				'message' => json_encode ( $message ), // 用户留言
 				'is_prop' => 0, // 是否使用道具 如优惠卷
 				'trade_no' => '', // 外部订单号
 				'type' => 1, // 购买方式 1普通 0 限时
 				'is_package' => $is_package, // 是否是优惠商品组合
 				'package_id' => $packageIds ,
 				'phone' => $phone
 		);
 		//对所有商品做出判断 是否上架 是否正常
 		foreach ( $goodsIdsArray as $gid ) {
 			if($gid){
 				$goodsInfo = getGoodsInfo ( $gid ); // 获得某一个商品
 				$goodsInfo = reset ( $goodsInfo );
 				if($goodsInfo['status'] != 1){
 					return array('fail'=>array('message' => '该订单里有已下架的商品'));
 				}
 			}	
 		}
 		
 		$orderId = $this->addOrder( $orderInfo );
 	
 		if($orderId){
 			foreach ( $goodsIdsArray as $gid ) {
 				$goodsInfo = getGoodsInfo ( $gid );
 				$goodsInfo = reset ( $goodsInfo );
 				$order_goods [] = array (
 						'user_id' => $user_id, // 下订单人关联前台用户
 						'user_name' => $user_name, // 下订单人关联前台用户
 						'message' => json_encode ( array (
 								'short_title' => $goodsInfo ['short_title']
 						) ),
 						'order_id' => $orderId,
 						'goods_id' => $goodsInfo ['goods_id'],
 						'style' => $goodsInfo ['server_pid'],
 						'yiji' => $goodsInfo ['now_servername'],
 						'erji' => $goodsInfo ['attr_name'],
 						'goods_price' => $goodsInfo ['now_price'],
 						'goods_thumb' => $goodsInfo ['thumb'],
 						'addtime' => time () ,
 						'goods_code' => $goodsInfo ['goods_code'],
 						'order_code' => $orderInfo ['order_card'],
 						'phone' => $phone,
 						'cost' => $goodsInfo ['cost'] ,
 						'service_price' =>$goodsInfo ['now_price'],
 				);
 			}
 			$this -> add_order_goods ( $order_goods, false );
 			deleteCartByCart($cartIds);//删除购物车信息
 			$this -> creator($creator, $orderId, $orderInfo ['order_card']);//处理业务人员从后台添加的订单
 		}
 		return array('success'=>array('orderId' => $orderId));	
		
	}
	
	/**
	 * 处理业务人员在后台添加的物品
	 * @param array $creator
	 */
	private function creator($creator, $orderId, $orderCode) {
		if($creator && is_array($creator)) {
			$model = M('Temp_cart_goods');
			$ids = implode(',', $creator);
			//字节都需要从临时表中copy
			$creators = $model -> where('cartid in ('.$ids.')') -> select();
			$needIds = array();
			$order_goods = array();
			$orderModel = M('Order_goods');
			$need = $orderModel -> field('max(need_id) as max') -> find();
			$needId = $need['max'];
			foreach ($creators as $cart) {
				++$needId;
				$oldNeedId = $cart['need_id'];
				if($oldNeedId) {
					$cart['need_id'] = $needId;
					$needIds [] = array('need_id' => $needId, 
										'id'=>$oldNeedId,
							            'cartid'=>$cart['cartid']
					);
				}
				
				$cart['order_code'] = $orderCode;
				$cart['order_id'] = $orderId;
				unset($cart['id']);
				unset($cart['cartid']);
				$order_goods[] = $cart;
			}
			//从临时表 copy 数据到order_goods
			$orderModel = M('Order_goods');
			$orderModel ->addAll($order_goods);
			
			//修改need数据表
			if($needIds){
				$sql= '';
				foreach ($needIds as  $carts){
					$sql .='UPDATE shop_goods_need SET order_id='.$orderId.',need_id='.$carts['need_id'].' WHERE id= '.$carts['id'].';';
				}
				$needModel =M();
				$needModel -> query($sql);
			}
			
		}
	}
	
	/**
	 * 添加订单关联的商品
	 *
	 * @param array $info
	 * @param int 1 单条插入数据 0 多条插入
	 *
	 */
	private function add_order_goods($info, $type) {
		if (empty ( $info )) {
			return false;
		}
		$Model = M ( 'Order_goods' );
		// 单条插入
		if ($type) {
			return $Model->add ( $info );
		} else {// 批量插入
			return $Model->addAll ( $info );
		}
		return false;
	}
	
	/**
	 * 添加订单数据
	 */
	public function addOrder($orderInfo) {
		
		if(is_array($orderInfo) && $orderInfo['user_id']) {
			if(!$orderInfo['pid']){
				//下订单前检测这个用户有没有分配跑堂，如果分配了 则写入订单数据
				$model = M('Boss_waiter_member');
				$res = $model -> field ('pid') -> where('kid ='.$orderInfo['user_id']) -> find();
			    if(isset($res['pid']) && $res['pid']>0) {
			    	$orderInfo['pid']=$res['pid'];
			    	$orderInfo['pname']=$orderInfo['user_name'];
			    }
			}
			$orderModel = M ( 'order' );
			$orderId = $orderModel->add ( $orderInfo );
			return $orderId;
		}
		
	}
}