<?php

/**
 * 道具使用 
 * 包括微信红包，优惠券
 * @author tll
 *
 */
namespace Home\Controller;

use Think\Controller;
use Home\Model\ActivityModel;
use Home\Model\PropModel;
define('WEIXIN_YOUHUI', 1);
class PropController extends Controller {
	
	const WEIXIN = 1;
	/**
	 * 用户领取优惠卷
	 */
	public function applyOnsale() {
		$session = session ( 'user' );
		$userId = $session ['user_id'];
		$userName = $session ['user_name'];
		
		if (empty ( $userId )) {
			$this->assign ('message', '请登录后再操作');
			$this->display ('Public/error');
			exit ();
		} 
		
		$get = I('get.');
		$id = isset($get['id']) ? intval($get['id']) : 0;
		
		$m = new PropModel();
		$result = $m -> getPropByOnsale(array('sale_id' => $id, 'uid' => $userId, 'is_use' => 0, 'endTime' => array('egt' , time())));
		
		if($result['num'] < 1) {
			$numId = $m ->addOnsale($userId, $userName, array('id' => $id));
			//领取成功
			if($numId > 0) {
				echo 1;exit();
			}else{//领取失败
				echo 2;exit();
			}
			
		}else{
			//已领取，没有使用
			echo 0;exit();
		}
		
	}
	
	public function useProp(){
		$test = PropFactory::createObj(WEIXIN_YOUHUI);
		echo  $result = $test->getValue();
	}
}

	/**
     * 基础操作类
     * 因为包含有抽象方法，所以类必须声明为抽象类
     */
     abstract class PropBase{
	     //抽象方法不能包含函数体
	     abstract public function getValue();//强烈要求子类必须实现该功能函数
	 }

	class weixinProp extends PropBase {
		public function getValue(){
			return 'weixin';
		}
	}
	
	class youhuiProp extends PropBase {
		public function getValue(){
			return 'youhui';
		}
	}
	/**
	 * 道具工厂类
	 * @author tll
	 *
	 */
	class PropFactory {
		public static function createObj($operate){
			switch ($operate) {
				case WEIXIN_YOUHUI:
					return new weixinProp();
				default:
					return new youhuiProp();
				break;
			}
		}
	}

