<?php
/**
 * 促销活动Model
 * @author lijiandong
 *
 */

namespace Home\Model;
use Think\Model;

class ActivityModel extends Model{
    
    /**
     * 获取指定条件的单个活动
     * @param substing $where 获取数据条件
     * @return array
     */
    public function getFindActivity($where){
        $activity = M("activity ac");
        $field="go.title,go.goods_pic,go.description,go.now_price,ac.act_content,ac.act_starttime,ac.act_endtime,ac.act_number,ac.act_goodsprice,ac.act_purchase_amount,ac.act_goodsid,ac.act_id,ac.act_photo";
        $activityData = $activity -> field($field) -> join("shop_goods As go ON ac.Act_goodsid=go.goods_id") -> where($where) -> find();
        if(!empty($activityData)){
            $activityData["act_purchase_amount"]=$activityData["act_number"] - $activityData["act_purchase_amount"];
        }
        return $activityData;
    }
    
    /**
     * 获取多条数据
     * @param substing $where 获取数据条件
     * @return array
     */
    public function getAllActivity($where='1=1'){
        $activity = M("activity ac");
        $field="go.title,go.goods_pic,go.description,go.now_price,go.thumb,ac.act_name,ac.act_id,ac.act_number,ac.act_content,ac.act_starttime,ac.act_goodsid,ac.act_endtime,ac.act_goodsprice,ac.act_purchase_amount,ac.act_id,ac.act_photo";
        $getData = $activity -> field($field) -> join("shop_goods As go ON ac.Act_goodsid=go.goods_id") -> where($where) -> select();
        $giftWhere = "is_gift=1";
        $getGift = $this -> getGiftData($giftWhere);
        
        $array[]=array();
        $giftArr=array();
        foreach($getData as $k => $v)
        {
            foreach($getGift as $key => $val)
            {
                if($val["act_id"]==$v["act_id"])
                {
                    $array[]=$val;
                }
            }
            $price = $v["now_price"]-$v["act_goodsprice"];
            if(!empty($array[0]))
            {
               $v["giftdata"]=$array; 
            }
            if(!empty($getData))
            {
                $v["chaprice"]=$price;
                $v["act_purchase_amount"]=$v["act_number"]-$v["act_purchase_amount"];
            }
            $giftArr[]=$v;
            $array=array();
        }
       
        return $giftArr;
    }
    
    /**
     * 获取商品数据
     * @param string $where 获取商品条件
     * @return array
     */
    private function getGiftData($where){
        $actGift = M("act_gift agt");
        $field="go.title,go.goods_pic,go.description,go.now_price,go.thumb,agt.act_id,agt.id,agt.goods_id,agt.giftid,agt.giftnumber,agt.number";
        $giftData = $actGift-> field($field) -> join("shop_goods AS go ON agt.giftid=go.goods_id") -> where($where) -> select();
        
        return $giftData;
    }
    
    /**
     * 获取当前时间段内活动
     * @return array 
     */
    public function getActivityId()
    {
        $arr = $this -> TimeRubbing();
        $nowTime = time();
        $timeQuantum = array();
        foreach($arr as $k => $v)
        {
            if($v[0] < $nowTime && $v[1] > $nowTime)
            {
                $timeQuantum["start"] = $v[0];
                $timeQuantum["end"] = $v[1];
            }
        }
        if(empty($timeQuantum))
        {
            switch ($nowTime)
            {
                case $arr[0][1] < $nowTime && $arr[1][0] > $nowTime: $timeQuantum["start"] = $arr[1][0];$timeQuantum["end"] = $arr[1][1];
                break;
                case $arr[1][1] < $nowTime && $arr[2][0] > $nowTime: $timeQuantum["start"] = $arr[2][0];$timeQuantum["end"] = $arr[2][1];
                break;
                case $arr[0][0] > $nowTime: $timeQuantum["start"] = $arr[0][0];$timeQuantum["end"] = $arr[0][1];
                break;
                case $arr[2][1] < $nowTime: $timeQuantum["start"] = $arr[2][0];$timeQuantum["end"] = $arr[2][1];
                break;
            }
        }
      
        $where = "act_starttime BETWEEN ".$timeQuantum['start']." AND ".$timeQuantum['end']."";
        $activityData = $this -> getFindActivity($where);
        $gather["data"] = $activityData;
        $gather["time"] = $timeQuantum;
        return $gather;
    }
    
    /**
     * 根据id获取活动信息
     * @param int $id
     * @return array
     */
    public function getActInfo($id){
    	if($id<=0){
            return false;
    	}
    
    	$field = 'act_type,act_purchase_amount,act_endtime,act_starttime,act_number,act_quoto,act_goodsprice,act_goodsid';
    
    	$m = M('activity');
    	$res = $m -> field($field) -> where(array('act_id' => $id)) -> find();
    
    	return $res;
    }
    /**
     * 判断前台时间轴
     * @param int $nowTime 当前时间搓
     * @return array 
     */
    public function getTime($nowTime){
         $arr = $this -> TimeRubbing();
        $array=array(
            "no_start"=>"<font style='font-size:18px; color:#2cc3af;' >未开始</font>",
            "start"=>"<font style='font-size:18px; color:#f6f52e;' >进行中</font>",
            "end"=>"<font style='font-size:18px; color:#ccc;' >已结束</font>"
        );
        $timeArr = array();
        switch ($nowTime)
        {
            case $arr[0][0] > $nowTime: 
                $timeArr[9]=$array["no_start"];
                $timeArr[10]=$array["no_start"];
                $timeArr[14]=$array["no_start"];
            break;
            case $arr[0][0] < $nowTime && $arr[0][1] > $nowTime: 
                $timeArr[9]=$array["start"];
                $timeArr[10]=$array["no_start"];
                $timeArr[14]=$array["no_start"];
            break;
            case $arr[1][0] < $nowTime && $arr[1][1] > $nowTime: 
                $timeArr[9]=$array["end"];
                $timeArr[10]=$array["start"];
                $timeArr[14]=$array["no_start"];
            break;
            case $arr[2][0] < $nowTime && $arr[2][1] > $nowTime: 
                $timeArr[9]=$array["end"];
                $timeArr[10]=$array["end"];
                $timeArr[14]=$array["start"];
            break;
            case $arr[0][1] < $nowTime && $arr[1][0] > $nowTime: 
                $timeArr[9]=$array["end"];
                $timeArr[10]=$array["no_start"];
                $timeArr[14]=$array["no_start"];
            break;
            case $arr[1][1] < $nowTime && $arr[2][0] > $nowTime:
                $timeArr[9]=$array["end"];
                $timeArr[10]=$array["end"];
                $timeArr[14]=$array["no_start"];
            break;
            case $arr[2][1] < $nowTime:
                $timeArr[9]=$array["end"];
                $timeArr[10]=$array["end"];
                $timeArr[14]=$array["end"];
            break;
        }
       return $timeArr;   
    }
    /**
     * 处理后将时间处理为时间搓
     */
    private function TimeRubbing()
    {
         $arr = array(
            array("9:0","9:30"),
            array("10:0","10:30"),
            array("14:0","15:0"),    
        );
        $timeArrRubbing = array();
        foreach($arr as $k => $v)
        {
           foreach($v as $key => $val)
           {
               $emtArr[]=strtotime(date("Y-m-d").$val);
           }
           $timeArrRubbing[]=$emtArr;
           $emtArr=array();
        }
        return $timeArrRubbing;
    }
    /**
     * 根据goodsId获取活动信息
     * @param int $goodId
     * @return array
     */
    public function getActInfoByGoodsId($goodId){
    	$goodId = intval($goodId);
    	if($goodId<=0){
    		return false;
    	}
    
    	$sql = 'SELECT b.giftid, b.giftnumber, b.number, a.act_endTime FROM shop_activity AS a, shop_act_gift AS b WHERE a.act_type = 1 AND a.act_goodsid = b.goods_id AND a.act_goodsid = '.$goodId;
    	$m = M();
    	$res = $m -> query($sql);
    	$goodsIds = array();
    	foreach ($res as $goods){
    		if($goods['number'] < $goods['giftnumber'] && $goods['act_endTime'] > time()){
    			$goodsIds[] = $goods['giftid'];
    		}
    	}
    	return $goodsIds;
    }
    
    /**
     * 根据goodsId获取活动商品信息
     * @param int $goodId
     * @return array
     */
    public function getActByGoodsInfo($goodId){
    	$goodId = intval($goodId);
    	if($goodId<=0){
    		return false;
    	}
    
    	$sql = 'SELECT b.giftid, b.giftnumber,b.number, a.act_endTime FROM shop_activity AS a, shop_act_gift AS b WHERE a.act_type = 1 AND a.act_goodsid = b.goods_id AND a.act_goodsid = '.$goodId;
    	$m = M();
    	$res = $m -> query($sql);
    	$goodsInfo=array();
	    if($res){
	    	$goodsIds = '';
	    	foreach ($res as $goods){
	    		if($goods['number'] < $goods['giftnumber']  && $goods['act_endTime'] > time()){
	    			$goodsIds .= $goods['giftid'].',';
	    		}
	    	}
	    	if($goodsIds!=''){
	    		$goodsIds = substr($goodsIds,0,strlen($goodsIds)-1);
	    		$field ='goods_id, short_title, title, description, now_price, old_price, server_pid, goods_code, server_name, attr_name, now_servername, thumb, status';
	    		$goodsModel = M('goods');
	    		$goodsInfo = $goodsModel ->field($field)->where('goods_id in ('.$goodsIds.')') ->select();
	    	}
	
	    }
    	return $goodsInfo;
    }
    
    public function addActPromotion($info) {
    	if(!isset($info)){
    		return false;
    	}
    	$model = M('Goods_promotion');
    	return $model ->addAll($info,array(),true);
    }
}
