<?php
/**
 * 客官管理
 * @author xxq
 */
namespace Admin\Model;

use Think\Model;

class WaiterModel extends Model{
    
    /**
     * 组合条件查询语句查询记录
     * @param time $start 
     * @param time $end
     * @param string $where
     * @return array
     */
    public function getUserInfo($start ,$end ,$where ){
        
        $data = M("adminuser")->where($where)->order(' id desc ')->limit($start,$end)->select();
        foreach ($data as $key => $val) {
                $arr = M("role") -> where("role_id = ".$val['role_id']."") -> find();
                $data[$key]['role_name'] = $arr['name'];
        }
        return $data;

    }
    /**
     * 根据用户id查询店铺名
     * @param int $uid
     * @return string
     */
    public function getShopsign( $uid ) {
        $AA = M("adminuser");
        $res = $AA -> field('shopsign')-> where("id=".$uid) -> find();
        //echo $AA -> getLastSQl();
        return $res['shopsign'];
    }
    /**
     * 根据角色名获取角色id
     * @param type $role_name
     * @return string 
     */
    public function getRole_id( $role_name ) {
        
        $AA = M("role");
        $res = $AA -> field('role_id')-> where("name='$role_name'") -> find(); 
  
        return $res['role_id'];
    }
    
    /**
     * 组合条件查询语句查询记录
     * @param string $where
     * @return array
     */
    public function getCount($where){
        
        return  $data = M("adminuser") -> where($where) ->count();
    }
    
    
    /**
     * 组合条件查询语句查询记录
     * @param time $start 
     * @param time $end
     * @param string $where
     * @return array
     */
    public function getAdminUser($start ,$end ,$where ){
        
        $data = M("adminuser") -> field("`id`,`card`,`truename`,`mobile`,`email`,`addtime`") -> where( $where ) -> order(' id desc ') -> limit( $start, $end) -> select();
        foreach ($data as $key => $val) {
                $lasttime = $this -> getLastLogin( $val['id'] );
                $data[$key]['last_time'] = $lasttime;
        }
        return $data;

    }
    /**
     * 获取某人最后登陆时间
     */
    private function getLastLogin( $uid ) {
        
        $AA = M("admin_log");
        $res = $AA -> field('createtime')-> where("uid='$uid'") -> find(); 
  
        return $res['createtime'];
    }
    /**
     * 更改跑堂状态 激活/停用
     * @param type $uid
     * @param type $code 需要更改的状态
     * @return boolean 
     */
    public function changeStatus( $uid, $code) {
        
        $AA = M("adminuser");
        
        $data = array();
        $data['status'] = $code;
        
        $res = $AA -> where('id='.$uid) -> save( $data );  
        
        return $res;
    }
    /**
     * 根据用户id获取当前用户所有信息
     * @param type $uid
     * @return array
     */
    public function getUserDetail( $uid ) {
        
        $AA = M("adminuser");
        
        $res = $AA -> where("id='$uid'") -> find(); 
       
        return $res;
    }
    /**
     * 根据角色id获取所有跑堂
     * @param $role_id 角色id 
     * @param $uid 需要被排除的当前id
     */
    public function getWaiters( $role_id ,$uid ) {
        
        $AA = M("adminuser");
        
        $res = $AA -> field('id,truename') -> where("role_id='$role_id' and status=1 and id!=$uid ") -> select(); 
       
        return $res;
        
    }
    /**
    * 获取客官数量
    * @param  string $where    查询条件
    */
    public function getMemCount($where){
        $userCount = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where($where) -> count();
        
        return $userCount;
    }
    /**
     * 根据跑堂id获取其添加的客官
     * @param type $uid 当前跑堂id
     * @return array
     */
    public function getAddMembers( $where, $start, $end) {
        
        $users = $this -> db(1,"DB_CONFIG1") -> table("ecs_users")->field('user_id,truename,user_name,email,mobile_phone,photo_add') -> where( $where ) -> order("reg_time desc") -> limit($start,$end) -> select();
        
        return $users;
    }
    /**
     * 更换跑堂
     * @param type $id 当前跑堂id
     * @param type $uids 需要更换的客官
     * @param type $pid
     */
    public function changeWaiter( $id, $uids, $pid) {
        $data = array();
       
        $data['creatorid'] = $pid;
        //$id 因角色问题 暂时弃用 creatorid=$id and user_id in( $uids )
        
        $res = $this -> db(1,"DB_CONFIG1") -> table("ecs_users")-> where( " user_id in( $uids )" )-> save( $data );
        
        return $res;
    }
    /**
     * 获取审核失败的数据
     */
    public function getFailData( $start, $end, $where ) {
        
        $AA = M("compile");
        
        $list = $AA->field('shop_compile.co_id, b.user_name, b.user_id, b.phone, b.bargain, b.service_price,b.style, b.erji, b.goods_price, b.message, b.pay_time, b.virt_status, b.id')->join('shop_order_goods b ON shop_compile.ordergoods_id=b.id')->where( $where ) ->limit( $start, $end ) ->order('b.id desc' )->select();
   //    echo $AA->getLastSQL();
        $statusDAta = array('1'=>'编修审核','2'=>'信息初审', '3'=>'报件', '4'=>'下发受理', '5'=>'已支付', '6'=>'服务已结束', '7'=>'服务已开始', '8'=>'下发注册证', '9'=>'初审');
        $colorDAta = array('1'=>'s_c_qingse','2'=>'s_c_blue', '3'=>'s_c_purple', '4'=>'s_c_yellow', '5'=>'s_c_red', '6'=>'s_c_grey', '7'=>'s_c_green', '8'=>'s_c_green2', '9'=>'s_c_brown');
        foreach( $list as $key=>$val ){
            $list[$key]['message'] = json_decode($val['message']);
            $list[$key]['status'] = $statusDAta[$val['virt_status']];
            $list[$key]['color'] = $colorDAta[$val['virt_status']];
        }
        return $list;
    }
    /**
     * 根据条件计算个数
     * @param string $where
     * @return array
     */
    public function getfailServerCount( $where ) {
        
        $AA = M("compile");
        
        $data = $AA -> where( $where ) ->count();
       
        return $data;
    }
    /**
     * 更改状态
     * @param int $co_id
     * @return unkonwn
     */
    public function changeIspass( $co_id ) {
        
        $AA = M("compile");
        
        $data = array();
        $data['is_pass'] = '0';
        
        $res = $AA -> where('co_id='.$co_id) -> save( $data );  
        
        return $res;
    }
    
}