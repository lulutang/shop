<?php
/**
 * 订单控制类
 *
 * @author tangll
 *
 */
namespace Home\Controller;
use Home\Model\UserModel;
use Think\Controller;
use Home\Model\PackageModel;
use Home\Model\ActivityModel;
use Home\Model\OnSaleModel;
use Home\Model\OrderModel;
use Home\Model\Goods_needModel;

class OrderController extends Controller {
	const ZHUCE = '法国注册';//需要跳转到详细需求信息的商品

	/**
	 * 购买优惠套餐服务信息页面
	 * @param int id 组合优惠商品id
	 * @display 组合商品页面
	 */
	public function packageInfo() {
		$id = I ( 'get.id' );
		$options = array (
				'package_id' => intval($id) 
		); // 组合查询条件
		$result = getPackageOne ( $options ); // 获得组合商品
		if($result[$id]['endtime'] <= time()) {//已结束的活动 跳转
		   	$this->assign ( 'message', '当前活动已到期' );
    		$this->display('Public/error');
    		exit();
		}
		$recommends = getRecommendShop (); // 获取推荐商品
		
		$session = session('user');
		$user_id = $session['user_id'];
		if($user_id){
			$price = $result[$id]['now_price'];
			$onSale = new OnSaleModel();
			$onSaleData = $onSale ->getUserOnsale(array('uid'=>$user_id));
			
			$onSaleArr = array();
			foreach ($onSaleData as $sale) {//过滤条件
		
				if($price >= $sale['is_where'] && $sale['startTime'] <= time() && $sale['endTime'] >= time()) {
					$onSaleArr [] = array('is_where' => $sale['is_where'], 'id' => $sale['id'], 'sale_money' => $sale['sale_money'], 'endTime'=> date('m月d日', $sale['endTime']));
				}
			}
			
			$this->assign ('onSaleArr', $onSaleArr);
		}
		
		$this->assign ( 'lists', $result );
		$this->assign ( 'recommends', $recommends );
		$this->display ( 'package' );
	}
	
	/**
	 * 购买服务信息页面
	 * @param int id 商品id
	 */
	public function goodsInfo() {
		$id = I ( 'get.id' );
		$num = I ( 'get.num' );
		$result = getGoodsInfo ( intval($id) ); 
		$num =  intval($num) >0 ? intval($num) : 1;
		if(empty($result)){
			$this->assign ('message', '商品不存在');
			$this->display('Public/error');
			exit();
		}
		if($num>50){
			$this->assign ('message', '购买的数量太多啦');
			$this->display('Public/error');
			exit();
		}
		if(isset($result[0]['status']) && $result[0]['status']!=1) {
			$this->assign ('message', '商品已下架');
			$this->display('Public/error');
			exit();
		}
		
		$actModel = new ActivityModel();
		$actRes = $actModel ->getActByGoodsInfo($id);
		if($actRes){
			foreach ($actRes as &$v){
				$v['is_zeng'] = 1;
			}
			$result =array_merge($result, $actRes);
		}
		$price = 0;
		foreach ($result as &$goods){
			if($goods['now_price'] && $goods['is_zeng'] != 1){
				$price +=$goods['now_price'];
				
			}else{
				$goods['now_price'] = 0;
			}
		}
		
		// 获取推荐商品
		$recommends = getRecommendShop ();
		
		//获取优惠卷
		$session = session('user');
		$user_id = $session['user_id'];
		if($num>1){ 
			$price = $price*$num;
		}
		
		if($user_id){
			$onSale = new OnSaleModel();
			$onSaleData = $onSale ->getUserOnsale(array('uid'=>$user_id));
			$onSaleArr = array();
			foreach ($onSaleData as $sale) {//过滤条件
				
				if($price >= $sale['is_where'] && $sale['startTime'] <= time() && $sale['endTime'] >= time()) {
					$onSaleArr [] = array('is_where' => $sale['is_where'], 'id' => $sale['id'], 'sale_money' => $sale['sale_money'], 'endTime'=> date('m月d日', $sale['endTime']));
				}
			}
			$this->assign ('onSaleArr', $onSaleArr);
		}
	
		$this->assign ('lists', $result);
		$this->assign ('num', $num);
		$this->assign ('price', $price);
		$this->assign ('recommends', $recommends);
		$this->assign ('goods_id', $id);
		$this->display ('goods');
	}
	
	/**
	 * 购买优惠服务填写详情页面
	 * @param int 组合商品id
	 */
	public function payApplyinfo() {
		$id = I ( 'get.id' );
		$onsaleId = I ( 'get.onsale' );//优惠卷的id
		// 组合查询条件
		$options = array (
				'package_id' => intval($id) 
		); 
		// 获得某一个组合商品
		$result = getPackageOne ( $options ); 
		
		if(empty($result)) {
			$this->assign ( 'message', '购买的组合不存在' );
			$this->display('Public/error');
			exit();
		}
		// 获取推荐商品
		$recommends = getRecommendShop (); 
		$addCart = array ();
		$goods = reset ( $result );
		
		if($goods['endtime'] <= time() || $goods['status'] != 1) {
			$this->assign ( 'message', '购买的组合已过期' );
			$this->display('Public/error');
			exit();
		}
		
		$goodsids = '';
		$session = session('user');
		$user_id = $session['user_id'];
		$user_name = $session['user_name'];
		$phone = $session['bind_mobile'];
		
		if(empty($user_id)) {
				$this->assign ( 'message', '请登录后再操作' );
				$this->display('Public/error');
				exit();
		}
		
		if ($goods ['goods']) {
			foreach ( $goods ['goods'] as $val ) {
				$goodsids .= $val ['goods_id'] . ',';
			}
			if ($goodsids) {
				$goodsids = substr ( $goodsids, 0, strlen ( $goodsids ) - 1 );
			}
		}
		$addCart = array (
				'userName' => $user_name,
				'user_id' => $user_id,
				'phone' => $phone,
				'type' => 0,
				'goods_id' => $goodsids,
				'package_id' => $goods ['package_id'],
				'code' => $goods ['package_code'],
				'short_title' => $goods ['short_title'],
				'title' => $goods ['title'],
				'now_price' => $goods ['now_price'],
				'old_price' => $goods ['old_price'],
				'thumb' => $goods ['thumb'] 
		);
		// 添加购物车
		$cart_ids = addCart ( $addCart ); 
		session('cart_ids', null);
		session('cart_ids',$cart_ids);	
	
		$this->assign ( 'onsaleId', $onsaleId );

		$this->assign ( 'lists', $result );
		$this->assign ( 'package_id', $id );
		$this->assign ( 'cart_id', $cart_ids );
		$this->assign ( 'recommends', $recommends );
		$this->assign ( 'self_style', C ( 'SELF_STYLE' ) );
		$this->display ( 'payPackageApplyinfo' );
	}
	
	/**
	 * 购买服务填写详情页面
	 * @param int goods_id 商品id
	 */
	public function payApplyGoodsinfo() {
		$id = I ( 'get.goods_id' );
		$num = I ( 'get.num' );
		$result = getGoodsInfo ( $id );
		
		if(!isset($result)) {
			$this->assign ( 'message', '数据异常，请稍后操作' );
			$this->display('Public/error');
			exit();
		}
		
		if(isset($result[0]['status']) && $result[0]['status']!=1) {
			$this->assign ( 'message', '商品已下架' );
			$this->display('Public/error');
			exit();
		}
		
		$num = $num > 1 ? intval($num) : 1;
		// 获取推荐商品
		$recommends = getRecommendShop (); 
		$session = session('user');
		$user_id = $session['user_id'];
		
		if(!isset($user_id)){
			$this->assign ( 'message', '请登录后再操作' );
			$this->display('Public/error');
			exit();
		}
		$user_name = $session['user_name'];
		$phone = $session['bind_mobile'];
		$addCart = array ();
		$goods = reset ( $result );
		
		$addCart = array (
				'userName' => $user_name,
				'user_id' => $user_id,
				'phone' => $phone,
				'type' => $goods ['server_pid'],
				'goods_id' => $id,
				'package_id' => '',
				'code' => $goods ['goods_code'],
				'short_title' => $goods ['short_title'],
				'title' => $goods ['title'],
				'now_price' => $goods ['now_price'],
				'old_price' => $goods ['old_price'],
				'thumb' => $goods ['thumb'] 
		);
		if($num==1){
			$cart_ids = addCart ( $addCart ); // 添加购物车
			
			session('cart_ids', null);
			session('cart_ids',$cart_ids);
		}
		
		

		$onsaleId = I ( 'get.onsale' );//优惠卷的id

		if($goods ['short_title'] == self::ZHUCE){
			$this->assign ( 'style', '1' );
		}else{
			$this->assign ( 'style', $goods['server_name'] );
		}

		
		$this->assign ( 'lists', $result );
		$this->assign ( 'onsaleId', $onsaleId );
		$this->assign ( 'server_pid', $addCart ['type'] );
		$this->assign ( 'goods_id', $id );
		$this->assign ( 'cart_ids', $cart_ids );
		$this->assign ( 'num', $num );
		$this->assign ( 'recommends', $recommends );
		$this->assign ( 'self_style', C ( 'SELF_STYLE' ) );
		
		$this->display ( 'payApplyinfo' );
	}
	
	/**
	 * 提交信息生成订单
	 * @param string type 来源 package ，cart 。普通
	 * @param int cart_ids 购物车id
	 */
	public function makeOrder() {
		$type = I ( 'get.type' );
		$cart_ids = session('cart_ids');
		$cid = I ( 'get.aid' );
		$onsaleId = I ( 'get.onsaleId' );
		$num = I ( 'get.num' );
		$num = $num >1 ? intval($num) : 1;

		$all = I ( 'get.all' )>0 ? intval(I ( 'get.all' )) : 0;
		if($all == 1) { //需要写完整信息的订单 
			return $this ->zhuceOne(I('get.'));
		}
		
		//促销活动的订单
		if($cid){
			if(DO_CUXIAO != 1){
				$this->assign ('message', '此活动已关闭');
				$this->display('Public/error');
				exit();
			}
			return $this->orderAct(I ( 'get.' ));
		}
		
		if(empty($cart_ids) && $type != 'cart' && $num == 1){
			$this->assign ( 'message', '购物车异常 请重新选择' );
			$this->display('Public/error');
			exit();
		}
		
		$session = session('user');
		$user_id = $session['user_id'];
		$user_name = $session['user_name'];
		$phone = $session['bind_mobile'];
		
		if(empty($user_id)){
			$this->assign ( 'message', '请登录后再操作' );
			$this->display('Public/error');
			exit();
		}
		
		if($onsaleId){
			$onsaleModel = new OnSaleModel();
			$onsaleInfo = $onsaleModel ->getOneOnsale(array('id' =>$onsaleId));
			if($onsaleInfo){
				$onsale_money =$onsaleInfo['sale_money'];
			}
		}
		
		if ($type == 'package') { // 商城优惠信息下单
			$text =  strip_tags(trim(I ( 'get.textarea' )));
			$package_id = I ( 'get.package_id' );
			// 组合查询条件
			$options = array (
					'package_id' => $package_id 
			); 
			$result = getPackageOne ( $options ); // 获得某一个组合商品
			if(empty($result)) {
				$this->assign ( 'message', '选择的优惠套餐不存在' );
				$this->display('Public/error');
				exit();
			}
			$goods = reset ( $result );
			if($goods['endtime'] < time() || $goods['status'] != 1) {
				$this->assign ( 'message', '优惠套餐组合已过期' );
				$this->display('Public/error');
				exit();
			}
			
			$goodsInfo = array (
					'package_id' => $goods ['package_id'],
					'package_code' => $goods ['package_code'],
					'short_title' => $goods ['short_title'],
					'title' => $goods ['title'],
					'description' => $goods ['description'],
					'now_price' => $goods ['now_price'],
					'old_price' => $goods ['old_price'],
					'thumb' => $goods ['thumb'] 
			);
			$goodsInfos = $goods ['goods'];
			$pack_goodsIds = array();
			if ($goodsInfos) {
				foreach ( $goodsInfos as $val ) {
					$pack_goodsIds[] = $val ['goods_id'];
					$goodsids .= $val ['goods_id'] . ',';
				}
				if ($goodsids) {
					$goodsids = substr ( $goodsids, 0, strlen ( $goodsids ) - 1 );
						
				}
			}
			
			$orderModel = M ( 'order' );
			$orderInfo = array (
					'order_card' => 'ZXRTC' . makeOrderCardId (), // 订单编号
					'goods_number' => count($pack_goodsIds),
					'totalprice' => $goodsInfo ['now_price']-$onsale_money, // 订单总额
					'onsale_money'=>$onsale_money,//优惠订单总额
					'onsale_id'=>$onsaleId,//优惠订单id
					'status' => 0, // 订单状态 关联ordermanage表id
					'createtime' => time (),
					'user_id' => $user_id, // 下订单人关联前台用户
					'user_name' => $user_name, // 下订单人关联前台用户
					'goods_id' => '', // 商品id
					'pay_type' => '', // 支付方式
					'is_invoile' => '', // 是否要发票
					'nvoile_title' => '', // 发票抬头
					'message' => json_encode ( array (
							'text' => $text,
							'short_title' => $goodsInfo ['short_title'] 
					) ), // 用户留言
					'is_prop' => 0, // 是否使用道具 如优惠卷
					'trade_no' => '', // 外部订单号
					'type' => 1, // 购买方式 1普通 0 限时
					'is_package' => 1, // 是否是优惠商品组合
					'package_id' => $package_id ,
					'phone' => $phone
					
			) ;// 组合商品id
					
			$orderId = $orderModel->add ( $orderInfo );
			
			if($onsaleInfo){
				$onsaleModel ->bindOneOnsale(array('id'=>$onsaleId),array('orderId'=>$orderId));
			}
			
			if(!$orderId) {
				$this->assign ( 'message', '生成订单异常，请重新操作' );
				$this->display('Public/error');
				exit();
			}
			foreach ( $pack_goodsIds as $gid ) {
				$goodsInfo = getGoodsInfo ( $gid ); // 获得某一个商品
				$goodsInfo = reset ( $goodsInfo );
					
				$order_goods [] = array (
						'message' => json_encode ( array (
									'text' => $text,
									'short_title' => $goodsInfo ['short_title'] 
									) ),
						'order_id' => $orderId,
						'user_id' => $user_id, // 下订单人关联前台用户
					    'user_name' => $user_name, // 下订单人关联前台用户
						'goods_id' => $goodsInfo ['goods_id'],
						'style' => $goodsInfo ['server_pid'],
						'yiji' => $goodsInfo ['now_servername'],
						'erji' => $goodsInfo ['attr_name'],
						'goods_price' => $goodsInfo ['now_price'],
						'goods_thumb' => $goodsInfo ['thumb'],
						'order_code' => $orderInfo ['order_card'],
						'goods_code' => $goodsInfo ['goods_code'],
						'addtime' => time (),
						'phone' => $phone,
						'cost' => $goodsInfo ['cost'] ,
						'service_price' =>$goodsInfo ['now_price'],
				);
			}
		
			$this->add_order_goods ( $order_goods, false );
			deleteCart($cart_ids);
			Header ( "Location: " . U ( '/home/order/pay?orderId=' . $orderId ) );
		} elseif ($type == 'cart') { // 从购物车直接生成订单
			$model = new OrderModel();
			$cartResult = $model->makeOrderByCart(I ( 'get.' ));
			die();
			Header ( "Location: " . U ( '/home/order/pay?orderId=' . $orderId ) );
			
			$package_ids = I ( 'get.package_ids' );
			$goods_ids = I ( 'get.goods_ids' );
			$godPrice = 0;
			
			$goodsModel = M('goods');
			if ($goods_ids) {
				$pack_goodsIds = explode ( ',', $goods_ids );
				foreach ($pack_goodsIds as $g){
					$gv = $goodsModel ->field('now_price')-> where ('goods_id = ('. $g .')') ->find();
					$godPrice += $gv['now_price'];
				}
			}
			$is_package = 0;
			$packageIds =array();
			if ($package_ids) {
				$is_package =1;
				$packageIds = explode ( ',', $package_ids );
				$pack_price = 0;
				
				foreach ( $packageIds as $package_id ) {
					$options = array (
							'package_id' => $package_id 
					); // 组合查询条件
					$result = getPackageOne ( $options ); // 获得某一个组合商品
					
					$goods = reset ( $result );
					if(empty($goods)) {
						$this->assign ( 'message', '所选的商品不存在' );
						$this->display('Public/error');
						exit();
					}
					if($goods['status'] != 1) {
						$this->assign ( 'message', '所选的优惠套餐里有已过期的商品' );
						$this->display('Public/error');
						exit();
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
					
					$pack_price += $goods ['now_price'];	
					$goodsInfos = $goods ['goods'];
					if ($goodsInfos) {
						$goodapp = '';
						foreach ( $goodsInfos as $val ) {
							$pack_goodsIds [] = $val ['goods_id'];
							$goodapp .= $val ['goods_id'].',';
						}
					}
				}
			}
			$message = array (
					'text' => '',
					'short_title' => '购物车组合商品' 
			);

			if(floatval($pack_price + $godPrice) <= 0) {
				$this->assign ( 'message', '订单金额异常，请重新操作' );
				$this->display('Public/error');
				exit();
			}
			$goods_id_num = explode(',', $goods_ids);
			$goods_id_nums = count($goods_id_num) + count($packageIds);
			$orderInfo = array (
					'order_card' => 'ZXRCART' . makeOrderCardId (), // 订单编号
					'goods_number' => $goods_id_nums,
					'totalprice' => $pack_price + $godPrice, // 订单总额
					'status' => 0, // 订单状态 关联ordermanage表id
					'createtime' => time (),
					'user_id' => $user_id, // 下订单人关联前台用户
					'user_name' => $user_name, // 下订单人关联前台用户
					'goods_id' => $goods_ids, // 商品id
					'pay_type' => '', // 支付方式
					'is_invoile' => '', // 是否要发票
					'nvoile_title' => '', // 发票抬头
					'message' => json_encode ( $message ), // 用户留言
					'is_prop' => 0, // 是否使用道具 如优惠卷
					'trade_no' => '', // 外部订单号
					'type' => 1, // 购买方式 1普通 0 限时
					'is_package' => $is_package, // 是否是优惠商品组合
					'package_id' => $package_ids ,
					'phone' => $phone
			); 

			if ($orderInfo) {
				$orderModel = M ( 'order' );
				foreach ( $pack_goodsIds as $gid ) {//先做判断
					$goodsInfo = getGoodsInfo ( $gid ); // 获得某一个商品
					$goodsInfo = reset ( $goodsInfo );
					if($goodsInfo['status'] != 1){
						$this->assign ( 'message', '该订单里有已下架的商品' );
						$this->display('Public/error');
						exit();
					}
				}
				
				$orderId = $orderModel->add ( $orderInfo );
				foreach ( $pack_goodsIds as $gid ) {
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

				$this->add_order_goods ( $order_goods, false );
			    deleteCartByCart($package_ids, $goods_ids);
			    Header ( "Location: " . U ( '/home/order/pay?orderId=' . $orderId ) );
			}
		} else { // 普通下单流程
			$text = strip_tags(trim(I ( 'get.textarea' ))) ;
			$goods_id = I ( 'get.goods_id' );
			
			$goodsInfo = getGoodsInfo ( $goods_id ); // 获得某一个商品
			$goodsInfo = reset ( $goodsInfo );
			
			if(empty($goodsInfo)) {
				$this->assign ( 'message', '商品异常，请重新操作' );
				$this->display('Public/error');
				exit();
			}
			if($goodsInfo['status'] != 1) {
				$this->assign ( 'message', '商品已下架' );
				$this->display('Public/error');
				exit();
			}
			$message = array (
					'text' => $text,
					'short_title' => $goodsInfo ['short_title'] !='' ? $goodsInfo ['short_title'] : ''
			);
			// 顶级业务id 1：商标服务 5：专利服务 6：版权服务
			if ($goodsInfo ['server_name'] == '商标服务') {
				$bianhao = 'ZXRRS';
				$message ['name'] = I ( 'get.name' );
				$message ['style'] = I ( 'get.style' );
			} else if ($goodsInfo ['server_name'] == '专利服务') {
				$bianhao = 'ZXRIP';
				$message ['style'] = I ( 'get.style' );
				$message ['name'] = I ( 'get.name' );
			} else {
				$bianhao = 'ZXRCS';
			}
			$orderModel = M ( 'order' );
			if($goodsInfo ['now_price']<=0) {
				$this->assign ( 'message', '订单金额异常，请重新操作' );
				$this->display('Public/error');
				exit();
			}
			if($num>1) {
				$goods_id = array_fill(0, $num, $goods_id);
				$goods_id = implode(',', $goods_id);
			}
			$orderInfo = array (
					'order_card' => $bianhao . makeOrderCardId (), // 订单编号
					'goods_number' => $num,
					'totalprice' => ($goodsInfo ['now_price']*$num)-$onsale_money, // 订单总额
					'onsale_money'=>$onsale_money,//优惠订单总额
					'onsale_id'=>$onsaleId,//优惠订单id
					'status' => 0, // 订单状态 关联ordermanage表id
					'createtime' => time (),
					'user_id' => $user_id, // 下订单人关联前台用户
					'user_name' => $user_name, // 下订单人关联前台用户
					'goods_id' => $goods_id, // 商品id
					'pay_type' => '', // 支付方式
					'is_invoile' => '', // 是否要发票
					'nvoile_title' => '', // 发票抬头
					'message' => json_encode ( $message ), // 用户留言
					'is_prop' => 0, // 是否使用道具 如优惠卷
					'trade_no' => '', // 外部订单号
					'type' => 1 ,
					'phone' => $phone
			);
			
			$orderId = $orderModel->add ( $orderInfo );
			
			if($onsaleInfo){//绑定优惠卷
				$onsaleModel ->bindOneOnsale(array('id'=>$onsaleId),array('orderId'=>$orderId));
			}
			
			$order_shops = array (
					'message' => json_encode ( $message ),
					'user_id' => $user_id, // 下订单人关联前台用户
					'user_name' => $user_name, // 下订单人关联前台用户
					'order_id' => $orderId,
					'goods_id' => $goods_id,
					'goods_price' => $goodsInfo ['now_price'],
					'goods_thumb' => $goodsInfo ['thumb'],
					'style' => $goodsInfo ['server_pid'],
					'yiji' => $goodsInfo ['now_servername'],
					'erji' => $goodsInfo ['attr_name'],
					'order_code' => $orderInfo ['order_card'],
					'goods_code' => $goodsInfo ['goods_code'],
					'addtime' => time () ,
					'cost' => $goodsInfo ['cost'] ,
					'service_price' =>$goodsInfo ['now_price'],
					'phone' => $phone
			);
			
			if($num>1) {
				$order_shops = array_fill(0, $num, $order_shops);
				$this->add_order_goods ($order_shops);
			} else {
				$this->add_order_goods ($order_shops, 1);
			}
	
			Header ( "Location: " . U ( '/home/order/pay?orderId=' . $orderId ) );
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
		$Model = M ( 'order_goods' );
		// 单条插入
		if ($type) {
			return $Model->add ( $info );
		} else {// 批量插入
			return $Model->addAll ( $info );
		} 
			return false;
	}
	
	/**
	 * 下订单时 如果有赠品的 则写入赠品数据表中
	 * @param int $actId 活动id
	 */
	private function addActGoods($orderId, $goodsId, $uid, $userName, $order_code) {
		$giftModel = M('act_gift');
		$res = $giftModel -> field('act_id, goods_id, giftid') -> where(array('goods_id in('. $goodsId .')')) -> select();
	
		if(!isset($res)) {
			return false;
		}
		$actModel = new ActivityModel();
		$actIds = array();
		$goodsInfo = array();
		$userInfo =array();
		
		$uActModel = m('User_act');
	
		foreach ($res as $aid) {
			$actInfo = $actModel->getActInfo($aid['act_id']);
			//setp1 首先查看 限购个数
			$num = $uActModel ->where('uid ='.$uid.' AND act_id='.$aid['act_id'])->sum('num');
			$num = $num>0?$num:0;
			if(!empty($actInfo['act_quoto']) && $num >= $actInfo['act_quoto'] ){
				continue;
			}
			
			//setp2 如果是赠品活动 并且时间也是正确的
			if($actInfo['act_type'] == 1  && $actInfo['act_endtime'] >= time() ) {

				//组合数据
				$goodsInfo[] = array('goods_id' => $aid['giftid'],
						    'order_code' => $order_code,
							'order_id'  => $orderId,
							'uid'=> $uid,
							'user_name'=> $userName,
							'status' => 1,
						    'num'=>1,
							'addTime' => time()
				);

				$actIds [] = $aid['act_id'];
			}
			//setp 3 插入数据
			if($actInfo['act_endtime'] >= time()){
				$userInfo [] = array(
						'goods_id' => $aid['giftid'],
						'act_goods_id' => $actInfo['act_goodsid'],
						'order_code' => $order_code,
						'uid'=> $uid,
						'user_name'=> $userName,
						'act_id' =>$aid['act_id'],
						'num' => 1,
						'act_type' => $actInfo['act_type'],
						'addTime' => time()
						
				);
			}
		}
		
		if(!empty($actIds)) {
			$actRes = $actModel -> addActPromotion($goodsInfo);
			$actIds = implode(',', $actIds);
			$giftModel -> where(array('act_id in('. $actIds .')')) -> setInc('number',1);
			$this->addUserActGoods($userInfo);
		}
		
		
	}
	
	/**
	 * 下订单时 如果有赠品的 则写入用户赠品数据表中
	 * @param int $actId 活动id
	 */
	private function addUserActGoods($userInfo) {
		if(!isset($userInfo)) {
			return false;
		}
		$m = M('User_act');
		$m->addAll($userInfo);
	
	}
	/**
	 * 支付方式
	 * @param int order_id 订单id
	 */
	public function pay() {
		$orderId = I ( 'get.orderId' );
		$model = M ( 'order' );
		$result = $model->field ( array (
				'totalprice',
				'order_card',
				'order_id',
				'message',
				'createtime' 
		) )->where ( array (
				'order_id' => $orderId 
		) )->find ();
		$user = session('user');
		if(isset($user['user_id'])) {
			$this->assign ('is_user', 1);
		} else {
			$this->assign ('is_user', 0);
		}
		
		//判断用户购买数量 1300的不做判断
		$actid = $result['message'] -> act >0 ? 1 : 0;
		if($actid >=0 && $result['totalprice'] < 1300){
			$userModel = m('Order');
			$num = $userModel ->where('user_id ='.$user_id.' AND status = 1 AND totalprice < 1300')->count();
			$num = $num>0?$num:0;
			if($num >= 1){
				$this->assign ( 'message', '该活动只能参加一次' );
				$this->display('Public/error');
				exit();
			}
		}
		
		$quaiqian = new KuaiqianController();
		$data = $quaiqian -> kuaiqianPay();	
		$message = json_decode ( $result ['message'] );
		$this->assign ('data', $data );
		
		$this->assign ('order', $result );
		$this->assign ('shanghu', C ( 'SELF_PAY_TYPE' ) );
		$this->assign ('short_title', $message->short_title);
		$this->display ('pay-pay');
	}
	
	/**
	 * 修改订单状态
	 * 
	 * @param string $orderId   订单ID
	 * @param int    $pay_type 	支付方式 0=>线下 1=>财付通 2=>银联 3=>支付宝
	 * @param float  $total_fee	订单总金额
	 */
	public function updateOrder($orderInfo) {
		if (! isset ( $orderInfo )) {
			return array (
					'fail' => array (
							'desc' => '订单参数信息错误',
							'code' => 1000 
					) 
			);
		}
		
		$model = M ( 'order' );
		$orderId = $orderInfo ['order_card'] ? $orderInfo ['order_card'] : '14181012175552561';
		$total_fee = $orderInfo ['total_fee'];
		$pay_type = $orderInfo ['pay_type'];
		
		$result = $model->field ( array (
				'totalprice',
				'user_id',
				'user_name',
				'order_card',
				'order_id',
				'status',
				'is_package',
				'package_id',
				'goods_id' ,
				'type',
				'message'
		) )->where ( array (
				'order_card' => $orderId 
		) )->find ();
		
		
		if (empty ( $result )) {// 数据空的返回空
			return array (
					'fail' => array (
							'desc' => '订单信息不存在',
							'code' => 1001 
					) 
			);
		}
		
		if ($result ['totalprice'] != $total_fee) {// 金额不一致返回空 这里以元为单位
			return array (
					'fail' => array (
							'desc' => '订单金额不一致',
							'code' => 1002 
					) 
			);
		}
		
		if ($result ['status'] == 1) {// 支付成功
			return array (
					'success' => array (
							'desc1' => '支付订单已处理且成功',
							'code' => 1003 
					) 
			);
		}
		
		if(!isset($result['user_id'])){
			return array (
					'fail' => array (
							'desc1' => '用户ID不存在',
							'code' => 1004
					)
			);
		}
		
		$goodsModel = M('order_goods');
		//修改订单商品里的状态
		$goodsModel -> where ( array (
							'order_id' => $result['order_id']
					) )->save ( array('is_pay' => 1, 'pay_time' =>time(), 'status' => 7, 'virt_status' =>5) );
		$data = array (
				'pay_money' => $total_fee,
				'pay_type' => $pay_type,
				'status' => 1,
				'trade_no'=>$orderInfo['trade_no'],
				'pay_time' => time () 
		);
		$model->where ( array (
				'order_card' => $orderId 
		) )->save ( $data ); 
		
	if($result['type'] == 2 ) {//是促销商品
			$message = json_decode($result['message']);
			$aid = isset($message -> act) ? $message -> act : 0;
			if($aid > 0) {
				$m = M('activity');
				$m -> where(array('act_id' => $aid)) -> setInc('act_purchase_amount',1);
				$actModel = new ActivityModel();
		
				$actInfo = $actModel->getActInfo($aid);
				if($actInfo['act_endtime'] >= time()){
					$userInfo [] = array(
							'goods_id' => '',
							'act_goods_id' => $actInfo['act_goodsid'],
							'order_code' => $orderId,
							'uid'=> $result['user_id'],
							'user_name'=> $result['user_name'],
							'act_id' =>$aid,
							'num' => 1,
							'act_type' => $actInfo['act_type'],
							'addTime' => time()
		
					);
					$this->addUserActGoods($userInfo);
				}
			}
		}

		//购买的组合商品加1
		if(isset($result['is_package']) && $result['is_package']== 1) {
			$packM = M('package');
			$packM -> where('package_id in ('.$result['package_id'].')') -> setInc('sales', 1);
		}
		//购买的商品加1
		if(isset($result['goods_id'])){
			$goodsM = M('goods');
			$goodsM -> where('goods_id in ('.$result['goods_id'].')') -> setInc('number', 1);	
		}
		$userModel = new UserModel();
		//增加用户消费的金额
		$userModel -> incUserInfo($result['user_id'], 'user_money', $total_fee);
		//插入是赠品的数据
		$this->addActGoods($result['order_id'], $result['goods_id'], $result['user_id'], $result['user_name'], $result['order_card']);
		
		//增加优惠卷
		$this->addOnsale($result['user_id'], $result['user_name'], $total_fee);
		if($result['onsale_id']){
			$onsaleModel = new OnSaleModel();
			$onsaleModel->bindOneOnsale(array('id'=>$result['onsale_id']), array('is_use'=>1,'use_time'=>time()));
		}
		return array (
				'success' => array (
						'desc1' => '支付订单成功',
						'code' => 1004 
				) 
		);
	}
	
	/**
	 * 修改订单状态
	 * @param string $orderId    订单ID
	 * @param int    $pay_type   支付方式 0=>线下 1=>财付通 2=>银联 3=>支付宝
	 * @param float  $total_fee  订单总金额
	 */
	public function ajaxUpdateOrder() {
		$get = I('get.');
		$order_card =$get['order_id'];
		$total_fee =$get['total_fee'];
		$orderInfo = array('pay_type' => 3, 'order_card' => $order_card, 'total_fee' => $total_fee);
		
		$model = M ( 'order' );
		$orderId = $orderInfo ['order_card'] ? $orderInfo ['order_card'] : '14181012175552561';
		$total_fee = $orderInfo ['total_fee'];
		$pay_type = $orderInfo ['pay_type'];
	
		$result = $model->field ( array (
				'totalprice',
				'user_id',
				'user_name',
				'order_card',
				'order_id',
				'status',
				'goods_id',
				'is_package',
				'package_id',
				'type',
				'message',
				'onsale_id'
		) )->where ( array (
				'order_card' => $orderId
		) )->find ();
		
		if (empty ( $result )) {// 数据空的返回空
			$json = array (
					'fail' => array (
							'desc' => '订单信息不存在',
							'code' => 1001
					)
			);
			echo json_encode($json);exit();
		}
	
		if ($result ['totalprice'] != $total_fee) {// 金额不一致返回空 这里以元为单位
			$json = array (
					'fail' => array (
							'desc' => '订单金额不一致',
							'code' => 1002
					)
			);
			echo json_encode($json);exit();
		}
 		if ($result ['status'] == 1) {// 支付成功
			$json = array (
					'success' => array (
							'desc1' => '支付订单已处理且成功',
							'code' => 1003
						)
			);
			echo json_encode($json);exit();
		} 
		if(!isset($result['user_id'])){
			$json = array (
				'fail' => array (
				'desc1' => '用户ID不存在',
				'code' => 1004
				)
			);			
		   echo json_encode($json);exit();
		}
	
		if(isset($result['is_package'])){//购买的组合商品加1
			$packM = M('package');
			$packM -> where('package_id in ('.$result['package_id'].')') -> setInc('sales',1);
		}
		
		if(isset($result['goods_id'])){//购买的商品加1
			$goodsM = M('goods');
			$goodsM -> where('goods_id in ('.$result['goods_id'].')') -> setInc('number',1);
		}
		$data = array (
				    'pay_money' => $total_fee,
					'pay_type' => $pay_type,
					'status' => 1,
					'pay_time' => time ()
					);
		$model->where ( array (
						'order_card' => $orderId
					) )->save ( $data ); // 使用锁功能 ?
		
		$goodsModel = M('order_goods');//修改订单商品里的状态
		$goodsModel -> where ( array (
							'order_id' => $result['order_id']
					) )->save ( array('is_pay' => 1, 'pay_time' =>time(), 'status' => 7, 'virt_status' =>5) );
		$userModel = new UserModel();//增加用户消费的金额
		$userModel -> incUserInfo($result['user_id'], 'user_money' , $total_fee);

		if($result['type'] == 2 ) {//是促销商品
			$message = json_decode($result['message']);
			$aid = isset($message -> act) ? $message -> act : 0;
			if($aid > 0) {
				$m = M('activity');
				$m -> where(array('act_id' => $aid)) -> setInc('act_purchase_amount',1);
				$actModel = new ActivityModel();
		
				$actInfo = $actModel->getActInfo($aid);
				if($actInfo['act_endtime'] >= time()){
					$userInfo [] = array(
							'goods_id' => '',
							'act_goods_id' => $actInfo['act_goodsid'],
							'order_code' => $orderId,
							'uid'=> $result['user_id'],
							'user_name'=> $result['user_name'],
							'act_id' =>$aid,
							'num' => 1,
							'act_type' => $actInfo['act_type'],
							'addTime' => time()
		
					);
					$this->addUserActGoods($userInfo);
				}
			}
		}
		//插入是赠品的数据
		$this->addActGoods($result['order_id'], $result['goods_id'], $result['user_id'], $result['user_name'], $result['order_card']);
		//增加优惠卷
		$this->addOnsale($result['user_id'], $result['user_name'], $total_fee);
		if($result['onsale_id']){
			$onsaleModel = new OnSaleModel();
			$onsaleModel->bindOneOnsale(array('id'=>$result['onsale_id']), array('is_use'=>1,'use_time'=>time()));
		}
		$json = array (
					'success' => array (
					'desc1' => '支付订单成功',
					'code' => 1004
							)
					);
		echo json_encode($json);exit();
	}
	
	/**
	 * 线下支付时发送短信
	 * @param int bank 选择线下银行
	 * @param string user_mobile 用户绑定的手机号
	 */
	public function ajaxSendSms() {
		$get = I('get.');
		$bank = $get['bank'];
		$userInfo = session('user');
		if(empty($userInfo['bind_mobile'])) {//@todo 验证手机号是否被验证
			echo 0;exit();
		}
		if($bank == 1) {
			$text = '户名：中技细软（北京）知识产权代理有限公司  账号：0200 0036 1920 1147 246  开户行：工行北京礼士路支行';
		}else{
			$text = '户名：中技细软（北京）知识产权代理有限公司 账号：0200 0036 1920 1147 246 开户行：工行北京礼士路支行';
		}
		//send_sms($userInfo['bind_mobile'], $text);
		echo $userInfo['bind_mobile'];exit();

	}
	
	/**
	 * ajax 验证session是否存在
	 * @return 0 失败 1开始 
	 */
	public function ajaxCheckSession(){
		$session = session('user');
		$user_id = $session['user_id'];
		if($user_id) {
			echo 1;exit;
		}else{
			echo 0;exit;
		}
	}
	
	/**
	 * 添加error log
	 * @param string $content
	 */
	private function log_result($content){
		$m = M('errorlog');
		$data['content'] = $content;
		$data['datetime'] = time();
		$m->add($data);
	}
	/**
	 * 支付成功
	 */
	public function pay_success(){
		$session = session('user');
		$user_id = $session['user_id'];
		$order_id = I('order_id');
		if(!$order_id){
			$this->assign ('message', '支付错误');
			$this->display('Public/error');
			exit();
		} 
		$m = M('order');
		$orderInfo = $m ->field('order_card,totalprice,pay_time,trade_no')->where('order_card = "'.$order_id .'" AND status=1')-> find();
		$this -> assign('order',$orderInfo);
		$this->display('pay_success');
	}
	/**
	 * 支付成功
	 */
	public function pay_successTest(){
		$session = session('user');
		$user_id = $session['user_id'];
		$order_id = I('order_id');
		if(!$order_id){
			$this->assign ('message', '支付错误');
			//$this->display('Public/error');
			//exit();
		}
		$m = M('order');
		$orderInfo = $m ->field('order_card, totalprice, pay_time, trade_no, onsale_money')->where('order_card = "'.$order_id .'" AND status=1')-> find();
		$this -> assign('order',$orderInfo);
		$this->display('pay_success');
	}
	/**
	 * 购买促销商品页面 
	 * @param int id 活动id
	 */
	public function actInfo() {
		if(DO_CUXIAO != 1){
			$this->assign ('message', '此活动已关闭');
			$this->display('Public/error');
			exit();
		}
		$id = I ( 'get.aid' );
		$model = new ActivityModel;
		$result = $model -> getActInfo ( intval($id) );
		
		if(empty($result)){
			$this->assign ('message', '促销商品不存在');
			$this->display('Public/error');
			exit();
		}
		//act_type=2 的才是能购买的
		if(isset($result['act_type']) && $result['act_type'] != 2) {
			$this->assign ('message', '赠品不能购买');
			$this->display('Public/error');
			exit();
		}
		//如果购买量大于库存量
		if(isset($result['act_purchase_amount']) && $result['act_purchase_amount'] >= $result['act_number']) {
			$this->assign ('message', '本期活动已结束');
			$this->display('Public/error');
			exit();
		}
		//如果购买时间大于活动结束时间
		if(isset($result['act_endtime']) && time() >= $result['act_endtime']) {
			$this->assign ('message', '本期活动已结束');
			$this->display('Public/error');
			exit();
		}
		//如果购买时间小于活动开始时间
		if(isset($result['act_starttime']) && time() < $result['act_starttime']) {
			$this->assign ('message', '本期活动未开始');
			$this->display('Public/error');
			exit();
		}
	

		$goodsId = $result['act_goodsid'];
		$goodsInfo = $this->getActGoodsInfo ( intval($goodsId), $result['act_goodsprice']);
		
		// 获取推荐商品
		$recommends = getRecommendShop ();
		$this->assign ('lists', $goodsInfo);
		$this->assign ('recommends', $recommends);
		$this->assign ('goods_id', $goodsId);
		$this->assign ('aid', $id);
		$this->display ('actGoods');
	}
	
	/**
	 * 获取促销商品信息
	 * @param int $goodsId
	 * @param float $price
	 * @return array
	 */
	private function getActGoodsInfo($goodsId, $price){
		$goodsInfo = getGoodsInfo ( intval($goodsId) );
		foreach ($goodsInfo as &$goods){
			if($goods['goods_id'] == $goodsId){
				$goods['now_price'] = $price;
			}
		}
		return $goodsInfo;
	}
	/**
	 * 购买促销申请
	 */
	public function payApplyAct(){
		
		if(DO_CUXIAO != 1){
			$this->assign ('message', '此活动已关闭');
			$this->display('Public/error');
			exit();
		}
		$id = I ( 'get.aid' );
		$model = new ActivityModel;
		$result = $model -> getActInfo ( intval($id) );
		
		if(empty($result)){
			$this->assign ('message', '促销商品不存在');
			$this->display('Public/error');
			exit();
		}
		//act_type=2 的才是能购买的
		if(isset($result['act_type']) && $result['act_type'] != 2) {
			$this->assign ('message', '赠品不能购买');
			$this->display('Public/error');
			exit();
		}
		//如果购买量大于库存量
		if(isset($result['act_purchase_amount']) && $result['act_purchase_amount'] >= $result['act_number']) {
			$this->assign ('message', '本期活动已结束');
			$this->display('Public/error');
			exit();
		}
		//如果购买时间大于活动结束时间
		if(isset($result['act_endtime']) && time() >= $result['act_endtime']) {
			$this->assign ('message', '本期活动已结束');
			$this->display('Public/error');
			exit();
		}
		//如果购买时间小于活动开始时间
		if(isset($result['act_starttime']) && time() < $result['act_starttime']) {
			$this->assign ('message', '本期活动未开始');
			$this->display('Public/error');
			exit();
		}
		
		$session = session('user');
		$user_id = $session['user_id'];
		
		if(!isset($user_id)){
			$this->assign ( 'message', '请登录后再操作' );
			$this->display('Public/error');
			exit();
		}
		$goodsId =  $result['act_goodsid'];
		//判断用户购买数量 1300的不做判断
		if($result['act_goodsprice'] < 1300){
			$userModel = m('Order');
			$num = $userModel ->where('user_id ='.$user_id.' AND status = 1 AND totalprice < 1300')->count();
			$num = $num>0?$num:0;
			if($num >= 1){
				$this->assign ( 'message', '该活动只能参加一次' );
				$this->display('Public/error');
				exit();
			}
		}

		$result = $this -> getActGoodsInfo ( $goodsId, $result['act_goodsprice']);
	
		// 获取推荐商品
		$recommends = getRecommendShop ();
	
		$this->assign ( 'lists', $result );
		$this->assign ( 'server_pid', $addCart ['type'] );
		$this->assign ( 'aid', $id );
		$this->assign ( 'goods_id', $goodsId );
		$this->assign ( 'cart_ids', '' );
		$this->assign ( 'recommends', $recommends );
		$this->assign ( 'style', $goods['server_name'] );
		$this->assign ( 'self_style', C ( 'SELF_STYLE' ) );
		
		$this->display ( 'payApplyinfo' );
	}
	
	/**
	 *促销商品的订单 
	 */
	private function orderAct($getInfo){
		$text = $getInfo['textarea']  ;
		$goodsId = $getInfo['goods_id'];
		$aid = $getInfo['aid'];
		$session = session('user');
		$user_id = $session['user_id'];
		$user_name = $session['user_name'];
		$phone = $session['bind_mobile'];
		
		$model = new ActivityModel;
		$result = $model -> getActInfo ( intval($aid) );
		$price = $result['act_goodsprice'];
		
		//act_type=2 的才是能购买的
		if(isset($result['act_type']) && $result['act_type'] != 2) {
			$this->assign ('message', '赠品不能购买');
			$this->display('Public/error');
			exit();
		}
		//如果购买量大于库存量
		if(isset($result['act_purchase_amount']) && $result['act_purchase_amount'] >= $result['act_number']) {
			$this->assign ('message', '本期活动已结束');
			$this->display('Public/error');
			exit();
		}
		//如果购买时间大于活动结束时间
		if(isset($result['act_endtime']) && time() >= $result['act_endtime']) {
			$this->assign ('message', '本期活动已结束');
			$this->display('Public/error');
			exit();
		}
		//如果购买时间小于活动开始时间
		if(isset($result['act_starttime']) && time() < $result['act_starttime']) {
			$this->assign ('message', '本期活动未开始');
			$this->display('Public/error');
			exit();
		}
		
		//判断用户购买数量 1300的不做判断
			if($result['act_goodsprice'] < 1300){
			$userModel = m('Order');
			$num = $userModel ->where('user_id ='.$user_id.' AND status = 1 AND totalprice < 1300')->count();
			$num = $num>0?$num:0;
			if($num >= 1){
				$this->assign ( 'message', '该活动只能参加一次' );
				$this->display('Public/error');
				exit();
			}
		}
		$goodsInfo = $this->getActGoodsInfo($goodsId, $price); // 获得某一个商品
	    $goodsInfo =reset($goodsInfo);
		if(empty($goodsInfo)) {
			$this->assign ( 'message', '商品异常，请重新操作' );
			$this->display('Public/error');
			exit();
		}
		if($goodsInfo['status'] != 1) {
			$this->assign ( 'message', '商品已下架' );
			$this->display('Public/error');
			exit();
		}
		$message = array (
				'text' => $text,
				'short_title' => $goodsInfo ['short_title'] !='' ? $goodsInfo ['short_title'] : ''
		);
		// 顶级业务id 1：商标服务 5：专利服务 6：版权服务
		if ($goodsInfo ['server_name'] == '商标服务') {
			$bianhao = 'ZXRRS';
			$message ['name'] = I ( 'get.name' );
			$message ['style'] = I ( 'get.style' );
		} else if ($goodsInfo ['server_name'] == '专利服务') {
			$bianhao = 'ZXRIP';
			$message ['style'] = I ( 'get.style' );
			$message ['name'] = I ( 'get.name' );
		} else {
			$bianhao = 'ZXRCS';
		}
		$orderModel = M ( 'order' );
		if($goodsInfo ['now_price']<=0) {
			$this->assign ( 'message', '订单金额异常，请重新操作' );
			$this->display('Public/error');
			exit();
		}
		$message ['act'] = $aid;//活动过来的 需要记录活动id 
		
		$orderInfo = array (
				'order_card' => $bianhao . makeOrderCardId (), // 订单编号
				'goods_number' => 1,
				'totalprice' => $goodsInfo ['now_price'], // 订单总额
				'status' => 0, // 订单状态 关联ordermanage表id
				'createtime' => time (),
				'user_id' => $user_id, // 下订单人关联前台用户
				'user_name' => $user_name, // 下订单人关联前台用户
				'goods_id' => $goodsId, // 商品id
				'pay_type' => '', // 支付方式
				'is_invoile' => '', // 是否要发票
				'nvoile_title' => '', // 发票抬头
				'message' => json_encode ( $message ), // 用户留言
				'is_prop' => 0, // 是否使用道具 如优惠卷
				'trade_no' => '', // 外部订单号
				'type' => 2 ,//活动方式购买
				'phone' => $phone
				
		);
			
		$orderId = $orderModel->add ( $orderInfo );
		$this->add_order_goods ( array (
				'message' => json_encode ( $message ),
				'user_id' => $user_id, // 下订单人关联前台用户
				'user_name' => $user_name, // 下订单人关联前台用户
				'order_id' => $orderId,
				'goods_id' => $goodsId,
				'goods_price' => $goodsInfo ['now_price'],
				'goods_thumb' => $goodsInfo ['thumb'],
				'style' => $goodsInfo ['server_pid'],
				'yiji' => $goodsInfo ['now_servername'],
				'erji' => $goodsInfo ['attr_name'],
				'order_code' => $orderInfo ['order_card'],
				'goods_code' => $goodsInfo ['goods_code'],
				'addtime' => time () ,
				'phone' => $phone,
				'cost' => $goodsInfo ['cost'] ,
				'service_price' =>$goodsInfo ['now_price'],
		), 1 );
		deleteCart($cart_ids);
		Header ( "Location: " . U ( '/home/order/pay?orderId=' . $orderId ) );

	}
	
	/**
	 * 获取注册的下级分类
	 */
	public function ajaxGetZhuceInfo(){
		$get = I('get.');
		if($get['type'] == 1) {
			 $arr = C ( 'SYS_ZHUCE' );
			 if($arr[$get['keyWord']] != '') {
			 	$result = $arr[$get['keyWord']];
			 	echo json_encode(array('success' => 1,'zuce' =>array_keys($result),'zuceOne'=>reset($result)));
			 }else{
			 	echo json_encode(array('success' => 0));
			 }
		}elseif ($get['type'] == 2){
			$arr = C ( 'SYS_ZHUCE' );
			if($arr) {
				$arr = array_values($arr);
				$res = array();
				//以下数据都可以加入缓存以减少计算增加的cpu
				foreach ($arr as $val){
					foreach ($val as $k => $v){
						$res[$k] = $v;
					}
				}
				$result = $res[$get['keyWord']];
				echo json_encode(array('success' => 1,'zuceOne'=>$result));
			}else{
				echo json_encode(array(
						'success' => 0 
				) );
			}
		}
	}
	
	/**
	 * 单独需要写注册信息的订单
	 * @param unknown $info
	 */
	private function zhuceOne($info) {

		$goods_id = I ( 'get.goods_id' );
		
		$goodsInfo = getGoodsInfo ( $goods_id ); // 获得某一个商品
		$goodsInfo = reset ( $goodsInfo );
		
		if (empty ( $goodsInfo )) {
			$this->assign ( 'message', '商品异常，请重新操作' );
			$this->display ( 'Public/error' );
			exit ();
		}
		if ($goodsInfo ['status'] != 1) {
			$this->assign ( 'message', '商品已下架' );
			$this->display ( 'Public/error' );
			exit ();
		}
		$message = array (
				'is_zhuce' => 1,
				'short_title' => $goodsInfo ['short_title'] != '' ? $goodsInfo ['short_title'] : '' 
		);
		// 顶级业务id 1：商标服务 5：专利服务 6：版权服务
		if ($goodsInfo ['server_name'] == '商标服务') {
			$bianhao = 'ZXRRS';
			$message ['name'] = I ( 'get.name' );
			$message ['style'] = I ( 'get.style' );
		} 
		$orderModel = M ( 'order' );
		if ($goodsInfo ['now_price'] <= 0) {
			$this->assign ( 'message', '订单金额异常，请重新操作' );
			$this->display ( 'Public/error' );
			exit ();
		}
		
		//计算次数
		$resultArr = $this ->getNum($info, $goodsInfo ['now_price']);
		
		if(!$resultArr){
			$this->assign ( 'message', '参数异常' );
			$this->display ( 'Public/error' );
			exit ();
		}
		
		$session = session('user');
		$user_id = $session['user_id'];
		$user_name = $session['user_name'];
		$phone = $session['bind_mobile'];
		
		$goods_number = $resultArr['goods_number'];
		$now_price = $resultArr['price'];
		
		$goods_ids = array();
		$goods_ids = array_pad($goods_ids, $goods_number, $goods_id);
		$goods_id_now  = implode(',', $goods_ids);
		
		$orderInfo = array (
				'order_card' => $bianhao . makeOrderCardId (), // 订单编号
				'goods_number' => $goods_number,
				'totalprice' => $now_price, // 订单总额
				'status' => 0, // 订单状态 关联ordermanage表id
				'createtime' => time (),
				'user_id' => $user_id, // 下订单人关联前台用户
				'user_name' => $user_name, // 下订单人关联前台用户
				'goods_id' => $goods_id_now, // 商品id
				'pay_type' => '', // 支付方式
				'is_invoile' => '', // 是否要发票
				'nvoile_title' => '', // 发票抬头
				'message' => json_encode ( $message ), // 用户留言
				'is_prop' => 0, // 是否使用道具 如优惠卷
				'trade_no' => '', // 外部订单号
				'type' => 1,
				'phone' => $phone 
		);
		
		$orderId = $orderModel->add ( $orderInfo );
		//查处来有需求id的数量
		$m = M('order_goods');
		$maxId = $m->field('max(need_id) as max')->find();
		$goodsInfos =array();
		$goodsNeedInfos =array();
		$i = (int)$maxId['max'] >0 ? (int)$maxId['max'] + 1 : 1;
		
		foreach ($resultArr['resultArr'] as $k1 => $goods) {
			$message ['style'] = $k1;
			$goodsInfos[] = array (
				'message' => json_encode ( $message ),
				'user_id' => $user_id, // 下订单人关联前台用户
				'user_name' => $user_name, // 下订单人关联前台用户
				'order_id' => $orderId,
				'goods_id' => $goods_id,
				'goods_price' => $goods ['price'],
				'goods_thumb' => $goodsInfo ['thumb'],
				'style' => $goodsInfo ['server_pid'],
				'yiji' => $goodsInfo ['now_servername'],
				'erji' => $goodsInfo ['attr_name'],
				'order_code' => $orderInfo ['order_card'],
				'goods_code' => $goodsInfo ['goods_code'],
				'addtime' => time (),
				'phone' => $phone ,
				'need_id'=> $i,
				'cost' => $goodsInfo ['cost'] ,
				'service_price' => $goods ['price'],
				'style_name' => $k1,
			);
			$goodsNeedInfos[] = array(
					'name' => $info['name'],
					'need_state' => is_array($info['need_state']) ? implode(',', $info['need_state']):'',
					'need_prior' => is_array($info['need_prior']) ? implode(',', $info['need_prior']):'',
					'area' => $info['area'],
					'need_time' => $info['need_time'],
					'need_number' => $info['need_number'],
					'style' => $k1,
					'style_part' => json_encode($goods),
					'state' => 0,
					'create_time' => time(),
					'goods_id' => $goods_id,
					'uid' => $user_id,
					'order_id' => $orderId,
					'user_name'=> $user_name,
					'phone' => $phone ,
					'subd' => $goods ['subd'],
					'subd_num' => $goods ['num'],
					'price' => $goods ['price'],
					'need_id' => $i,
			);
			$i++;
		}
		
		$addAll = $this->add_order_goods ($goodsInfos);
		$model = new Goods_needModel();
		$add = $model -> addInfo($goodsNeedInfos);
		deleteCart ( $cart_ids);
		Header ( "Location: " . U ( '/home/order/pay?orderId=' . $orderId ) );
	}
	
	/**
	 * 获取多个商品的数量及其价格
	 * @param array $info
	 * @param number $price
	 * @return boolean|multitype:number Ambigous <multitype:, number>
	 */
	private function getNum($info, $price = 0){
		$getInfo = array_keys($info);
		$fenlei =array();
		
		foreach ($getInfo as $keyInfo){
			if(substr_count($keyInfo, 'check') >= 1 && count($info[$keyInfo]) > 1) {
				$fenlei[$keyInfo] = $info[$keyInfo];
			}
		}
		
		if(empty($fenlei)) {
			return false;	
		}
		
		$resultArr = array();
		foreach ($fenlei as $lei){
			$leiVal = end($lei);
			$fenleiArr = explode('*', $leiVal);
			$onelei = end($fenleiArr);
			$erlei = reset($fenleiArr);
			unset($lei[count($lei)-1]);
			$resultArr[$onelei][$erlei][] = $lei;
			$resultArr[$onelei]['num'] += count($lei);
			$duoyu = 0;
			if($resultArr[$onelei]['num'] >= 10 ) {
				$duoyu = ($resultArr[$onelei]['num'] - 10) * 100;
			}
			$xiaoleiRes = '';
			foreach ($lei as $key => $xiaolei){
				$key += 1;
				$r = explode('、', $xiaolei);
				$xiaoleiRes .=  $key.'、'.end($r).';';
			}
			$xiaoleiRes = substr($xiaoleiRes, 0, strlen($xiaoleiRes)-1);
			$resultArr[$onelei]['subd'] = $xiaoleiRes;
			$resultArr[$onelei]['price'] = $price + $duoyu;
		}
	
		$priceZong =0;
		foreach ($resultArr as $val){
			$priceZong += $val['price'];
		}
	
		$array = array('price' => $price * count($resultArr) + $duoyu, 'goods_number' => count($resultArr), 'resultArr' => $resultArr);
		return $array;
	}
	/**
	 * 增加用户优惠卷
	 * @param int $uid 用户uid
	 * @param float $money 消费金额
	 */
	private function addOnsale($uid, $userName, $money){
		$model = new OnSaleModel();
		$list = $model->getOnsaleList();
		$result = array();
		foreach ($list as $val) { 
			if($money >= $val['sale']) {
				$result[$val['money']] = $val;
			}
		}
	
		if(empty($result)){
			return false;
		}
		$key = array_keys($result);
		rsort($key);
		$key = reset($key);
		$youhui = $result[$key];
		$startTime = $youhui['sale_startTime']>0?$youhui['sale_startTime']:'';
		$data = array('uid' => (int)$uid,
				'user_name' => $userName, 
				'name' => $youhui['name'],
				'sale_id' => $youhui['id'], 
				'sale_money' => $youhui['money'],
				'is_where' => $youhui['sale_where'],
				'startTime' => (int)$startTime,
				'endTime' =>(int)$youhui['sale_endTime'],
				'createTime' =>time(),
		);
		$model ->addUserOnsale($data);	
	}
	
	//删除订单
	public function deleteOrder(){
		$get = I('get.');
		$id = $get['id'];
		$id = intval($id);
		$model = new OrderModel();
		$flag = $model ->deleteOrder($id);
		if($flag) {
			echo 1;
		}else{
			echo 0;
		}
		exit();
	}
}