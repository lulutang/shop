<?php
/**
 * 商品处理Model
 * @author 李建栋
 *
 */
namespace Home\Model;
use Think\Model;
header("content-type:text/html;charset=utf8");
class GoodsModel extends Model{
    Protected $autoCheckFields = false;

    /**
     * 通过商品ID获取商品所属业务
     * @param int $value 商品Id
     * @return array
     */
    public function CheckGoodsStatus($value){
        $data = $this -> where("goods_id = ".$value."") -> find();
        return $data['status'];
    }
    
    public function getServceType($value){
        $data = $this -> where("goods_id = ".$value."") -> select();
        if(!empty($data)){
            return $data;
        }
        return FALSE;
    }

    /**
     *  通过业务判断是否有属性
     * @param int $value 业务ID
     * @return boolean
     */
    public function findAtrbyServer($value){
        $atr = M("server_attr") -> where("s_id =".$value."") -> select();
        if(!empty($atr)){
            return $atr;
        }
        return FALSE;

    }

    /**
     * 通过属性id取出属性值
     * @param array $value 属性Id
     * @return boolean
     */
    public function findAtrValbyAtr($value){
        $atr = M("attr_value") -> where("attr_id =".$value[0]['id']."") -> select();
        $list[$value[0]['attr_id']] = $atr;
        $list[$value[0]['attr_id']]['attr_name'] = $value[0]['attr_name'];
        if(!empty($list)){
            return $list;
        }
        return FALSE;
    }
 
    /**
     * 通过商品ID获取属性值
     * @param int $value 商品Id
     * @return boolean
     */
    public function findAtrValbygods($value){
        $atr = M("goods_attr") -> field("value_id") -> where("goods_id =".$value."") -> select();
        if($atr){
            return TRUE;
        }
        return FALSE;					   
    }
    
    /**
     * 通过属性值，属性判断拥有那些商品
     * @param array $data 属性数据
     * @return boolean
     */
    public function findGodsbyAtr($data){
        $shop = M('goods_attr');
        foreach ($data as $key => $value) {
            if(is_array($value)){
                foreach ($value as $k => $v) {
                    $result = $shop -> field('a.value_id,g.goods_id,g.goods_code,g.short_title,g.title,g.description,g.now_price,g.old_price,g.goods_pic,g.content,g.answer,g.status')
                                            -> table("shop_goods_attr a,shop_goods g") 
                                            -> where("attr_id = ".$v['attr_id']." and value_id = ".$v['id']." and a.goods_id = g.goods_id")
                                            -> find();
                    $value_name = M('attr_value') -> where("id = ".$result['value_id']."") -> find();
                    if(!empty($value_name)){
                        $result['value_name'] = $value_name['value'];
                    }
                    if($result!=NULL){
                        $list['attr']['attr_name'] = $data[$key]['attr_name'];
                        $list['attr'][] = $result;
                    }
                }
            }		
        }
        if(!empty($list)){
            return $list;
        }
        return FALSE;
    }
    
    /**
     * 根据商品求得套餐
     * @param int $val 商品Id
     * @return boolean
     */
    public function getPackageByGods($val){
        $time = time();
        $str=M('package_goods') -> table("shop_package p,shop_package_goods g")
                                                    -> where("goods_id = ".$val." and ".$time." < p.endtime and p.package_id = g.package_id")
                                                    -> select();
        foreach ($str as $k => $v) {
            $source=explode(',',$v['zuhe']);
            foreach ($source as $key=>$val) {
                $str[$k]['shop'][]=$this -> field("goods_id,short_title,thumb") -> where("goods_id = ".$val."") -> find();
            }
        }
        if(!empty($str)){
            return $str;
        }
        return FALSE;
    }
    
    /**
     * 
     * @param int $pid      业务父类Id
     * @param string $pname 业务名称
     * @return array
     */
    public function getIndexTrademark(  $pid,$pname ){
        $service = D('Service');
        $arr_serverids = $service->field('id,server_name')->where(' parent_id='.$pid) ->order(' orderid asc ') ->select();
        $serverids = '0'; 
        if( $arr_serverids ){
            foreach ($arr_serverids as $key=>$val) {
                //检测有无商品
                $jj = $this-> checkHaveGoods($val['id']);
                if( $jj ){
                    $SS_serverids[$key] = $val;
                }
                $serverids .= $val['id'].','; 
            }
        }
            
        $serverids = trim($serverids,',' );
        $data['data'] = $this->where('status=1  and is_index=1 and s_id in('.$serverids.')')->order('index_order')->limit(4)->select();
        //带回一级业务下的二级业务
        $data['secodserver'] = array_slice($SS_serverids,0,4); 
        $data['secodserverhidden'] = array_slice($SS_serverids,4);
        $data['name'] = $pname;
        $data['pid'] = $pid;
        return $data;    
    }

    /**
     *检测二级下是否有上架商品 
     * @param int $id 业务Id
     * @return array
     */
    public function checkHaveGoods($id){
        return $this ->field('goods_id')->where("s_id = " . $id." and status=1 and is_gift=0") -> select();
    }
    
    /**
     * 获取商品信息
     * @param array $arr_data 业务类型信息
     * @param int $lim        限制条数
     * @param int $server_id  业务Id
     * @return array
     */
    public function goods_data($arr_data , $lim , $server_id = '') {
        $goods = array();
        foreach($arr_data as $k => $v)
        {
            if($server_id == '')
            {
                $goods[ $v['id'] ] = $this ->order("index_order asc") ->where("s_id = " . $v['id']." and status=1 and is_gift=0") -> limit("12") -> select();
            }else {
                if($v['id'] == $server_id)
                {
                    $goods[ $v['id'] ] = $this ->order("index_order asc")->where("s_id = " . $v['id']." and status=1 and is_gift=0") -> select();
                }else if($v['id'] != $server_id){
                    $goods[ $v['id'] ] = $this -> order("index_order asc")->where("s_id = " . $v['id'] ." and status = 1 and is_gift=0") -> limit("12") -> select();
                }
            }
        }
        return array("list" => $goods , "lim" => $lim);
    }

    /**
     * 根据二级业务ID获取商品
     * @param int $server_id 业务Id
     * @return array
     */
   public function goods_ul($server_id) {
        return $this -> order("index_order asc") -> where("s_id = " . $server_id ." and status = 1 and is_gift=0") -> select();
    }
        
}