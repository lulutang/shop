<?php
/**
 * 后台用户订单控制类
 *
 * @author tangll
 *
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\UserOrderModel;
use Page\Page;
use Think\Upload;

include (COMMON_PATH . "/Class/Page.class.php");

class UserOrderController extends Controller {
	/**
	 * 初始化
	 */
	public function _initialize() {
		header("content-type:text/html;charset=utf8");
		$uid = session("userid");
		if(empty($uid))
		{
            echo '<script>top.location.href="/Admin/Login/index";</script>';
            exit;
			
		}
	}
	
	/**
	 * 获取用户订单信息
	 * @param int p 第几页分页
	 * @param int pageSize 分页数据多少条
	 * @param int sort 订单生成排序 1 desc 0 asc
	 * @param int sort_pay 按照支付时间排序 2 desc 3 asc
	 */
	public function Orders() {
		$get = I ( 'get.' );
		
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		$sort_pay = isset ( $post ['sort_pay'] ) ? intval ( $post ['sort_pay'] ) : 2;
		
		$model = new UserOrderModel ();
		$orderInfo = $model->getUserOrderInfo ( $page, $pageSize, array ('status' => 1), $sort, $sort_pay, 1);
	
		$pageModel = new Page ( $orderInfo ['count'] );
		$pages = $pageModel->show ();
		
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sort_pay', $sort_pay );
		$this->assign ( 'sort', $sort );
		$this->assign ( 'dayCount', $orderInfo ['dayCount'] );
		$this->assign ( 'orders', $orderInfo ['orders'] );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'count', $orderInfo ['count'] );
		$this->assign ( 'goods_number', $orderInfo ['goods_number'] );
		
		$this->display ( 'orders' );
	}
	
	/**
	 * 搜索
	 * @param string key 搜索类型
	 * @param string zffs 支付方式
	 * @param string keywords 搜索关键字
	 * @param string buy_timeb 搜索支付开始时间
	 * @param string buy_timeend 搜索结束结束时间
	 * @param float money 搜索开始金钱
	 * @param float money_end 搜索结束金钱
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 * @param int sort 订单排序 1 desc 0 asc
	 * @param int sort_pay 按照支付时间排序  2 desc 3 asc
	 */
	public function search() {
		$post = I ( 'get.' );
		$key = $post ['key'];
		$zffs = $post ['zffs'];
		$keywords = $post ['keywords'];
		$buy_timeb = str_replace('+', ' ', $post ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $post ['buy_timeend']);
		$money = isset ( $post ['money'] ) ? floatval ( $post ['money'] ) : '';
		$money_end = isset ( $post ['money_end'] ) ? floatval ( $post ['money_end'] ) : '';
		$pageSize = isset ( $post ['pageSize'] ) ? intval ( $post ['pageSize'] ) : 10;
		$page = isset ( $post ['p'] ) ? intval ( $post ['p'] ) : 1;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		$sort_pay = isset ( $post ['sort_pay'] ) ? intval ( $post ['sort_pay'] ) : 2;
		
		$model = new UserOrderModel ();
		$where = array ();
		
		// 顶级业务id 1：商标服务 5：专利服务 6：版权服务
		$where ['status'] = 1;
		
		if ($zffs && $zffs == '线下支付') {
			$where ['pay_type'] = 0;
		} else if($zffs && $zffs == '线上支付'){
			$where ['pay_type'] = array (
					'neq',
					0 
			);
		}
		if(isset($key) && $key != '关键词'){
			if (! empty ( $keywords ) && $keywords != '请输入订单编号、用户昵称 进行模糊搜索') {
				if ($key == '用户昵称') // 用户名称
					$where ['user_name'] = array ('like','%' . $keywords . '%');
				else if($key == '订单编号')// 订单编号
					$where ['order_card'] = array ('like','%' . $keywords . '%');
				else
					$where ['phone'] = array ('like','%' . $keywords . '%');	
			}
		}
		
		if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
			$where ['pay_time'] = array (
					'between',
					strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend ) 
			);
		} else if (! empty ( $buy_timeb )) {
			$where ['pay_time'] = array (
					'egt',
					strtotime ( $buy_timeb ) 
			);
		} else if (! empty ( $buy_timeend ))
			$where ['pay_time'] = array (
					'elt',
					strtotime ( $buy_timeend ) 
			);
		if (floatval($money) > 0 || floatval($money_end) > 0 ) { // 订单编号
			if (! empty ( $money ) && ! empty ( $money_end )) {
				$where ['totalprice'] = array (
						'between',
						array($money,$money_end) 
				);
			} else if (! empty ( $money )) {
				$where ['totalprice'] = array (
						'egt',
						$money 
				);
			} else if (! empty ( $money_end ))
				$where ['totalprice'] = array (
						'elt',
						$money_end 
				);
		}
		$cartInfo = $model->getUserOrderInfo ( $page, $pageSize, $where, $sort,$sort_pay,1);
		$pageModel = new Page ( $cartInfo ['count'] );
		$pages = $pageModel->show ();
		
		$this->assign ( 'keywords', $keywords );
		if ($money > 0)
			$this->assign ( 'money', $money );
		if ($money_end > 0)
			$this->assign ( 'money_end', $money_end );
		$this->assign ( 'sort', $sort );
		$this->assign ( 'sort_pay', $sort_pay );
		$this->assign ( 'zffs', $zffs );
		$this->assign ( 'key1', $key );
		$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
		$this->assign ( 'dayCount', $cartInfo ['dayCount'] );
		$this->assign ( 'orders', $cartInfo ['orders'] );
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sum', $cartInfo ['sum'] );
		$this->assign ( 'count', $cartInfo ['count'] );
		$this->assign ( 'goods_number', $cartInfo ['goods_number'] );
		
		$this->display ( 'orders' );
	}
	
	/**
	 * 线下支付订单管理
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 */
	public function OfflinePayment() {
		$get = I ( 'get.' );
		
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		
		$model = new UserOrderModel ();
		$where = 'pay_type = 0 and status = 1';
		$orderInfo = $model->getUserOrderInfo ( $page, $pageSize, $where );
		$this->assign ( 'orders', $orderInfo ['orders'] );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'count', $orderInfo ['count'] );
		$this->display ( 'offlinePayment' );
	}
	
	/**
	 * 上传凭据 修改订单状态  暂时不使用
	 * @param int order_id 订单id
	 * @param string zhanghu 账户
	 * @param float money 支付金额
	 */
	public function updateOfflinePayment() {
		$post = I ( 'post.' );
		$orderId = $post ['order_id'];
		$bank = $post ['zhanghu'];
		$money = $post ['money'];
		if($money<=0)
			echo '<script>alert("请输入正确数据");</script>';
		
		if (! empty ( $orderId ) && is_numeric ( $money ) && $bank && $_FILES ['pingju']['name'] != '') {
			$config = array (
					'maxSize' => 3145728,
					'savePath' => './../uploads/Public/Uploads/pingju',
					'saveName' => array (
							'uniqid',
							'' 
					),
					'exts' => array (
							'jpg',
							'gif',
							'png',
							'jpeg' 
					),
					'autoSub' => true,
					'subName' => array (
							'date',
							'Ymd' 
					) 
			);
			$upload = new Upload ( $config ); // 实例化上传类
			$info = $upload->uploadOne ( $_FILES ['pingju'] );
			
			$proofModel = M ( 'order_proof' );
			
			$proofModel->add ( array (
					'img_name' => $info ['name'],
					'img_path' => $info ['savepath'],
					'img_savename' => $info ['savename'],
					'order_id' => $orderId,
					'bank' => $bank,
					'createtime' => time (),
					'user_id' => 1,
					'user_name' => 'XXX' 
			) );
			
			$m = M ( 'order' );
			$m->where ( 'order_id =' . $orderId )->save ( array (
					'status' => 1,
					'pay_time' => time (),
					'pay_money' => $money 
			) );
		} else {
			echo '<script>alert("请输入正确数据")</script>';
		}
		
		$page = isset ( $get ['page'] ) ? intval ( $get ['page'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		
		$model = new UserOrderModel ();
		$where = 'pay_type = 0 and status = 0';
		$orderInfo = $model->getUserOrderInfo ( $page, $pageSize, $where );
		
		$this->assign ( 'orders', $orderInfo ['orders'] );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'count', $orderInfo ['count'] );
		$this->display ( 'offlinePayment' );
	}
	
	/**
	 * 搜索线下支付订单管理 暂时不使用
	 * @param string key 搜索类型
	 * @param string ywlx 业务类型
	 * @param string keywords 搜索关键字
	 * @param string buy_timeb 搜索开始时间
	 * @param string buy_timeend 搜索结束时间
	 * @param float money 搜索开始金钱
	 * @param float money_end 搜索结束金钱
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 */
	public function searchOfflinePayment() {
		$post = I ( 'get.' );
		
		$ywlx = $post ['ywlx'];
		$key = $post ['key'];
		$keywords = $post ['keywords'];
		$buy_timeb = str_replace('+', ' ', $post ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $post ['buy_timeend']);
		$money = isset ( $post ['money'] ) ? floatval ( $post ['money'] ) : '';
		$money_end = isset ( $post ['money_end'] ) ? floatval ( $post ['money_end'] ) : '';
		$pageSize = isset ( $post ['pageSize'] ) ? intval ( $post ['pageSize'] ) : 10;
		$page = isset ( $post ['p'] ) ? intval ( $post ['p'] ) : 1;
		
		$model = new UserOrderModel ();
		$where = array ();
		
		$where ['pay_type'] = 0;
		$where ['status'] = 1;
		
		// 顶级业务id 1：商标服务 5：专利服务 6：版权服务
		if ($ywlx) {
			switch ($ywlx) {
				case '北京银行' :
					$type = 1;
					break;
				case '中国工商银行' :
					$type = 2;
					break;
			}
			if($type>0) {
				$where['pay_bank'] = $type;
			}
		}
		
		if(isset($key) && $key != '关键词'){
			if (! empty ( $keywords ) && $keywords != '请输入订单编号、用户昵称、用户联系方式') {
				if ($key == '用户昵称') // 用户名称
					$where ['user_name'] = array ('like','%' . $keywords . '%');
				else if($key == '订单编号')// 用户昵称
					$where ['order_card'] = array ('like','%' . $keywords . '%');
				else
					$where ['phone'] = array ('like','%' . $keywords . '%');	
			}
		}
		if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
			$where ['createtime'] = array (
					'between',
					strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend ) 
			);
		} else if (! empty ( $buy_timeb )) {
			$where ['createtime'] = array (
					'egt',
					strtotime ( $buy_timeb ) 
			);
		} else if (! empty ( $buy_timeend ))
			$where ['createtime'] = array (
					'elt',
					strtotime ( $buy_timeend ) 
			);
		
		if ($money>0 || $money_end>0) { // 订单编号
			if (! empty ( $money ) && ! empty ( $money_end )) {
				$where ['totalprice'] = array (
						'between',
						$money . ',' . $money_end 
				);
			} else if (! empty ( $money )) {
				$where ['totalprice'] = array (
						'egt',
						$money 
				);
			} else if (! empty ( $money_end ))
				$where ['totalprice'] = array (
						'elt',
						$money_end 
				);
		}
		$orderInfo = $model->getUserOrderInfo ( $page, $pageSize, $where );
		$pageModel = new Page ( $orderInfo ['count'] );
		$pages = $pageModel->show ();
		
		$this->assign ( 'ywlx', $ywlx );
		$this->assign ( 'key1', $key );
		$this->assign ( 'keywords', $keywords );
		if ($money > 0)
			$this->assign ( 'money', $money );
		if ($money_end > 0)
			$this->assign ( 'money_end', $money_end );
		
		$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
		$this->assign ( 'dayCount', $orderInfo ['dayCount'] );
		$this->assign ( 'orders', $orderInfo ['orders'] );
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'count', $orderInfo ['count'] );
		
		$this->display ( 'offlinePayment' );
	}
	
	/**
	 * 订单商品管理
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 * @param int sort 排序 1 desc 0 asc
	 */
	public function OrdersGoods() {
		// $cache = S('admin_user_info');
		$get = I ( 'get.' );
		
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		$sort = isset ( $get ['sort'] ) ? intval ( $get ['sort'] ) : 1;
		
		$model = new UserOrderModel ();
		
		$orderInfo = $model->getOrdersGoods ($page, $pageSize, array('is_pay' => 1), $sort);
	
		$yewu = $model->getYewu ( '0' );
		$pageModel = new Page ( $orderInfo ['count'] );
		$pages = $pageModel->show ();
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sort', $sort );
		$this->assign ( 'count', $orderInfo ['count'] );
		$this->assign ( 'orders', $orderInfo ['goods'] );
		$this->assign ( 'dayCount', $orderInfo ['dayCount'] );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'yewu', $yewu );
		$this->display ( 'ordersGoods' );
	}
	
	/**
	 * 搜索订单商品管理
     * @param string key 搜索类型
	 * @param string ywlx 业务类型
	 * @param string erjiywlx 二级业务类型
	 * @param string keywords 搜索关键字
	 * @param string buy_timeb 搜索开始时间
	 * @param string buy_timeend 搜索结束时间
	 * @param float money 搜索开始金钱
	 * @param string zhuangtai 服务状态  6 服务已结束 7 服务已开始
	 * @param float money_end 搜索结束金钱
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 */
	public function SearchOrdersGoods() {
		$post = I ( 'get.' );
		$page = isset ( $post ['p'] ) ? intval ( $post ['p'] ) : 1;
		$pageSize = isset ( $post ['pageSize'] ) ? intval ( $post ['pageSize'] ) : 10;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		$ywlx = $post ['ywlx'];
		$key = $post ['key'];
		$erjiywlx = $post ['erjiywlx'];
		$keywords = $post ['keywords'];
		$buy_timeb = str_replace('+', ' ', $post ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $post ['buy_timeend']);
		$zhuangtai = $post ['zhuangtai'];
		$pageSize = isset ( $post ['pageSize'] ) ? intval ( $post ['pageSize'] ) : 10;
		$page = isset ( $post ['p'] ) ? intval ( $post ['p'] ) : 1;
		$model = new UserOrderModel ();
		$where ['is_pay'] = 1;
		if (! empty ( $erjiywlx ) && $erjiywlx != '请选择') {
			$arr = explode ( '_', $erjiywlx );
			$erjiywlxId = reset ( $arr );
			$where ['yiji'] = $erjiywlx = end ( $arr );
		}
		if (! empty ( $ywlx ) && $ywlx != '请选择') {
			$arr = explode ( '_', $ywlx );
			$where ['style'] = reset ( $arr );
			$ywlx = end ( $arr );
			
			$yewuErji = $model->getYewu ( $where ['style'] );
			$this->assign ( 'yewuErji', $yewuErji );
		}
		
		
		if(isset($key) && $key != '关键词'){
			if (! empty ( $keywords ) && $keywords != '请输入订单编号、用户昵称、用户联系方式') {
				if ($key == '用户昵称') // 用户名称
					$where ['user_name'] = array ('like','%' . $keywords . '%');
				else if($key == '订单编号')// 用户昵称
					$where ['order_code'] = array ('like','%' . $keywords . '%');
				else
					$where ['phone'] = array ('like','%' . $keywords . '%');	
			}
		}
		
		if ($zhuangtai && $zhuangtai == '服务已开始') {
			$where ['status'] = '7';
		} elseif($zhuangtai && $zhuangtai == '服务已结束'){
			$where ['status'] = '6';
		}elseif($zhuangtai && $zhuangtai == '未操作'){
			$where ['status'] = '0';
		}
		
		
		if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
			$where ['addtime'] = array (
					'between',
					strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend ) 
			);
		} else if (! empty ( $buy_timeb )) {
			$where ['addtime'] = array (
					'egt',
					strtotime ( $buy_timeb ) 
			);
		} else if (! empty ( $buy_timeend ))
			$where ['addtime'] = array (
					'elt',
					strtotime ( $buy_timeend ) 
			);
		$orderInfo = $model->getOrdersGoods ( $page, $pageSize, $where, $sort);
		$yewu = $model->getYewu ( '0' );
		
		$pageModel = new Page ( $orderInfo ['count'] );
		$pages = $pageModel->show ();
	
		$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
		$this->assign ( 'ywlx', $ywlx );
		$this->assign ( 'key1', $key );
		$this->assign ( 'erjiywlx', $erjiywlx );
		$this->assign ( 'keywords', $keywords );
		$this->assign ( 'count', $orderInfo ['count'] );
		$this->assign ( 'dayCount', $orderInfo ['dayCount'] );
		$this->assign ( 'orders', $orderInfo ['goods'] );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'yewu', $yewu );
		$this->assign ( 'erjiywlxId', $erjiywlxId );
		$this->assign ( 'zhuangtai', $zhuangtai );
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sort', $sort );
		$this->display ( 'ordersGoods' );
	}
	
	/**
	 * 根据传来的id获取二级业务ID
	 * @param int yewu_id 业务id
	 * @return json
	 */
	public function ajaxGetYewu() {
		$get = I ( 'get.' );
		$yewu_id = $get ['yewu_id'];
		$model = new UserOrderModel ();
		$yewu = $model->getYewu ( $yewu_id );
		
		echo json_encode ( $yewu );
	}
	
	/**
	 * 获取订单 
	 * @param int order_id 订单
	 * @return json
	 */
	public function ajaxGetOrder() {
		$get = I ( 'get.' );
		$orderId = $get ['order_id'];
		$page = 1;
		$pageSize = 100;
		
		$model = new UserOrderModel ();
		$cartInfo = $model->getOrderInfo ( $orderId );
		
		$info = $cartInfo ['order'];
		$order_username = '';
		$img = '';
		$path = '';
		$bank = '';
		$youhui = $info ['onsale_money'];
		$num = $info ['goods_number'];
		if ($info ['pay_type'] == 0) {
			$order_user = M ( 'order_proof' );
			$order_user_info = $order_user->where ( array (
					'order_id' => $orderId 
			) )->find ();
			$order_username = $order_user_info ['user_name'];
			$bank = $order_user_info ['bank'];
			$img = $order_user_info ['img_savename'];
			$path = $order_user_info ['img_path'];
		}
		
		echo json_encode ( array (
				'num' => $num,
				'youhui' => $youhui,
				'path' => $path,
				'img' => $img,
				'bank' => $bank,
				'userName' => $order_username,
				'pay_type' => $info ['pay_type'],
				'status' => $info ['status'],
				'pay_time' => $info ['pay_time'],
				'createtime' => $info ['createtime'],
				'code' => $info ['order_card'],
				'trade_no' => $info ['trade_no'],
				'carts' => $cartInfo ['goods'] 
		) );
	}
	
	/**
	 * 未支付订单处理
	 * @param int sort 排序  
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 */
	public function NotPayOrder() {
		$get = I ( 'get.' );
		
		$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
		$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		
		$model = new UserOrderModel ();
		$orderInfo = $model->getUserOrderInfo ( $page, $pageSize, array ('status' => 0), $sort );
		
		$pageModel = new Page ( $orderInfo ['count'] );
		$pages = $pageModel->show ();
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sort', $sort );
		$this->assign ( 'dayCount', $orderInfo ['dayCount'] );
		$this->assign ( 'orders', $orderInfo ['orders'] );
		$this->assign ( 'sum', $orderInfo ['sum'] );
		$this->assign ( 'count', $orderInfo ['count'] );
		$this->assign ( 'goods_number', $orderInfo ['goods_number'] );
		$this->display ( 'notPay_orders' );
	}
	
	/**
	 * 搜索未支付订单
	 * @param string key 搜索类型
	 * @param string keywords 搜索关键字
	 * @param string buy_timeb 搜索开始时间
	 * @param string buy_timeend 搜索结束时间
	 * @param float money 搜索开始金钱
	 * @param float money_end 搜索结束金钱
	 * @param int pagesize 分页条数
	 * @param int p 分页
	 * @param int sort 1 desc 0 asc
	 */
	public function NotPaysearch() {
		$post = I ( 'get.' );
		$keywords = $post ['keywords'];
		$key = $post ['key'];
		$buy_timeb = str_replace('+', ' ', $post ['buy_timeb']) ;
		$buy_timeend = str_replace('+', ' ', $post ['buy_timeend']);
		$money = isset ( $post ['money'] ) ? floatval ( $post ['money'] ) : '';
		$money_end = isset ( $post ['money_end'] ) ? floatval ( $post ['money_end'] ) : '';
		$pageSize = isset ( $post ['pageSize'] ) ? intval ( $post ['pageSize'] ) : 10;
		$page = isset ( $post ['p'] ) ? intval ( $post ['p'] ) : 1;
		$sort = isset ( $post ['sort'] ) ? intval ( $post ['sort'] ) : 1;
		$model = new UserOrderModel ();
		$where = array (
				'status' => 0 
		);
		if(isset($key) && $key != '关键词'){
			if (! empty ( $keywords ) && $keywords != '请输入订单编号、用户昵称、用户联系方式') {
				if ($key == '用户昵称') // 用户名称
					$where ['user_name'] = array ('like','%' . $keywords . '%');
				else if($key == '订单编号')// 用户昵称
					$where ['order_card'] = array ('like','%' . $keywords . '%');
				else
					$where ['phone'] = array ('like','%' . $keywords . '%');	
			}
		}
		
		if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
			$where ['createtime'] = array (
					'between',
					strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend ) 
			);
		} else if (! empty ( $buy_timeb )) {
			$where ['createtime'] = array (
					'egt',
					strtotime ( $buy_timeb ) 
			);
		} else if (! empty ( $buy_timeend ))
			$where ['createtime'] = array (
					'elt',
					strtotime ( $buy_timeend ) 
			);
		
		if (! is_numeric ( $keywords )) { // 订单编号
			if (! empty ( $money ) && ! empty ( $money_end )) {
				$where ['totalprice'] = array (
						'between',
						$money . ',' . $money_end 
				);
			} else if (! empty ( $money )) {
				$where ['totalprice'] = array (
						'egt',
						$money 
				);
			} else if (! empty ( $money_end ))
				$where ['totalprice'] = array (
						'elt',
						$money_end 
				);
		}
		$cartInfo = $model->getUserOrderInfo ( $page, $pageSize, $where, $sort);
		$pageModel = new Page ( $cartInfo ['count'] );
		$pages = $pageModel->show ();
		
		$this->assign ( 'keywords', $keywords );
		if ($money > 0)
			$this->assign ( 'money', $money );
		if ($money_end > 0)
			$this->assign ( 'money_end', $money_end );
		
		$this->assign ( 'sort', $sort );
		$this->assign ( 'key1', $key );
		$this->assign ( 'zffs', $zffs );
		$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
		$this->assign ( 'dayCount', $cartInfo ['dayCount'] );
		$this->assign ( 'orders', $cartInfo ['orders'] );
		$this->assign ( 'pages', $pages );
		$this->assign ( 'sum', $cartInfo ['sum'] );
		$this->assign ( 'count', $cartInfo ['count'] );
		$this->assign ( 'goods_number', $cartInfo ['goods_number'] );
		$this->display ( 'notPay_orders' );
	}
	
	/**
	 * 订单商品批量修改状态
	 * @param string update_ids 需要更新的id
	 * @return json
	 */
	public function ajaxUpdateOrderGoods() {
		$get = I ( 'get.' );
		$update_ids = $get ['update_ids'];
		if (empty ( $update_ids ))
			echo json_encode ( array (
					'fail' => array (
							'desc' => '参数错误' 
					) 
			) );
		$action = '';
		if (isset ( $get ['type'] ) && $get ['type'] == 1) {
			$options = array (
					'status' => 7 
			);
			$action = '服务已开始';
		} else {
			$options = array (
					'status' => 6 
			);
			$action = '服务已结束';
		}
		$update_ids = substr ( $update_ids, 0, strlen ( $update_ids ) - 1 );
		$model = M ( 'order_goods' );
		$model->where ( 'id in (' . $update_ids . ' )' )->save ( $options );
		$userModel = new UserOrderModel ();
		$sql = $model->getLastsql ();
		$userModel->add_admin_log ( array (
				'user_id' => session('userid'),
				'user_name' => session('username'),
				'model' => 1,
                                'IP' => $this->getIP(),
				'action' => '订单商品修改' . $action,
				'description' => json_encode ( array (
						'sql' => $sql 
				) ) 
		) );
		echo json_encode ( array (
				'success' => array (
						'desc' => '操作成功' 
				) 
		) );
	}
	
	/**
	 * 获取客户端ip
	 */
	private function getIP() {
		 if (@$_SERVER ["HTTP_CLIENT_IP"])
			$ip = $_SERVER ["HTTP_CLIENT_IP"];
		else if (@$_SERVER ["REMOTE_ADDR"])
			$ip = $_SERVER ["REMOTE_ADDR"];
		else if (@getenv ( "HTTP_X_FORWARDED_FOR" ))
			$ip = getenv ( "HTTP_X_FORWARDED_FOR" );
		else if (@getenv ( "HTTP_CLIENT_IP" ))
			$ip = getenv ( "HTTP_CLIENT_IP" );
		else if (@getenv ( "REMOTE_ADDR" ))
			$ip = getenv ( "REMOTE_ADDR" );
		else if (@$_SERVER ["HTTP_X_FORWARDED_FOR"])
			$ip = $_SERVER ["HTTP_X_FORWARDED_FOR"];
		else
			$ip = "Unknown";
		return $ip;
	}

        
	/**
	 * ajax获得用户信息
	 * @param int user_id 用户id
	 * @return json
	 */
	public function ajaxGetUserInfo() {
		$get = I ( 'get.' );
		$userId = $get ['user_id'];
		if (empty ( $userId ))
			echo json_encode ( array (
					'fail' => array (
							'desc' => '参数错误' 
					) 
			) );
		$model = new UserOrderModel ();
		$userInfo = $model->getUserInfoById ( $userId );
		echo json_encode ( $userInfo );exit();
	}
	
	/**
	 * ajax获取订单商品需求
	 * @param int goods_id 商品id
	 */
	public function ajaxGetOrderInfo() {
		$get = I ( 'get.' );
		$update_ids = $get ['goods_id'];
		if (empty ( $update_ids ))
			echo json_encode ( array (
					'fail' => array (
							'desc' => '参数错误' 
					) 
			) );
		$model = new UserOrderModel ();
		$userInfo = $model->getOrderGoodsInfo ( $update_ids );
		
		echo json_encode ( $userInfo );exit();
	}
	
	/**
	 * 获取赠品
	 */
	public function ajaxGetZengping(){
		$get = I ( 'get.' );
		$goodsId = $get ['goods_id'];
		$goodsCode = $get ['goods_code'];
		$uid = $get ['uid'];
		
		$m = M('User_act as u');
		$res=$m ->field('u.order_code,u.user_name,u.addTime,g.short_title') ->join('LEFT join shop_goods as g on g.goods_id = u.goods_id')->
		where(array('u.act_goods_id'=>$goodsId, 'u.order_code'=>$goodsCode, 'u.uid'=>$uid)) -> select();
		echo json_encode(array('zeng'=>$res));
	}
	

}