<?php
/**
 * 登录Controller
 * @author 李建栋
 *
 */
namespace Admin\Controller;

use Admin\Model\adminuserModel;
use Admin\Model\IndexModel;
use Admin\Model\Role_userModel;
use Think\Controller;
use Think\Verify;

class LoginController extends Controller {
    /**
     * 登录
     */
    public function index() {
        $this->display ( "login" );
    }
    
    /**
     * 登录处理
     * @param string yzm      登录验证码
     * @param string username 登录用户名
     * @param string password 登录密码
     */
    public function Login_Do() {
        $adminuser = new adminuserModel ();
        $code = trim ( I ( "yzm" ) );
        $verify = new Verify ();
        $yzm = $verify->check ( $code, $id );
        if (! empty ( $yzm )) {
            $username = mysql_real_escape_string(strip_tags ( trim ( I ( 'post.username' ) ) ));
            $password = mysql_real_escape_string(strip_tags ( I ( 'post.password' ) ));
            $user = $adminuser->Cue ( $username, $password );
            $uid = session ( "userid" );
            if (! empty ( $uid )) {
                $role = $adminuser->role_show ( $uid );
                session ( $uid, $role );
                echo json_encode ( array (
                    "code" => 1,
                    "data" => "登录成功" 
                ) );
            } else {
                echo json_encode ( array (
                    "code" => 0,
                    "data" => $user 
                ) );
            }
        }else {
            echo json_encode ( array (
                "code" => 0,
                "data" => "验证码错误" 
            ) );
        }
    }

    /**
     * 验证码
     */
    public function Verify() {
        $Verify = new Verify ();
        $Verify->fontSize = 150;
        $Verify->length = 4;
        $Verify->useNoise = true;
        echo $Verify->entry ();
    }
}