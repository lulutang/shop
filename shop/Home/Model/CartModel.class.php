<?php
namespace Home\Model;
use Think\Model;
/**
 * 购物车model类
 * @author tangll
 *
 */
class CartModel extends Model {
	
	/**
	 * 获取购物车
	 * @param array or string  $where 搜索条件
	 */
	public function getCart($where) {
		
		if(is_array($array)) {
			$m = M('Cart');
			$result = $m -> field('id') -> where($where) -> select();
			return $result;
		}else{
			$m = M('Cart');
			$result = $m -> field('id, goods_id, package_id, now_price, creator') -> where($where) -> select();
			echo $m->getLastSql();
			return $result;
		}
		
	}

}