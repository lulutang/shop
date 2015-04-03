<?php
/**
 * 财务对账Model
 * @author lijiandong
 * @createtime 2015-3-2
 */
namespace Admin\Model;
use Think\Model;

class UorderModel extends Model {
    public $zero   = 0;
    public $one    = 1;
    public $two    = 2;
    public $five   = 5;
    public $six    = 6;
    public $seven  = 7;
    public $eight  = 8;
    public $nine   = 9;
    public $ten    = 10;
    public $seixzz = 86400;
    public $twenty  = 20;
    public $fifty  = 50;
    public $mem    ='m';
    private $flowdel = array(2,3,4,5,6,7);
    //订单商品流程轴顺序
    private $processOrder = array(5,7,2,1,3,4,9,8,6);
    public $orderGoodsStatus = array(
        "1" => array("编修审核",   "s_c_qingse", "time" => "com_time" , "name" =>"co_user_name"),
        "2" => array("信息初审",   "s_c_blue",   "time" => "infomationtime" , "name" =>"informationhuman"),
        "3" => array("报件",      "s_c_purple", "time" => "into_pieces" , "name" =>"pie_user_name"),
        "4" => array("下发受理",   "s_c_yellow", "time" => "into_accept_time" , "name" =>"xf_user_name"),
        "5" => array("已支付",     "s_c_red",    "time" => "pay_time" , "name" => "user_name"),
        "6" => array("服务已结束",  "s_c_grey",   "time" => "server_endtime" , "name" =>"server_endhuman"),
        "7" => array("服务已开始",  "s_c_green",  "time" => "server_starttime" , "name" =>"server_starthuman"),
        "8" => array("下发注册证",  "s_c_green2", "time" => "into_res" , "name" =>"zc_user_name"),
        "9" => array("初审公告",    "s_c_brown",  "time" => "trialtime" , "name" =>"cs_user_name"),
    );

    /**
     * 获取订单
     * @param string $where 查询条件
     * @param int $start 查询开始位置
     * @param int $end 查询结束位置
     * @param array $sort 按照时间排序
     * @param string $where 搜索条件
     */
    public function getOrderAll($start , $end , $sort , $where) {
        $order = M("order");
        if(empty($sort))
        {
            $sort=array("order_id","desc");
        }
        //订单商品
        $orderGoods = $this -> getOrderGoods();
        //订单
        $getOrder = $order -> where($where) -> order("$sort[0] $sort[1]") -> limit($start,$end) ->select();
        //用户
        $bossWitMemData = $this -> bossWaiterMember();
        $interArr= array();
        $orderAll = array();
        $str=$this -> zero;
        foreach($getOrder as $k => $v)
        {
            foreach($bossWitMemData as $bw => $md)
            {
                if($v["user_id"] == $md["kid"])
                {
                    $v["salesman"] = $md["pname"];
                }
            }
            foreach($orderGoods as $key => &$val) 
            {
                if($v["order_id"] == $val["order_id"])
                {
                   
                    $interArr[]=$val;
                    $str++;
                }   
            }
            if($k%$this -> two == $this -> zero)
            {
                $v["trcolor"]="tr_bgc1";
            }else{
                $v["trcolor"]="tr_bgc2";
            }
            $v['orderfind'] = $interArr[0];
            unset($interArr[0]);
            $v["orderg"] = $interArr;
            $v["count"] = $str;
            $str=$this -> zero;
            $orderAll[] = $v;
            $interArr = array();
        }
        return $orderAll;
      
    }
    
    /**
     * 拼接跳转链接
     */
    public function cateNote($get , $give)
    {
        foreach($get as $k=>$v)
        {
            if(!empty($get[$k]) || $get[$k] == number_format($this -> zero))
            {
                $give .= $k."/".$get[$k]."/";
            }
        }
        return $give;
    }
    
    /**
     * 统计订单数量
     * @param string $where 查询条件
     */
    public function getOrderCount($where) {
        $order = M("order");
        return $order -> where($where) -> count();
    }
    
    /**
     * 拼接订单搜索条件
     * @param array $getData 表单提交查询数据
     */
    public function term($getData) {
        $start = str_replace("+"," ",$getData["starttime"]);
        $end = str_replace("+"," ",$getData["endtime"]);
        if(isset($getData["status"]))
        {
            $where = "status=".$getData["status"];
        }else{
            $where = "status=1";
        }
        if(!empty($start) && !empty($end))
        {
            $where .=" AND ". $getData['ordertime'] ." BETWEEN ". strtotime($start) ." AND ". strtotime($end);
        }else if(!empty($start) && empty($end))
        {
            $where .=" AND ". $getData['ordertime']."=". strtotime($start);
        }else if(empty($start) && !empty($end))
        {
            $where .=" AND ". $getData['ordertime']."=". strtotime($end);
        }
        if(!empty($getData["order_card"]))
        {
            $where .=" AND order_card like '%". $getData["order_card"] ."%'";
        }
        return $where;
    }
    
    /**
     * 根据条件获取订单单条数据
     * @param string $where 查询条件
     */
    public function getOrderData($where) {
        $order = M("order");
        $getOrderFind = $order -> where($where) ->find();
        $addressWhere = "user_id =".$getOrderFind["user_id"];
        $receiver = $this -> cargo($addressWhere);
        return array(
                        "getOrderFind" => $getOrderFind ,
                        "receiver"     => $receiver
                    );
    }
    
    /**
     * 获取发票信息
     */
    public function getBillFind($where) {
        $bill = M("bill");
        $getBillFind = $bill -> where($where) ->find();
        return $getBillFind;
    }

    /**
     * 获取订单商品
     * @param string $where 查询条件
     * @param int    $start 开始位置
     * @param int    $end   结束位置
     * @param string $sort  排序方式
     */
    public function getOrderGoods($where , $start , $end , $sort) {
        $orderGoods = M("order_goods");
        if(empty($where))
        {
            $where = "1=1";
        }
        $needData = $this -> goodsNeedAll();
        $brandStatus   = $this -> orderGoodsStatus;
        if(!empty($end))
        {
            $orderGoodsData = $orderGoods -> order("pay_time $sort") -> where($where) -> limit($start , $end) ->select();
        }else{
            $orderGoodsData = $orderGoods -> where($where) ->select();
        }
        $goodsData = array();
        for($i=$this -> one ; $i<=count($brandStatus) ; $i++)
        {
            foreach($orderGoodsData as $k => $v)
            {
                if($i == $v["virt_status"])
                {
                    $orderGoodsData[$k]["brandStatus"] = $brandStatus[$i][$this->zero];
                    $orderGoodsData[$k]["color"] = $brandStatus[$i][$this->one];
                }
            }
        }
        foreach($orderGoodsData as $k => &$v)
        {
            foreach($needData as $nk => $nv)
            {
                if($nv["goods_id"] == $v["id"]){
                    $v["subd_num"] = $nv["subd_num"] - $this -> ten;
                    $v["subd"] = $nv["subd"];
                }
            }
            if($k%$this -> two == $this ->zero)
            {
                $v["trcolor"]="tr_bgc1";
            }else{
                $v["trcolor"]="tr_bgc2";
            }
            $v["message"]  = json_decode($v["message"],true);
            if(!empty($v["message"]["text"])){
                 $v["mbtext"] = mb_substr($v["message"]["text"], $this -> zero, $this -> six , 'utf-8');
            }
        }
        return $orderGoodsData;
    }
    
    /**
     * 拼接订单商品搜索条件
     * @param array $getData 要拼接数据
     */
    public function termOrderGoods($getData) {
        $where = "is_pay=1";
        if(isset($getData["titlename"]) && !empty($getData["searchdata"]))
        {
            $where .=" AND ". $getData["titlename"]." like '%".$getData["searchdata"]."%'";
        }
        if(!empty($getData["starttime"]) && !empty($getData["endtime"]))
        {
            $where .=" AND pay_time BETWEEN ". strtotime($getData["starttime"]) ." AND ". strtotime($getData["endtime"]);
        }else if(!empty($getData["starttime"]) && empty($getData["endtime"]))
        {
            $where .=" AND pay_time"."=". strtotime($getData["starttime"]);
        }else if(empty($getData["starttime"]) && !empty($getData["endtime"]))
        {
            $where .=" AND pay_time"."=". strtotime($getData["endtime"]);
        }
        if(!empty($getData["erji"]))
        {
            $where .=" AND goods_id =". $getData["erji"];
        }
        if(!empty($getData["status"]))
        {
            $where .=" AND virt_status =". $getData["status"];
        }
        
        return $where;
    }
    
    /**
     * 统计已支付订单商品数量
     * @param string $where 查询条件
     */
    public function orderGoodsCount($where) {
        $orderGoods = M("order_goods");
        $orderGoodsData = $orderGoods -> where($where) -> count();
        return $orderGoodsData;
    }
    
    /**
     * 获取订单商品单条数据
     * @param string $where 查询条件
     */
    public function getFindOrderGoods($where) {
        $orderGoods = M("order_goods");
        $orderGoodsFind = $orderGoods -> where($where) -> find();
        $needWhere = "goods_id=".$orderGoodsFind["id"];
        $goodsNeedFind  = $this ->goodsNeed($needWhere);
        if(!empty($goodsNeedFind["goods_id"])){
            $orderGoodsFind["subd_num"] = $goodsNeedFind["subd_num"];
            $orderGoodsFind["subd"] = $goodsNeedFind["subd"];
        }
        $orderGoodsFind["message"] = json_decode($orderGoodsFind["message"],true);
        return $orderGoodsFind;
    }
    
    /**
     * 申请人信息
     * @param string $where 查询条件
     */
    public function getFindtrader($where) {
        $trader = M("trader");
        $traderFind = $trader -> where($where) -> find();
        return $traderFind;
    }
    
    /**
     * 前台用户
     * @param string $where 会员搜索条件
     */
    public function vipUser($where) {
        $userInfos = $this -> db($this -> one,"DB_CONFIG1") -> table("ecs_users")-> where($where) -> find();
        return $userInfos;
    }
    
    /**
     * 获取收货人地址
     * @param string $where 查询条件
     */
    private function cargo($where) {
        $address = M("address");
        $receiver = $address -> where($where) -> find();
        return $receiver;
    }
    
    /**
     * 获取excel订单下载数据
     * @param string $where 下载订单搜索条件
     */
    public function orderDownData($where) {
        $order = M("order od");
        $field="od.paypaper,od.coil_money,od.pay_money,od.onsale_money,od.goods_number,od.totalprice,od.order_card,od.status,od.createtime,od.pay_time,od.is_invoile,ogs.erji,ogs.goods_price,ogs.bargain,ogs.user_name,ogs.phone,goods.short_title,goods.cost,bwm.pname";
        $odOgsGoods= $order -> field($field) -> join("shop_order_goods AS ogs ON od.order_id=ogs.order_id") -> join("shop_goods AS goods ON ogs.goods_id=goods.goods_id") -> join("shop_boss_waiter_member AS bwm ON od.user_id=bwm.kid") -> where($where) -> select();
        foreach($odOgsGoods as $k => &$v)
        {
            if($v["status"] == $this -> one){
                $v["status"]="已支付";
                $v["pay_time"] = date("Y-m-d H:i:s" , $v["pay_time"]);
            }else if($v["status"] == $this -> zero){
                $v["status"]="未支付";
            }
            if($v["is_invoile"] == $this -> one){
                $v["is_invoile"]="开";
            }else if($v["is_invoile"] == $this -> zero){
                $v["is_invoile"]="不开";
            }
            $v["createtime"] = date("Y-m-d H:i:s" , $v["createtime"]);
            $v["profit"] = $v["cost"] - $v["goods_price"];
            
        }
        return $odOgsGoods;
    }
    
    /**
     * 获取业务员掌柜客官的信息
     */
    public function bossWaiterMember()
    {
        $bosWaiMem     = M("boss_waiter_member");
        $bosWaiMemData = $bosWaiMem -> select();
        return $bosWaiMemData;
    }
    
    /**
     * 处理查询业务数据
     */
    public function handleService($parentservice) {
        $servicedata = array();
        foreach($parentservice as $k => $v)
        {
            foreach($v["two"] as $key => $val)
            {
                $servicedata[] = $val;
            }
        }
        return $servicedata;
    }
    
    /**
     * 商品流程轴
     * @param int $ordergid 订单商品id
     */
    public function commodityFlow($ordergid) {
        $orderGoodsWhere = "id=$ordergid";
        $process = $this -> processOrder;
        $flowAxis = array();
        $num='';
        $goodsFlow = $this -> orderGoodsStatus;
        $orderGoodsData = $this -> getFindOrderGoods($orderGoodsWhere);
        $compileWhere ="ordergoods_id =". $orderGoodsData['id'];
        $editData = $this -> compileData($compileWhere);
        foreach($process as $key => $val)
        {
            foreach($goodsFlow as $k => &$v)
            {
                if($val == $k)
                {
                    $v["flowstatus"] = $k;
                    if($val == $this -> five){
                        $v["numtime"] = $orderGoodsData[$v["time"]];
                        $v["uname"]   = $orderGoodsData[$v["name"]];
                    }else{
                        $v["numtime"] = $editData[$v["time"]];
                        $v["uname"]   = $editData[$v["name"]];
                    }
                    
                    $flowAxis[] = $goodsFlow[$k];
                }    
            } 
        }
        foreach($flowAxis as $k => &$v)
        {
            if($v["flowstatus"] == $orderGoodsData["status"])
            {
                   $num=$k;
            }
            if($k > $num && !empty($num))
            {
                if($k == $this -> eight)
                {
                    $v["flowcolor"] = "last";
                }else{
                    $v["flowcolor"] = "";
                }
            }elseif($k < $num || empty($num)){
                $v["flowcolor"] = "s_stated";
            }elseif($orderGoodsData["status"] == $this -> six){
                $v["flowcolor"] = "cur last";
                $tecolor= $k;
            }else{
                $v["flowcolor"] = "cur";
                $tecolor= $k;
            }
            $v["nowstatus"] = $orderGoodsData["status"];
        }
        if(!empty($flowAxis[8]["numtime"]))
        {
            $flowAxis[8]["flowcolor"] = "s_stated last";
            $flowAxis[7]["flowcolor"] = "s_stated";
        }else{
            $flowAxis[$tecolor-$this -> one]["flowcolor"]="s_stated cured";
        }
        return $flowAxis;
    }
    
    /**
     * 处理后订单商品流程轴
     * @param int $orderGid 订单商品id
     */
    public function commodityFlowTwo($orderGid) {
        $goodsFlow = $this -> commodityFlow($orderGid);
        $flownumk = $this -> flowdel;
        for($i=$this -> zero ; $i<count($flownumk); $i++)
        {
            unset($goodsFlow[$flownumk[$i]]);
        }
        if($goodsFlow[8]["nowstatus"] == $this -> six && empty($goodsFlow[8]["numtime"]))
        {
            $goodsFlow[1]["flowcolor"] = "s_stated cured";
        }
        return $goodsFlow;
    }
    
    /**
     * 修改专利部分信息
     * @param array $orderGoods 订单商品数据
     * @param array $get 要修改的数据
     * @param string $where 修改条件
     */
    public function overPatentData($orderGoods , $get , $where)
    {
        $order = M("order_goods");
        $orderGoods = $orderGoods["message"];
        $overData["name"] = !empty($get["name"]) ? str_replace("\n", "", $get["name"]) : $orderGoods["name"];
        $overData["text"] = !empty($get["text"]) ? str_replace("\n", "", $get["text"]) : $orderGoods["text"];
        $overData["style"] = !empty($get["style"]) ? str_replace("\n", "", $get["style"]) : $orderGoods["style"];
        $overData["short_title"]    = $orderGoods["short_title"];
        $overData["j_info"]         = $orderGoods["j_info"];
        $overData["address"]        = $orderGoods["address"];
        $jsonMessage["message"]     = json_encode($overData);
        $upStatus =  $order -> where($where) -> save($jsonMessage);
        return $upStatus;
    }
    /**
     * 商标注册修改 
     * @param array $post 修改数据
     */
    public function tardUpData($orderGoods , $post , $ordergid) {
        $orderG = M("order_goods");
        $trader = M("trader");
        $goodsNeedModel = M("goods_need");
        $orderGoods = $orderGoods["message"];
        $jsonMessage = array(
                        "name"        => $post["goods_name"],
                        "text"        => $orderGoods["text"],
                        "short_title" => $orderGoods["short_title"],
                        "style"       => $post["style_name"],
                        "j_info"      => $orderGoods["j_info"],
                        "address"     => $orderGoods["address"]
                );
        $dataArr= explode('|', $post["twosmall"]);
        $goodsNeedWhere = "goods_id=$ordergid";
        $goodsDataFind = $this -> goodsNeed($goodsNeedWhere);
        foreach($dataArr as $k=>$v)
        {
            $strsmall[] = explode(":",$v);
        }
        foreach($strsmall as $key=>$val)
        {
            $bigData[$val[0]][]=explode(";",$val[1]);
        }
        $bigData["num"]=$goodsDataFind["style_part"]["num"];
        $bigData["price"]=$goodsDataFind["style_part"]["price"];
        
        //订单商品
        $orderGoodsData["style_name"] = $post["style_name"];
        $orderGoodsData["message"]    = json_encode($jsonMessage);
        !empty($post["enroll"]) ? $orderGoodsData["enroll"]     = $post["enroll"] : false;
        
        //申请人表
        $traderData["apply"] = implode(",", $post["apply"]);
        $traderData["priority"] = implode(",", $post["priority"]);
        $traderData["applyshowstate"] = $post["applyshowstate"];
        $traderData["applyshowtime"]  = strtotime($post["applyshowtime"]);
        $traderData["applynum"]       = $post["applynum"];
        $traderData["trader_name"]    = $post["trader_name"];
        
        //商品需求表
        $goodsNeedsave["name"] = $post["goods_name"];
        $goodsNeedsave["need_state"] = implode(",", $post["priority"]);
        $goodsNeedsave["need_prior"] = implode(",", $post["apply"]);
        $goodsNeedsave["area"] = $post["applyshowstate"];
        $goodsNeedsave["need_time"] = strtotime($post["applyshowtime"]);
        $goodsNeedsave["need_number"] = $post["applynum"];
        $goodsNeedsave["style"] = $post["style_name"];
        !empty($post["twosmall"]) ? $goodsNeedsave["style_part"]=  json_encode($bigData):false;
        $goodsNeedsave["trader_uname"] = $post["trader_name"];
        $goodsNeedsave["subd_num"]   = count(explode(";",$post["subd"]));
        $goodsNeedsave["subd"]       = $post["subd"];
        
        $orderGstatus  = $orderG -> where("id = $ordergid") -> save($orderGoodsData); 
        $traderstatus  = $trader -> where("ordergoods_id = $ordergid") -> save($traderData);
        $goodsSaveData = $goodsNeedModel -> where($goodsNeedWhere) -> save($goodsNeedsave);
        if(isset($orderGstatus) && isset($traderstatus) && isset($goodsSaveData)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 商品需求信息
     * @param string $name Description
     */
    public function goodsNeed($where) {
        $goodsNeedModel = M("goods_need");
        $goodsData = $goodsNeedModel -> where($where) -> find();
        $goodsData["style_part"] = json_decode($goodsData["style_part"],true);
        return $goodsData;
    }
    
    /**
     * 获取商品需求信息
     * @param string $where 查询条件
     */
    public function goodsNeedAll($where) {
        $need = M("goods_need");
        if(empty($where)){
            $where = "1=1";
        }
        $needData = $need -> where($where) -> select();
        return $needData;
    }
    
    /**
     *获取服务商品流程信息 
     * @param string $where 查询条件
     */
    public function compileData($where) {
       $compile = M("compile");
       $comData = $compile -> where($where) -> find();
       $comData["user_message"] = json_decode( $comData["user_message"] , true);
       return  $comData;
    }
    
    /**
     *服务开始添加信息 
     * @param array $getData 要添加数据
     * @param array $user    用户信息
     */
    public function addCompile($user , $getData) {
       $compile    = M("compile");
       $orderGoods = M("order_goods");
       $where = "id = ".$getData["ordergoods_id"];
       $getFindorderGoods = $this -> getFindOrderGoods($where);
       $flowArr = $this -> processOrder;
       if($getData["goodsstatus"] != $this -> six)
       {
            $key = array_search($getData["goodsstatus"] , $flowArr);
            if(!empty($getData["status"])){
                $orderGoodsData["status"] = $getData["status"];
            }else{
               $orderGoodsData["status"] = $flowArr[$key+1]; 
               $orderGoodsData["virt_status"] = $flowArr[$key];
            }
            $orderGoods -> where("id=".$getData["ordergoods_id"]) -> save($orderGoodsData); 
       }
       if($getData["goodsstatus"] == $this -> seven)
        {
            unset($getData["goodsstatus"]);
            $getData["server_starttime"]    = time();
            $getData["server_starthuman"]   = $user["truename"];
            $getData["server_starthumanid"] = $user["userid"];
            $getData["order_id"]            = $getFindorderGoods["order_id"];
            $compile -> add($getData);
       }elseif($getData["goodsstatus"] == $this -> two){
            unset($getData["goodsstatus"]);
            $getData["into_time"]          = time();
            $getData["run_name"]           = $user["truename"];
            $getData["run_phone"]          = $user["phone"];
            $getData["run_branch"]         = $user["branch"];
            $getData["infomationtime"]     = time();
            $getData["informationhuman"]   = $user["truename"];
            $getData["informationhumanid"] = $user["userid"];
            $getData["status"]             = $this -> one;
            $compile -> where("ordergoods_id =". $getData["ordergoods_id"]) -> save($getData);
       }else{
            $getGoods["virt_status"] = $getData["goodsstatus"];
            $getData["status"] = $getData["goodsstatus"];
            unset($getData["goodsstatus"]);
            $getData["server_endtime"]     = time();
            $getData["server_endhuman"]   = $user["truename"];
            $getData["server_endhumanid"] = $user["userid"];
            $orderGoods -> where("id=".$getData["ordergoods_id"]) -> save($getGoods);
            $compile -> where("ordergoods_id =". $getData["ordergoods_id"]) -> save($getData);
       }
       return $getData["ordergoods_id"];
    }
  
    
    /**
     * 根据时间获取发票数据
     * @param type $getData
     */
    public function getWhere($getData) {
        if($getData["daytime"] == $this -> one)
        {
            //天
            $nowtime = strtotime(date('Y-m-d'));
            $num     = $getData["daytime"];
        }elseif($getData["daytime"] == $this -> seven){
            //周
            $nowtime = strtotime(date("Y-m-d H:i:s",mktime($this -> zero,$this -> zero,$this -> zero,date("m"),date("d")-date("w")+$this -> one,date("Y"))));
            $num     = $getData["daytime"];
        }elseif($getData["daytime"] == $this -> mem){
            //月
            $nowtime = strtotime(date("Y-m-d H:i:s",mktime($this -> zero, $this -> zero, $this -> zero,date("m"),$this -> one,date("Y"))));
            $num     = date(t,strtotime(date("Y-m-d")));
        }
        //结束时间
        $endtime = $nowtime+$num*$this -> seixzz;
        $where   ="pay_time >".$nowtime." AND pay_time<=".$endtime;
        if(!empty($getData["daytime"])){
            return $where;
        }   
    }
    
    /**
     * 发票管理详细信息
     * @param int $order_id 订单id
     */ 
    public function getDetailed($order_id) {
        $order = M("order od");
        $where = "od.order_id =". $order_id;
        $field = "od.order_id,od.order_card,od.totalprice,bl.order_id,bl.bill_type,";
        $field.="bl.bill_id,bl.bill_status,bl.company_name,bl.taxes_number,bl.address,bl.phone,bl.bank,bl.bank_number,bl.company_linease,bl.allow_phoro,bl.taxes_phore,bl.taxes_prove";
        $orderReceipt = $order ->field($field) -> join("shop_bill AS bl ON od.order_id=bl.order_id") -> where($where) -> find();
        return $orderReceipt;
    }
    
    /**
     * 发票开票成功
     * @param int $order_id 订单id
     */
    public function upSuccess($order_id) {
        $order = M("order");
        $bill  = M("bill");
        $where = "order_id =". $order_id;
        $updata["bill_status"] = $this -> one;
        $updata["bill_createtime"] = time();
       $dataOrder = $order -> where($where) -> save($updata);
       $dataBill  = $bill -> where($where) -> save($updata);
        if(!empty($dataOrder) && !empty($dataBill)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 开发票失败
     * @param int $bill_id 发票id
     */
    public function defeated($bill_id)
    {
        $bill  = M("bill");
        $where = "bill_id =". $bill_id;
        $updata["is_reject"]  = $this -> one;
        $updata["rejecttime"] = time();
        $billData = $bill -> where($where) -> save($updata);
        if(!empty($billData)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 发票管理下载excel表格
     * @param string $downWhere 下载条件
     */
    public function novileDownData($downWhere) {
        $order = M("order od");
        $field = "og.order_id,og.pay_time,og.bargain,og.user_name,og.erji,";
        $field.= "bl.order_id,bl.bill_type,bl.bill_title,bl.taxes_phore,bl.address,bl.phone,bl.bank,bl.bank_number,bl.bill_status,";
        $field.= "od.order_id,od.order_card,od.goods_number,od.totalprice,od.status,od.is_invoile,od.pay_time";
        $orderData   = $order ->field($field) ->join("shop_order_goods AS og ON od.order_id=og.order_id") -> join("shop_bill AS bl ON od.order_id=bl.order_id") -> where($downWhere) -> select();
        foreach($orderData as $k=>&$v)
        {
            $v["bankbank_number"] = $v["bank"]."|".$v["bank_number"];
            $v["addressphone"]    = $v["address"]."|".$v["phone"];
            if($v["bill_type"] == $this -> one){
                $v["bill_type"]   = "专票";
            }else{
                $v["bill_type"]   = "普票";
            }
            if($v["bill_status"] == $this -> one){
                $v["bill_status"] = "已开发票";
            }else{
                $v["bill_status"] = "未开发票";
            }
            $v["pay_time"]=date("Y-m-d H:i:s",$v["pay_time"]);
        }
        return $orderData;
    }
    
    /**
     * 调节注册商标大类小类
     * @param array $style 类别数组
     * @param array $get   匹配名称和状态
     */
    public function findCategory($style , $get) {
       return $get["code"]==$this -> one ? $this -> bigCategory($style , $get["catename"]) : $this -> smaillCategory($style , $get["twocatename"] , $get["onecatename"]);
    }
    
    /**
     * 根据大类匹配二级分类
     * @param array  $style    类别数组
     * @param string $catename 一级类别名称
     */
    private function bigCategory($style , $catename) {
        $twoCategory= array();
        foreach($style as $k=>$v)
        {
            if($catename == $k)
            {
               foreach($v as $key=>$val)
               {
                    $twoCategory[]["name"] = $key;
               }
            }
        }
        return $twoCategory;
    }
    
    /**
     * 根据大类匹配三级分类
     * @param array  $style    类别数组
     * @param string $catename 二级类别名称
     */
    private function smaillCategory($style , $catename ,$onecatename) {
        $twoCategory= array();
        foreach($style as $k=>$v)
        {
            if($onecatename == $k)
            {
                foreach($v as $key=>$val)
                {
                    if($key == $catename)
                    {
                        foreach($val as $tk=>$tv)
                        {
                            $twoCategory[]["name"] = $val[$tk];
                        }
                    }
                }
            }
        }
        return $twoCategory;
    }
    
    /**
     * 拼接下载条件
     * @param array  $get 要拼接数据
     * @param string $where 查询条件
     */
    public function downWhere($get , $where) {
        $start = strtotime(str_replace("+"," ",$get["starttime"]));
        $end = strtotime(str_replace("+"," ",$get["endtime"]));
        if(is_numeric($start) && is_numeric($end)){
            $where .= " AND od.pay_time" ." BETWEEN ". $start ." AND ". $end;
        }else{
            if(is_numeric($get["daytime"])){
                $gwtwhere = $this -> getWhere($get);
                if(!empty($gwtwhere))
                {
                    $where .= " AND ".str_replace("pay_time", "od.pay_time", $gwtwhere);
                }
            }
        }
        return $where;
    }
    
    /**
     * 获取所有的商品
     * @param string goodsDataAll 获取商品条件
     */
    function goodsDataAll($where) {
        $goods = M("goods");
        $goodsData = $goods -> where() -> select();
        return $goodsData;
    }
    
    /**
     * 匹配类别
     * @param array $style 所有类别数组
     * @param array $get   搜索条件
     */
    public function searchCate($style , $get) {
        return count($get)==$this -> two ? $this -> twoCate($style , $get) : $this -> threeCate($style , $get);
    }

    /**
     * 搜索二级分类
     * @param array $style 所有类别数组
     * @param array $get   搜索条件
     */
    public function twoCate($style ,$get) {
        $twoCategory = array();
        foreach($style as $k => $v)
        {
            if($k == $get["catename"])
            {
                foreach($v as $key => $val){
                    $percent = strpos($key,$get["twoname"]);
                    if(!empty($percent))
                    {
                        $twoCategory[]["name"] = $key;
                    }
                }
            }
        }
        return $twoCategory;
    }

    /**
     * 搜索三级分类
     * @param array $style 所有类别数组
     * @param array $get   搜索条件
     */
    public function threeCate($style , $get) {
        $twoCategory = array();
        foreach($style as $k => $v)
        {
            if($k == $get["onename"])
            {
                foreach($v as $key => $val){
                    if($key == $get["twoname"])
                    {
                        foreach($val as $kk => $vv){
                            $percent = strpos($vv,$get["threename"]);
                            if(!empty($percent))
                            {
                                $twoCategory[]["name"] = $vv;
                            }
                        }
                    }
                   
                }
            }
        }
        return $twoCategory;
    }
    
    /**
     * 申请人多条信息
     * @param string $where 查询条件
     */
    public function getAlltrader($where) {
        $trader = M("trader");
        $traderAll = $trader -> where($where) -> select();
        return $traderAll;
    }
}