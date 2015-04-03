<?php
/**
 * 业务
 * @author xxq
 */
namespace Admin\Model;

use Think\Model;

class ServiceModel extends Model {
    
    /**
    * 根据业务父类ID获取下级业务类型
    */
    function Server_arr($Server_pid) {
            return $this -> where("parent_id = $Server_pid") -> select();
    }

    /**
     * 获取所有业务列表信息
     * @return type
     */
    public function getService() {
        
        $Onetype = $this -> where('parent_id=0') -> order('id') -> select();
        foreach ($Onetype as $key => $value) {
            $Twotype = $this -> where('parent_id='.$value['id']) -> order('id') -> select();
            $Onetype[$key]['two'] = $Twotype;
        }
        
        if( $Onetype )  return $Onetype;
    }
    
    /**
    * 根据顶级业务ID获取所有的二级业务信息
    */

    public function Getdata($id){
        $res =  $this -> field('id') -> where("parent_id = $id") -> select();

        if( $res ){
           $str = '';
            foreach ($res as $val) {
                $str .=$val['id'].',';
            }
            $res = trim($str,',');
        }

       return $res;
    }
    
    /**
     * 获取所有一级业务的id 、name
     */
    public function getFirstServer() {
        
        return $this -> field('id,server_name,orderid') -> where('parent_id=0') -> order(' orderid ') -> select();
      
    }
    
    /**
     * 获取二级
     * @param int $id
     * @return array
     */
    public function getTwotypes($id){
	return  $this -> field('id,server_name,orderid') -> where("parent_id = $id") -> order(' orderid ') -> select();
    }

    /**
     * 取业务名
     * @param int $tid
     * @return array
     */
    public function getName($tid) {
        return $this -> field('server_name,orderid') -> where("id = $tid") -> find();
    }
    /**
     * 获取最上级业务名称
     */
    function Getserverpname($server_id)
    {
            $server_arr = $this -> where("id =".$server_id) -> find();
            return $server_arr["server_name"];
    }
} 