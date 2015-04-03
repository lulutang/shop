<?php
/**
 * event model类
 *
 * @author tangll
 *
 */
namespace Admin\Model;
use Think\Model;

class EventModel extends model{
	/**
	 * 每天需要执行的计划任务
	 * 增加活动
	 */
	public function dayEvent() {
		$m = M('errorlog');
		//促销时间段 促销数量 限购个数 价格
		$buy_timeb = date('Y-m-d');
		$buy_timeend = date('Y-m-d 23:59');
		
		if(time() >= strtotime ( '2015-03-01' ) ){//只添加到3月1号
			return false;
		}
		$where ['act_addtime'] = array (
				'between',
				strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend )
		);
		$model = M('Activity');
		$result = $model ->field('act_name')->where($where)->find();
		//$m ->add(array('content'=>'执行了计划任务'.date('Y-m-d H:i:d'),'datetime'=>time()));
		if($result){
			return array('fail'=>'已经添加');
		}
		
		$cuxiaoTime = array(array('9:00','9:30'), array('10:00','10:30'), array('14:00','15:00'));
		
		$result = $model ->field('act_name, act_goodsid, act_goodsprice, act_number, act_photo, act_content, act_type, act_quoto')->order('act_addTime ASC')->limit(3)->select();
		$add = array();
		foreach ($result as $key => $val) {
			if($val){
				
				$info = $val;
				$info['act_starttime'] = strtotime(date('Y-m-d ').$cuxiaoTime[$key][0]);
				$info['act_endtime'] = strtotime(date('Y-m-d ').$cuxiaoTime[$key][1]);
				$info['act_addtime'] = time();
				$add[] = $info;
			}
		}
		$res = $model->addAll($add);
		return $res;
	}
	
	/**
	 * 每天30分钟
	 * 需要把订单超过30分钟还没有支付的的数据清理掉
	 */
	public function min30Event() {
		$model = M('order');
		$res = $model -> find();
		dump($res);die;
	
	}
	/**
	 * 每5分钟
	 * 需要把订单超过24小时的还没有支付的的数据清理掉
	 */
	public function min5Event(){
		$m = M('Order');
		$time = time()-86400;
		$result = $m -> field('order_id, onsale_id, onsale_money, totalprice') -> where('createtime <='.$time.' AND status = 0 AND onsale_money > 0 AND onsale_id > 0')->select();

		foreach ($result as $val){
			$onsale_id = $val['onsale_id'];
			
			if($onsale_id) {
				$data = array('onsale_money' => 0,
						'onsale_id' => 0,
						'totalprice'=> $result['totalprice'] + $result['onsale_money']);
			
				$m ->where('order_id = '. $val['order_id'])->save($data);
					
				$onsaleMoedl = M('User_sale');
				$onsale =array('state' => 0, 'orderId' => 0);
				$onsaleMoedl ->where('id = '. $onsale_id)->save($onsale);
			}
		}		
		echo 'success';
	}
}
/**
 * event type 类
 *
 * @author tangll
 *
 */
class EventTypes {
	const EVENT_TYPE_DAY		= 0x1001; // 每天执行的 相当于 23点59分执行
	const EVENT_TYPE_30_MIN		= 0x1002; // 每30分钟执行的
	const EVENT_TYPE_5_MIN		= 0x1003; // 每5分钟执行的
}

