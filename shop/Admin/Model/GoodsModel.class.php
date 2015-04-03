<?php
/**
 * 商品处理Model
 * @author 李建栋
 *
 */
namespace Admin\Model;

use Think\Model;

class GoodsModel extends Model{
    /**
     * 根据商品Id获取单条商品信息
     * @param int $Goods_id 商品Id
     */
    public function GetgoodsId($Goods_id)
    {
        foreach($Goods_id as $k => $v) {
            return $this -> where("goods_id = ". $v['goods_id']. "") ->find();
        }
    }

    /**
     * 添加商品
     * @param int $Goods_arr 添加商品数据
     */
    public function GoodsAdd($Goods_arr)
    {
        return $this -> add($Goods_arr);
    }

    /**
     * 修改商品
     * @param array $UP_goods  商品数据
     * @param string $goods_id 商品Id
     */
    public function GoodsUp($UP_goods , $goods_id)
    {
        return $this ->where("goods_id = " .$goods_id. "") -> save($UP_goods);
    }

    /**
     * 获取商品总的记录条数
     * @param string $where 查询条件
     */
    public function Getcount($where)
    {
        return $this -> field("goods_id") ->join("LEFT join shop_service on shop_service.id = shop_goods.s_id") ->join("LEFT join shop_adminuser on shop_adminuser.id = shop_goods.adduser") ->where($where) -> count();
    }

    /**
     * 获取商品所有数据
     * @param int $start    开始位置
     * @param int $end      结束位置
     * @param string $where 查询条件
     */
    public function Getdata($start , $end ,$where) {
        $field="card,truename,username,goods_id,goods_id,s_id,goods_code,short_title,title,description,now_price,old_price,is_limit,limit_num,is_index,is_hot,is_gift,number,goods_pic,content,adduser,index_order,index_isshow,server_order,server_isshow,answer,success,give_sale,server_name,attr_name,now_servername,server_pid,cost,shop_goods.thumb goods_thumb ,shop_goods.addtime goods_addtime , shop_goods.status goods_status";
        return $Goods_arr = $this ->field($field) ->join("left join shop_adminuser on shop_adminuser.id = shop_goods.adduser") -> order("shop_goods.addtime desc")  -> where($where) ->limit($start , $end) -> select();
    }

    /**
     * 拼接搜索where条件
     * @param array $term_data 搜索条件
     * @return string 
     */
    public function Term($term_data)
    {
        $where="shop_goods.status = 1";
        if(!empty($term_data["server"]))        
        {
            $where .= " and shop_goods.server_pid=" .$term_data["server"];
        }
        if(!empty($term_data["status"]))
        {
            $str=explode("|",$term_data["status"]);
            $where .= " and shop_goods.". $str[1] ."=" .$str["0"];
        }
        if(!empty($term_data["keyword"]))
        {
            $where .= " and (shop_goods.goods_code like '%".$term_data["keyword"] . "%'or shop_goods.short_title like '%".$term_data["keyword"] ."%')";
        }
        return $where;
    }

    /**
     * 根据商品ID删除商品
     * @param string $Goods_id 商品Id
     * @return string 
     */
    public function Del_Goods($Goods_id)
    {
        $str ='';
        $goods_arr = $this -> where("goods_id in (" .$Goods_id. ")") ->select();
        foreach($goods_arr as $k => $v)
        {
            if($v["status"] == 0)
            {
                $str .= $v["goods_id"].',';
            }
        }
        $str = trim($str , ",");
        $this -> where("goods_id in (" .$str. ")") -> delete();
        return $str;
    }

    /**
     * 批量修改商品状态
     * @param string $Bth   状态
     * @param int $Goods_id 商品Id
     */
    public function status_UP($Bth , $Goods_id)
    {
        $Str = explode("@" , $Bth);
        if($Str[1] == 'is_index' || $Str[1] == 'is_hot')
        {
            $data[$Str[1]] = $Str[0];
            $data["status"] = 1;
        }else{
            if($Str[1] == 'status' && $Str[0] == 2){
                $data["is_hot"] = 0;
                $data["is_index"] = 0;
            }
            $data[$Str[1]] = $Str[0];

        }
        return $this -> where("goods_id in (" .$Goods_id. ")") ->save($data);	
    }

    /**
     * 根据多个商品Id获取商品信息
     * @param string $more_goodsID 商品Id
     */
    public function moreID($more_goodsID)
    {
        return $this -> where("goods_id in (".$more_goodsID.")") -> select();
    }

    /**
     * 查询二级业务下是否有商品
     * @param string $attr_id 属性Id
     * @return string 
     */
    public function checkIsHaveGood( $attr_id ){
        $res = $this->field('goods_id')->where('s_id in('.$attr_id.')')->select();
        if( $res ){
            $str = '';
            foreach ($res as $val) {
                $str .=$val['goods_id'].',';
            }
            $res = trim($str,',');
        }
        return $res;
    }
    /**
     * 获取首页已上架商品
     * @param int $id 二级业务Id
     */
    public function getThreeGoods($id) {
        $res =  $this->field('goods_id,title,now_price,index_order,is_index,is_hot,short_title')->where(' status=1 and s_id='.$id) ->order(' index_order ')->select();
        return $res;
    }

    /**
     * 根据商品Id删除商品属性关联表对应数据
     * @param int $goods_id 商品Id
     */
    public function Del_Goodsattr($goods_id)
    {
        $goods_attr = M("goods_attr");
        return $goods_attr -> where("goods_id in (".$goods_id.")") -> delete();
    }

    /**
     * 根据商品判断是否有套餐
     * @param string $goods_str 商品Id
     * @param array $Pack_arr   套餐商品数据
     * @return string
     */
    public function Goods_Packs($goods_str , $Pack_arr)
    {
        $goods_arr = explode(',',$goods_str);
        $arr = array();
        $str ="";
        foreach($Pack_arr as $k => $v)
        {
            $arr[] = $v["goods_id"];
        }
        foreach($goods_arr as $key => $val)
        {
            if(!in_array($val , $arr))
            {
                $str .=$val.",";
            }
        }
        return trim($str,",");
    }
}