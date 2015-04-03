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
define('WEIXIN_YOUHUI', 1);
class PropController extends Controller {
	/**
	 * 红包展示
	 */
	const WEIXIN = 1;
	public function hbShow() {
		$this->assign ( "datatime", $dataTime );
		$this->display ();
	}
	/**
	 * 用户申请红包
	 */
	public function applyHongbao() {
		/* $session = session ( 'user' );
		$user_id = $session ['user_id'];
		
		if (empty ( $user_id )) {
			$this->assign ( 'message', '请登录后再操作' );
			$this->display ( 'Public/error' );
			exit ();
		} */
		
		
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

