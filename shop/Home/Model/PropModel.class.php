<?php
namespace Home\Model;
use Think\Model;
/**
 * 优惠卷model类
 * @author tangll
 *
 */
class PropModel extends Model {
	
	/**
	 * 获取优惠卷
	 * @param $where 搜索条件
	 */
	public function getPropByOnsale($where) {
		$model = M('User_sale');
		$res = $model -> field('count(*) as num') -> where($where) -> find();
		return $res;
	}
	
	/**
	 * 增加用户优惠卷
	 * @param int $uid 用户uid
	 * @param int $userName 用户username
	 * @param int $where 消费金额
	 */
	public function addOnsale($uid, $userName, $where) {
		$model = new OnSaleModel();
		$youhui = $model->getOnsaleOne($where);

		if(empty($youhui)) {
			return false;
		}
		$startTime = $youhui['sale_startTime'] > 0 ? $youhui['sale_startTime'] : '';
		$data = array('uid' => (int)$uid,
				'user_name' => $userName,
				'name' => $youhui['name'],
				'sale_id' => (int)$youhui['id'],
				'sale_money' => (float)$youhui['money'],
				'is_where' => (float)$youhui['sale_where'],
				'startTime' => (int)$startTime,
				'endTime' =>(int)$youhui['sale_endTime'],
				'createTime' =>time(),
		);
		
		return $model ->addUserOnsale($data);
	}

}