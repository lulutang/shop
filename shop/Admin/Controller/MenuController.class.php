<?php
/**
 * 后台菜单控制类
 *
 * @author tangll
 *
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\MenuModel;

include (COMMON_PATH . "/Class/Page.class.php");
class MenuController extends Controller {
    /**
     * 初始化
     */
	public function _initialize() {
		header ( "content-type:text/html;charset=utf8" );
		$uid = session ( "userid" );
		if (empty ( $uid )) {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
		}
	}
	
	/**
	 * 菜单显示列表
	 */
	public function lists(){
		$model = new MenuModel;
		$menuInfo = $model ->getMenulist();
		$this->assign('menuInfo', $menuInfo);
		$this->display();
	}
	
	/**
	 * ajax增加菜单
	 */
	public function ajaxAddMenu(){
		$get = I('get.');
		$info=array();
		
		$info['p_id'] = isset($get['pid'])?intval($get['pid']):0;
		$info['mname'] = trim($get['name']);
		$info['url']  = $get['url'];
		
		$model = new MenuModel;
		$flag = $model ->getMenuByName($info['mname']);
		if($flag){
			echo 2;exit();
		}
		$menuInfo = $model ->addMenu($info);
		
		if($menuInfo){
			echo 1;exit();
		}else{
			echo 0;exit();
		}
		
	}
	
	/**
	 * 增加菜单
	 */
	public function addMenu(){
		$get = I('get.');
		$info=array();
	
		$info['p_id'] = isset($get['pid'])?intval($get['pid']):0;
		$info['mname'] = trim($get['name']);
		$info['url']  = $get['url'];
	
		$model = new MenuModel;
		$flag = $model ->getMenuByName($info['mname']);
		if($flag){
			$this->success('已经存在或者存在一样的名字');
			
		}
		$menuInfo = $model ->addMenu($info);
	
		if($menuInfo){
			$this->success('添加成功');
		}else{
			$this->success('添加失败');
		}
	}
	
	/**
	 * 修改菜单
	 */
	public function ajaxUpdateMenu(){
		$get = I('get.');
		$info=array();

		$info['mname'] = trim($get['name']);
		$info['url']  = $get['url'];
		$id = $get['pid'];
		
		$model = new MenuModel;
		$flag = $model ->getMenuById($id);
		if(!$flag){
			$this->success('不存在');	
		}
		$menuInfo = $model ->updateMenu(array('id='.$flag['id']), $info);
		
		if($menuInfo){
			echo 1;exit();
		}else{
			echo 0;exit();
		}
	}
	
	/**
	 * 删除菜单选项
	 */
	public function ajaxDelMenu(){
		$get = I('get.');
		$id = intval($get['id']);
		
		$model = new MenuModel;
		$flag = $model ->getMenuById($id);
		if(!$flag){
			echo 0;exit();
		}
		//如果是父id 则需要把所有的都删除掉
		if($flag['p_id']==0){
			$result = $model ->delMenu('id = ' . $id .' or p_id=' . $id);
		}else{
			$result = $model ->delMenu(array('id' => $id));
		}
			
		if($result) {
			echo 1;exit();
		}else{
			echo 0;exit();
		}
	}
}