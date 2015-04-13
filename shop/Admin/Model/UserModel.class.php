<?php
/**
 * 系统管理
 * @author xxq
 */
namespace Admin\Model;

use Think\Model;

class UserModel extends Model{
    
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
     * 获取菜单信息
     * @return array
     */
    public function getMenu() {
        //先求一级
        $one = M('Menu') -> where('p_id=0') -> select();
        //求子级
        foreach ( $one as $key=>$value) {
            $one[$key]['son'] = M('Menu')->where("p_id =".$value['id'])->select();
        }
        return $one;

    }
    /*
     * 删除原有权限 添加新设的权限
     * $priv array 具体权限
     * $role_id 角色id
     */
    public function delAddRole($priv, $role_id) {

        if( $priv ){
            //删除
            M('Role_priv')->where('role_id='.$role_id)->delete();

            $data['role_id'] = $role_id;

            foreach ($priv as $key => $value) {
                $data['p_id'] = $key;
                //写入新权限
                foreach ($value as $vv) {

                    $data['priv_id'] = $vv;

                    M('Role_priv')->data($data)->add();
                }
            }
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
        $userInfos = $this -> field('user_id,email,mobile_phone,reg_time,last_login,bind_mobile')-> db(1,"DB_CONFIG1") -> table("ecs_users") -> where($where) -> order("reg_time desc") -> limit($start,$end) -> select();
        return $userInfos;
    }
    /**
    * Ec添加用户
    * @param  array $data 用户信息
    */
    public function AddUserInfos($data){
        $username = $data['user_name'];
        $password = $data['uc_password'];
        $email = $data['email'];
        $result = $this->UcAdd( $username, $password, $email);
        if($result['status'] == 200){
            $str = "";
            foreach ($data as $k => $v){
                $str.="'".$v."',";
            }
            $str = substr($str, 0, -1);
            $sql = "insert into ecs_users(user_name,email,mobile_phone,password,bind_mobile,home_phone,reg_time,is_hand,salesman) values(".$str.")";
            $results = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") ->query($sql);
            return TRUE;   
        }
        return $result;
    }
    /**
    * uc添加用户
    * @param  string $username 用户帐号
    * @param  string $password 密码
    * @param  string $email    邮箱地址
    */
    private function UcAdd($username, $password, $email){
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
        $result = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") -> where("mobile_phone = '".$phone."'") -> find();
        if($result){
            return TRUE;
        }
        return FALSE;
    }
    /**
    * 查看用户详情
    * @param  string $user_id 用户UID
    */
    public function LookDetail($user_id){
        $result = $this -> db(1,"DB_CONFIG1") -> table("ecs_users") ->where("user_id = ".$user_id."") ->find();
        return $result;
    }
    
    /**
     * 获取首页收集的信息
     */
    public function getGleanUserInfo($page, $pageSize, $where = null){
    	$m = M('Glean_info');
    	if ($where) {
    		$info = $m->where ( $where )->order ( 'addTime DESC' )->page ( $page, $pageSize )->select (); 
    		$count = $m->where ( $where )->count ();
    	} else {
    		$info = $m->order ('addTime DESC')->page ( $page, $pageSize )->select (); 
    		$count = $m->count ();
    	}
    	
    	return array('count' => $count, 'info' => $info);
    }
    
}