<?php
/**
 * 前台个人中心
 * author  cjl
 * 
 */
namespace Home\Controller;
use Home\Model\LoginModel;
use Home\Model\UserModel;
use Think\Controller;
class UserController extends Controller{
   
    public function _initialize() {
        $user = session('user');
        if(!isset($user)) {
                $this->redirect('/Home/index/');
        }
    }
    
    /**
    * 显示个人中心
    */
    public function userhome(){
        $_user = new UserModel();
        $result = $_user->getUserInfo();
        $userinfo = ($result==FALSE) ? FALSE : $result;
        $re = session('user');
        $username = $re['user_name'];
        require_once APP_UC_PATH . '/uc_config.php';
        require_once APP_UC_PATH . '/uc_client/client.php';
        $uc_info = uc_get_user($username);
        $uc_id = $uc_info[0];
        $photo = '<img src="'.UC_API.'/avatar.php?uid='.$uc_id.'&type=virtual&size=big">';
        $userinfo['avatar'] = $photo;
        $this->assign('userinfo',$userinfo);
        $this->display();
    }
    /**
    * 显示编辑个人资料页面
    */
    public function showeditpage(){
        $_user = new UserModel();
        $result = $_user->getUserInfo();
        $userinfo = ($result==FALSE) ? FALSE : $result;
        $re = session('user');
        $username = $re['user_name'];
        require_once APP_UC_PATH . '/uc_config.php';
        require_once APP_UC_PATH . '/uc_client/client.php';
        $uc_info = uc_get_user($username);
        $uc_id = $uc_info[0];
        $avatar = uc_avatar($uc_id,'virtual','1');
        $userinfo['avatar'] = $avatar;
        $this->assign('birthyeayhtml',getbirthyeayhtml($userinfo['year']));
        $this->assign('birthmonthhtml',getbirthmonthhtml($userinfo['month']));
        $this->assign('birthdayhtml',getbirthdayhtml($userinfo['day']));
        $this->assign('userinfo',$userinfo);
        $this->display();
    }
    /**
    * 编辑个人详细资料
    */
    public function edituserinfo(){
        if(IS_POST){
            $_user = new UserModel();
            $data = I('post.');
            $result = $_user->EditMyInfo($data);
            echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
            echo "<script>alert('个人资料修改成功!');location.href='userhome';</script>";
        }
    }
    /**
    * 获取交易人信息列表
    */
    public function getdealinfo(){
        $_user = new UserModel();
        $result = $_user->getDealList();
        $this->assign('deallist',$result);
        $this->display();
    }
    /**
    * 显示添加交易人信息页面
    */
    public function showadddealinfo(){
        $this->display('members_trader');
    }
    /**
     * 上传文件
     * @param string file 文件域name
     * @return string
     */
    public function uploadpic($file){
        $extend = explode(".",$_FILES[$file]["name"]);
        $key = count($extend)-1;
        $ext = ".".$extend[$key];
        if($file == 'y_file'){
            $newfile = 'y_'.time().$ext;
        }else{
            $newfile = 's_'.time().$ext;
        }
        
        $savePath = "./uploads/thumb/".date('Ymd',time())."/"; 
        if(is_dir($savePath)==FALSE){
              mkdir($savePath,0777);  
        } 
        
        $thumb_path=$savePath.$newfile;
        move_uploaded_file($_FILES[$file]['tmp_name'] , $thumb_path);
        return substr($thumb_path, 1);

    }
    /**
    * 添加交易人信息
    */
    public function savetraders(){
        $arr = I('post.');
        $arr['y_url'] = $this -> uploadpic('y_file');
        $arr['s_url'] = $this -> uploadpic('s_file');
        $user = new UserModel();
        $str = $user -> SaveTraderInfo($arr);
        if(empty($str)){
            echo "<script>alert('Soory,Inter故障，请稍后再试。。。');location.href='/Home/User/getdealinfo';</script>";die;
        } 
        $this -> redirect('/Home/User/getdealinfo');
        
    }
   
    /**
    * 显示修改交易人信息
    */
    public function edittrader(){
        $id = isset($_GET['id']) ? $_GET['id'] : "";
        if(!is_numeric($id)){
           $this->assign ( 'message', '非法参数' );
           $this->display('Public/error');
           exit();
        }
        $_user = new UserModel();
        $result=$_user->getTraderInfo($id);
        $this->assign('trader',$result);
        $this->display('Edit_members_trader');
    }
    /**
    * 修改交易人信息
    */
    public function EditTraders(){
        $_user = new UserModel();
        $data = I('post.');
        
        if(empty($_FILES['y_file']['name'])){
            $data['y_url'] = "";
        }  else {
            $data['y_url'] = $this -> uploadpic('y_file');
        }
        if(empty($_FILES['s_file']['name'])){
            $data['s_url'] = "";
        }  else { 
            $data['s_url'] = $this -> uploadpic('s_file');
        }
        $arr = $_user->EditTraderInfo($data);
        if(empty($arr)){
            echo "<script>alert('网络故障，修改失败。。');location.href='/Home/User/getdealinfo';</script>";die;
        }
        $this -> redirect('/Home/User/getdealinfo');
    }
    /**
    * 收货地址列表信息
    */
    public function shipadresslist(){
        $_user = new UserModel();
        $result=$_user->getShipAdressInfo();
        $addressinfo = ($result==FALSE) ? FALSE : $result;
        $this->assign('addressinfo',$addressinfo);
        $this->display();
    }
    /**
    * 设置默认收货地址
    */
    public function siteaddress(){
        $id = $_GET['id'];
        $_user = new UserModel();
        $result = $_user->SiteAddress($id);
        if($result==TRUE){
                $this->redirect('Home/User/shipadresslist');
        }
    }
    /**
    * 添加或者修改收货地址
    */
    public function saveaddress(){
        $id = $_POST['id'];
        $_user = new UserModel();
        $result = $_user->getAddress($id);
        $addressinfo = ($result==FALSE) ? FALSE : $result;
        echo json_encode($addressinfo);	
    }
    /**
    * 添加收货地址
    */
    public function addsaddress(){
        $_user = new UserModel();
        $data = I('post.');
        $result = $_user->addsAddress($data);
        if($result==TRUE){
                header("location:shipadresslist");
        }else{
                header("location:shipadresslist");
        }
    }
    /**
    * 修改收货地址
    */
    public function editsaddress(){
        if(IS_POST){
                $_user = new UserModel();
                $data = I('post.');
                $result = $_user->EditsAddress($data);
                if($result==TRUE){
                        header("location:shipadresslist");
                }else{
                        header("location:shipadresslist");
                }
        }
    }
    /**
    * 显示安全设置页面
    */
    public function safetypage(){
        $_user = new UserModel();
        $result = $_user -> getSelfInfo();
        $selfinfo = ($result == FALSE) ? FALSE : $result;
        $this->assign('selfinfo',$selfinfo);
        $this->display();
    }
    /**
    * 显示验证密码页面
    */
    public function changepassword(){
            $this->display();
    }
    /**
    * 验证密码是否正确
    */
    public function checkpassword(){
        $old_pass = $_POST['old_pass'];
        $_user = new UserModel();
        $result = $_user -> CheckPassword($old_pass);
        if($result == TRUE){
                echo 'true';
        }else{
                echo 'false';
        }
    }
    /**
    * 修改当前密码
    */
    public function editpassword(){
        $old_pass = trim($_POST['old_pass']);
        $new_pass = trim($_POST['new_pass']);
        $_user = new UserModel();
        $result = $_user -> EditPassword($old_pass,$new_pass);
        if($result == TRUE){
                header("location:userhome");
        }else{
                header("location:changepassword");
        }
    }
    /**
    * 绑定手机
    */
    public function bindphone(){
            $this->display();
    }
    /**
    * 发送短信验证
    */
     public function send_note(){
        $mobile=$_POST['phone'];
        $con=rand(100000,999999);
        setcookie('dx',$con,time()+120,'/'); 
        $name = 'ciprun';
        $pwd  = '111222';
        $dst  = $mobile;
        $msg  = "本次验证码为".$con."提示：验证码仅供绑定手机可用，不可用作其他用途";
        $msg = iconv('utf-8','gbk',$msg);
        $fp = fopen("http://203.81.21.34/send/gsend.asp?name=$name&pwd=$pwd&dst=$dst&msg=$msg","r");  //取消时间参数
        $ret= fgetss($fp,255);
        fclose($fp);
        $rStr = explode('&',$ret);
        foreach ($rStr as $v){
            $k = explode('=', $v);
            $rArr[$k[0]] = $k[1];
        }
        //$this->sendLog($mobile, $con, $rArr['errid'], $rArr['num']);   //记录日志
        if($rArr['errid']==0){
            echo  1;
        }else{
            echo  0;
        }
    }
    /**
    * 检查验证码
    */
     public function check_note(){
        echo (cookie('dx') == $_POST['note']) ? 'Y' : 'N';
     }
    /**
    * 绑定我的手机
    */
     public function bindmyphone(){
        $phone = $_POST['phone'];
        $_user = new UserModel();
        $result=$_user->BindMyPhone($phone);
        if($result == TRUE){
                echo '1';
        }else{
                echo '0';
        }
     }
    /**
    * 绑定邮箱
    */
     public function bindemail(){
        $this->display();
     }
    /**
    * 已购买的商品
    */
     public function allorder(){
        $_user = new UserModel();
        $result=$_user->GetAllOrder();
        $usrinfo = $_user->GetJaoInfo();
        $resinfo = $_user->GetResInfo();
        $this->assign('style',C('SELF_STYLE'));
        $this->assign('usrinfo',$usrinfo);
        $this->assign('resinfo',$resinfo);
        $this->assign('orderinfo',$result);
        $this->display();
    }
    /**
    * 得到完善信息
    */
     public function getmessage(){
        header("Content-type:application/json;charset=utf-8");
        $id = $_POST['id'];
        $order_id = $_POST['order_id'];
        $_user = new UserModel();
        $info = $_user->GetMessageInfo($id,$order_id);
        echo $info;
     }
    /**
    * 修改完善信息
    */
     public function updatemessage(){
        $data = I('post.');
        $_user = new UserModel();
        $info = $_user->UpdateMessage($data);
        header("location:allorder");
     }
    /**
    * 查看订单详细
    */
     public function lookorder(){
        $order_id = $_GET['order_id'];
        $id = $_GET['id'];
        $_user = new UserModel();
        $info = $_user->LookOrder($order_id);
        $infos = $_user->LookOrders($order_id,$id);
        $gods = $_user->GetGods($id);
        $this->assign('gods',$gods);
        $this->assign('info',$info);
        $this->assign('infos',$infos);
        $this->display();
     }
    /**
    * 得到未支付订单
    */
     public function getallorderpay(){
        $_user = new UserModel();
        $result=$_user->GetAllOrderPay();
        $this->assign('orderinfo',$result);
        $this->display();
     }
    /**
    * 我的购物车
    */  
     public function mycart(){
        $_user = new UserModel();
        $result=$_user->GetAllCartInfo();
        $this->assign('cartinfo',$result);
        $this->display();
     }
    /**
    * 删除选中购物车商品
    */ 
     public function delcart(){
        $id = $_POST['id'];
        $_user = new UserModel();
        $result = $_user->DelCart($id);
        if($result==TRUE){
                echo 'success';
        }
     }
    /**
    * 删除选中单个购物车商品
    */ 
     public function delselfcart(){
        $id = $_POST['id'];
        $_user = new UserModel();
        $result = $_user->DelSelfCart($id);
        if($result==TRUE){
                echo 'success';
        }
     }
    /**
    * 检测本地数据库是否存在该用户，存在返回uid，不存在返回false
    */ 
    public function check_local_username($username){
        $_login = new LoginModel();
        return $uid = $_login->check_local_user($username);
    }
    /**
    * 把uc的用户添加到本地数据库，返回uid
    */
    public function add_local_user($username){
        $_login = new LoginModel();
        return $uid = $_login->add_local_user($username);
    }
    
    
    public function myaccount(){
        $_user = new UserModel();
        $str = $_user -> getUserInfo();
        $this->assign('acinfo',$str);
        $this->display('members_account');
    }
    
    public function mydetail(){
        $this->display('members_detail');
    }
    
    public function accountsafe(){
        $_user = new UserModel();
        $str = $_user -> getUserInfo();
        $this->assign('acinfo',$str);
        $this->display();
    }
    
    public function resetpasswd(){
        $_user = new UserModel();
        $str = $_user -> getUserInfo();
        $this->assign('acinfo',$str);
        $this->display();
    }
    
    public function bindpaynum(){
        if($_SERVER['HTTP_REFERER'] != 'http://'.$_SERVER['HTTP_HOST'].'/Home/User/resetpasswd'){
            $this->assign ( 'message', '网络错误，请稍后..' );
            $this->display('Public/error');
            exit();
        }
        
        $this->display();
    }
    public function resetpsword(){
        $pswd = I('password');
        $_user = new UserModel();
        $str = $_user -> savePayPasswd($pswd);
    }
            
}