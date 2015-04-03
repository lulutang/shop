<?php
/**
 * 促销商品控制类
 * author  cjl
 * @var act 活动实例对象
 */
namespace Admin\Model;
use Think\Model;
class ActivityModel extends Model{
    /**
     * 获取促销商品所需信息
     * @var goods 商品实例化对象
     * @return array
     */
    public function getGoodsInfo(){
        $goods = M('goods');
        return $goods -> field('goods_id,short_title,now_price') -> where("is_gift = 0 and status = 1") -> select();
    }
    /**
     * 获取促销赠品所需信息
     * @var goods 商品实例化对象
     * @return array
     */
    public function getGiftInfo(){
        $goods = M('goods');
        return $goods -> field('goods_id,short_title,now_price') -> where("is_gift = 1 and status = 1") -> select();
    }
    /**
     * 对活动信息进行添加
     * @var ob 商品实例化对象
     * @param array data 添加所需的活动信息
     * @return array
     */
    public function addActivityInfo($data){
        $ob = $this -> add($data);
        if($ob > 0){
            return $this->getLastInsID();
        }else{
            return FALSE;
        }
    }
    /**
     * 对活动赠品信息进行添加
     * @param array gift 赠品id
     * @param int act 活动id
     * @param int goodsid 商品ID
     * @param array giftnum 赠品数量
     * @return bool
     */
    public function addActGiftInfo($gift, $act, $goodsid, $giftnum){
        $sql = "insert into shop_act_gift(act_id,goods_id,giftid,giftnumber) value";
        $where = "";
        foreach($gift as $key => $val){
            $where.="(".$act.",".$goodsid.",".$gift[$key].",".$giftnum[$key]."),";
        }
        $sql = substr($sql.$where,0,-1);
        $data = $this -> query($sql);
        return TRUE;
    }
    
    
    /**
     * 获得总数
     * @param string $where 组合条件
     * @return array
     */
    
    public function getCount($where){
	return  $data = $this -> where( $where ) ->count();
    }
    
    /**
     * 活动列表
     * @param  $start 开始时间
     * @param  $end 结束时间
     * @param  $where 组合语句
     * @return array
     */
    public function getActInfo($start,$end,$where){
        
        $data = $this -> where( $where )-> order(' act_id desc ') -> limit( $start, $end ) -> select();
            
        return $data;
    }
    /**
     * 更新活动信息状态
     * @param int $id  活动id
     * @param array $data 活动信息
     * @return booble
     */
    public function activityDel( $id ,$data) {
        
       return $this -> where( 'act_id='.$id ) -> save( $data ); 
    }
    /**
     * 编辑活动
     * @param type $id 活动id
     * 根据goodsId获取活动商品信息
     * @param int $goodId
     * @return array
     */

    public function activityEdit( $id ) {
        
       return $this -> where( 'act_id='.$id ) -> find();    
    }

    
    /**
     * 获取某活动的赠品
     * @param type $id 活动id
     * @return array
     */
    public function activityGift( $id ) {
       $GG = M('act_gift');
       return $GG -> where( 'act_id='.$id ) -> select(); 
    }
    /**
     * 编辑活动信息
     * @param array $data
     */
    public function updateActivityInfo( $data ) {
        
        return $this -> where('act_id='.$data['act_id'] ) -> save( $data );
       
    }
    /**
     * 编辑赠品
     * @param type $gift 赠的商品id
     * @param type $act_id  活动id
     * @param array $goodsid  主商品id
     * @param array $giftnum 赠的商品数量
     */
    public function updateActAndGift($gift,$act_id, $goodsid, $giftnum) {
        //删除原有的数据
        $this -> delteGift( $goodsid );
        //添加新加的数据
        $result = $this ->addActGiftInfo($gift, $act_id, $goodsid, $giftnum);
        return $result;
    }
    /**
     * 删除赠品
     * @param int $goodsid 商品id
     */

    public function delteGift( $goodsid ) {
        
        if( $goodsid ){
            
            $GG = M('act_gift');
            $GG -> where('goods_id='.$goodsid) -> delete(); 
        }  
    }

}


