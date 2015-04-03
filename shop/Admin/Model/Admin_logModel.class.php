<?php
namespace Admin\Model;
use Think\Model;
class Admin_logModel extends Model{
    
        public function getLogInfo($start,$end,$where){
            $data = M("admin_log")->where($where)->order(' id desc ')->limit($start,$end)->select();
            
            return $data;
	}
	public function getCount($where){
		return  $data = M("admin_log") -> where($where) ->count();
	}

}