<?php
/**
 * 系统管理
 * @author xxq
 */
namespace Admin\Controller;
use Admin\Model\UserModel;
use Admin\Model\Admin_logModel;
use Admin\Model\EcsuserModel;
use Think\Controller;
use Page\Page;
include(COMMON_PATH."Class/Page.class.php");

class UserController extends Controller{
    
    public function _initialize() {
        
        header("content-type:text/html;charset=utf8");
        $uid = session("userid");
        if( empty( $uid ) )
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }  
    }
    /**
     * 获取用户信息
     */
    public function userinfo(){

        $where =" truename!='admin' ";

        if(!empty($_REQUEST['keywords'])){
            $where.=" and   truename like '%".$_REQUEST['keywords']."%' or card like '%".$_REQUEST['keywords']."%' ";
        }
        
        $_user = new UserModel();
        $Count = $_user -> getCount($where);
        //每页显示条数
        $page_count = 10; 
        $Page = new Page($Count, $page_count);
        //回扣值
        $map['keywords'] = @$_REQUEST['keywords']; 
        
        foreach($map as $key => $val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize = $Page -> show(); //得到分页模板
        $userinfo = $_user -> getUserInfo($Page->firstRow , $Page->listRows , $where);
       // print_r($userinfo);
        $roleinfo = $_user -> getRoleInfo();
        $this -> assign('map',$map);
        $this -> assign('p',trim(I("p")));
        $this -> assign('page' , $Pagesize);
        $this -> assign('roleinfo',$roleinfo);
        $this -> assign('userinfo',$userinfo);
        $this -> display('users');
    }
    /**
     * 添加后台管理成员
     */
    public function adduserinfo(){
        if(IS_POST ){
            $data = I('post.');
            $_user = new UserModel();
            //验证信息
            $rules = array(
              array('card','require','员工工号不能为空！'),
              array('role_id','require','请选择角色！'),
              array('truename','require','员工姓名不能为空！'),
              array('mobile','require','手机号码不能为空！'),
              array('email','email','email格式错误'),
              array('qq','require','QQ不能为空！'),
              array('desc','require','员工描述不能为空！'),
              array('password','password2','确认密码不正确',0,'confirm'),
              array('username','require','系统登录名不能为空！')
            );
            //错误提示
            if (!$_user -> validate( $rules ) -> create()){
                exit( $_user -> getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
            }

            if( $data['id'] ){//更新
                if( $_FILES["file"]["name"] ) {//图片处理
                    $data['thumb'] = $this -> uploadpic();
                }
                if( !empty($data['password']) &&  ($data['password'] ==$data['password2'] )){
                    $data['password'] = md5($data['password']);
                }else{
                    unset($data['password']);
                }
            }else{//添加
                $data['thumb'] = $this -> uploadpic();
                
                if( !empty($data['password']) &&  ($data['password'] ==$data['password2'] )){
                    $data['password'] = md5($data['password']);
                }
            }   
            //确认密码
            unset($data['password2']);
            
            $result = $_user -> AddUserInfo($data);
            
            if($result==TRUE){
                    $this -> redirect('Admin/User/userinfo');
            }
        }
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
     * ajax 获取编辑信息
     */
    public function edituserinfo(){
        $id = intval($_REQUEST['id']);
        
        if($id && $_REQUEST['type']==='editMember'){
            $_user = new UserModel();
            $result = $_user -> getOneUserInfo($id);
            echo json_encode($result);
        }
    }

    /**
     * 删除员工信息 
     */
    public function delMember(){
        $id = intval($_REQUEST['id']);
        if($id && $_REQUEST['type'] === 'delMember'){
             //删除员工和角色的关系
             M('Role_user') -> where('user_id='.$id )->delete();
             //删除登录关系表
             M('Loginuser') -> where('uid='.$id )->delete();
             //删除员工
             M('Adminuser') -> where('id='.$id )->delete();
             echo '1';exit();
        }
    }
    /**
     * 保存用户信息
     */
    public function saveuserinfo(){
        if(IS_POST){ 
            $data = I('post.');
            
            $_user = new UserModel();
            $result = $_user -> SaveUserInfo($data);
            
            if($result==TRUE){
                 header("location:userinfo");
            }else{
                 header("location:userinfo");
            }
        }
    }

    /**
     * 角色列表
     */
    public function roleinfo(){
        $_user = new UserModel();
        $roleinfo = $_user -> getRoleInfo();
        $menus = $_user->getMenu();
        
        $this->assign('roleinfo',$roleinfo);
        $this->assign('menus',$menus);
        $this->display('role');
    }

    /**
     * 角色添加
     */
    public function addrole(){ 
        $role_id = isset($_REQUEST['rid']) ? $_REQUEST['rid'] : '';     
        
        $SS = M("Role");
        //验证信息
        $rules = array(
          array('name','require','角色名称不能为空！')
        );
        //错误提示
        if (!$SS->validate($rules)->create()){
            exit($SS->getError().' [ <A HREF="javascript:history.back()">返 回</A> ]');
        }else{
            $data['role_id'] = $role_id;
            $data['name'] = trim( $_POST['name'] );
            $data['remark'] = trim( $_POST['remark'] );
            $data['status'] = trim( $_POST['status'] ) == 'on' ? '1' :'0' ;
            $data['addtime'] = time();

            if( $role_id ){ 
                $result = $SS -> where('role_id='.$role_id) -> save($data);   
            }else{
                $result = $SS -> data( $data )->add(); 
                $role_id = $result;
            }
             //判断是否有权限 有：删除原有 添加new 
            if( $_POST['Son'] ){
                $_user = new UserModel();
                $_user -> delAddRole( $_POST['Son'],$role_id );
            }

            if( $result ){         
               $this->redirect('Admin/User/roleinfo');
            }      
        }  

    }
    
    /**
     * 改变角色状态
     */
    public function changeRoleStatus() {

        $id = trim($_REQUEST['id'],','); 
        $type = $_REQUEST['type']; 

        if( $id && ($type==='changeRoleStatus') ){
            $status = $_REQUEST['status']; 

            M()->query(" update shop_role set status =$status where role_id in( $id ) ");

            echo "1";
        } 
    }
    /**
     * 改变角色状态
     */
    public function changeStatus() {

        $id = trim($_REQUEST['id'],','); 
        $type = $_REQUEST['type']; 

        if( $id && ($type==='changeStatus') ){
            $status = $_REQUEST['status']; 

            M()->query(" update shop_adminuser set status =$status where id in( $id ) ");

            echo "1";
        } 
    }
  
    /**
     * ajax传递角色信息
     */
    public function getRoleInfo() {

        $id = intval($_REQUEST['rid']); 
        $type = $_REQUEST['type']; 

        if( $id && ($type==='getRoleInfo') ){

            $RR = M("Role");  

            $result = $RR->where('role_id='.$id)->find();
            $result['priv'] = M("Role_priv")->where('role_id='.$id)->select();

            echo json_encode($result);exit();
        }
    }

    /**
     * 角色修改
     */
    public function updaterole(){
        
            $_user = new UserModel();
            $roleinfo = $_user -> getRoleInfo();
        
            $this->assign('roleinfo',$roleinfo);

            $this->display('role');
    }

    /**
     * ajax传递角色和会员信息
     */
    public function getSetMember() {

        $id = $_REQUEST['rid']; 
        $type = $_REQUEST['type']; 

        if( $id && ($type==='setMember') ){
            //角色信息
            $RR = M("Role");
            $result = $RR->where('role_id='.$id)->find();
            //除超级管理员外的会员信息
            $members = M("Adminuser")->field('id,truename')->where(" truename!='admin' ")->order('id')->select();

            $result['role_user']= M()->query("select distinct user_id,(SELECT truename from shop_adminuser where shop_adminuser.id=user_id) as truename FROM shop_role_user where role_id=$id");

          //处理显示左侧显示缺少右侧的会员
            $str = '';
            foreach ($result['role_user'] as $value) {
                $str.=$value['user_id'].',';
            }
            $str = explode(',', trim($str,','));

            foreach( $members as $key=>$value ){
                if( in_array( $value['id'], $str ) ){ unset( $members[$key] ); }
            } 

            sort($members);
            $result['member'] = $members;

            echo json_encode($result);exit();
        }
    }

    /**
     * 根据ajax 传递数据更改角色
     */
    public function addRoleMem() {

        $type = $_REQUEST['type']; 

        if( $_REQUEST['rid'] && ($type==='addRoleMem') ){

            $data['role_id'] = intval( $_REQUEST['rid'] );
            $ids = trim( $_REQUEST['ids'],',' );
            $idarr = explode(',', $ids);

            for( $i = 0 ; $i < count($idarr) ; $i++ ){
                $data['user_id'] = $idarr[$i];

                //删除之前的角色 
                M('Role_user')->where('user_id='.$data['user_id'])->delete(); 

                //更新adminuser 表用户的权限信息
                $adminuser['role_id'] = $data['role_id'];
                M('Adminuser')->where('id='.$data['user_id'])->data($adminuser)->save(); 

                $res = M('Role_user')->add($data);
            }
            if( $res ){ echo '1'; }
        }
    }
    /**
     * 根据ajax 传递数据更改角色
     */
    public function delRoleMem() {

        $type = $_REQUEST['type']; 

        if( $_REQUEST['rid'] && ($type==='delRoleMem') ){

            $role_id = intval( $_REQUEST['rid'] );
            $ids = trim( $_REQUEST['ids'],',' );

            //清除用户表的角色和员工关系
            $adminuser['role_id'] = '';
            M('Adminuser')->where(' id in('.$ids.')' )->data($adminuser)->save(); 
         //   echo M('Adminuser')->getLastSql();
            $res = M('Role_user')->where(' role_id='.$role_id.' and user_id in('.$ids.')' )->delete();

            if( $res ){ echo '1'; }
        }
    }
    
    /**
     * 角色删除
     */
    public function DelRole() {

        $type = $_REQUEST['type']; 

        if( $_REQUEST['rid'] && ($type==='delRole') ){

            $role_id = intval( $_REQUEST['rid'] );
            //查看该角色是否有员工 删除该角色下所有员工
            M('Role_user')->where('  role_id='.$role_id )->delete();

            //删除角色权限
            M('Role_priv')->where('  role_id='.$role_id )->delete();

            //删除角色

            $query = M('Role')->where('  role_id='.$role_id )->delete();
            if( $query ){
                echo '1';
            }
        }
    }

    /**
     * 操作日志列表
     */
    public function log(){

        $where =' 1=1 ';
        if(!empty($_REQUEST['keywords'])){
            $where.=" and user_name like '%".$_REQUEST['keywords']."%' or action like '%".$_REQUEST['keywords']."%' ";
        }
        if(!empty($_REQUEST['start'])){
            $starttime = strtotime(trim($_REQUEST['start']));
            $where.=" and createtime >= $starttime";
        }
        if(!empty($_REQUEST['end'])){
            $endtime = strtotime(trim($_REQUEST['end']));
            $where.=" and createtime <= $endtime ";
        }

        $log = new Admin_logModel();
        $Count = $log -> getCount($where);
        $page_count = 10; //每页显示条数
        $Page = new Page($Count, $page_count);// 实例化分页类 传入总记录数
        $map['keywords'] = @$_REQUEST['keywords']; //回扣值
        $map['start'] = @$_REQUEST['start'];
        $map['end'] = @$_REQUEST['end'];
        foreach($map as $key=>$val) {   
            $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $Pagesize =$Page ->show(); //得到分页模板
        $loginfo = $log -> getLogInfo($Page->firstRow , $Page->listRows , $where);

        $this->assign('map',$map);
        $this->assign('p',trim(I("p")));
        $this->assign('page' , $Pagesize);
        $this->assign('loginfo',$loginfo);
        $this->display('log');
    }
    
    /**
    * 用户前台会员管理
    */
    public function usershow(){
        $where ='1=1';
        if(!empty($_GET['keywords'])){
                $where.=" and user_name like '%".$_GET['keywords']."%' or email like '%".$_GET['keywords']."%' or mobile_phone like '%".$_GET['keywords']."%'";
        }
        $user =new UserModel();
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
        $this->display();
    }
    
    /**
    * 前台会员添加
    */
    public function saveuser(){
        $user =new UserModel();
        $data = I('post.');
        $password = md5($data['mobile_phone']);
        $data['password'] = $password;
        $data['uc_password'] = $data['mobile_phone'];
        $data['email'] = $data['email'];
        $data['user_name'] = $data['user_name'];
        $data['bind_mobile'] = $data['mobile_phone'];
        $data['reg_time'] = time();
        $data['is_hand'] = 1;
        $data['salesman'] = session('username');
        $result = $user -> AddUserInfos($data);
        if($result==TRUE){
            header("location:usershow");
        }else if($result['status'] == 301 || 302 || 303 || 304 || 305 || 306 || 307){
            $this ->error($result['msg'],'usershow');
        }else{
            $this ->error('服务器异常，请稍后继续执行操作...','usershow');
        }
    }
    
    /**
    * 检查会员手机是否存在
    */
    public function checkphone(){
        $user =new UserModel();
        $phone = $_POST['phone'];
        $result = $user->CheckPhone($phone);
        if($result==TRUE){ echo 1;}
    }
    
    /**
    * 查看会员详细信息
    */
    public function lookdetail(){
        $user =new UserModel();
        $user_id = $_POST['user_id'];
        $userinfo = $user -> LookDetail($user_id);
        echo json_encode($userinfo);
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
     * 首页搜集的信息
     */
    public function getGleanUserInfo() {
    	$get = I('get.');
    	$page = isset ( $get ['p'] ) ? intval ( $get ['p'] ) : 1;
    	$pageSize = isset ( $get ['size'] ) ? intval ( $get ['size'] ) : 10;
    	$keywords = isset ( $get ['keywords'] ) ? $get ['keywords'] : '';
    	
    	$buy_timeb = str_replace('+', ' ', $get ['buy_timeb']) ;
    	$buy_timeend = str_replace('+', ' ', $get ['buy_timeend']);
    	$model = new UserModel();
    	$where = array();
    	if($keywords) {
    		$where['name|user_name|phone|qq|style'] = array('like', '%'.$keywords.'%');
    		$this->assign ( 'keywords', $keywords );
    	}
    	
    	if (! empty ( $buy_timeb ) && ! empty ( $buy_timeend )) {
    		$where ['addTime'] = array (
    				'between',
    				strtotime ( $buy_timeb ) . ',' . strtotime ( $buy_timeend )
    		);
    	} else if (! empty ( $buy_timeb )) {
    		$where ['addTime'] = array (
    				'egt',
    				strtotime ( $buy_timeb )
    		);
    	} else if (! empty ( $buy_timeend ))
    		$where ['addTime'] = array (
    				'elt',
    				strtotime ( $buy_timeend )
    		);
    	$list = $model ->getGleanUserInfo($page, $pageSize, $where);
    	$pageModel = new Page ( $list ['count'], $pageSize );
    	$pages = $pageModel->show ();
    	
    	$this->assign ( 'pages', $pages );
    	$this->assign ( 'buy_timeb', $buy_timeb );
		$this->assign ( 'buy_timeend', $buy_timeend );
    	$this->assign ( 'list', $list['info'] );
    	$this->display();
    }
        
}