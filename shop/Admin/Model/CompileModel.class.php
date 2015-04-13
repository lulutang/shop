<?php
/**
 * 编修模块总模型类
 * author  cjl
 * createtime 2015-3-2 13:41:20
 */
namespace Admin\Model;
use Think\Model;
class CompileModel extends Model{

    /**
     * 初始化方法
     * @var int $this->userid 当前登录用户ID
     * @var string $this->username 当前登录用户名字
     */
    public function _initialize() {
        $this->userid = session("userid");
        $this->username = session("truename");
    }
    /**
     * 获取编修信息
     * @param  int $start [分页开始值]
     * @param  int $end [分页结束值]
     * @param  string $where    [查询条件]
     * @return array 
     * @1 代表按照进入编修审核时间排序
     * @2 代表按照编修审核时间排序
     * @3 代表按照报至商标局时间排序
     * @4 代表按照编修受理时间排序
     * @5 代表按照注册下发时间排序
     */
    public function GetComPileInfo($start,$end,$where,$order,$type){
        switch ($type) {
            case 1: if($order == 'Q'){
                        $str = 'c.into_time desc';
                    }else{
                        $str = 'c.into_time';
                    } break;
            case 2: if($order == 'Q'){
                        $str = 'c.com_time desc';
                    }else{
                        $str = 'c.com_time';
                    } break;
            case 3: if($order == 'Q'){
                        $str = 'c.pieces_time desc';
                    }else{
                        $str = 'c.pieces_time';
                    } break;
            case 4: if($order == 'Q'){
                        $str = 'c.trialtime desc';
                    }else{
                        $str = 'c.trialtime';
                    } break;
            case 5: if($order == 'Q'){
                        $str = 'c.into_res desc';
                    }else{
                        $str = 'c.into_res';
                    } break;
            default: break;
        }
        
        $data = M() -> field('o.id,o.goods_id,o.message,c.co_id,o.pay_time,o.order_code,o.style_name,c.res_status,c.why_pass,c.run_branch,c.into_res,c.pieces_rennumber,c.status,c.into_time,c.com_time,c.pieces_status,c.pieces_time,c.trialtime')
                    -> table('shop_order_goods o, shop_compile c, shop_goods_need n')
                    -> where("o.id = c.ordergoods_id and o.need_id = n.need_id and ".$where)
                    -> order($str)
                    -> limit($start,$end)
                    -> select();
        
        foreach ($data as $key => $val) {
            $arr = json_decode($val['message'],true);
            $data[$key]['short_title'] = $arr['name'];
        }
        if(!empty($data)){
                return $data;
        }
        return FALSE;
    }
    /**
     * 获取编修审核的数量
     * @param  string $where    查询条件
     * @return int
     */
    public function GetComPileNum($where){
        $data = M() -> table('shop_order_goods o, shop_compile c, shop_goods_need n')
                    -> where("o.id = c.ordergoods_id and o.need_id = n.need_id and ".$where)
                    -> count("DISTINCT o.id");

        return $data;
    }
    
    /**
     * 编修详细
     * @param int $oid 订单商品id
     * @param int $cid 编修数据id
     * @return array
     */
    public function GetComMinute($cid){
        $fie = 'o.id oid,o.goods_id,o.need_id,o.message,o.enroll,c.co_id,o.consignee_name,o.user_name,o.consignee_phone,o.consignee_address,o.order_code,o.deal_id,o.addtime,o.pay_time,o.style_name,o.goods_id,o.goods_thumb,c.trialtime,c.into_pieces,c.pieces_time,c.infomationtime,c.res_status,c.run_name,c.run_phone,c.run_branch,c.server_starttime,c.into_time,c.is_pass,c.com_time,c.co_user_name,c.into_accept_time,c.pie_user_name,c.pieces_status,c.server_starthuman,c.informationhuman,c.pieces_rennumber,c.dispatch_time,c.c_dispatch_time,c.c_dispatch_num,c.c_accept_adj,c.dispatch_num,c.xf_user_name,c.accept_adj,c.server_endtime,c.server_endhuman,c.why_pass,c.zc_user_name,c.user_message,c.into_res,c.into_endtime,c.into_firsttime,c.into_endtime';
        $data = M() -> field($fie)
                    -> table('shop_order_goods o, shop_compile c')
                    -> where("o.id = c.ordergoods_id and  c.co_id = ".$cid."")
                    -> find();
        
        $goodsname = M('goods') -> field('short_title')
                                -> where("goods_id = ".$data['goods_id']."")
                                -> find();
                                
        $obj = M('trader') -> where("id = ".$data['deal_id']."")
                           -> find();
        $arr = json_decode($data['message'],true);
        
        foreach ($obj as $k => $v) {
            $data[$k] = $v;
        }
        $str = M('goods_need') -> where("need_id = ".$data['need_id']."") -> find();
        foreach ($str as $ks => $vs) {
            $data[$ks] = $vs;
        }
        $arro = explode(',', $data['need_state']);
        $string = '';
        foreach($arro as $key=>$val){
            switch ($val) {
                case 1: $string .= '集体商标  ';break;
                case 2: $string .= '证明商标  ';break;
                case 3: $string .= '以三维标志申请商标注册  ';break;
                case 4: $string .= '以颜色组合申请商标注册  ';break;
                case 5: $string .= '以声音标志申请商标注册  ';break;
                case 6: $string .= '两个以上申请人共同申请注册同一商标  ';break;
            }
        }
        $data['need_state'] = $string;
        $arrt = explode(',', $data['need_prior']);
        $strings = '';
        foreach($arrt as $key=>$val){
            switch ($val) {
                case 1: $strings .= '基于第一次申请的优先权  ';break;
                case 2: $strings .= '基于展会的优先权  ';break;
                case 3: $strings .= '优先权证明文件后补  ';break;
            }
        }
        $data['need_prior'] = $strings;
        $data['user_message'] = json_decode($data['user_message'], true);
       
        $data['goods_name'] = $arr['name'];
        return $data;
        
    }
    /**
     * 编修判定结果录入
     * @param array $data 录入数据
     * return int
     */
    public function SaveBxshData($data){
        $arr['com_time'] = time();
        $arr['is_pass'] = $data['is_pass'];
        $arr['why_pass'] = $data['causes'];
        $arr['co_user_id'] = $this->userid;
        $arr['co_user_name'] = $this->username;
        if($data['is_pass'] == 1){
            $arr['status'] = 2;
            $arr['into_pieces'] = time();
        }
     return $this -> where("co_id = ".$data['cid']."") -> save($arr);
    }
    
    /**
     * 报件结果录入
     * @param array $data 录入数据
     * return int
     */
    public function SaveBxbjData($data){
        $arr['pieces_status'] = $data['pie_type'];
        $arr['pieces_rennumber'] = $data['pie_number'];
        $arr['pieces_time'] = strtotime($data['pie_time']);
        $arr['into_accept_time'] = time();
        $arr['status'] = 3;
        $arr['pie_user_id'] = $this->userid;
        $arr['pie_user_name'] = $this->username;
        return $this -> where("co_id = ".$data['cid']."") -> save($arr);
    }
    /**
     * 受理结果录入
     * @param array $data 录入数据
     * return int
     */
    public function SaveXfslData($data){
        $arr['status'] = 4;
        $arr['dispatch_num'] = $data['dis_num'];
        $arr['dispatch_time'] = strtotime($data['dis_time']);
        $arr['accept_adj'] = $data['ac_url'];
        $arr['trialtime'] = time();
        $arr['xf_user_id'] = $this->userid;
        $arr['xf_user_name'] = $this->username;
        return $this->where("co_id = ".$data['cid']."")->save($arr);
    }
    /**
     * 初审结果录入
     * @param array $data 录入数据
     * return int
     */
    public function SaveCsggData($data){
        $arr['status'] = 5;
        $arr['c_dispatch_num'] = $data['dis_num'];
        $arr['c_dispatch_time'] = strtotime($data['dis_time']);
        $arr['c_accept_adj'] = $data['ac_url'];
        $arr['into_res'] = time();
        $arr['res_status'] = 1;
        $arr['cs_user_id'] = $this->userid;
        $arr['cs_user_name'] = $this->username;
        return $this -> where("co_id = ".$data['cid']."") -> save($arr);
    }
    /**
     * 注册证下发数据存储
     * @param array $data 注册证下发所需数据
     * @return string
     */
    public function SaveZcxfData($data){
        if($data['is_call'] == 1){
            $arr['res_status'] = 2;
            $arr['into_firsttime'] = time();
            return $this -> where("co_id = ".$data['cid']."") -> save($arr);
        }else{
            $obj['express'] = $data['express'];
            $obj['numbers'] = $data['numbers'];
            $obj['name'] = $data['name'];
            $obj['phone'] = $data['phone'];
            $obj['address'] = $data['address'];
            $arr['user_message'] = json_encode($obj);
            $arr['res_status'] = 3;
            $arr['zc_user_id'] = $this->userid;
            $arr['zc_user_name'] = $this->username;
            $arr['into_endtime'] = time();
            return $this -> where("co_id = ".$data['cid']."") -> save($arr);   
        }
    }
    /**
     * 得到第一条信息
     * @param int $status 得到数据的条件
     * return array
     */
    public function GetFirstData($status){
        $str = '';
        switch ($status) {
            case 1: $str = 'c.into_time'; $status .= " and c.is_pass != 2";break;
            case 2: $str = 'c.com_time'; break;
            case 3: $str = 'c.pieces_time'; break;
            case 4: $str = 'c.trialtime'; break;
            case 5: $str = 'c.into_res'; break;
            default: break;
        }
        return $this -> field('c.co_id,c.ordergoods_id')
                     -> table('shop_order_goods o, shop_compile c, shop_goods_need n')
                     -> where("o.id = c.ordergoods_id and o.need_id = n.need_id and c.status = ".$status."")
                     -> order($str)
                     -> limit(1)
                     -> select();
    }
    
    /**
     * 得到下发注册证第一条信息
     * @param int $status 得到数据的条件
     * return array
     */
    public function GetFirstValData($res){
        return $this -> field('c.co_id,c.ordergoods_id')
                     -> table('shop_order_goods o, shop_compile c, shop_goods_need n')
                     -> where("o.id = c.ordergoods_id and o.need_id = n.need_id and c.status = 5 and c.res_status = ".$res."")
                     -> order('c.into_res')
                     -> limit(1)
                     -> select();     
    }
    /**
     * 记录审核失败记录
     * @param array $obj 失败录入数据
     */
    public function ComFailLog($obj){
        M('auditlog') -> add($obj);
    }
    /**
     * 获取审核失败记录
     * @param array $order 合同号
     */
    public function GetAuditLog($order){
        return M('auditlog') -> where("order_code = '".$order."'") -> select();
    }
    
    /**
     * 改变订单表面的状态
     * @param int $a 状态a
     * @param int $b 状态b
     * @param int $oid 订单商品id号
     */
    public function ChangeStatus($a, $b, $oid){
        
        $data['virt_status'] = $a;
        $data['status'] = $b;
        M('order_goods') -> where("id = ".$oid."") -> save($data);
    }
    /**
     * 获取店铺信息
     * @return array
     */
    public function findStore(){
       return M('adminuser') -> field('distinct shopsign')
                             -> where("shopsign != ''")
                             -> select();
    }
    
    /**
     * 编修审核失败改变订单状态
     * @param int $id 订单商品ID
     */
    public function ChageOrderStatus( $oid ){
        $arr = array('is_pass'=>2);
        M('order_goods') -> where("id = ".$oid."") -> save($arr);
    }
}