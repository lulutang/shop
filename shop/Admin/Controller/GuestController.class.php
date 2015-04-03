<?php
/**
 * 客官管理
 * @author xxq
 */
namespace Admin\Controller;

use Admin\Model\GuestModel;
use Admin\Model\Admin_logModel;
use Think\Controller;
use Page\Page;
include(COMMON_PATH."Class/Page.class.php");

class GuestController extends Controller{
    
    public function _initialize() {
        
        header("content-type:text/html;charset=utf8");
        $uid = session("userid");
        if( empty( $uid ) )
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }  
    }
    /**
     * 获取客官信息列表
     */
    public function lists(){

        $where ='1=1';
        if(!empty($_GET['keywords'])){
                $where.=" and user_name like '%".$_GET['keywords']."%' or email like '%".$_GET['keywords']."%' or mobile_phone like '%".$_GET['keywords']."%'";
        }
        $user = new GuestModel();
        $Count = $user -> UserCount($where);
        $page_count = 10; 
        $Page = new Page($Count, $page_count);
        $map['keywords'] = @$_GET['keywords'];
        foreach($map as $key=>$val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize =$Page ->show(); 
        $userinfo = $user -> UserInfos($Page->firstRow , $Page->listRows ,$where);
      
        $this->assign('map',$map);
        $this->assign('p',trim(I("p")));
        $this->assign('page' , $Pagesize);
        $this->assign('userinfo',$userinfo);
        $this -> display();
    }
    /**
     * 添加即客官
     * sub 保存
     * sub1 保存并发送短信
     * sub2 保存并添加订单
     */
    public function addGuest(){  

        if( $_POST ){       
            $data = I('post.'); 
            if( $data['user_name']==='请输入客官昵称' ) $data['user_name']='';
            if( $data['truename']==='请输入客官姓名' ) $data['truename']='';
            if( $data['mobile_phone']==='请输入手机号码' ) $data['mobile_phone']='';
            if( $data['email']==='请输入正确的邮箱格式' ) $data['email']='';
            if( $data['qq']==='请输入正确格式的QQ号码' ) $data['qq']='';
        }
        if( $_POST['hid']==='1' ){

            $result = $this -> saveGuest( $data );
            
            if( $result ) $this->redirect("Admin/Guest/lists");
            else{ $this->error('添加出现未知错误！请联系管理员', "Admin/Guest/lists"); }
                
        }elseif( $_POST['hid']==='2' ){
                
            $result = $this -> saveGuest( $data );
            if( $result ){
                //发送短信
                $phone = trim( $data['mobile_phone'] );
                $message = "尊敬的客户您好，您已购买中细软知识产权相关服务，可登录中细软服务商城查看订单详情，登陆名、密码为此手机号，请及时修改密码。";

                $res = send_msgnote($phone,$message);

                if( $res ){ 
                    $this->success('已发送短信，请耐心等待！',"Admin/Guest/lists");
                }
            }else{ $this->error('添加出现未知错误！请联系管理员', "Admin/Guest/lists"); }
                
        }elseif( $_POST['hid']==='3' ){
               
            $result = $this -> saveGuest( $data );
            
            session('addguestname',$data['truename']);
            session('addguestuser_name',$data['user_name']);
            session('addguestmobile',$data['mobile_phone']);
            session('addguestemail',$data['email']);
            //添加订单
            if( $result ) $this->redirect("Admin/Guest/addMemOrder/uid/$result");    
        }

        $this -> display();   
    }
    
    /**
     * 添加信息
     */
    private function saveGuest( $data ){
        
        $W = new GuestModel();
   
        $arr['user_name'] = $data['user_name']; //昵称
        $arr['truename'] = $data['truename']; //姓名
        $password = md5($data['mobile_phone']);
        $arr['password'] = $password;
        $arr['email'] = $data['email'];//邮箱
        $arr['mobile_phone'] = $data['mobile_phone'];//手机号码
        $arr['bind_mobile'] = $data['mobile_phone'];//手机号码
        $arr['qq'] = $data['qq'];//qq  
        $arr['reg_time'] = time();
        $arr['is_hand'] = 1;
        $arr['salesman'] = $_SESSION['username'];
        $arr['creatorid'] = $_SESSION['userid'];
        $arr['last_ip'] = $this -> ip();
        
        //向uc里添加数据
        //  $res = $W -> addUc( $arr['user_name'], $password, $arr['email'] );
        //  print_r($res);exit;
         
        //向ec里添加数据
        $res['status'] = 200;
        if( $res['status'] == 200 ){
            $result = $W -> AddUserInfos($arr);   
        }
        return $result;
    }
    /**
     * 添加订单
     */
    public function addMemOrder() {
        
        $uid = $_REQUEST['uid'];
        
        $UU = new GuestModel();
        
        //取一级业务
        $oneType = $this -> getOneType();

        //session取其刚添加的用户名 手机号
        $user['truename'] = session('addguestname');
        $user['user_name'] = session('addguestuser_name');
        $user['mobile'] = session('addguestmobile');
        $user['email'] = session('addguestemail');
        
        $this -> assign('uid',$uid);
        $this -> assign('user',$user);
        $this -> assign('oneType',$oneType);
        
        if( $_POST['typefrm'] ){  
            $oneTypeid = $_POST['oneType'];
            $twoType = $_POST['twoType'];
            $threeType = $_POST['threeType'];
            //获取商品信息
            $goods = $UU -> getGoods( $threeType );
            $twotypename = $UU -> getTwoName( $twoType );
           
            $data = array(
                'goods_id' => $goods['goods_id'] ,
                'old_price' => $goods['old_price'],
                'now_price' => $goods['now_price'],
                'thumb' => $goods['thumb'],
                'code' => $goods['goods_code'],
               // 'cost' => $goods['cost'],
                'userName' =>$user['user_name'] ,
                'user_id' => $uid,
                'phone' => $user['mobile'],
                'type' => $oneTypeid,
                'title' => $goods['title'] ,
                'short_title' => $goods['short_title'] ,
                'addtime' => time()   
            );
           
            //向购物车cart表插入一条数据
          //  $cartId = $UU -> saveGoodsCart( $data );
            
            $dataorder = array(
                'goods_id' => $goods['goods_id'] ,
                'goods_price' => $goods['now_price'],
                'goods_thumb' => $goods['thumb'],
                'goods_code' => $goods['goods_code'],
                'cost' => $goods['cost'],
                'user_name' =>$user['user_name'] ,
                'user_id' => $uid,
                'phone' => $user['mobile'],  
                'style' => $oneTypeid,
                'yiji' => $twotypename ,
                'erji' => $goods['short_title'] ,
                'addtime' => time()   
            );
           // $orderId = $UU -> saveGoodsOrder( $dataorder );
            $this -> assign('short_title',$goods['short_title']);
            $this -> assign('oneTypeid',$oneTypeid);
            $this -> assign('oneType',$oneType);
            $this -> assign('twoType',$twoType);
            $this -> assign('threeType',$threeType);
            $this -> assign('cartId',$cartId);
            $this -> assign('orderId',$orderId);
            //根据类型判断加载页面
            if( $oneTypeid =='38' ){
                //国外注册模板
                if( $twoType !='40' ){ $this -> display('addGWOrder'); 
                //国内注册模板
                }else{
                    //获取所有一级大类
                    $bigtype = $UU -> getBigType();
                    
                    $this -> assign('bigtype',$bigtype);
                    $this -> display('addSBOrder');
                    
                }  
            }
            if( $oneTypeid =='5' ) $this -> display('addZLOrder');   
 
            if( $oneTypeid =='6' ) $this -> display('addBQOrder');   
        }else{
            $this -> display();    
        }
    }
    /**
     * 添加商标数据购物车和订单 国内
     */
    public function addSBMemOrder() {
        $UU = new GuestModel();
        

        if( $_POST ){
            
            $uid = $_POST['uid']; //用户id
           // $cartId = $_POST['cartId']; //购物车id
            $data['id'] = $_POST['orderId']; //未支付订单id

            $message = array(
                'text'=> $_POST['message'],
                'short_title'=> $_POST['short_title'],
                'name'=> $_POST['name'],
                'style'=> $_POST['style']
            );  
            $data['message'] = json_encode($message);
            $data['goods_style'] = $_POST['style'];
//            $data['style_name'] = $_POST['']; //一级大类
//            $data['enroll'] =  ;//商标图片
//            $data['service_price'] = ;//总价钱
//            $data['subd'] = ;//商品小类
            //生成订单编号
            $data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['GWSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess");
            }
            if( $res && $_POST['GWSave']==='2'){
                $this->redirect("Admin/Guest/addMemOrder/uid/$uid");
            }
        } 
    }
    
    /**
     * 添加商标数据购物车和订单 国外
     */
    public function addGWMemOrder() {
        $UU = new GuestModel();
        
        if( $_POST ){
            
            $uid = $_POST['uid']; //用户id
           // $cartId = $_POST['cartId']; //购物车id
            $data['id'] = $_POST['orderId']; //未支付订单id

            $message = array(
                'text'=> $_POST['message'],
                'short_title'=> $_POST['short_title'],
                'name'=> $_POST['name'],
                'style'=> $_POST['style']
            );  
            $data['message'] = json_encode($message);
            $data['goods_style'] = $_POST['style'];

            //生成订单编号
            $data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['GWSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess");
            }
            if( $res && $_POST['GWSave']==='2'){
                $this->redirect("Admin/Guest/addMemOrder/uid/$uid");
            }
        }
    }
    
    /**
     * 添加版权购物车和订单
     */
    public function addBQMemOrder() {
        
        $UU = new GuestModel();
        
        if( $_POST ){
            
            $uid = $_POST['uid']; //用户id
           // $cartId = $_POST['cartId']; //购物车id
            $data['id'] = $_POST['orderId']; //未支付订单id

            $message = array(
                'text'=> $_POST['message'],
                'short_title'=> $_POST['short_title'],
                'name'=> $_POST['name'],
                'style'=> $_POST['type']
            );  
            $data['message'] = json_encode($message);

            //生成订单编号
            $data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['BQSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess");
            }
            if( $res && $_POST['BQSave']==='2'){
                $this->redirect("Admin/Guest/addMemOrder/uid/$uid");
            }
        }
        
    }
    
    /**
     * 添加专利购物车和订单
     */
    public function addZLMemOrder() {
        
        $UU = new GuestModel();
        
        if( $_POST ){
            
            $uid = $_POST['uid']; //用户id
           // $cartId = $_POST['cartId']; //购物车id
            $data['id'] = $_POST['orderId']; //未支付订单id

            $message = array(
                'text'=> $_POST['message'],
                'short_title'=> $_POST['short_title'],
                'name'=> $_POST['name'],
                'style'=> $_POST['type']
            );  
            $data['message'] = json_encode($message);

            //生成订单编号
            $data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['ZLSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess");
            }
            if( $res && $_POST['ZLSave']==='2'){
                $this->redirect("Admin/Guest/addMemOrder/uid/$uid");
            }
        }
    }

    /**
     * 取一级业务
     * @return array
     */
    private function getOneType(){
        
        $UU = new GuestModel();
        
        $data = $UU -> getOneType();
        return $data;
    }
    /**
     * 添加购物车和订单成功
     */
    public function addProductsSuccess() {
        
        $this -> display();    
    }

    /**
     * 会员详细信息
     */
    public function detail() {
        
        $uid = $_REQUEST['uid'];
        
        $UU = new GuestModel();
        
        if( $uid ){
            //获取会员基本信息
            $data = $UU -> getUserOneInfo( $uid );
            //获取创建人的名字
            if( $data['creatorid'] ){
                $data['creator'] = $UU -> getWaiterName( $data['creatorid'] );   
            }
            $role_id = $UU -> getRole_id( '跑堂' );
            //所有跑堂
            $waiters = $UU -> getWaiters( $role_id ,$data['creatorid'] );
            
            //查询购物车商品
            $carts = $UU -> getCarts($uid);
            $cartsnum = $carts ? count($carts):'0';
            //查询未支付订单
            $carts = $UU -> getNoPayorders($uid);
            $cartsnum = $carts ? count($carts):'0';
            
            $this -> assign('carts',$carts);
            $this -> assign('cartsnum',$cartsnum);
            $this -> assign('waiters',$waiters);
            $this -> assign('data',$data);
        }
        $this -> display();    
    }
    /**
     * 获取请求ip
     *
     * @return ip地址
     */
    private function ip() {
        if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
    }
    
    /**
     * 上传图片
     * @return type
     */
    public function uploadpic(){
    
        if( $_FILES["file"]["name"] ){
            
            if( $_FILES["file"]["size"] /1024 > 2048 ) {
                exit( '图片太大！建议不超过2M。 [ <A HREF="javascript:history.back()">返 回</A> ]');
            }
            
            $extend = explode(".",$_FILES["file"]["name"]);
            $key = count( $extend )-1;
            $ext = ".".$extend[ $key ];
            $newfile = time().$ext;
            $savePath = "./uploads/avatar/".date('Ymd',time())."/"; 
            if( is_dir( $savePath )==FALSE){
                  mkdir( $savePath );  
            } 
            $thumb_path = $savePath.$newfile;
            move_uploaded_file( $_FILES['file']['tmp_name'] , $thumb_path);

            return substr($thumb_path, 1);
        }

    }


    /**
    * 给会员发送短信
    */
    public function send_notes(){
        $phone = $_GET['phone'];
        $str="尊敬的客户您好，您已购买中细软知识产权相关服务，可登录中细软服务商城查看订单详情，登陆名、密码为此手机号，请及时修改密码。";
        //$str = 'sss';
        send_msgnote($phone,$str);
    }

    //判断唯一性
    public function checkOlayOne(){

        $type = $_REQUEST['type']; 

        if( $_REQUEST['card'] && ($type==='checkOlayOne') ){
            $card = $_REQUEST['card'];
            $query = M('Adminuser')->where("  card='".$card."'" )->select();

            if( $query ){
                echo '1';
            }else{
                echo '0';
            }
        }
    }
    /**
     * ajax获取二级业务类型
     */
    public function showTwoType(){

        $type = $_REQUEST['type']; 

        if( $_REQUEST['oneid'] && ($type==='showTwoType') ){
            $oneid = $_REQUEST['oneid'];
            $res = M('service') -> field('`id`,`server_name`') -> where(" parent_id=".$oneid ) -> select();
            
            echo json_encode($res);
        }
    }
    /**
     * ajax获取三级业务类型
     */
    public function showThreeType(){

        $type = $_REQUEST['type']; 

        if( $_REQUEST['twoid'] && ($type==='showThreeType') ){
            $twoid = $_REQUEST['twoid'];
            $res = M('goods') -> field('`goods_id`,`short_title`') -> where(" s_id=".$twoid ) -> select();
           // echo M('service')->getLastSQl();
            if( $res ) echo json_encode($res);
        }
    }
    
    /**
     * ajax 获取二级商品小类
     */
    public function getTwoType() {
        
        $type = $_REQUEST['type']; 

        if( $_REQUEST['oneID'] && ($type==='getTwoType') ){
            $oneID = $_REQUEST['oneID'];
            $res = M('brand_group') -> where(' cat_id='.$oneID  ) -> order(" item_id  " ) -> select();

            foreach ($res as $key => $val) {
                $rows[$key]['item_id'] = $val['item_id'];
                $rows[$key]['id'] = $val['group_id'];
                $rows[$key]['name'] = '['.$val["group_id"].']'.$val['group_name'];
                $rows[$key]['pid'] = $oneID;
                $rows[$key]['childsum'] = $this -> getCountThree( $val["item_id"] );
            }
           
            if( $res ) echo json_encode($rows);
        }
    }
    
    public function getCountThree( $twoid ) {
        
        $UU = new GuestModel();
        $res = $UU -> getCountThree( $twoid );
        return $res;
    }
    /**
     * ajax 获取三级商品小类
     */
    public function checkThreeSB() {
        
        $type = $_REQUEST['type']; 

        if( $_REQUEST['twoID'] && ($type==='checkThreeSB') ){
            $twoID = $_REQUEST['twoID'];
            $res = M('brand_category_detail') -> where(' item_id='.$twoID  ) -> order(" id " ) -> select();

            foreach ($res as $key => $val) {
                $rows[$key]['id'] = $val['key_name'];
                $rows[$key]['name'] = $val['value_name'];
                $rows[$key]['pid'] = $twoID;
            } 
            if( $res ) echo json_encode($rows);
        }
    }
    

}