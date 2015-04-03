<?php
/**
 * 业务属性
 * @author xxq
 */
namespace Admin\Model;

use Think\Model;

class Server_attrModel extends Model {
    
    /**
     * 根据父id获取
     * @param int $id 
     * @return array
     */
    public function getIdAttr( $id ) {
        
        $lists['data'] = $this -> where('s_id='.$id) -> order('id') -> select();
     
        if( $lists )  return $lists;
    }
    
    /**
     * 根据自增id获取
     * @param int $id
     * @return array
     */
    public function getPrIdAttr($id) {
        
        $lists['data'] = $this -> where('id='.$id) -> order('id') -> select();
   
        if( $lists )  return $lists;
    }
    
    /**
     * 删除二级业务下的属性
     * @param int $id
     */
    public function delTwoAttr( $id ) {
        
        //删除属性
        $this -> where('s_id='.$id) -> delete(); 
        
    }

    /**
     * 获取二级业务下所有的属性id
     * @param int $id
     * @return string
     */
    public function getTwotypes( $id ){
           $res = $this -> field('id') -> where('s_id in('.$id.')') -> select();
           
            if( $res ){
               $str = '';
                foreach ($res as $val) {
                    $str .= $val['id'].',';
                }
                $res = trim($str,',');
           }
           
           return $res;
        }

		
    /**
     * 根据业务类型ID获取属性值
     */
	
    function Get_attr($server_id)
    {
	return $this -> where("s_id = $server_id") -> select();
    }

    /**
     * 根据业务ID获取属性及属性值
     */
    function Get_attr_val($server_id)
    {
	return $this -> join("shop_attr_value on shop_server_attr.id=shop_attr_value.attr_id") -> where("s_id = $server_id") -> select();
    }
} 