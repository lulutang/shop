<?php
/**
 * 后台主Controller
 * @author 李建栋
 *
 */
namespace Admin\Controller;

use Admin\Model\adminuserModel;
use Admin\Model\IndexModel;
use Think\Controller;

class IndexController extends Controller {
    public function _initialize() {
        header("content-type:text/html;charset=utf8");
        $uid = session("userid");
        if(empty($uid))
        {
            echo '<script>top.location.href="/Admin/Login/index";</script>';
        }
    }
    
    /**
     * 后台左侧菜单栏
     * @param int userid      用户Id
     * @param string username 用户名称
     */
    public function left(){
        $uid = session('userid');
        $username = session('username');
        if($username ==='admin'){
            $priv='all';
        }else{
            $where = '';
            $noroles = M('Role')->field(' role_id ')->where(' status!=1')->select();
            $rr = '' ;
            foreach( $noroles as $v){
              $rr .= $v['role_id'].',';   
            }
            $ro = trim($rr,',');
            if($ro) $where = '   and role_id not in('.$ro.') ';

            $roles = M('Role_user')->field(' role_id ')->where(' user_id='.$uid. $where )->select();
            if( !$roles ){
                session(null);
                echo "<script>alert('请联系超级管理员分配正确的角色！');parent.location.reload(); </script>";
            }
            $str = '' ;
            foreach( $roles as $key=>$vv){
              $str .= $vv['role_id'].',';   
            }
            $str = trim($str,',');
            $privs = M('Role_priv')->field(' priv_id,p_id ')->where(' role_id in('.$str.')' )->order(' p_id ')->select();
            $priv = array();
            foreach ($privs as $kk=>$val) {
                $priv[$val['p_id']][$kk] = $val['priv_id'];
            }
        }
        $this->assign('priv',$priv);
        $this->display('Public/left');
    }
    
    /**
     * 控制台
     * @param int userid 用户Id
     */
    public function main()
    {
        $user_id = session("userid");
        $arr = array();
        $useradmin = new adminuserModel();
        $Index  = new IndexModel();
        $nowday = $Index -> order_day();
        $newc_data = count($nowday["newc_data"]);
        $oldc_data = count($nowday["old_data"]["oldc_data"]);
        $new_arr = $nowday["new_data"];
        $old_arr = $nowday["old_data"]["old_data"]; 
        if($oldc_data == 0 && $newc_data != 0){
            $arr["new_order"]  = 100;
        }else{
            $arr["new_order"]  = (($newc_data - $oldc_data)/$oldc_data)*100;
        }
        if($old_arr["down_order"] == 0 && $new_arr['down_order'] != 0){
            $arr["down_order"] = 100; 
        }else{
            $arr["down_order"] = (($new_arr["down_order"] - $old_arr["down_order"])/$old_arr["down_order"])*100; 
        }
        if($old_arr["pay_order"] == 0 && $new_arr['pay_order'] != 0){
            $arr["pay_order"]  = 100; 
        }else{
            $arr["pay_order"]  = (($new_arr["pay_order"] - $old_arr["pay_order"])/$old_arr["pay_order"])*100; 
        }
        if($old_arr["total"] == 0 && $new_arr['total'] != 0){
            $arr["total"]      = 100; 
        }else{
            $arr["total"]      = (($new_arr["total"] - $old_arr["total"])/$old_arr["total"])*100; 
        }
        $this -> order_lv  = $arr; 
        $this -> new_arr   = $new_arr; 
        $this -> old_arr   = $old_arr;
        $this -> newc_data = $newc_data;
        $this -> oldc_data = $oldc_data;
        $user_arr = $useradmin -> Getuser($user_id);
        $this -> user_arr =$user_arr;
        $this->display();
    }

    /**
     * 取出年月日订单
     * @param int num 时间对应数字 
     */
    public function order_show()
    {
        $num = trim(I("num")); 
        $Index  = new IndexModel();
        if($num == 2) {
            $order_time = $Index -> order_oldday(); 
        }else if($num == 3) {
            $order_time = $Index -> order_week();    
        }else if($num == 4) {
            $order_time = $Index -> order_month();    
        }else if($num == 5) {
            $order_time = $Index -> order_quarter(); 
        }else if($num == 6){
            $order_time = $Index -> order_year();  
        }else if($num == 7) {
            $order_time = $Index -> order_all();  
        }
        $order_time["old_data"]["new_order"] = count($order_time["oldc_data"]);
        $this -> order_time = $order_time;
        $this -> display();	
    }
        
    /**
     * 修改密码
     * @param string old 旧密码
     * @param string new 新密码
     */
    public function up_pws()
    {
        $Index  = new IndexModel();
        if(IS_POST)
        {
            $old = trim(I("old"));
            $new = trim(I("new"));
            $is = $Index -> up_PAss($old , $new);
            if(isset($is) == 1)
            {
                session('userid',null);
                echo "<script>parent.location.reload(); </script>";
            }else{
                $this -> success("修改失败，旧密码错误" , "/Admin/Index/up_pws");
            }
        }else{
            $this -> display();
        }

    }
    
    /**
     * 退出
     * @param int userid 用户Id
     */
    public function GO_out()
    {
        session('userid',null); 
        $uid = session("userid");
        if(empty($uid))
        {
            $this -> redirect("/Admin/Login/index");
        }
    }
}