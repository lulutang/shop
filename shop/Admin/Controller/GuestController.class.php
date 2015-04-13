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

        $where =' 1=1 ';
        
        $titlename = $_POST['titlename'];
        $searchdata = $_POST['searchdata'];
        
        if( $searchdata ){
            $where .= " and $titlename like '%$searchdata%' ";
        }

        //创建时间排序
        if(  I('regtime')  &&  I('regtime') ==='asc' ){
            $order = " reg_time asc ";
            $ordertime ='desc';
        }else{
            $order = " reg_time desc ";
            $ordertime ='asc';
        }
        
        if( I('login') && I('login') ==='asc' ){
            $order = " last_login asc ";
            $login ='desc';
        }
        if( I('login') && I('login') ==='ascdesc' ){
            $order = " last_login desc ";
            $login ='';
        }

        $user = new GuestModel();
        $Count = $user -> UserCount($where);
        $page_count = 10; 
        $Page = new Page($Count, $page_count);
        
        $map['titlename'] = $titlename;
        $map['searchdata'] = $searchdata;
        $map['regtime'] = $regtime;
        $map['login'] = $login;
        foreach($map as $key=>$val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }

        $Pagesize =$Page ->show(); 
        $userinfo = $user -> UserInfos($Page->firstRow , $Page->listRows , $where, $order);
      
        $this->assign('map',$map);
        $this->assign('ordertime',$ordertime);
        $this->assign('login',$login);
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
          //  if( $result ) $this->redirect("Admin/Guest/addMemOrder/uid/$result"); 
            if( $result ) $this->redirect("Admin/Guest/addDealer/uid/$result"); 
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
     * 添加申请人
     */
    public function addDealer() {
        
        $this -> display();    
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
                'creator' => $_SESSION['user_id'],
                'addtime' => time()   
            );
          
            //向购物车cart表插入一条数据
            $cartId = $UU -> saveGoodsCart( $data );

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
                'cartid' => $cartId,
                'addtime' => time()   
            );
            $orderId = $UU -> saveGoodsOrder( $dataorder );
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
      //  var_dump($_POST);

        if( $_POST ){
            
            $uid = $_POST['uid']; //用户id
            $cartId = $_POST['cartId']; //购物车id  
            $orderId = $_POST['orderId']; //未支付订单id
            
            //根据大类个数进行处理
            $need_state = implode(',',I('need_state'));
            $need_prior = implode(',',I('need_prior'));
            $thumb = $this -> uploadpic();
            $name = I('name');
            if( empty( $name ) || empty( $thumb )) {
                exit( '商标名称为空或商标图样没有上传！ [ <A HREF="javascript:history.back()">返 回</A> ]');
            }
            
            $OneType = I('OneType');
            $subtype = I('subtype');
            $serverprice = I('serverprice');
            $subnum = I('subnum');
            $message = array(
                'text'=> I('message'),
                'short_title'=> I('short_title'),
                'name'=> I('name'),
                'style'=> I('style')
            );  
            
            $commoninfo = $UU -> getCart(' id='.$cartId );
            
            $count = count(I('OneType'));
            for( $i = 0; $i < $count; $i++ ){
                //更新need表 第一次的时候update >1进行复制添加操作
                $need = array(
                    'uid' => $commoninfo['user_id'],
                    'user_name' => $commoninfo['userName'],
                    'phone' => $commoninfo['phone'],
                    'goods_id' => $commoninfo['goods_id'],
                    'name' => $name,
                    'need_state' => $need_state,
                    'need_prior' => $need_prior,
                    'area' => I('area') ,
                    'need_time' => strtotime(I('need_time')),
                    'need_number' => I('need_number'),
                    'style' => $OneType[$i],
                 //   'style_part' => I('OneType')[$i],二级小类和三级组合
                 //   'trader_uname' => I('OneType')[$i],商标申请人
                    'subd' => $subtype[$i],
                    'price' => $serverprice[$i],
                    'subd_num' => $subnum[$i],
                    'create_time' => time()  
                );
              
                $needId = $UU -> saveGoodsNeed( $need );
                
                if( $i>= 1) {
                   //cart表  >1进行复制添加操作
                   $cartId = $UU -> copyCartRecord( $cartId );
                }
               //更新cart的now_price字段
               $cart = array('now_price'=>$serverprice[$i]);
               $UU -> updateCartRecord($cart,$cartId);
               
               //临时表 第一次的时候update >1进行复制添加操作
                $dataorder = array(

                    'message' => json_encode($message), 
                    'style_name' => $OneType[$i],
                    'enroll' => $thumb,
                //    'deal_id' =>,
//                    'deal_name' =>,
//                    'deal_phone' =>,
//                    'deal_address' =>,
                    'service_price' => $serverprice[$i] ,
                    'subd' => $subtype[$i],
                    'subd_num' => $subnum[$i],
                    'cartid' => $cartId,
                    'need_id' => $needId   
                );
                
                if( $i >= 1 ) {
                    //复制更新操作
                    $where = " id=$orderId  ";
                    $neworderId = $UU -> copyTempCart($where);
                    
                    $res = $UU -> updateData(' id ='.$neworderId, $dataorder);                  
                }else{
                    $res = $UU -> updateData(' id ='.$orderId, $dataorder);
                }                 
            }

            if( $res && $_POST['GNSave']==='1'){
                $this->redirect("/Admin/Guest/addProductsSuccess/cid/$cartId");
            }
            if( $res && $_POST['GNSave']==='2'){
                $this->redirect("/Admin/Guest/addMemOrder/uid/$uid");
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
            $cartId = $_POST['cartId']; //购物车id
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
           // $data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['GWSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess/cid/$cartId");
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
            $cartId = $_POST['cartId']; //购物车id
            $data['id'] = $_POST['orderId']; //未支付订单id

            $message = array(
                'text'=> $_POST['message'],
                'short_title'=> $_POST['short_title'],
                'name'=> $_POST['name'],
                'style'=> $_POST['type']
            );  
            $data['message'] = json_encode($message);

            //生成订单编号
           // $data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['BQSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess/cid/$cartId");
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
            $cartId = $_POST['cartId']; //购物车id
            $data['id'] = $_POST['orderId']; //未支付订单id

            $message = array(
                'text'=> $_POST['message'],
                'short_title'=> $_POST['short_title'],
                'name'=> $_POST['name'],
                'style'=> $_POST['type']
            );  
            $data['message'] = json_encode($message);

            //生成订单编号
            //$data['order_code'] = makeOrderCardId();
            //生成合同号

            $res = $UU -> updateData(' id ='.$data['id'], $data);

            if( $res && $_POST['ZLSave']==='1'){
                $this->redirect("Admin/Guest/addProductsSuccess/cid/$cartId");
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
        
        $cartid = $_REQUEST['cid'];
        
        if( $cartid ){
            
            $UU = new GuestModel();
            
            $data = $UU -> getTempCartGoods( $cartid );
            $total = 0;
            foreach ($data as $key => $value) {
                $username = $value['user_name'];
                $uid = $value['user_id'];
                $total +=$value['service_price'];
            }

            $this -> assign('total',$total);
            $this -> assign('username',$username);
            $this -> assign('uid',$uid);
            $this -> assign('data',$data);
        }
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
            $noPays = $UU -> getNoPayorders($uid);
            $noPaysnum = $noPays ? count($noPays):'0';
            //算总钱和拆分json
            $noPays = $this ->processPaysArray( $noPays );
           
            //查询支付订单
            $Pays = $UU -> getPayorders($uid);
            $Paysnum = $noPays ? count($Pays):'0';
            //算总钱和拆分json
            $Pays = $this -> processPaysArray( $Pays );
            //交易人信息
            $Traders = $UU -> getTraders( $uid );
            //收货人
            $Consignee = $UU -> getConsignee($uid);
            //优惠券
            $Coupon = $UU -> getCoupon($uid);
            
            $this -> assign('carts',$carts);
            $this -> assign('cartsnum',$cartsnum);
            $this -> assign('noPays',$noPays);
            $this -> assign('noPaysnum',$noPaysnum);
            $this -> assign('Pays',$Pays);
            $this -> assign('Paysnum',$Paysnum);
            $this -> assign('Person',$Traders['Person']);
            $this -> assign('Company',$Traders['Company']);
            $this -> assign('Consignee',$Consignee);
            $this -> assign('Coupon',$Coupon);
            $this -> assign('waiters',$waiters);
            $this -> assign('data',$data);
        }
        $this -> display();    
    }
    /**
     * 处理得到相应数据
     * @param array $data
     * @return array
     */
    public function processPaysArray( $data ) {

        if( $data ){
            
            $total = $panchan = $youhui = 0;
            $PiPeiData = array( '1' =>'编修审核' ,'2' =>'信息初审' ,'3' =>'报件' ,'4' => '下发受理','5' => '已支付','6' => '服务已结束','7' => '服务已开始','8' =>'下发注册证' ,'9' => '初审');
            $snum = 0;
            
            foreach ($data as $key => $value) {
 
                foreach ($value['goods'] as $k => $val) {
                   
                     $message = json_decode($val['message'],true); 
                     $data[$key]['goods'][$k]['short_title'] = $message['short_title'];
                     $data[$key]['goods'][$k]['name'] = $message['name'];
                     $data[$key]['goods'][$k]['style'] = $message['style'];  
                    // echo $val['virt_status'];echo "====";
                     if( $val['virt_status'] ){ $data[$key]['goods'][$k]['status'] = $PiPeiData[$val['virt_status']] ;}
                } 
                $snum += count( $value['goods'] );
                
                $panchan +=$value['coil_money'];
                $youhui +=$value['onsale_money'];
                $total +=$value['totalprice'];
            }
            
            $data['snum'] = $snum; //记录服务商品数
            $data['total'] = $total;
            $data['coil_money'] = $panchan ? $panchan : '0';
            $data['onsale_money'] = $youhui ? $youhui: '0';
            $data['pay_money'] = $total- $panchan -$youhui;
         //    print_r($data);
            return $data;
        }
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
                $rows[$key]['id'] = $val['id'];
                $rows[$key]['key_name'] = $val['key_name'];
                $rows[$key]['name'] = $val['value_name'];
                $rows[$key]['pid'] = $twoID;
            } 
            if( $res ) echo json_encode($rows);
        }
    }
    
    /**
     * ajax发送短信
     */
    public function sendInfoOrder(){

        $type = $_REQUEST['type']; 
        $sucess = $_REQUEST['sucess']; 
        
        if( $_REQUEST['uid'] && ($type==='sendInfoOrder') ){
            //获取用户手机号码
            $UU = new GuestModel();
            $uid = $_REQUEST['uid'];
            $where = " user_id='$uid' ";
            $info = $UU -> getUidPhone( $where );
            $phone = $info['mobile_phone'];
            $name = $info['user_name']?$info['user_name']:$info['truename'];
        //    $phone = '13011855842';
            if( $sucess ==='3' ) {
                $str="$name:您好，您已在中细软服务商城购买中细软知识产权相关服务，请到 http://shop.gbicom.cn [个人中心]->[我的购物车]进行确认。";
            }else{
                $str="$name:您好，您在中细软服务商城购买的中细软知识产权相关服务还有未付款商品，如您还有需求请确认。";
            }
           
            send_msgnote($phone,$str);
        }
    }

}