<?php
/**
 * 属性值
 * @author xxq
 */
namespace Admin\Model;

use Think\Model;

class Attr_valueModel extends Model {
    
    /**
     * 根据attr_id获取value信息
     * @param int $id
     * @return array
     */
    public function getattrValue($id){
        
        $result = $this->where('attr_id='.$id)->select();
        return $result;
    }
} 