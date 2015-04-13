<?php
/**
 * 跑堂管理
 * @author xxq
 */
namespace Admin\Controller;

use Admin\Model\WaiterModel;
use Think\Controller;
use Page\Page;
include(COMMON_PATH."Class/Page.class.php");

class WaiterController extends Controller{
    
    public function _initialize() {
        
        header("content-type:text/html;charset=utf8");
        $uid = session("userid");
        if( empty( $uid ) )
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }  
    }
    /**
     * 获取跑堂信息列表
     */
    public function lists(){
       
        //获取跑堂角色id
        $WW = new WaiterModel();
        $role_id = $WW -> getRole_id( '跑堂' );
        
        $curname = $_SESSION['username']; //创建者
        $curid = $_SESSION['userid'];//创建者id
        $where =" status='1' and role_id= $role_id and ( creator='$curname' or creator_uid=$curid ) ";
        
        if( $_REQUEST['sel']=='1' ){
            $where.=" and   truename like '%".$_REQUEST['keyword']."%' ";
        }
        if( $_REQUEST['sel']=='2' ){
            $where.=" and   mobile='$_REQUEST[keyword]' ";
        }
        if( $_REQUEST['sel']=='3' ){
            $where.=" and   email='$_REQUEST[keyword]' ";
        }
        
        $Count = $WW -> getCount($where);
        //每页显示条数
        $page_count = 10; 
        $Page = new Page($Count, $page_count);
        //回扣值
        $map['keyword'] = @$_REQUEST['keyword']; 
        $map['sel'] = @$_REQUEST['sel']; 
        foreach($map as $key => $val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize = $Page -> show(); //得到分页模板
        $data = $WW -> getAdminUser($Page->firstRow , $Page->listRows , $where);
        
        $this -> assign('sel',$_REQUEST['sel']);
        $this -> assign('keyword',$_REQUEST['keyword']);
        $this -> assign('map',$map);
        $this -> assign('p',trim(I("p")));
        $this -> assign('page' , $Pagesize);
        $this -> assign('data',$data);
        $this -> display();  
    }
    /**
     * 添加后台管理成员即跑堂
     */
    public function addWaiter(){
      
        //读取店铺名字和跑堂角色id
        $res = $this -> getShopsignAndRole();
        $data = array();
        
        $data['role_id'] = $res['role_id'];
        $data['shopsign'] = $res['shopsign'];  
        
        if( $_POST ){
           
            $waiter = M('adminuser');
            //验证信息
            $rules = array(
                array('truename','require','请输入跑堂工牌号的姓名！'),
                array('email','require','请输入跑堂的邮箱！'),
                array('mobile','require','请输入跑堂的手机号码！'),
                array('card','require','请输入跑堂的工号！'),
                array('password','repassword','确认密码不正确',0,'confirm')
            );
            //错误提示
            if (!$waiter -> validate( $rules ) -> create()){
                exit( $waiter -> getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
            }

            $data['truename'] = $_POST['truename'];
            $data['email'] = $_POST['email'];
            $data['mobile'] = $_POST['mobile'];
            $data['shopsign'] = $_POST['shopsign'];
            $data['card'] = $_POST['card'];
            //对图片路径做处理
            $data['pic'] = substr($_POST['pic'],stripos($_POST['pic'],'/uploads'));
            $data['thumb'] = substr($_POST['img0'],stripos($_POST['img0'],'/uploads'));
            $data['thumbtwo'] = substr($_POST['img1'],stripos($_POST['img1'],'/uploads'));
            $data['thumbthree'] = substr($_POST['img2'],stripos($_POST['img2'],'/uploads'));
            $data['sex'] = $_POST['sex'];
            //判断密码
            $data['password'] = md5( $_POST['password'] );
           
            $data['birth'] = strtotime( trim( $_POST['birth'] ) );
            $data['identitytype'] = $_POST['identitytype'];
            $data['IDnum'] = $_POST['IDnum'];
            $data['desc'] = $_POST['desc'];
            $data['creator'] = $_SESSION['username'];
            $data['creator_uid'] = $_SESSION['userid'];
            $data['addtime'] = time();
           
            $result = $waiter -> add( $data );
           
            if( $result ){
               $this -> redirect('/Admin/Waiter/activityWaiter');
            }else{
               exit( $waiter -> getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
           }
        }
       
        $this ->assign('shopsign', $data['shopsign']);
        
        $this -> display();   
    }
    
    /**
     * 获取当前登陆者的店铺名和跑堂角色id
     */
    private function getShopsignAndRole() { 
        
        $uid = $_SESSION['userid'];
        if( $uid ){
            
            $WW = new WaiterModel();
            
            $shopsign = $WW -> getShopsign( $uid );
            $role_id = $WW -> getRole_id( '跑堂' );
            
            $res = array('shopsign'=>$shopsign,'role_id'=>$role_id);
            return $res;
        }else{
            unset($_SESSION);
        }
        
    }

    /**
     * 获取需要激活的跑堂列表
     * 
     */
    public function activityWaiter(){
        
        //获取跑堂角色id
        $WW = new WaiterModel();
        $role_id = $WW -> getRole_id( '跑堂' );
        
        $curname = $_SESSION['username']; //创建者
        $curid = $_SESSION['userid'];//创建者id
        $where =" status='2' and role_id= $role_id and ( creator='$curname' or creator_uid=$curid ) ";
        
        if( $_REQUEST['sel']=='1' ){
            $where.=" and   truename like '%".$_REQUEST['keyword']."%' ";
        }
        if( $_REQUEST['sel']=='2' ){
            $where.=" and   mobile='$_REQUEST[keyword]' ";
        }
        if( $_REQUEST['sel']=='3' ){
            $where.=" and   email='$_REQUEST[keyword]' ";
        }
        
        $Count = $WW -> getCount($where);
        //每页显示条数
        $page_count = 10; 
        $Page = new Page($Count, $page_count);
        //回扣值
        $map['keyword'] = @$_REQUEST['keyword']; 
        $map['sel'] = @$_REQUEST['sel']; 
        
        foreach($map as $key => $val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize = $Page -> show(); //得到分页模板
        $data = $WW -> getAdminUser($Page->firstRow , $Page->listRows , $where);
        
        $this -> assign('sel',$_REQUEST['sel']);
        $this -> assign('keyword',$_REQUEST['keyword']);
        $this -> assign('map',$map);
        $this -> assign('p',trim(I("p")));
        $this -> assign('page' , $Pagesize);
        $this -> assign('data',$data);
        $this -> display();   
    }
    
    
    /**
    * 检查手机是否存在
    */
    public function checkphone(){
        
        $user = new WaiterModel();
        if( $_POST['type'] == 'checkphone' && !empty( $_POST['phone'] ) ){
            $phone = $_POST['phone'];
            $result = $user -> CheckPhone($phone);
            if( $result ){ echo 1;}
        }
        
    }
    /**
     * 更改跑堂状态 激活/停用
     */
    public function changeStatus() {
        
        $WW = new WaiterModel();
        
        if( $_POST['type'] === 'changeStatus' && !empty( $_POST['uid'] ) ){
            
            $uid = $_POST['uid'];
            $code = $_POST['code'];
            
            $result = $WW -> changeStatus( $uid, $code);
            if( $result ){ echo 1;}
        }
    }
    /**
     * 跑堂详情
     */
    public function detailWaiter() {
       
        $uid = trim( $_GET['id'] );
        if( $uid ){
            //获取当前用户所有信息
            $WW = new WaiterModel();
            $data = $WW -> getUserDetail( $uid );

            // 获取所有跑堂
            $role_id = $WW -> getRole_id( '跑堂' );
            $waiters = $WW -> getWaiters( $role_id,$uid );
           
            //获取当前跑堂添加过的客官
            $cid = $_SESSION['userid'];
            $where =" creatorid=$cid ";
            $Count = $WW -> getMemCount( $where );
            
            //每页显示条数
            $page_count = 10; 
            $Page = new Page($Count, $page_count);
            //回扣值
            $map['keyword'] = @$_REQUEST['keyword']; 
            $map['sel'] = @$_REQUEST['sel']; 

            foreach($map as $key => $val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
            }
            $Pagesize = $Page -> show(); //得到分页模板
            $members = $WW -> getAddMembers( $where ,$Page->firstRow , $Page->listRows );
           
            $this -> assign('data',$data);  
            $this -> assign('waiters',$waiters); 
            $this -> assign('map',$map);
            $this -> assign('p',trim(I("p")));
            $this -> assign('page' , $Pagesize);
            $this -> assign('members',$members); 
            
            $this -> display();    
        }
    }
    /**
     * 替换跑堂
     * @return unknown 
     */
    public function changeWaiter() {
        
        $WW = new WaiterModel();
        
        if( $_POST['type'] === 'changeWaiter' && !empty( $_POST['pid'] )  && !empty( $_POST['uids'] )){
            
            $uids = trim( $_POST['uids'],',' );
            $pid = trim( $_POST['pid'] );
            $id = trim( $_GET['user_id'] );
            
            $result = $WW -> changeWaiter( $id, $uids,$pid);
            if( $result ){ echo 1;}
        }
    }
    /**
     * 编修失败列表
     */
    public function failServer() {
        
        $WW = new WaiterModel();
        
        $where = ' shop_compile.is_pass=2 and shop_compile.status=1 ';
     //   $where = ' a.order_id=b.id and a.is_pass=2  ';
        
        $titlename = $_POST['titlename'];
        $searchdata = $_POST['searchdata'];
        $starttime = $_POST['starttime'] ? strtotime($_POST['starttime']) :'';
        $endtime = $_POST['endtime'] ? strtotime($_POST['endtime']) :'';
        
        if( $searchdata ){
            $where .= " and b.$titlename like '%$searchdata%' ";
        }
        if( $starttime ){
            $where .= " and b.pay_time >= '$starttime' ";
        }
        if( $endtime ){
            $where .= " and b.pay_time <= '$endtime' ";
        }
        
        $Count = $WW -> getfailServerCount( ' is_pass = 2 and a.status=1 ' );
    //    $Count = $WW -> getfailServerCount( ' is_pass = 2  ' );
       
        $page_count = 10; 
        $Page = new Page($Count, $page_count);
        //回扣值      
        $map['titlename'] = $titlename;
        $map['searchdata'] = $searchdata;
        $map['starttime'] = date('Y-m-d H:i:s',$starttime);
        $map['endtime'] = date('Y-m-d H:i:s',$endtime);
        foreach($map as $key => $val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize = $Page -> show(); //得到分页模板
    
        $data = $WW -> getFailData( $Page->firstRow , $Page->listRows , $where);
        
        $this -> assign('map',$map);
        $this -> assign('p',trim(I("p")));
        $this -> assign('page' , $Pagesize);
        $this -> assign("data" , $data);
        $this -> display();
    }
    
    /**
     * 处理编修失败数据
     * @return unknown 
     */
//    public function changeIspass() {
//        
//        $WW = new WaiterModel();
//        
//        if( $_POST['type'] === 'changeIspass' && !empty( $_POST['co_id'] ) ){
//            
//            $co_id = trim( $_POST['co_id'] );
//            
//            $result = $WW -> changeIspass( $co_id );
//            if( $result ){ echo 1;}
//        }
//    }
   
}