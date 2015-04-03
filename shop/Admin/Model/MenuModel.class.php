<?php
/**
 * 后台菜单Model
 *
 * @author tangll
 *
 */
namespace Admin\Model;
use Think\Model;

class MenuModel extends Model {
	/**
	 * 获取菜单信息
	 * @param string $where
	 * @return array
	 */
	public function getMenulist($where) {
		$result = M ( 'menu' );
		if(isset($where)) {
			$menuInfo = $result -> where ( $where ) -> select (); 
		} else {
			$menuInfo = $result -> select ();
		}
		$infos = array();
		foreach ($menuInfo as $info) {
			if($info['p_id'] == '0'){
				$infos[$info['id']][] = $info;
			}else{
				$infos[$info['p_id']][] = $info;
			}
		}
		return $infos;
	}
	
	/**
	 * 通过id得到菜单信息
	 * @param int $id
	 * @return array
	 */
	public function getMenuById($id){
		if(!isset($id)) {
			return false;
		}
		$result = M ( 'menu' );
		$menuInfo = $result -> where ( 'id = '.$id ) -> find ();
		return $menuInfo;
	}
	
	/**
	 * 通过name得到菜单信息
	 * @param string name
	 * @return array
	 */
	public function getMenuByName($name){
		if(!isset($name)) {
			return false;
		}
		$result = M ( 'menu' );
		$menuInfo = $result -> where ( 'mname="'.$name.'"' ) -> find ();
		return $menuInfo;
	}
	/**
	 * 增加菜单信息
	 * @param array $info
	 * @return bool
	 */
	public function addMenu($info){
		if(!isset($info)) {
			return false;
		}
		$result = M ( 'menu' );
		$flag = $result -> add ($info);
		return $flag;
	}
	/**
	 * 通过条件修改菜单信息
	 * @param array $query
	 * @param array $update
	 * @return bool
	 */
	public function updateMenu($query, $update){
		$result = M ( 'menu' );
		$flag = $result -> where($query) -> save ($update);
		
		return $flag;
	}
	/**
	 * 通过条件删除菜单信息
	 * @param array $query
	 * @return bool
	 */
	public function delMenu($query){
		$result = M ( 'menu' );
		$flag = $result -> where($query) -> delete ();
		return $flag;
	}
}