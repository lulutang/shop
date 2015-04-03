<?php
/**
 * 块钱支付控制类
 * @author tangll
 *
 */
namespace Home\Controller;
use Think\Controller;
use Home\Controller\OrderController;
use Home\Model\ActivityModel;
header("content-type:text/html;charset=utf8");
class KuaiqianController extends Controller {
	const PAY_TYPE = 2;//快钱支付
	
	 /**
     * 快钱支付下单
     */
    public function kuaiqianPay(){
    	$session = session('user');
    	if(!isset($session)) {
    		$this->assign ( 'message', '请登录后再操作' );
    		$this->display('Public/error');
    		exit();
    	}
    	$get = I('get.');
    	
    	// 获取提交的订单号 
    	$out_trade_no = $get["orderId"];	
    	$orderModel = M('order');
    	$orderInfo = $orderModel -> where("user_id =".$session['user_id']." AND order_id = '".$get["orderId"]."'") -> find();
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
    
    return $this->getKuaiqianData($orderInfo['totalprice'], $orderInfo['order_card']);
    }
    /**
     * 
     * @param float $orderAmount
     * @param string $orderId 商户订单号，以下采用时间来定义订单号，商户可以根据自己订单号的定义规则来定义该值，不能为空。
     * @return string
     */
private function getKuaiqianData($orderAmount, $orderId){
	/* 商品名称 */
	$desc = "中细软服务商城-知识产权服务";
	//人民币网关账号，该账号为11位人民币网关商户编号+01,该参数必填。
	$merchantAcctId = "1002421524501";
	//编码方式，1代表 UTF-8; 2 代表 GBK; 3代表 GB2312 默认为1,该参数必填。
	$inputCharset = "1";
	//接收支付结果的页面地址，该参数一般置为空即可。
	$pageUrl = "";
	//服务器接收支付结果的后台地址，该参数务必填写，不能为空。
	$bgUrl = "http://shop.gbicom.cn/home/kuaiqian/payNotifyUrl/";
	//网关版本，固定值：v2.0,该参数必填。
	$version =  "v2.0";
	//语言种类，1代表中文显示，2代表英文显示。默认为1,该参数必填。
	$language =  "1";
	//签名类型,该值为4，代表PKI加密方式,该参数必填。
	$signType =  "4";
	//支付人姓名,可以为空。
	$payerName= "";
	//支付人联系类型，1 代表电子邮件方式；2 代表手机联系方式。可以为空。
	$payerContactType =  "";
	//支付人联系方式，与payerContactType设置对应，payerContactType为1，则填写邮箱地址；payerContactType为2，则填写手机号码。可以为空。
	$payerContact =  "";
	
	
	//订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试。该参数必填。
	$orderAmount = $orderAmount*100;
	//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101，不能为空。
	$orderTime = date("YmdHis");
	//商品名称，可以为空。
	$productName = $desc;
	//商品数量，可以为空。
	$productNum = '';
	//商品代码，可以为空。
	$productId = "";
	//商品描述，可以为空。
	$productDesc = $desc;
	//扩展字段1，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
	$ext1 = "";
	//扩展自段2，商户可以传递自己需要的参数，支付完快钱会原值返回，可以为空。
	$ext2 = "";
	//支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10，必填。
	$payType = "00";
	//银行代码，如果payType为00，该值可以为空；如果payType为10，该值必须填写，具体请参考银行列表。
	$bankId = "";
	//同一订单禁止重复提交标志，实物购物车填1，虚拟产品用0。1代表只能提交一次，0代表在支付不成功情况下可以再提交。可为空。
	$redoFlag = "";
	//快钱合作伙伴的帐户号，即商户编号，可为空。
	$pid = "";
	// signMsg 签名字符串 不可空，生成加密签名串
	$kq_all_para=$this->kq_ck_null($inputCharset,'inputCharset');
	$kq_all_para.=$this->kq_ck_null($pageUrl,"pageUrl");
	$kq_all_para.=$this->kq_ck_null($bgUrl,'bgUrl');
	$kq_all_para.=$this->kq_ck_null($version,'version');
	$kq_all_para.=$this->kq_ck_null($language,'language');
	$kq_all_para.=$this->kq_ck_null($signType,'signType');
	$kq_all_para.=$this->kq_ck_null($merchantAcctId,'merchantAcctId');
	$kq_all_para.=$this->kq_ck_null($payerName,'payerName');
	$kq_all_para.=$this->kq_ck_null($payerContactType,'payerContactType');
	$kq_all_para.=$this->kq_ck_null($payerContact,'payerContact');
	$kq_all_para.=$this->kq_ck_null($orderId,'orderId');
	$kq_all_para.=$this->kq_ck_null($orderAmount,'orderAmount');
	$kq_all_para.=$this->kq_ck_null($orderTime,'orderTime');
	$kq_all_para.=$this->kq_ck_null($productName,'productName');
	$kq_all_para.=$this->kq_ck_null($productNum,'productNum');
	$kq_all_para.=$this->kq_ck_null($productId,'productId');
	$kq_all_para.=$this->kq_ck_null($productDesc,'productDesc');
	$kq_all_para.=$this->kq_ck_null($ext1,'ext1');
	$kq_all_para.=$this->kq_ck_null($ext2,'ext2');
	$kq_all_para.=$this->kq_ck_null($payType,'payType');
	$kq_all_para.=$this->kq_ck_null($bankId,'bankId');
	$kq_all_para.=$this->kq_ck_null($redoFlag,'redoFlag');
	$kq_all_para.=$this->kq_ck_null($pid,'pid');
	
	
	$kq_all_para=substr($kq_all_para,0,strlen($kq_all_para)-1);
	
	/////////////  RSA 签名计算 ///////// 开始 //
	
	$fp = fopen(APP_PATH."Home/Common/kuaiqian/99bill-rsa.pem", "r");
	$priv_key = fread($fp, 123456);
	fclose($fp);
	$pkeyid = openssl_get_privatekey($priv_key);
	// compute signature
	openssl_sign($kq_all_para, $signMsg, $pkeyid,OPENSSL_ALGO_SHA1);
	
	// free the key from memory
	openssl_free_key($pkeyid);
	
	$signMsg = base64_encode($signMsg);
	$data[inputCharset]=$inputCharset;
	$data[pageUrl]=$pageUrl;
	$data[bgUrl]=$bgUrl;
	$data[version]=$version;
	$data[language]=$language;
	$data[signType]=$signType;
	$data[merchantAcctId]=$merchantAcctId;
	$data[payerName]=$payerName;
	$data[payerContactType]=$payerContactType;
	$data[payerContact]=$payerContact;
	$data[orderId]=$orderId;
	$data[orderAmount]=$orderAmount;
	$data[orderTime]=$orderTime;
	$data[productName]=$productName;
	$data[productNum]=$productNum;
	$data[productId]=$productId;
	$data[productDesc]=$productDesc;
	$data[ext1]=$ext1;
	$data[ext2]=$ext2;
	$data[payType]=$payType;
	$data[bankId]=$bankId;
	$data[redoFlag]=$redoFlag;
	$data[pid]=$pid;
	$data['signMsg'] = $signMsg;
	return $data;
}
    
    /**
     * 支付异步通知方法
     */
    Public function payNotifyUrl(){
    	$this->log_result('块钱支付返回的数据：'.json_encode($_SERVER["QUERY_STRING"]));
    	$kq_check_all_para= $this->kq_ck_null($_REQUEST[merchantAcctId],'merchantAcctId');
    	//网关版本，固定值：v2.0,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[version],'version');
    	//语言种类，1代表中文显示，2代表英文显示。默认为1,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[language],'language');
    	//签名类型,该值为4，代表PKI加密方式,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[signType],'signType');
    	//支付方式，一般为00，代表所有的支付方式。如果是银行直连商户，该值为10,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[payType],'payType');
    	//银行代码，如果payType为00，该值为空；如果payType为10,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[bankId],'bankId');
    	//商户订单号，,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[orderId],'orderId');
    	//订单提交时间，格式：yyyyMMddHHmmss，如：20071117020101,该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[orderTime],'orderTime');
    	//订单金额，金额以“分”为单位，商户测试以1分测试即可，切勿以大金额测试,该值与支付时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[orderAmount],'orderAmount');
    	// 快钱交易号，商户每一笔交易都会在快钱生成一个交易号。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[dealId],'dealId');
    	//银行交易号 ，快钱交易在银行支付时对应的交易号，如果不是通过银行卡支付，则为空
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[bankDealId],'bankDealId');
    	//快钱交易时间，快钱对交易进行处理的时间,格式：yyyyMMddHHmmss，如：20071117020101
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[dealTime],'dealTime');
    	//商户实际支付金额 以分为单位。比方10元，提交时金额应为1000。该金额代表商户快钱账户最终收到的金额。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[payAmount],'payAmount');
    	//费用，快钱收取商户的手续费，单位为分。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[fee],'fee');
    	//扩展字段1，该值与提交时相同
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[ext1],'ext1');
    	//扩展字段2，该值与提交时相同。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[ext2],'ext2');
    	//处理结果， 10支付成功，11 支付失败，00订单申请成功，01 订单申请失败
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[payResult],'payResult');
    	//错误代码 ，请参照《人民币网关接口文档》最后部分的详细解释。
    	$kq_check_all_para.= $this->kq_ck_null($_REQUEST[errCode],'errCode');
    	
    	
    	$out_trade_no = $_REQUEST['orderId'];
    	$trans_body=substr($kq_check_all_para,0,strlen($kq_check_all_para)-1);
    	$MAC=base64_decode($_REQUEST[signMsg]);
    	
    	$fp = fopen(APP_PATH."Home/Common/kuaiqian/99bill.cert.rsa.20340630.cer", "r");
    	$cert = fread($fp, 8192);
    	fclose($fp);
    	$pubkeyid = openssl_get_publickey($cert);
    	$ok = openssl_verify($trans_body, $MAC, $pubkeyid);
    	$rtnOK = 0;
    	//$this->log_result('块钱支付返回的数据ok：'.$ok);
    	if ($ok == 1) {
    		$rtnOK = 1;
    		switch($_REQUEST[payResult]){
    			case '10':
    				//此处做商户逻辑处理
    				$order = new OrderController();
    				$result = $order -> updateOrder(array('pay_type' => self::PAY_TYPE,'trade_no'=>$_REQUEST['dealId'] , 'order_card' => $_REQUEST['orderId'], 'total_fee' => $_REQUEST['payAmount'] / 100));
    				if(array_key_exists('fail', $result)) {
    					//@TODO订单操作 页面跳转失败页面
    				}else{
    					//@TODO订单操作 页面跳转成功页面
    					$rtnUrl= 'http://shop.gbicom.cn/Home/Order/pay_success?order_id='.$out_trade_no;
    				}
 
    				break;
    			default:
    				
    				//以下是我们快钱设置的show页面，商户需要自己定义该页面。
    				$rtnUrl= 'http://shop.gbicom.cn/Home/Order/pay_success?order_id='.$out_trade_no;
    				break;
    		}
    	
    	}else{
    		//以下是我们快钱设置的show页面，商户需要自己定义该页面。
    		$rtnUrl= 'http://shop.gbicom.cn/Home/Order/pay_success?order_id='.$out_trade_no;
    	}
    	//$this->log_result('块钱支付返回的数据ok：'.$rtnUrl);
    	echo "<result>".$rtnOK."</result><redirecturl>".$rtnUrl."</redirecturl>";die;
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
    
    private function kq_ck_null($kq_va,$kq_na){
    	if($kq_va == "") {
    		$kq_va="";
    	}
    	else{
    		return $kq_va=$kq_na.'='.$kq_va.'&';
    	}
    }
}