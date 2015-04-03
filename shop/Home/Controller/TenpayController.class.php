<?php
/**
 * 腾讯财付通控制类
 * @author tangll
 *
 */
namespace Home\Controller;
use Think\Controller;
use tenpayCenter\RequestHandler;
use tenpayCenter\ResponseHandler;
use tenpayCenter\TenpayHttpClient;
use tenpayCenter\ClientResponseHandler;
use Home\Controller\OrderController;
use Home\Model\ActivityModel;
include_once (APP_PATH.'Home/Common/classes/RequestHandler.class.php');
include_once (APP_PATH.'Home/Common/classes/ResponseHandler.class.php');
include_once (APP_PATH.'Home/Common/classes/client/TenpayHttpClient.class.php');
include_once (APP_PATH.'Home/Common/classes/client/ClientResponseHandler.class.php');
header("content-type:text/html;charset=utf8");
class TenPayController extends Controller {
	const PAY_TYPE = 1;
	 /**
     * 财付通支付下单
     */
    public function tenpay(){
    	$session = session('user');
    	if(!isset($session)) {
    		$this->assign ( 'message', '请登录后再操作' );
    		$this->display('Public/error');
    		exit();
    	}
    	$get = I('post.');
    	// 获取提交的订单号 
    	$out_trade_no = $get["order_no"];	
    	$orderModel = M('order');
    	$orderInfo = $orderModel -> where("user_id =".$session['user_id']." AND order_card = '". $out_trade_no."'") -> find();
    	if(! $orderInfo){
    		$this->assign ( 'message', '订单号异常' );
    		$this->display('Public/error');
    		exit();
    	}
    	$goods_ids = $orderInfo['goods_id'];
    	
    	$message = $orderInfo['message'];
    	
    	$aid = isset($message -> act) ? $message -> act : 0;
    	if($aid > 0) {
    		$model = new ActivityModel();
    		$actInfo = $model -> getActInfo ( intval($aid) );
    		
    		if(empty($actInfo)){
    			$this->assign ('message', '促销商品不存在');
    			$this->display('Public/error');
    			exit();
    		}
    		//act_type=2 的才是能购买的
    		if(isset($actInfo['act_type']) && $actInfo['act_type'] != 2) {
    			$this->assign ('message', '赠品不能购买');
    			$this->display('Public/error');
    			exit();
    		}
    		//如果购买量大于库存量
    		if(isset($actInfo['act_purchase_amount']) && $actInfo['act_purchase_amount'] >= $actInfo['act_number']) {
    			$this->assign ('message', '本期活动已结束');
    			$this->display('Public/error');
    			exit();
    		}
    		//如果购买时间大于活动结束时间
    		if(isset($actInfo['act_endtime']) && time() >= $actInfo['act_endtime']) {
    			$this->assign ('message', '本期活动已结束');
    			$this->display('Public/error');
    			exit();
    		}
    		//如果购买时间小于活动开始时间
    		if(isset($actInfo['act_starttime']) && time() < $actInfo['act_starttime']) {
    			$this->assign ('message', '本期活动未开始');
    			$this->display('Public/error');
    			exit();
    		}
    	}
    	
    	if($goods_ids) {
    		$goodsModel = M('goods');
    		$goodsInfo = $goodsModel -> field('status') -> where('goods_id in ('.$goods_ids.')') -> select();
    		foreach($goodsInfo as $val) {
    			if($val['status'] !=1 ){
    					$this->assign ( 'message', '您操作的订单里有已下架的商品' );
    					$this->display('Public/error');
    					exit();
    			}
    		}
    	}
    	
    	$package = $orderInfo['package_id'];
    	if($package) {
    		$goodsModel = M('package');
    		$goodsInfo = $goodsModel ->field('endtime,status')->where('package_id in ('.$package.')') -> select();
    	
    		foreach($goodsInfo as $val) {
    			if($val['endtime'] <= time() || $val['status'] != 1) {
    				$this->assign ( 'message', '您操作的订单里有已下架的套餐' );
    				$this->display('Public/error');
    				exit();
    			}
    		}
    	}
    
    	$message = json_decode($orderInfo['message']);
    	
    	/* 获取提交的商品名称 */
    	$product_name = $message -> short_title;
    	/* 获取提交的商品价格 */
    	$order_price = $orderInfo["totalprice"];
    	/* 获取提交的备注信息 */
    	$remarkexplain = $message -> text;
    	/* 支付方式 */
    	$trade_mode = 1;//1 默认 即时到账
    	$config = C('SELF_PAY_TYPE');
    	$strDate = date("Ymd");
    	$strTime = date("His");
    	
    	/* 商品价格（包含运费），以分为单位 */
    	$total_fee = $order_price*100;
    	
    	/* 商品名称 */
    	$desc = "中细软服务商城-知识产权服务";
    	
    	/* 创建支付请求对象 */
    	$reqHandler = new RequestHandler();
    	$reqHandler->init();
    	$reqHandler->setKey($config['key']);
    	$reqHandler->setGateUrl("https://gw.tenpay.com/gateway/pay.htm");
    	
    	//----------------------------------------
    	//设置支付参数
    	//----------------------------------------
    	$reqHandler->setParameter("partner", $config['partner']);//支付商户id
    	$reqHandler->setParameter("out_trade_no", $out_trade_no);
    	$reqHandler->setParameter("total_fee", $total_fee);  //总金额
    	$reqHandler->setParameter("return_url", $config['return_url']);
    	$reqHandler->setParameter("notify_url", $config['notify_url']);
    	$reqHandler->setParameter("body", $desc);
    	$reqHandler->setParameter("bank_type", "DEFAULT");  	  //银行类型，默认为财付通
    	//用户ip
    	$reqHandler->setParameter("spbill_create_ip", $_SERVER['REMOTE_ADDR']);//客户端IP
    	$reqHandler->setParameter("fee_type", "1");               //币种
    	$reqHandler->setParameter("subject",$desc);          //商品名称，（中介交易时必填）
    	
    	//系统可选参数
    	$reqHandler->setParameter("sign_type", "MD5");  	 	  //签名方式，默认为MD5，可选RSA
    	$reqHandler->setParameter("service_version", "1.0"); 	  //接口版本号
    	$reqHandler->setParameter("input_charset", "utf-8");   	  //字符集
    	$reqHandler->setParameter("sign_key_index", "1");    	  //密钥序号
    	
    	//业务可选参数
    	$reqHandler->setParameter("attach", "");             	  //附件数据，原样返回就可以了
    	$reqHandler->setParameter("product_fee", "");        	  //商品费用
    	$reqHandler->setParameter("transport_fee", "0");      	  //物流费用
    	$reqHandler->setParameter("time_start", date("YmdHis"));  //订单生成时间
    	$reqHandler->setParameter("time_expire", "");        //订单失效时间 24小时失效
    	$reqHandler->setParameter("buyer_id", "");                //买方财付通帐号
    	$reqHandler->setParameter("goods_tag", "");               //商品标记
    	$reqHandler->setParameter("trade_mode",$trade_mode);      //交易模式（1.即时到帐模式，2.中介担保模式，3.后台选择（卖家进入支付中心列表选择））
    	$reqHandler->setParameter("transport_desc","");           //物流说明
    	$reqHandler->setParameter("trans_type","1");              //交易类型
    	$reqHandler->setParameter("agentid","");                  //平台ID
    	$reqHandler->setParameter("agent_type","");               //代理模式（0.无代理，1.表示卡易售模式，2.表示网店模式）
    	$reqHandler->setParameter("seller_id","");                //卖家的商户号

    	//请求的URL
    	$reqUrl = $reqHandler->getRequestURL();
    	//获取debug信息,建议把请求和debug信息写入日志，方便定位问题
    	$debugInfo = $reqHandler->getDebugInfo();
   		redirect($reqUrl);
   	
    }
    /**
     * 支付同步通知方法
     */
    Public function payReturnUrl(){
    	//$this->log_result(json_encode($_SERVER["QUERY_STRING"]));
    	$config = C('SELF_PAY_TYPE');
    	$resHandler = new ResponseHandler();
    	$resHandler->setKey($config['key']);
    	//判断签名
    	if($resHandler->isTenpaySign()) {
    		//通知id
    		$notify_id = $resHandler->getParameter("notify_id");
    		//商户订单号
    		$out_trade_no = $resHandler->getParameter("out_trade_no");
    		//财付通订单号
    		$transaction_id = $resHandler->getParameter("transaction_id");
    		//金额,以分为单位
    		$total_fee = $resHandler->getParameter("total_fee");
    		//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
    		$discount = $resHandler->getParameter("discount");
    		//支付结果
    		$trade_state = $resHandler->getParameter("trade_state");
    		//交易模式,1即时到账
    		$trade_mode = $resHandler->getParameter("trade_mode");
    	
    		if("1" == $trade_mode ) {
    			if( "0" == $trade_state){
    				
    	            $order = new OrderController();
    	            $result = $order -> updateOrder(array('pay_type' => self::PAY_TYPE,'trade_no'=>$transaction_id, 'order_card' => $out_trade_no, 'total_fee' => $total_fee / 100));

    	            if(array_key_exists('fail', $result)) {
    	            	//@TODO订单操作 页面跳转失败页面
    	            }else{
    	            	//@TODO订单操作 页面跳转成功页面
    	            	Header ( "Location: " . U ( '/Home/Order/pay_success/order_id/'.$out_trade_no ) );
    	            	
    	            }
    	
    			} else {
    				//当做不成功处理
    				//@TODO订单操作 页面跳转失败页面
    			}
    		}
    	} else {
    		//@TODO订单操作 页面跳转失败页面
    	}
    }
    
    /**
     * 支付异步通知方法
     */
    Public function payNotifyUrl(){
    	//$this->log_result(json_encode($_SERVER["QUERY_STRING"]));
    	/* 创建支付应答对象 */
    	$resHandler = new ResponseHandler();
    	$config = C('SELF_PAY_TYPE');
    	$key = $config['key'];
    	$partner =  $config['partner'];
    	$resHandler->setKey($key);
    
    	//判断签名
    	if($resHandler->isTenpaySign()) {
    		//$this->log_result('验证签名正常'.json_encode($resHandler->getAllParameters()));
    		//通知id
    		$notify_id = $resHandler->getParameter("notify_id");
    	
    		//通过通知ID查询，确保通知来至财付通
    		//创建查询请求
    		$queryReq = new RequestHandler();
    		$queryReq->init();
    		$queryReq->setKey($key);
    		$queryReq->setGateUrl("https://gw.tenpay.com/gateway/simpleverifynotifyid.xml");
    		$queryReq->setParameter("partner", $partner);
    		$queryReq->setParameter("notify_id", $notify_id);
    	
    		//通信对象
    		$httpClient = new TenpayHttpClient();
    		$httpClient->setTimeOut(5);
    		//设置请求内容
    		$httpClient->setReqContent($queryReq->getRequestURL());
    	
    		//后台调用
    		if($httpClient->call()) {
    			//设置结果参数
    			$queryRes = new ClientResponseHandler();
    			$queryRes->setContent($httpClient->getResContent());
    			$queryRes->setKey($key);
    			//$this->log_result('请求正常'.json_encode($httpClient->getResContent()));
    			 
    			if($resHandler->getParameter("trade_mode") == "1"){
    				//判断签名及结果（即时到帐）
    				//只有签名正确,retcode为0，trade_state为0才是支付成功
    				if($queryRes->isTenpaySign() && $queryRes->getParameter("retcode") == "0" && $resHandler->getParameter("trade_state") == "0") {
    					//$this->log_result("即时到帐验签ID成功");
    					//取结果参数做业务处理
    					$out_trade_no = $resHandler->getParameter("out_trade_no");
    					//财付通订单号
    					$transaction_id = $resHandler->getParameter("transaction_id");
    					//金额,以分为单位
    					$total_fee = $resHandler->getParameter("total_fee");
    					//如果有使用折扣券，discount有值，total_fee+discount=原请求的total_fee
    					$discount = $resHandler->getParameter("discount");
    	
    					//------------------------------
    					//处理业务开始
    					//------------------------------
    					$order = new OrderController();
    					//$this->log_result(json_encode($total_fee / 100));
    					$result = $order -> updateOrder(array('pay_type' => self::PAY_TYPE, 'trade_no' => $transaction_id, 'order_card' => $out_trade_no, 'total_fee' => $total_fee / 100));
    					//$this->log_result(json_encode($result));
    					//处理数据库逻辑
    					//注意交易单不要重复处理
    					//注意判断返回金额
				    	if(array_key_exists('fail', $result)) {
				    		echo "fail";
				    	}
    					//------------------------------
    					//处理业务完毕
    					//------------------------------
    					//$this->log_result("即时到帐后台回调成功");
    					echo "success";
    	
    				} else {
    					//错误时，返回结果可能没有签名，写日志trade_state、retcode、retmsg看失败详情。
    					//echo "验证签名失败 或 业务错误信息:trade_state=" . $resHandler->getParameter("trade_state") . ",retcode=" . $queryRes->                         getParameter("retcode"). ",retmsg=" . $queryRes->getParameter("retmsg") . "<br/>" ;
    					//$this->log_result("即时到帐后台回调失败");
    					echo "fail";
    				}
    			}
    			//获取查询的debug信息,建议把请求、应答内容、debug信息，通信返回码写入日志，方便定位问题
    
    		}else
    		{
    			//通信失败
    			echo "fail";
    			//后台调用通信失败,写日志，方便定位问题
    			echo "<br>call err:" . $httpClient->getResponseCode() ."," . $httpClient->getErrInfo() . "<br>";
    		}
    	} else
    	{
    		echo "<br/>" . "认证签名失败" . "<br/>";
    		echo $resHandler->getDebugInfo() . "<br>";
    	}
    }
    /**
     * 添加日志
     * @param string $content
     */
    private function log_result($content){
    	$m = M('errorlog');
    	$data['content'] = $content;
    	$data['datetime'] = time();
    	$m->add($data);
    }
}