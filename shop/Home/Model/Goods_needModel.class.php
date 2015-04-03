<?php
/**
 * 商品注册填写完整信息Model
 * @author tll
 *
 */
namespace Home\Model;
use Think\Model;
class Goods_needModel extends Model{

   /**
    * 增加数据
    * @param array $value
    * @param int $type
    * @return boolean
    */
    public function addInfo($value,$type){
	    
    	if(is_array($value)) {
    		$model = M('Goods_need');
    		if($type) {
    			return $model ->add($value);
    		}else {
    			return $model ->addAll($value);
    		}
    	}
        return false;
    }
    
        
}