<?php
/**
 * 客官管理
 * @author xxq
 */
namespace Admin\Model;

use Think\Model;

class GuestModel extends Model{

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
     * 组合条件查询语句查询记录
     * @param string $where
     * @return array
     */
    public function getCount($where){
        
        return  $data = M("adminuser") -> where($where) ->count();
    }
    /**
     * 获取除超级管理员外的角色集
     * @return array
     */
    public function getRoleInfo(){
        $data = M('role')->select();
        foreach ($data as $key => $val) {
                if($val['name']!='超级管理员')
                $list[]=$val;
        }
        return $list;
    }
    /**
     * 添加会员数据
     * @param array $data
     * @return boolean
     */
    public function AddUserInfo($data){

        $data['addtime'] = time();

        if( $data['id'] ){//更新

            $result = M('adminuser')->where('id='.$data['id'])->save($data);
            //更新角色和用户关系
            $roarr['role_id'] = $data['role_id'];
            M('Role_user')->where('user_id='.$data['id'])->save($roarr);

        }else{

            $result = M('adminuser')->add($data);

            $roarr['role_id'] = $data['role_id'];
            $roarr['user_id'] = $result;
            //更新角色和用户关系
            M('Role_user')->add($roarr);
        }

        $arr['username'] = $data['username'];
        if($data['password']) $arr['password'] = $data['password'];
        $arr['uid'] = M('adminuser')->getLastInsID();

        $results = M('loginuser')->add($arr);

        if($result>0 && $results>0){
                M('adminuser')->commit();
                return TRUE;
        }else{
                M('adminuser')->rollback();
        }
    }
    /**
     * 根据id获取相应管理者信息
     * @param int $id
     * @return array
     */
    public function getOneUserInfo($id){
        return  M("adminuser")->where("id = ". $id)->find();
    }
    /**
     * 编辑保存管理者用户信息
     * @param array $data
     * @return boolean
     */
    public function SaveUserInfo($data){
        $datas['card'] = $data['card'];
        $datas['role_id'] = $data['role_id'];
        $datas['truename'] = $data['truename'];
        $datas['mobile'] = $data['mobile'];
        $datas['email'] = $data['email'];
        $datas['qq'] = $data['qq'];
        $datas['sex'] = $data['sex'];
        $datas['desc'] = $data['desc'];
        $result = M('adminuser') -> where("id = ".$data['id']) -> save();
        if($result>0){
                return TRUE;
        }else{
                return FALSE;
        }
    }



    /**
    * 获取用户数量
    * @param  string $where    查询条件
    */
    public function UserCount($where){
        $userCount = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where($where) -> count();
        return $userCount;
    }
    /**
    * 获取用户信息
    * @param  int $start 分页开始值
    * @param  int $end 分页结束值
    * @param  string $where    查询条件
    */
    public function UserInfos($start,$end,$where){
        $userInfos = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where($where) -> order("reg_time desc") -> limit($start,$end) -> select();
        return $userInfos;
    }
    /**
    * Ec添加用户
    * @param  array $data 新添加用户id
    */
    public function AddUserInfos($data){

            $results = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") ->add($data);
           
            return $results;   
    }
    /**
    * uc添加用户
    * @param  string $username 用户帐号
    * @param  string $password 密码
    * @param  string $email    邮箱地址
    */
    public function addUc($username, $password, $email){

        require_once(APP_UC_PATH ."uc_config.php");
        require_once (APP_UC_PATH.'uc_client/client.php');
        $uid = uc_user_register($username, $password, $email);
        if ($uid <= 0) {
            if ($uid == -1) {
                return array('status' => 301, 'msg' => '用户帐号不合法');
            } elseif ($uid == -2) {
                return array('status' => 302, 'msg' => '包含不允许注册的词语');
            } elseif ($uid == -3) {
                return array('status' => 303, 'msg' => '用户帐号已经存在');
            } elseif ($uid == -4) {
                return array('status' => 304, 'msg' => '邮件格式有误');
            } elseif ($uid == -5) {
                return array('status' => 305, 'msg' => '邮件不允许注册');
            } elseif ($uid == -6) {
                return array('status' => 306, 'msg' => '该邮箱已经注册过帐号,请换一个');
            } else {
                return array('status' => 307, 'msg' => '未定义');
            }
        } else {
            return array('status' => 200, 'msg' => '注册成功');
        }
    }
    /**
    * 检查手机
    * @param  string $phone 用户手机
    */
    public function CheckPhone($phone){
        $result = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where("mobile_phone = '".$phone."'") -> select();
        if($result){
            return TRUE;
        }
        return FALSE;
    }
    /**
     * 获取一级业务
     * @return array 
     */
    public function getOneType() {
        
        $SS = M('service');
        $Onetype = $SS ->field('`id`,`server_name`') -> where('parent_id=0') -> order('id') -> select();
        return $Onetype;
    }
    /**
     * 获取商品信息
     * @param type $goods_id
     * @return array 
     */
    public function getGoods( $goods_id ) {
        
        $SS = M('goods');
        $goods = $SS ->field('`goods_id`,`old_price`,`now_price`,`thumb`,`goods_code`,`cost`,`short_title`,`title`') -> where('goods_id='.$goods_id) -> find();
       
        return $goods;
    }
    /**
     * 根据业务类型id获取业务名
     * @param type $id 业务类型id
     */
    public function getTwoName( $id ) {
        
        $SS = M('service');
        $data = $SS ->field('`server_name`') -> where( 'id='.$id ) -> find();
        return $data['server_name'];
    }
    /**
     * 保存商品订单
     * @param array $data 数组
     */
    public function saveGoodsCart( $data ){
        
        $OO = M('cart');
        $insertId = $OO -> data( $data ) -> add();
        
        return $insertId;
    }
    
    /**
     * 保存商品订单
     * @param array $data 数组
     */
    public function saveGoodsOrder( $data ){
        
        $OO = M('order_goods');
        $insertId = $OO -> data( $data ) -> add();
        
        return $insertId;
    }
    /**
     * 更新订单表的内容
     * @param type $where 条件
     * @param type $data
     * @return Boolean
     */
    public function updateData( $where, $data) {
        
        $OO = M('order_goods');
        $res = $OO -> where( $where ) -> save($data);
        
        return $res;
    }
    /**
     * 获取一级大类
     * @return type
     */
    public function getBigType() {
        $BB = M('brand_category');
        $data = $BB -> order( ' cat_id  ' ) -> select();
        return $data;
    }
    /**
     * 获取二级下的三级个数
     * @param type $twoid 二级小类
     * @return type
     */
    public function getCountThree( $twoid ) {
        
        return  $data = M("brand_category_detail") -> where(' item_id='.$twoid ) ->count();
    }
    /**
     * 根据用户id获取用户基本信息
     * @param type $uid
     * @return array 
     */
    public function getUserOneInfo( $uid ) {
        $userInfos = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where(' user_id='.$uid ) -> find();
        return $userInfos;
    }
    /**
     * 获取相对应的跑堂名字
     * @param type $creatorid 跑堂id
     * @return string
     */
    public function getWaiterName( $creatorid ){
        $SS = M('adminuser');
        $data = $SS ->field('`truename`') -> where( 'id='.$creatorid ) -> find();
        return $data['truename'];
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
     * 获取用户购物车内商品
     * @param type $uid 用户id
     * @return array
     */
    public function getCarts( $uid ) {
        
        $CC = M("carts");
        
        $res = $CC -> where(" user_id='$uid' ") -> select(); 
       
        return $res;
        
    }
    /**
     * 获取未支付订单
     * @param type $uid
     */
    public function getNoPayorders( $uid ) {
        
    }
    
}