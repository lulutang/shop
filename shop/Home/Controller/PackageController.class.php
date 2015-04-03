<?php
/**
 * 优惠套餐控制类
 * @author tangll
 *
 */
namespace Home\Controller;
use Think\Controller;
use Think\Cache\Driver\Redis;
use Home\Model\PackageModel;
use Home\Model\OnSaleModel;
class PackageController extends Controller {
	/**
	 * 优惠列表页面
	 */
    public function lists() {
        $options = array();//组合查询条件
        $result = getPackages($options, 1);//正常
        $out = getPackages($options, 0);//过期
        $result = array_merge($result, $out);
        $this->assign('lists', $result); 
        $this->display('lists');
    }
    
    /**
     * 优惠详情页面
     * @param $id 组合优惠id
     */
    public function details() {
    	$id = I('get.id');
    	$options = array('package_id' => intval($id));//组合查询条件
        $result = getPackageOne($options);
        if(empty($result)) {
        	$this->assign ('message', '该组合不存在');
        	$this->display('Public/error');
        	exit();
        }
        
        $this->assign('short_title', $result[$id]['short_title']);
    	$this->assign('lists', $result);
    	$this->display('details');
    }
    
    public function myOnsale(){
    	$model = new OnSaleModel();
    	$session = session('user');
    	$user_id = $session['user_id'];
    	$date = $model->getUserOnsale(array('uid' => $user_id));
    	$this->assign('lists', $date);
    	$this->display();
    }
    
    /**
     * 删除
     */
    public function delOnsale(){
    	//确认有id
    	$id = intval($_REQUEST['id']);
    	 
    	if($id){
    		$OO = M("User_sale");
    		$result = $OO ->where('id = '.$id) ->delete();
    		if( $result ){
    			echo 1;
    			exit();
    		}
    	}
    	echo 0;
    	exit();
    
    }
}