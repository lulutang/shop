<?php
/**
 * 后台赠品管理Model
 *
 * @author tangll
 *
 */
namespace Admin\Model;
use Think\Model;

class GiftModel extends Model {
	/**
	 * 获取数据
	 */
	public function getGiftList( $page, $pageSize, $get, $sort){
		$result = M ( 'goods_promotion p' ); // 获得赠品
		$field="g.short_title,g.description,p.id,p.goods_id,p.user_name,p.uid,p.user_name,p.order_code,p.num,p.status,p.addTime";
		if($sort) {
			$sort ='p.addTime DESC';
		} else {
			$sort ='p.addTime ASC';
		}
		$dayCount = $result->where ( 'p.addTime > ' . strtotime ( date ( 'Y-m-d' ), time () ) ) -> count ();
		if ($get) {
			$where = array();
			$keywords = $get ['keywords'];
			if($get ['key']!='关键词') {
				if($get ['key']=='用户昵称'){
					$where['p.user_name'] =  array (
								'like',
								'%' . $keywords 
						);
				}elseif($get ['key']=='订单编号'){
					$where['p.order_code'] =  array (
								'like',
								'%' . $keywords 
						);
				}else{
					$where['g.short_title'] =  array (
								'like',
								'%' . $keywords 
						);
						
				}
			}
			
			$buy_timeb = str_replace('+', ' ', $get ['buy_timeb']) ;
			$buy_timeend = str_replace('+', ' ', $get ['buy_timeend']);
			if($buy_timeb && $buy_timeend){
				$where ['p.addTime'] = array (
						'between',
						strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend )
				);
			} elseif ($buy_timeb && $buy_timeend == ''){
				$where ['p.addTime'] = array (
						'egt',
						strtotime ( $buy_timeb )
				);
			} elseif ($buy_timeb=='' && $buy_timeend){
				$where ['p.addTime'] = array (
						'elt',
						strtotime ( $buy_timeb )
				);
			}
			
			$giftInfo = $result->field($field)-> join("shop_goods AS g ON p.goods_id = g.goods_id") -> where ( $where ) -> order ( $sort ) -> page ( $page, $pageSize ) -> select (); 
		
			$count = $result->join("shop_goods AS g ON p.goods_id = g.goods_id") ->where ( $where )->count ();
		} else {
			$giftInfo = $result->field($field)-> join("shop_goods AS g ON p.goods_id = g.goods_id") -> order ( $sort ) -> page ( $page, $pageSize ) -> select (); 
			$count = $result->count ();
		}
		
		return array (
				'count' => $count,
				'dayCount' => $dayCount,
				'info' => $giftInfo 
		);
	}
}