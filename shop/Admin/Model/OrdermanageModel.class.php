<?php
namespace Admin\Model;
use Think\Model;
class OrdermanageModel extends Model {
    
//    protected $_validate = array(
//        array('statusid','require','订单流程ID不能为空！'),
//        array('orderstatus','require','订单流程名称不能为空！')     
////        array('title','','标题已经存在！',0,'unique',1)
//    );
    //获取所有订单流程列表信息
    public function getList() {
        
        $lists = $this->order('orderid')->select();
  
        if( $lists )  return $lists;
    }
    
} 