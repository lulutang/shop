<?php
/**
 * 运营活动model
 * tll
 * 订单，单品，红包
 */
namespace Admin\Model;
use Think\Model;
use Admin\Controller\PublicController;
class OperationModel extends Model {
	const STATE = 2;//单品优惠未确认状态
    //获取所有订单流程列表信息
    public function getGoodsList($page, $pageSize, $groupTypeKey) {
        $m = M('goods');
        $where =array();
        if($groupTypeKey && $groupTypeKey!='-1') {
        	$where['now_servername'] = $groupTypeKey;
        	$lists = $m -> field('goods_id, short_title, title, now_price, thumb, goods_code, now_servername') -> where($where) -> page($page, $pageSize) -> select();
        	$count = $m -> where($where) -> count();
        } else {
        	$lists = $m -> field('goods_id, short_title, title, now_price, thumb, goods_code, now_servername') -> page($page, $pageSize) -> select();
        	$count = $m -> count();
        }
 
		$groupType = $m->query('SELECT now_servername from '.SELF_DB_PREFIX.'goods group by now_servername');
		
		$result = array();
        if( $lists ) {
        	$result = array('list' => $lists, 'count' =>$count, 'groupType' => $groupType);
        }else{
        	$result = array('groupType' => $groupType);
        }
        
        return $result;
    }
    
    //获取满足需求的优惠卷活动
    public function getOnsaleList(){
    	$m = M('Onsale');
    	$where ['endTime'] = array (
					'egt',
					time() 
		);
    	$lists = $m->field('id, sale, money, use, sale_where, startTime, sale_startTime, sale_endTime,sale_use')->where($where)->select();
    	$result = array();
    	foreach ($lists as $list) {
    	 	if(time() >= $list['startTime']) {
    	 		$result[] = $list;
    	 	}
    	}
    	return $result;
    }
    
    //获取满足需求的用户优惠
    public function getUserOnsaleList($where, $page, $pageSize){
    	$m = M('User_sale');
    	if ($where) {
			$info = $m->where ( $where )->order ( 'createTime desc' )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $m->where ( $where )->count ();
		} else {
			$info = $m->order ( 'createTime desc' )->page ( $page, $pageSize )->select (); // 获取购物车商品
			$count = $m->count ();
		}
		$result =array('info' => $info, 'count' => $count);
    	return $result;
    }
    
    //获取所有订单流程列表信息
    public function getOnsaleUserInfo($where) {
    	$m = M('User_sale');
    	$lists = $m -> field('count(*) as num ,sum(sale_money) as money') -> where($where) -> find();
  
    	if( $lists ) return $lists;
    }
    
    /**
     * 活动核对
     * 添加数据到shop_onsale 并且如果是商品优惠卷则要更新shop_goods数据 
     * 同时shop_goods表数据每个商品所对应的优惠活动是唯一的
     */
    public function confirmCoupon($info){
    
    	if(is_array($info)){
    		$model = M('Onsale');
    		//对应商品促销的优惠活动
    		if(isset($info['data'])) {
    			$data = $info['data'];
    			$info['type'] = $info['type'] == '单品促销' ? 1 : 2;
    			$info['startTime'] = strtotime($info['startTime']);
    			$info['endTime'] = strtotime($info['endTime']);
    			$info['sale_startTime'] = strtotime($info['sale_startTime']);
    			$info['sale_endTime'] = strtotime($info['sale_endTime']);
    			$info['createTime'] = time();
    			$info['sale'] = (int)$info['sale'];
    			$info['money'] = (int)$info['money'];
    			$info['sale_where'] = (int)$info['sale_where'];
    			$info['use'] = (int)$info['use'];
    			$info['state'] = 2;//未确认状态
				if(!empty($data)){
					$dataArr = explode(',', $data);
					$info['num'] = count($dataArr);
				}else{
					$info['num'] = 0;
				}
    			
    			unset($info['data']);
    			
    		}
    		
    		$session = session('adminOnsaleId');
    		$sessionArr = array();
    		if($session) {
    			foreach ($session as  $da) {
    				if($info['sale_endTime'] == $da['sale_endTime'] && $data == $da['data'] && $da['sale_endTime'] >= time()) {
    					return array('fail' => array('desc' =>'已添加，不要重复添加数据', 'data' => $data,'onsaleId' => $da['onsaleId']));
    				}
    			}
    		}
    		$onsaleId = $model ->add($info);
    		
    		unset($info['num']);//把数量消除
    		
    		if($session) {
    			$sessionArr = array_merge($session,array(array('onsaleId' => $onsaleId, 'sale_endTime' => $info['sale_endTime'], 'data' => $data)));
    		}else{
    			$sessionArr[] = array('onsaleId' => $onsaleId, 'sale_endTime' => $info['sale_endTime'], 'data' => $data);
    		}
    		
    		session('adminOnsaleId',$sessionArr);
    		unset($info['sale']);
    		$info['state'] = self::STATE;//未确认状态
    		$i = 0;
    		$goodsModel = M('goods');
    		$ids ='';
    		$goodsOnsale = array();
    		
    		if(isset($dataArr) && $onsaleId) {
    			
    			foreach ($dataArr as $goodsId) {
    				if($goodsId) {
    					$insertGoods .= 'UPDATE '.SELF_DB_PREFIX.'goods SET onsale_id ='.$onsaleId.' WHERE goods_id = '.$goodsId.' ;';
    					$ids .= $goodsId.',';
    					$i++;
    					$info['goods_id'] = $goodsId;
    					$info['onsale_id'] = $onsaleId;
    					$goodsOnsale[] = $info;
    				}
    			}
    			
    			if($insertGoods) {
    				$goodsModel ->query($insertGoods);
    				$goodsOnsaleModel = M('Goods_onsale');
    				$goodsOnsaleModel->addAll($goodsOnsale);
    				$ids = substr($ids, 0, strlen($ids)-1);
    				$goodsOnsaleModel->join('shop_goods ON shop_goods_onsale.goods_id = shop_goods.goods_id');
    			    $goodsList = $goodsOnsaleModel ->field('shop_goods.goods_id,shop_goods_onsale.money, shop_goods_onsale.state,shop_goods_onsale.sale_startTime,shop_goods_onsale.sale_endTime,shop_goods.thumb,shop_goods.short_title,shop_goods.title') -> where('shop_goods_onsale.goods_id in('.$ids.')') ->select();
    			}
    		}
    		return array('id' => $onsaleId, 'count' => $i, 'goodsList' => $goodsList);
    		
    	}
    	return false;
    	
    }
    
    /**
     * 根据优惠卷id获取活动信息
     * @param unknown $saleId
     */
    public function getConfirmCoupon($onsaleId, $data){
    	$goodsModel = M('Goods_onsale');
    	$onsaleModel = M('Onsale');
    	$onsaleList = $onsaleModel ->where('id = '. $onsaleId) -> find();
    	$dataArr = explode(',', $data);
    	
    	if(isset($dataArr) && $onsaleId) {
    		 
    		foreach ($dataArr as $goodsId) {
    			if($goodsId) {
    				$ids .= $goodsId.',';
    			}
    		}

    		$ids = substr($ids, 0, strlen($ids) - 1);
    		$goodsModel->join('shop_goods ON shop_goods_onsale.goods_id = shop_goods.goods_id');
    		$shopGoodsList = $goodsModel ->field('shop_goods_onsale.use, shop_goods_onsale.name, shop_goods_onsale.faxing, shop_goods_onsale.id, shop_goods.goods_code,shop_goods.goods_id,shop_goods_onsale.money, shop_goods_onsale.state,shop_goods_onsale.sale_startTime,shop_goods_onsale.sale_endTime,shop_goods.thumb,shop_goods.short_title,shop_goods.title') -> where('shop_goods_onsale.goods_id IN('.$ids.') AND shop_goods_onsale.onsale_id = '. $onsaleId ) -> select();
    	
    		
    	}else{
    		
    	}
    	return array('onsaleList' => $onsaleList, 'id' => $onsaleId, 'count' => count($shopGoodsList), 'goodsList' => $shopGoodsList);
    }
    
    /**
     * 获取shop_goods_onsale数据表里面的一条数据
     * @param int $id 
     * @return array
     */
    public function getCouponOne($id) {
    	$model = M('Goods_onsale');
    	$result = $model -> where('id = '.$id) -> find();
    	return $result;
    }
    
    /**
     * 删除参加活动的商品
     * 
     */
    public function delCoupon($where){
    	$model = M('Goods_onsale');
    	$result = $model -> where($where) -> delete() ;
    	return $result;
    }
    
    /**
     * 修改参加活动的商品
     * @param $where array Or string 条件（可以是数组也可也是string）
     * @param $save array 保存的数据
     */
    public function couponUpdate($where, $save){
    	if($where && $save) {
    		//商品确定
    		$model = M('Goods_onsale');
    		$result = $model -> where($where) -> save($save);
    		//活动确定
    		$model = M('Onsale');
    		$result = $model -> where('id = '.$where['onsale_id']) -> save($save);
    		return $result;
    	}else{
    		return false;
    	}
    	
    }
    
    /**
     * 获取单个活动信息
     * @param $saleId
     */
    public function getCouponInfoOne($saleId){
    	if($saleId) {
    		$model = M('Onsale');
    		$result = $model -> field('name, startTime, endTime, type') -> where('id = '.$saleId) -> find() ;
    		$goodsModel = M('Goods_onsale');
    		$count = $goodsModel -> where('onsale_id = '.$saleId) -> count() ;
    		return array('name' => $result['name'],'type' => $result['type'], 'count' => $count, 'startTime' => $result['startTime'], 'endTime' => $result['endTime']);
    	}else{
    		return false;
    	}
    	 
    }
    
    /**
     * 根据单个活动获取所有商品的具体数据
     * @param unknown $saleId
     * @return multitype:number unknown
     */
    public function getconfirmCouponShow($saleId) {
    	$model = M('Onsale');
    	$result = $model -> field('name, startTime, endTime, type') -> where('id = '.$saleId) -> find() ;
    	$goodsModel = M('Goods_onsale');
    	$list = $goodsModel ->  field('goods_id') ->where('onsale_id = '.$saleId) -> select() ;
    	
    	if($list) {
    		$ids = '';
    		foreach ($list as $goodsId) {
    			if($goodsId) {
    				$ids .= $goodsId['goods_id'].',';
    			}
    		}
    		$ids = substr($ids, 0, strlen($ids) - 1);
    		$goodsModel->join('shop_goods ON shop_goods_onsale.goods_id = shop_goods.goods_id');
    		$shopGoodsList = $goodsModel ->field('shop_goods_onsale.use, shop_goods_onsale.name, shop_goods_onsale.faxing, shop_goods_onsale.id, shop_goods.goods_code,shop_goods.goods_id,shop_goods_onsale.money, shop_goods_onsale.state,shop_goods_onsale.sale_startTime,shop_goods_onsale.sale_endTime,shop_goods.thumb,shop_goods.short_title,shop_goods.title') -> where('shop_goods_onsale.goods_id IN('.$ids.') AND shop_goods_onsale.onsale_id = '. $saleId ) -> select();
    		return  array('name' => $result['name'],'list' => $shopGoodsList,'count' => count($list), 'startTime' => $result['startTime'], 'endTime' => $result['endTime']);
    	}else{
    		return  array('name' => $result['name'],'type' => $result['type'], 'startTime' => $result['startTime'], 'endTime' => $result['endTime']);
    	}
    }
    
    /**
     * ajax根据分类获取商品
     * @param unknown $saleId
     * @return multitype:number unknown
     */
    public function ajaxGetCouponFenlei($fenlei) {
    	$model = M('Goods');
    	$res = $model -> field('goods_id, short_title') -> where('now_servername = "'.$fenlei.'"') ->select();
    	return $res;
    }
    
    /**
     * ajax根据分类获取商品
     * @param unknown $goodsId
     * @return multitype:number unknown
     */
    public function ajaxGetCouponFenleiInfo($goodsId){
    	$model = M('Goods');
    	$res = $model -> field('goods_id, short_title, goods_code, description, now_price, old_price') -> where('goods_id = '.$goodsId) ->find();
    	return $res;
    	
    }
    
    /**
     * 添加数据
     * @param $goodsId 商品id
     * @param $money 优惠券价格
     * @param $use 试用范围
     * @param $saleId 活动id
     */
    public function ajaxAddCouponOne($goodsId, $money, $use, $saleId) {
    	$model = M('Onsale');
    	$res = $model -> field('name, sale_startTime, sale_endTime, sale_where, faxing') -> where('id = '.$saleId) ->find();
    	if($res) {
    		$res['goods_id'] = $goodsId;
    		$res['state'] = 2;
    		$res['money'] = $money;
    		$res['use'] = $use;
    		$res['onsale_id'] = $saleId;
    		$res['createTime'] = time();
    		$add = $res;
    		return M('Goods_onsale')->add($add);
    	}
    	return false;
    }
    
    /**
     * 获取优惠活动
     */
    public function getActivity($page, $pageSize) {
    	$model = M('Onsale');
    	$res = $model -> field('id, name, money, startTime, endTime, sale_where, faxing, num') ->page($page, $pageSize)-> select();
    	$count = $model -> field('count(*) as num') -> count();
    	return array('list' => $res, 'count' => $count['num']);
    }
    
    /**
     * 获取优惠活动
     */
    public function searchActivity($page, $pageSize, $where) {
    	$model = M('Onsale');
    	if($where) {
    		$res = $model -> field('id, name, money, startTime, endTime, sale_where, faxing, num') -> where($where) -> page($page, $pageSize) -> select();
    		$count = $model -> field('count(*) as num') -> where($where) -> count();
    	} else {
    		$res = $model -> field('id, name, money, startTime, endTime, sale_where, faxing, num') -> page($page, $pageSize) -> select();
    		$count = $model -> field('count(*) as num') -> count();
    	}
    
    	return array('list' => $res, 'count' => $count['num']);
    }
    /**
     * 获取优惠活动详细信息
     * @param int $id 活动id
     */
    public function getActivityDetail($id) {
    	if($id > 0) {
    		//活动商品信息
    		$model = M('Goods_onsale as g');
    		$model->join('shop_goods as s ON s.goods_id = g.goods_id');
    		$goodsList = $model -> field('g.money, g.use, g.sale_where, g.sale_startTime, g.sale_endTime, s.goods_id, s.short_title, s.goods_code, s.thumb') -> where('g.onsale_id = '.$id ) -> select();
    		//活动信息
    		$shopModel = M('Onsale');
    		$huodong = $shopModel -> field('id, name, type, num, startTime, endTime ') -> where('id = '.$id ) -> find();
    		
    		return array('list' => $goodsList, 'huodong' => $huodong);
    		
    	}
    	
    }
    
} 