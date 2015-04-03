<?php
/**
 * 后台用户处理Model
 * @author 李建�
 *
 */
namespace Admin\Model;

use Think\Model;

class adminuserModel extends Model{

    /**
     * 验证用户密码
     * @param string $username
     * @param string $password
     * @return array
     */
    function Cue($username , $password)
    {
            return $this -> Cue_user($username , $password);
    }

    /**
     * 验证用户
     * @param string $username
     * @param string $password
     * @return string
     */
    function Cue_user($username , $password)
    {
        $user_arr = $this -> where("card ='".$username."' and status =1") -> select();
       
        if(!empty($user_arr))
        {
            $this -> Cue_psw($user_arr , $password);
        }else{
            return "员工工号不存在";
        }
        
    }

    /**
     * 验证密码
     * @param type $user_arr
     * @param type $password
     */
    function Cue_psw($user_arr , $password)
    {
        $admin_log = M("admin_log");
        $user_find = '';
        foreach($user_arr as $k=>$v)
        {
            
            if($v["password"] == md5(trim($password , "'")))
            {
                    session('userid', $v["id"]);
                    session("username",$v["username"]);
                    session("truename",$v["truename"]);
                    $user_find = 1;
            }
        }

        if($user_find != 1)
        {
            echo json_encode(array("code"=>0,"data"=>"密码错误"));die;
        }else{
            $uid = session("userid");
                $data["user_id"] = $uid;
                foreach($user_arr as $key =>$val)
                {
                        $data["user_name"]   = $val["truename"];
                        $data["model"]       = 2;
                        $data["action"]      = "登录";
                        $data["description"] = $val["truename"]."登录";
                        $Cilent = get_client_ip();
                        $data["IP"]          = $Cilent;
                        $data["createtime"]  = time();
                }
           $admin_log -> add($data);
        }	
    }
    /**
     * 根据用户id查询用户数据
     * @param int $Uid
     * @return array
     */
    function Getuser($Uid)
    {
            return $this -> where("id =".$Uid) -> find();
    }

    /**
     * 取出该用户所有的权限
     * @param int $Uid
     * @return array
     */
    function role_show($uid)
    {
            $role_user = M("role_user");
            return $role_user -> where("user_id =".$uid) -> select();
    }
}