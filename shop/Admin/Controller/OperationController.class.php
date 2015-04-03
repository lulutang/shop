<?php
/**
 * 运营活动管理
 * @author tll
 */
namespace Admin\Controller;
use Page\Page;
use Think\Controller;
use Admin\Model\OperationModel;

include (COMMON_PATH . "Class/Page.class.php");
class OperationController extends Controller {
   public function _initialize() {
		$uid = session("userid");
		if( empty( $uid ) )
		{
            echo '<script>top.location.href="/Admin/Login/index";</script>';
		}
	} 
    /**
     * 单品促销商品页面
     */
    public function singlePromotion() {
    	$goodsModel = new OperationModel();
    	$get = I('get.');
    	
    	$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
    	$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
    	$groupTypeKey = isset ( $get ['groupType'] ) ? $get ['groupType'] : -1;
    	$list = $goodsModel ->getGoodsList($page, $pageSize, $groupTypeKey);
    	
    	$pageModel = new Page ( $list ['count'] );
    	$pages = $pageModel->show ();
    	$this -> assign('pages' ,$pages);
    		
    	$this -> assign('list' ,$list['list']);
    	$this -> assign('groupType' ,$list['groupType']);
    	$this -> assign('groupTypeKey' ,$groupTypeKey);
      	$this -> display();
    }
    
    public function giftPromotion() {
    	$this -> display();
    }
	
    /**
     * 优惠券
     */
    public function coupon() {
    	$goodsModel = new OperationModel();
    	$get = I('get.');
    	 
    	$pageSize = isset ( $get ['pageSize'] ) ? intval ( $get ['pageSize'] ) : 10;
    	$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
    	$groupTypeKey = isset ( $get ['groupType'] ) ? $get ['groupType'] : -1;
    	$list = $goodsModel ->getGoodsList($page, $pageSize, $groupTypeKey);
    	 
    	$pageModel = new Page ( $list ['count'] );
    	$pages = $pageModel->show ();
    	$this -> assign('pages' ,$pages);
    	
    	$this -> assign('list' ,$list['list']);
    	$this -> assign('groupType' ,$list['groupType']);
    	$this -> assign('groupTypeKey' ,$groupTypeKey);
    	$this -> display();
    }
    
    public function money() {
    	$this -> display();
    }
    
    public function activity() {
    	$this -> display();
    }
    
    public function commonSet(){
    	
    	$this -> display();
    }
    /**
     * 优惠券设置
     */
    public function couponSet(){
    	$get = I('get.');
    	$data = $get['data'];
    	if($data) {
    		$this -> assign('data', $data);
    		$key = '单品促销';
    	}else{//优惠券通用设置
    		$key = '优惠券促销';
    	}
    	 
    	$this -> assign('key', $key);
    	$this -> display();
    }
    
    /**
     * 添加数据活动核对
     */
    public function confirmCoupon(){
    	$get = I('post.');
    	$model = new OperationModel();
    	foreach ($get as $key => $val) {
    		if($key != 'data' && !$val) {
    			$this -> error('缺少正确的参数,3秒后自动返回上一页');
    			exit();
    		}
    	}
    	
    	$info = $model -> confirmCoupon($get);
    	$saleId = $info['id'];
    	if(array_key_exists('fail', $info)){
    		$saleId = $info['fail']['onsaleId'];	
    	}
    	Header ( "Location: " . U ( '/admin/Operation/confirmCouponShow?saleId=' . $saleId ) );

    }
    /**
     * 活动核对显示
     */
    public function confirmCouponShow(){
    	$get = I('get.');
    	$model = new OperationModel();
    	$saleId = $get['saleId'];
  
    	$info = $model -> getconfirmCouponShow($saleId);
    	
    	$count = $info['count'];
    	$saleId = $saleId;
    	$count = $count >0 ? $count : 0;
    	$this -> assign('get', $info);
    	$this -> assign('goodsList', $info['list']);
    	$this -> assign('count', $count);
    	$this -> assign('saleId', $saleId);
    	$groupType = getCategoryErji();

    	$this -> assign('groupType', $groupType);
    	$this -> display('confirmCoupon');
    }
    
    /**
     * 优惠券-发布
     */
    public function couponrelease_success() {
    	$get = I('get.');
    	$saleId = intval($get['saleId']) > 0 ? intval($get['saleId']) : 0;
    	if($saleId>0){
    		$model = new OperationModel();
    		$info = $model -> getCouponInfoOne($saleId);
    		$this -> assign('info', $info);
    		$this -> display();
    	}else{
    		$this -> error('缺少正确的参数,3秒后自动返回上一页');
    		exit();
    	}
    	
    }
    
    /**
     * ajax优惠券-发布
     */
    public function ajaxCouponrelease_success() {
    	$get = I('get.');
    	$saleId = intval($get['saleId']) > 0 ? intval($get['saleId']) : 0;
    	if($saleId>0){
    		$model = new OperationModel();
    		$info = $model -> couponUpdate(array('onsale_id' =>$saleId ),array('state' => 0));
    		echo 1;exit();
    	}else{
    		$this -> error('缺少正确的参数,3秒后自动返回上一页');
    		echo 0;exit();
    	}
    	 
    }
    /**
     * 获取修改商品的优惠券信息
     */
    public function coupon_update() {
    	$get = I('get.');
    	$id = intval($get['id'])>0 ? intval($get['id']) : 0; 
    	if($id>0){
    		$model = new OperationModel();
    		$info = $model -> getCouponOne($id);
    		$this -> assign('info', $info);
    		$this -> assign('id', $id);
    		$this -> display();
    	}else{
    		$this -> error('缺少正确的参数,3秒后自动返回上一页');
    		exit();
    	}
    	
    }
    
    /**
     * 修改商品的优惠券信息
     */
    public function updateCoupon(){
    	$get = I('get.');
    	$id = intval($get['id'])>0 ? intval($get['id']) : 0;
    	if($id>0){
    		$model = new OperationModel();
    	
    		$data =array(
    				'money' => (int)$get['money'],
    				'use' => (int)$get['use'],
    				'sale_startTime' => strtotime($get['sale_startTime']),
    				'sale_endTime' => strtotime($get['sale_endTime']),
    				'sale_where' => (int)$get['sale_where']
    		);
    	
    		$model -> couponUpdate(array('id'=>$id),$data);

    	}
    	$info = $model -> getCouponOne($id);
    	$this -> assign('info', $info);
    	$this -> display('coupon_update');
    }
    
    /**
     * 删除参加活动的商品
     * @param id 商品在活动表里的id
     * @param saleId 所属活动id
     * 
     */
    public function delCoupon() {
    	$get = I('get.');
    	$id = intval($get['id'])>0 ? intval($get['id']) : 0;
    	$saleId = intval($get['saleId'])>0 ? intval($get['saleId']) : 0;
    	
    	if($id && $saleId){
    		$model = new OperationModel();
    		$result = $model ->delCoupon('id = '.$id.' AND onsale_id='.$saleId);
    		if($result){
    			echo 1;exit();
    		}
    	}
    	echo 0;exit();	 
    }
    
    /**
     * ajax获取商品信息
     */
    public function ajaxGetCouponFenlei() {
    	$get = I('get.');
    	$type = isset($get['type']) ? $get['type'] : '';
    	if($type){
    		$model = new OperationModel();
    		$res = $model ->ajaxGetCouponFenlei($type);
    		echo json_encode(array('success' => 1,'list' => $res));
    		exit();
    	}
    	echo json_encode(array('success'=>0));
    	exit();
    }
    
	/**
     * ajax获取商品信息
     */
    public function ajaxGetCouponFenleiInfo() {
    	$get = I('get.');
    	$goodsId = isset($get['goodsId']) ? intval($get['goodsId']) : '';
    	if($goodsId){
    		$model = new OperationModel();
    		$res = $model ->ajaxGetCouponFenleiInfo($goodsId);
    		echo json_encode(array('success' => 1,'list' => $res));
    		exit();
    	}
    	echo json_encode(array('success'=>0));
    	exit();
    }
    
    /**
     * 在优惠活动里增加一条商品数据
     */
    public function ajaxAddCouponOne() {
    	$get = I('get.');
    	$goodsId = isset($get['goodsId']) ? intval($get['goodsId']) : '';
    	$money = isset($get['money']) ? intval($get['money']) : '';
    	$use = isset($get['use']) ? intval($get['use']) : '';
    	$saleId = isset($get['saleId']) ? intval($get['saleId']) : '';
    	if($goodsId && $money && $use && $saleId) {
    		$model = new OperationModel();
    		$res = $model ->ajaxAddCouponOne($goodsId, $money, $use, $saleId);
    		if($res){
    			echo json_encode(array('success' => 1));
    		}else{
    			echo json_encode(array('success' => 0));
    		}
    		
    		exit();
    	}
    	echo json_encode(array('success'=>0));
    	exit();
    }
}