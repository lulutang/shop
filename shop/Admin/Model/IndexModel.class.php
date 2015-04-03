<?php
/**
 * 后台主页面Model
 * @author 李建栋
 *
 */
namespace Admin\Model;

use Think\Model;

class IndexModel extends Model {

    /**
     * 根据时间取出订单的数据,今天
     * @return  array
     */
    public function order_day()
    {
        $nowtime = strtotime(date('Y-m-d')); 
        $data = $this -> Getall($nowtime,1);
        $c_query = $this -> Get_count($nowtime,1);
        $old_data = $this -> order_oldday();        
        $sdata = $this -> order_handle($data);
        return array("new_data" => $sdata , "old_data" => $old_data ,"newc_data" => $c_query);
    }

    /**
     * 根据时间取出订单的数据,昨天
     * @return array
     */
    public function order_oldday()
    {
        $nowtime = strtotime(date('Y-m-d',strtotime('-1 days'))); 
        $data = $this -> Getall($nowtime,1);
        $c_query = $this -> Get_count($nowtime,1);
        $sdata = $this -> order_handle($data);
        return array("old_data"=>$sdata,"oldc_data"=>$c_query);
    }

    /**
     * 根据时间取出订单的数据,本周
     * @return array
     */
    public function order_week()
    {
       $nowtime = strtotime(date("Y-m-d H:i:s",mktime(0,0,0,date("m"),date("d")-date("w")+1,date("Y")))); //昨天时间
       $data = $this -> Getall($nowtime,7);
       $c_query = $this -> Get_count($nowtime,7);
       $sdata = $this -> order_handle($data);
       return array("old_data"=>$sdata,"oldc_data"=>$c_query);
    }

    /**
     * 根据时间取出订单的数据,本月
     * @return array
     */
    public function order_month()
    {
       $nowtime = strtotime(date("Y-m-d H:i:s",mktime(0, 0, 0,date("m"),1,date("Y")))); 
       $data = $this -> Getall($nowtime,date(t,strtotime(date("Y-m-d"))));
       $c_query = $this -> Get_count($nowtime,date(t,strtotime(date("Y-m-d"))));
       $sdata = $this -> order_handle($data);
       return array("old_data"=>$sdata,"oldc_data"=>$c_query);
    }

    /**
     * 根据时间取出订单的数据,本季度
     * @return array
     */
    public function order_quarter()
    {
       $season = ceil((date('n'))/3);
       $nowtime = strtotime(date('Y-m-d H:i:s', mktime(0, 0, 0,$season*3-3+1,1,date('Y')))); //昨天时间
       $nowendtime = strtotime(date('Y-m-d H:i:s', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'))));
       $data = $this -> Getall($nowtime ,'',$nowendtime);
       $c_query = $this -> Get_count($nowtime,'',$nowendtime);
       $sdata = $this -> order_handle($data);
       return array("old_data"=>$sdata,"oldc_data"=>$c_query);
    }
    
    /**
     * 根据时间取出订单的数据,本年
     * @return array
     */
    public function order_year()
    {
       $nowtime = strtotime(date("y")."-01-01 00:00:00");
       $nowendtime = strtotime((date('Y')+1)."-01-01 00:00:00");
       $data = $this -> Getall($nowtime ,'',$nowendtime);
       $c_query = $this -> Get_count($nowtime,'',$nowendtime);
       $sdata = $this -> order_handle($data);
       return array("old_data"=>$sdata,"oldc_data"=>$c_query);
    }

    /**
     * 根据时间取出订单的数据,全部
     * @return array
     */
    public function order_all()
    {
       $Order = M("order");
       $all_sql = "select * from shop_order";
       $all_arr = $Order -> query($all_sql);
       $sdata = $this -> order_handle($all_arr);
       return array("old_data"=>$sdata,"oldc_data"=>$all_arr);
    }

    /**
     * 根据支付时间取出订单的数据
     * @param string $timeone 开始时间
     * @param int $num        天数
     * @param string $timetwo 结束时间
     * @return array
     */
    private function Getall($timeone ,$num, $timetwo=''){
        if(!empty($timetwo))
        {
            $endtime = $timetwo;
        }else{
            $endtime= $timeone+$num*86400;
        }
        $Order = M("order");
        $getall = $Order -> where("pay_time >".$timeone." and pay_time<=".$endtime) -> select();
        return $getall; 
    }

    /**
     * 根据创建时间取出订单的数据
     * @param string $timeone 开始时间
     * @param int $num        天数
     * @param string $timetwo 结束时间
     * @return array
     */
    private function Get_count($timeone ,$num, $timetwo='')
    {
        if(!empty($timetwo))
        {
            $endtime = $timetwo;
        }else{
            $endtime= $timeone+$num*86400;
        }
        $Order = M("order");
        $getall = $Order -> where("createtime >".$timeone." and createtime<=".$endtime) -> select();
        return $getall;
    }

    /**
     * 返回处理后的数据
     * @param array $order_arr 订单数据
     * @return array
     */
    private function order_handle($order_arr)
    {
        //线下支付确认订单
        $down_order = '0'; 
        //今日付款订单
        $pay_order  = '0';
         //今日总订单金额
        $total      = '0'; 
        foreach($order_arr as $k => $v)
        {
            $new_order = $new_order+1;
            if($v["pay_type"] == 0 && $v["status"] == 1)
            {
                $down_order = $down_order+1;
            }
            if($v["status"] == 1)
            {
                $pay_order = $pay_order+1;
            }
            $total += $v["pay_money"];
        }
        return array("down_order" => $down_order , "pay_order" => $pay_order , "total" => $total);
    }
}
