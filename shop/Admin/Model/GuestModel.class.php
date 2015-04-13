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
    public function UserInfos( $start, $end, $where, $order){
        
        $userInfos = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where($where) -> order( $order ) -> limit($start,$end) -> select();
        foreach ($userInfos as $key => $value) {
            //获取订单总数
            $OO = M('order');
            $userInfos[$key]['totalorder'] = $OO -> where( ' user_id ='.$value['user_id'] ) -> count();
            $MM = M('order_goods');
            //获取服务总数
            $userInfos[$key]['totalserver'] = $MM -> where( ' user_id ='.$value['user_id'] ) -> count();
        }
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
        
        $OO = M('temp_cart_goods');
        $insertId = $OO -> data( $data ) -> add();
        
        return $insertId;
    }
    /**
     * 获取用户购物车内商品
     * @param type $uid 用户id
     * @return array
     */
    public function getCart( $where ) {
        
        $CC = M("cart");
        
        $res = $CC -> where( $where ) -> find(); 

        return $res;
        
    }
    /**
     * 添加need表信息
     * @param type $need
     * @return type
     */
    public function saveGoodsNeed( $need ) {
        $OO = M('goods_need');
        $insertId = $OO -> data( $need ) -> add();
        
        return $insertId;
    }
    /**
     * 复制一条记录
     * @param type $cartId
     */
    public function copyCartRecord( $cartId ) {
        
        $OO = M('cart');
        $data = $OO -> where( ' id='.$cartId ) -> find(); 
        unset( $data['id'] );
        $insertId = $OO -> data( $data ) -> add();
        
//        $sql = "insert into shop_cart(`goods_id`,`user_id`,`phone`,`userName`,`old_price`,`now_price`,`thumb`,`short_title`,`title`,`code`,`type`,`addtime`,`creator`) select `goods_id`,`user_id`,`phone`,`userName`,`old_price`,`now_price`,`thumb`,`short_title`,`title`,`code`,`type`,`addtime`,`creator` from shop_cart where id=$cartId";
//        $insertId = $OO -> execute( $sql );

        return $insertId;
    }
    /**
     * 更新订单表的内容
     * @param type $where 条件
     * @param type $data
     * @return Boolean
     */
    public function updateData( $where, $data) {
        
        $OO = M('temp_cart_goods');
        $res = $OO -> where( $where ) -> save($data);
        
        return $res;
    }
    /**
     * 更新need信息表
     * @param array $need  表数据
     * @param int $needId  更新条件
     * @return boolean
     */
    public function updateNeed( $need ,$needId) {
        $OO = M('goods_need');
        $res = $OO -> where( ' id = '.$needId ) -> save( $need );
        
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
        
        $CC = M("cart");
        
     //   $res = $CC -> where(" user_id='$uid' ") -> select(); 
        $totalprice = 0;
        
        $res = $CC->join('shop_temp_cart_goods ON shop_temp_cart_goods.cartid = shop_cart.id')->where(" shop_cart.user_id='$uid' ")->select();
        foreach ( $res as $key => $val ) {
            $res[$key]['message'] = json_decode( $val['message'], true);
            $res[$key]['creator'] = $this -> getName($val['creator']);
            
            $res[$key]['subd_num'] = $val['subd_num'] > 10 ? $val['subd_num']-10 :'0';
            $totalprice += $val['now_price'];
        }
        $res['totalprice'] = $totalprice;
        return $res;
        
    }
    /**
     * 获取创建者名字
     * @param type $creator id
     */
    private function getName( $creator ) {
        if( $creator ){
            $CC = M("adminuser");
            $res = $CC->field(' username ')->where(' id= '.$creator )->find();
            return $res['username'];
        }
    }
    /**
     * 获取未支付订单
     * @param type $uid
     * @return array
     */
    public function getNoPayorders( $uid ) {
        $OO = M("order");
        //获取当前人的订单
       // $uid = 92652;
        $orders = $OO -> field(' `order_id`,`order_card`,`goods_number`,`createtime`,`totalprice` ') -> where(" user_id='$uid' and  `status`=0 ") -> select(); 
       // echo $OO->getLastSQL();
        $CC = M("order_goods");  
        
        if( $orders ){
            foreach ($orders as $key => $value) {
                $orders[$key]['goods'] = $CC ->field(' `goods_id`,`goods_price`,`service_price`,`message` ') -> where(" `order_id`='".$value['order_id']."'" ) -> select();  
            //    echo $CC ->getLastSQl();
            }           
        }
        return $orders;    
    }
    /**
     * 获取支付订单
     * @param type $uid
     * @return array
     */
    public function getPayorders( $uid ) {
        
        $OO = M("order");
        //获取当前人的订单
        //$uid = 92652;
        $orders = $OO -> field(' `order_id`,`order_card`,`goods_number`,`createtime`,`totalprice`,`onsale_money`,`coil_money` ') -> where(" user_id='$uid' and  `status`=1 ") -> select(); 
        
        $CC = M("order_goods");  
        
        if( $orders ){
            foreach ($orders as $key => $value) {
                $orders[$key]['goods'] = $CC ->field(' `id`,`goods_id`,`goods_price`,`service_price`,`message`,`virt_status`,`erji` ') -> where(" `order_id`='".$value['order_id']."'" ) -> select();  
              //  echo $CC ->getLastSQl();
            }           
        }       
        //print_r($orders);
        return $orders;    
        
    }
    /**
     * 得到相应交易人的信息
     * @param type $uid
     */
    public function getTraders( $uid ) {
        
        $TT = M("trader");
       // $uid = 3177;
        $res = $TT -> where(" trader_belong='$uid' ") -> select(); 
        if( $res ){
            $Person = $Company = array();
            foreach ($res as $key => $value) {
                //个人
                if( $value['is_person'] =='1' ){
                    $Person[] = $value;
                }
                 //公司
                if( $value['is_person'] =='2' ){
                    $Company[] = $value;
                }
            }
        }
        
        $data = array('Person'=>$Person,'Company'=>$Company);
        
        return $data;
    }
    /**
     * 获取相应收货人信息
     * @param type $uid
     */
    public function getConsignee($uid) {
        if( $uid ){
            $AA = M("address");
           //  $uid = 3177;
            $res = $AA -> where(" user_id='$uid' ") -> select(); 
          
            return $res;
        }
    }
    
    /**
     * 获取优惠券
     * @param type $uid
     */
    public function getCoupon( $uid ) {
        
        if( $uid ){
          
            $AA = M("user_sale");
          //  $uid = 3177;
            $nowTime = time();
            $res = $AA -> field(' `is_where`,`sale_money`,`startTime`,`endTime` ') -> where(" uid='$uid' and state=1 and endTime > $nowTime ") -> select(); 
           
            return $res;
        } 
    }
    
   /**
    * 获取用户手机号
    * @param  string $where    查询条件
    */
    public function getUidPhone(  $where ){
        
        $data = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where($where) -> find();
        
        return $data;
    }
    /**
     * 获取具体的购物车信息
     * @param int $cartid
     * @return array 
     */
    public function getTempCartGoods( $cartid ) {
        
        if( $cartid ){
          
            $AA = M("temp_cart_goods");
           // $cartid = 1302;
           $sql = " SELECT `style_name`,`erji`,`service_price`,`user_name`,`user_id` FROM `shop_temp_cart_goods` WHERE ( addtime=(SELECT addtime FROM shop_temp_cart_goods where cartid=$cartid) )  ";
           $data = $AA -> query( $sql ); 

            return $data;
        } 
    }
    
    /**
     * 获取用户临时购物车内商品
     * @param type $where 条件
     * @return array
     */
    public function copyTempCart( $where ) {
        
        $CC = M("temp_cart_goods");
        $data = $CC -> where( $where ) -> find(); 
        unset( $data['id'] );
        $insertId = $CC -> data( $data ) -> add();
        
//        $sql = "insert into temp_cart_goods(`user_name`,`user_id`,`goods_id`,`goods_price`,`goods_thumb`,`addtime`,`style_name`,`message`,`style`,`yiji`,`erji`,`goods_code`,`phone`,`deal_name`,`deal_phone`,`deal_address`,`consignee_name`,`consignee_phone`,`consignee_address`,`deal_id`,`cost`,`service_price`,`subd`,`subd_num`,`enroll`,`cartid`,`need_id`) ".
//                " select `user_name`,`user_id`,`goods_id`,`goods_price`,`goods_thumb`,`addtime`,`style_name`,`message`,`style`,`yiji`,`erji`,`goods_code`,`phone`,`deal_name`,`deal_phone`,`deal_address`,`consignee_name`,`consignee_phone`,`consignee_address`,`deal_id`,`cost`,`service_price`,`subd`,`subd_num`,`enroll`,`cartid`,`need_id` from temp_cart_goods $where";
//        $query = $CC -> execute( $sql );
//       
//        $insertId = $CC -> getLastInsID();
        return $insertId;  
    }
    /**
     * 更新购物车表的价格
     * @param array $cart 更改的数据
     * @param int $cartId 自增id
     */
    public function updateCartRecord($cart,$cartId) {
        $CC = M("cart");
        
        $res = $CC -> where( 'id='.$cartId ) -> save( $cart );

        return $res;
    }
    
    
}