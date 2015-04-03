<?php
/**
 * 用户登录model
 * 此层作为信任层 可对传来的参数不必校验,在调用此model里的方法时需要对参数做严格判断
 * tanglulu
 */
namespace Home\Model;
use Think\Model;

class LoginModel extends Model {
	private $model;
	private $field;
	public  function __construct() {
		//可查询字节
		$this-> field = 'user_id,email,user_name,sex,user_money,reg_time,photo_add,bind_mobile';
		$this->model  = $this->db(1,"DB_CONFIG1")->table("ecs_users");
	}
	
	/**
	 * 用户登录
	 * @param string $userName 用户登录名称
	 * @param string $password 用户登录密码
	 */
	public function userLogin($userName,$password){
		$password = MD5($password);
		if(is_numeric($userName)) {
			$user = $this->model ->field( $this->field ) -> where('mobile_phone = "' .$userName .'" AND password ="'. $password.'"') ->find();
		} else {
			$user = $this->model ->field( $this->field ) -> where('user_name ="' .$userName .'" AND password ="'. $password.'"') ->find();
		}
		if($user['user_id']) {
			$this-> model ->table("ecs_users") -> where('user_id = '.$user['user_id']) ->save(array('last_login' => time()));
			
		}
		return $user;
	}
	/**
	 * 用户登录
	 * @param string $userName 用户登录名称
	 */
	public function userLoginByUc($userName){	
		if(is_numeric($username)) {
			$user = $this->model-> field( $this->field ) -> where('mobile_phone = "' .$userName .'"') ->find();
		} else {
			$user = $this->model-> field( $this->field ) -> where('user_name ="' .$userName .'"') ->find();
		}
		if($user['user_id']) {
			$this-> model ->table("ecs_users") -> where('user_id = '.$user['user_id']) ->save(array('last_login' => time()));
		}
		return $user;
	}
	/**
	 * 用户登录
	 * @param string $userName 用户登录名称
	 */
	public function userLoginByMP($userName){

		$user = $this->model -> field( $this->field ) -> where('mobile_phone ="' .$userName .'"') ->find();
		if($user['user_id']) {
			$this-> model -> where('user_id = '.$user['user_id']) ->save(array('last_login' => time()));
		}
		return $user;
	}
	
	/**
	 * 用户登录
	 * @param string $userName 用户登录名称
	 */
	public function userLoginByUserName($userName){
	
		$user = $this->model -> field( $this->field ) -> where('user_name ="' .$userName .'"') ->find();
		if($user['user_id']) {
			$this-> model -> where('user_id = '.$user['user_id']) ->save(array('last_login' => time()));
		}
		return $user;
	}
    /**
     * 检测本地数据库是否存在该用户
     * @param string $username
     * @return array
     */
    public function check_local_user($username){
        $uid = $this->model -> where(array('user_name' => $username)) -> getField(user_id);
        if($uid){
            return $uid;
        }else{
            return false;
        }
    }

    /**
     * 将用户添加到本地数据库
     * @param string $username
     * @param string $password
     * @return array
     */
    public function add_local_user($username, $password){
         $data['user_name'] = $username;
         $data['password'] = md5($password);
         $uid = $this->model->add($data);
        return $uid;
    }

    /**
     * 通过用户名获取uid
     * @param string $username
     * @return array
     */
    public function get_uid($username){
        $uid = $this->model->where(array('user_name'=>$username))->getField('user_id');
        return $uid;
    }
}