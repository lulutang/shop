<?php
/**
 * 后台购物车Model
 *
 * @author tangll
 *
 */
namespace Admin\Model;
use Think\Model;

class CartModel extends Model {
	/**
	 * 获取购物车信息
	 * @param int $page
	 * @param int $pageSize
	 * @param string $where
	 * @param int $sort
	 * @return array
	 */
	public function getCartInfo($page, $pageSize, $where = null, $sort) {
		$result = M ( 'cart' ); // 获得组合商品
		$money = 0;
		if($sort){
			$sort ='addtime DESC';
		}else{
			$sort ='addtime ASC';
		}
		$dayCount = $result->where ( 'addtime > ' . strtotime ( date ( 'Y-m-d' ), time () ) ) -> count ();
		if ($where) {
			$cartInfo = $result->where ( $where )->order ( $sort )->page ( $page, $pageSize )->select (); // 获取购物车商品  // echo $result -> getLastSql();
			$count = $result->where ( $where )->count ();
			$money = $result->where ( $where )->sum ('now_price');
		} else {
			$cartInfo = $result->order ( $sort )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $result->count ();
			$money = $result->sum ('now_price');
		}

		return array (
				'count' => $count,
				'dayCount' => $dayCount,
				'carts' => $cartInfo ,
				'money'=>$money
		);
	}
	
	/**
	 * 获取用户信息
	 * 
	 * @param unknown $userId        	
	 */
	private function getCartUserInfo($userId) {
		$model = M ( 'user' );
		$userInfo = $model->where ( 'uid  = ' . $userId )->limit ( 1 )->find ();
		return $userInfo;
	}
	/**
	 * 根据条件获取商品
	 * 
	 * @param unknown $where        	
	 * @return unknown
	 */
	public function getGoods($where) {
		$model = M ( 'goods' );
		$info = $model->where ( $where )->select ();
		return $info;
	}
	/**
	 * 根据条件获取某个组合
	 * 
	 * @param unknown $where        	
	 * @return unknown
	 */
	public function getPackageOne($where) {
		$model = M ( 'package' );
		$info = $model->where ( $where )->find ();
		return $info;
	}
}