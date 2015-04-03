<?php
/**
 * 属性值处理Model
 * @author 李建栋
 *
 */
namespace Admin\Model;
use Think\Model;
class Attr_valueModel extends Model{

    /**
     * 根据属性值获取商品
     * @param string $attr_ID 属性值Id
     * @param int $attr_count 属性值数量
     */
    public function Getattr_goods($attr_ID , $attr_count)
    {
        $Attr_valId = explode(',' , $attr_ID);
        $str=array();
        if($attr_count == count($Attr_valId))
        {
            for($i = 0 ; $i < count($Attr_valId) ; $i++)
            {
                if($i==0)
                  $sql="select goods_id from shop_goods_attr where value_id=".$Attr_valId[$i];
                else 
                  $sql='select goods_id from shop_goods_attr where value_id='.$Attr_valId[$i].'  and  goods_id in('.$sql.')';

            }
            return $this->query($sql);
        }
    }

   /**
     * 根据属性类型删除属性值
     * @param string $id 属性值Id
     */
    public function delAttr($id){
        if( $id ){
            $this->where('attr_id in('.$id.')')->delete();  
        }
    }
        
   /**
     * 根据属性值判断是否有商品 且商品是上架的
     * @param string $id 属性值Id
     */
   public function getValIdattr($id){
        $Goods = M("goods");
        if( $id ){
            return  $Goods->query("SELECT b.goods_id
                                    FROM shop_goods_attr a
                                    LEFT JOIN shop_goods b 
                                    ON a.goods_id=b.goods_id
                                    WHERE value_id IN($id) AND b.`status`=1;");
        }
   }
   
    /**
      * 获取属性值表id
      * @param int $id 属性Id
      * @return string
      */
    public function getvalId( $id ) {
        if( $id ){
            $res = $this->field(' id ')->where( ' attr_id='.$id )->select();
            if( $res ){
                $str = '';
                foreach ($res as $val) {
                    $str .=$val['id'].',';
                }
                $result = trim($str,',');
            } 
            return $result ? $result:''; 
        }
    }
}