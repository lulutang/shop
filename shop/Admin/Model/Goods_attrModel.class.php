<?php
/**
 * 商品属性Model
 * @author 李建栋
 *
 */
namespace Admin\Model;

use Think\Model;

class Goods_attrModel extends Model{
    /**
     * 添加商品属性
     * @param array $attr_add
     * @param string $attr 属性Id
     * @param array $attr_val 属性和属性值
     */
    public function Goods_attradd($attr_add , $attr , $attr_val)
    {
        $sql="insert into shop_goods_attr(attr_id , value_id , goods_id , s_id) values(";
        foreach($attr_val as $k => $v)
        {
            $sql .=$k.",".$v.",".$attr_add["goods_id"].",". $attr_add["s_id"] ."),(";	
        }
        $sql=trim(trim($sql,"("),",");
        return $this->query($sql);
    }

    /**
     * 根据属性Id获取组合属性
     * @param array $attr_id 属性值Id
     * @return string
     */
    public function Getattr_gname($attr_id)
    {
        $attr_val = M("attr_value");
        $str =implode(',' , $attr_id);
        $attr_arr = $attr_val -> where ("id in (". $str .")") -> select();
        $string='';
        foreach($attr_arr as $k => $v)
        {
            $string .=$v["value"]."+";
        }
        return trim($string,'+');
    }
}