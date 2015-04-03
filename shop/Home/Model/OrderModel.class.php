<?php
namespace Home\Model;
use Think\Model;
/**
 * 订单model类
 * @author tangll
 *
 */
class OrderModel extends Model {
	
	/**
	 * 删除order
	 */
	public function deleteOrder($id){
		$m = M('Order');
		$data = array('status' => 2);
		$this->cancelOrderBind($id);
		return $m ->where('order_id = '. $id)->save($data);
	}
	
	/**
	 * 取消用户绑定order的优惠卷
	 */
	public function cancelOrderBind($id){
		$id = intval($id);
		$m = M('Order');
		if($id > 0) {
			$result = $m -> field('onsale_id, onsale_money, totalprice') -> where('order_id = '. $id.' AND status = 0')->find();
			$onsale_id = $result['onsale_id'];
			
			if($onsale_id) {
				$data = array('onsale_money' => 0, 
							  'onsale_id' => 0, 
							  'totalprice'=> $result['totalprice'] + $result['onsale_money']);
				
				 $m ->where('order_id = '. $id)->save($data);
				 
				 $onsaleMoedl = M('User_sale');
				 $onsale =array('state' => 0, 'orderId' => 0);
				 $onsaleMoedl ->where('id = '. $onsale_id)->save($onsale);
				 return true;
			}	
		}
		return false;
	}
	
	/**
	 * 通过购物车添加订单
	 * @param array $get get数组
	 */
	public function makeOrderByCart($get) {
		
	}
	
}