<?php
namespace Home\Model;
use Think\Model;
class ServiceModel extends Model {
	/*
		* 根据顶级业务ID获取所有的二级业务信息
	*/

	function Getdata($id)
	{
	    return $this -> where("parent_id = $id") -> select();
	}

	/*
		* 获取业务类型
	*/

	function Getid($pro_count , $server_id='' , $id)
	{
		$server_arr = $this -> field("id,server_name")-> order("orderid asc") -> where("parent_id = $id") -> select();
		return $this->Getagin($server_arr , $pro_count , $server_id , $id);
	}

	/*
		* 处理业务类型
	*/
	function Getagin($server_arr , $pro_count , $server_id , $id)
	{
		$ser_goods =array();
		$goods     =array();
		$where     = '1 = 1';
		foreach($server_arr as $k => $v)
		{
			$server_count[$v['id']]=$this -> join("shop_goods on shop_goods.s_id = shop_service.id")->where("shop_goods.s_id = " . $v['id']." and shop_goods.status=1 and shop_goods.is_gift=0") -> count();
                        if($server_id == $v['id'])
			{
				$server_count[$v['id']] = $server_count[$v['id']] - $pro_count;
			}
			//echo "<pre>";
			//echo $server_count[$v['id']]."@";
			if($server_count[$v['id']] == 0)
			{
				$str=$v['id'];
			}
			if($server_count[$v['id']] != 0)
			{
				$ser_goods[$v['id']] = $server_count[$v['id']];
			}
			
		}
		if(!in_array(0,$ser_goods)) {
			$where .= " and parent_id = $id";
		}else {
			$where .= ' and parent_id = $id and id<>$str';
		}
		$server_data = $this -> field("id,server_name")-> order("orderid asc") -> where($where) -> select();
		foreach($ser_goods as $k => $v)
		{
			foreach($server_data as $key => $val)
			{
				if($k == $val['id'])
				{
					$val['more'] = $ser_goods[$k];
					$goods[] = $val;
				}
			}
		}
		return array("goods" => $goods , "pro_count" => $pro_count ,'id' => $id);
	}
        
     /*
		* 获取一级业务
     */
        
    public function getFirstType(){

            $data = $this->field('id,server_name')->where('parent_id=0')->order(' orderid asc ')->select();
            return $data;
    }
    
} 