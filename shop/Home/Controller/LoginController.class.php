<?php

/**
 * 用户登录控制类
 * tanglulu
 */
namespace Home\Controller;

use Home\Model\LoginModel;
use Think\Controller;
use Think\Cache\Driver\Redis;

class LoginController extends Controller {
	
	const COOKTIMES = 604800; //记录登录一周时间
	/**
	 * 通过cookie用户登录
	 * 
	 * @param string $userName  用户名称
	 * @param string $password  用户密码
	 */
	public function login() {
		// 如果存在session
		if (! $_SESSION ['user'] && ! isset ( $_SESSION ['user'] ['user_name'] )) {
			$userName = trim ( cookie ( 'user_name' ) );
			$password = trim ( cookie ( 'password' ) );
			if ($userName != '' && $password != '') { 
				require_once (APP_UC_PATH . "uc_config.php");
				require_once (APP_UC_PATH . 'uc_client/client.php');
				$uc_dologin = uc_user_login ( $userName, $password );
				// $uc_dologin[1]用户名
				// $uc_dologin[2]密码
				// $uc_dologin[3]email
				// $uc_dologin[4]用户是否重名
				if ($uc_dologin [0] > 0) { // 登录成功
					$model = new LoginModel ();
					$userInfo = $model->userLoginByUc ( $userName );
					if($userInfo){
						session ( 'user', $userInfo ); // session('name','value'); 设置session
						$loginurl = uc_user_synlogin ( $uc_dologin [0] ); // 把这个输出到页面中执行
						$return = array (
								'status' => 0,
								'loginurl' => $loginurl
						);
						return $return;
					}
					
					
				} elseif ($uc_dologin [0] = - 1) { // 用户不存在，或者被删除
					$return = array (
							'status' => - 1 
					);
					$this->ajaxReturn ( $return );
				} elseif ($uc_dologin [0] = - 2) { // 密码错误
					$return = array (
							'status' => - 2 
					);
					$this->ajaxReturn ( $return );
				} elseif ($uc_dologin [0] = - 3) { // 安全提问错误
					$return = array (
							'status' => - 3 
					);
					$this->ajaxReturn ( $return );
				}
			}
		}
	}
	
	/**
	 * 通过uc用户登录
	 * 
	 * @param string $userName
	 *        	用户名称
	 *        	
	 */
	public function loginByUc($userName) {
		if (! $_SESSION ['user'] && ! isset ( $_SESSION ['user'] ['user_name'] )) {
			// 不存在session先在redis里获取
			//$this->log_result ( 'user_name*' . $userName );
			if ($userName != '') {
				$model = new LoginModel ();
				$userInfo = $model->userLoginByUc ( $userName );
				// 没有用户 需要去注册 或者密码错误
				if (empty ( $userInfo )) { 
					return array (
							'fail' => array (
									'desc' => '登录失败，密码错误或者账号不存在' 
							) 
					);
				}
			}
			session ( 'user', $userInfo ); 
		}
	}
	
	/**
	 * 通过uc用户登录记录session
	 * 
	 * @param string $userName 用户名称
	 *        	
	 */
	public function loginByUcInfo($userName) {
		// 如果存在session
		if (! $_SESSION ['user'] && ! isset ( $_SESSION ['user'] ['user_name'] )) {
			// 如果缓存不存在则去数据库里查询
			if ($userName != '') { 
				$model = new LoginModel ();
				$userInfo = $model->userLoginByUc ( $userName );
				// 没有用户 需要去注册 或者密码错误
				if (empty ( $userInfo )) {
					return array (
							'fail' => array (
									'desc' => '登录失败，密码错误或者账号不存在' 
							) 
					);
				}
			}
			session ( 'user', $userInfo ); 
		}
	}
	
	/**
	 * 退出登录 同时注销redis的值
	 */
	public function logOutByUc() {
		require_once (APP_UC_PATH . "uc_config.php");
		require_once (APP_UC_PATH . 'uc_client/client.php');
		// 清除session
		session ( 'user', null ); 
		uc_user_synlogout ();
	}
	
	/**
	 * ajax用户登录
	 * 
	 * @param string $userName
	 * @param string $password
	 * @return echo 0 , 1
	 */
	public function ajaxLogin() {
		$get = I ( 'get.' );
		$userName = trim ( $get ['user_name'] );
		$password = trim ( $get ['password'] );
		$isji = trim ( $get ['isji'] );
		
		require_once (APP_UC_PATH . "uc_config.php");
		require_once (APP_UC_PATH . 'uc_client/client.php');
		$model = new LoginModel ();
		if(preg_match("/^1[34578]\d{9}$/", $userName)) {//手机号
			$userInfoOld = $model->userLoginByMP ($userName);
			if($userInfoOld) {
				$userName = $userInfoOld['user_name'];
			}
		}
		$uc_dologin = uc_user_login ( $userName, $password );
		// $uc_dologin[1]用户名
		// $uc_dologin[2]密码
		// $uc_dologin[3]email
		// $uc_dologin[4]用户是否重名
		if ($uc_dologin [0] > 0) { // 登录成功
			$id = $uc_dologin [0];
			$userInfo = $model->userLoginByUserName ($userName);
			// 没有用户数据情况下需要添加用户数据
			if (empty ( $userInfo )) {
				$id = $model->add_local_user ( $userName ,$password);
				$userInfo = array (
						'user_id' => $id,
						'user_name' => $userName
				);
			}
			session ( 'user', $userInfo ); // session('name','value'); 设置session
		
			$loginurl = uc_user_synlogin ( $id ); // 把这个输出到页面中执行
			$return = array (
					'status' => 0,
					'loginurl' => $loginurl
			);
		
			if ($isji == 1) {
				cookie ( 'user_name', $userName, self::COOKTIMES );
				cookie ( 'password', $password, self::COOKTIMES );
			}
			$this->ajaxReturn ( $return );
		} elseif ($uc_dologin [0] = - 1) { // 用户不存在，或者被删除
			$return = array (
					'status' => - 1
			);
			$this->ajaxReturn ( $return );
		} elseif ($uc_dologin [0] = - 2) { // 密码错误
			$return = array (
					'status' => - 2
			);
			$this->ajaxReturn ( $return );
		} elseif ($uc_dologin [0] = - 3) { // 安全提问错误
			$return = array (
					'status' => - 3
			);
			$this->ajaxReturn ( $return );
		}
	}
	/**
	 * ajax用户登录test
	 *
	 * @param $userName
	 * @param $password
	 * @return echo 0 , 1
	 */
	public function ajaxLoginTest() {
		$get = I ( 'get.' );
		$userName = trim ( $get ['user_name'] );
		$password = trim ( $get ['password'] );
		$isji = trim ( $get ['isji'] );
	
		
		$model = new LoginModel ();
		//如果是手机号则先到lastgbi上查询一次 找到user_name 然后再去ucenter里查询

			$userInfo = $model->userLogin ($userName, $password);
			if($userInfo){
				session ( 'user', $userInfo ); 
				$return = array (
						'status' => 0,
						'loginurl' => 1
				);
				$this->ajaxReturn ( $return );
			}else{
				$return = array (
						'status' => 1,
						'loginurl' => 1
				);
				$this->ajaxReturn ( $return );
			}
	}
	
	/**
	 * ajax退出登录
	 */
	public function ajaxLogOut() {
		require_once (APP_UC_PATH . "uc_config.php");
		require_once (APP_UC_PATH . 'uc_client/client.php');
		session ( 'user', null );
		$synlogout = uc_user_synlogout ();
		cookie ( 'user_name', null );
		cookie ( 'password', null );
		$this->ajaxReturn ( array (
				'url' => $synlogout 
		) );
	}
	
	/**
	 * 添加error log
	 * 
	 * @param unknown $content        	
	 */
	private function log_result($content) {
		$m = M ( 'errorlog' );
		$data ['content'] = $content;
		$data ['datetime'] = time ();
		$m->add ( $data );
	}
	
	/**
	 * 添加首页搜索信息
	 */
	public function ajaxAddInfo(){
		$data = I('get.');
		if(is_array($data)){
			$m = M ( 'Glean_info' );
			$data ['addTime'] = time ();
			$m->add ( $data );
			echo 1;exit();
		}
		echo 0;exit();
		
	}
}