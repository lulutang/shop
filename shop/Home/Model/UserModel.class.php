<?php
/**
 * 前台个人中心
 * author  author
 * $_user  用户的UID
 * $model  链接外库变量
 */
namespace Home\Model;
use Think\Model;
class UserModel extends Model {
    protected $autoCheckFields = false;
    protected $_user;
    private $model;

    /**
    * 获取session初始�
    */
    public function __construct() {
        header ( "content-type:text/html;charset=utf-8" );
        $u = (session ( 'user' ) != NULL) ?   session ( 'user' )  : " ";
        $this->model  = $this->db(1,"DB_CONFIG1")->table("ecs_users");
        //$this->_user = 3177;
        $this->_user = $u['user_id'];
        $this->_username = $u['user_name'];
    }
    /**
    * 获取个人详细信息
    * @return array
    */
    public function getUserInfo() {
        $obj = $this->model;
        $data = $obj->field('user_id,user_name,email,mobile_phone,travelling,rank,growup,sex,birthday,address,bind_mobile') -> where ( "user_id = " . $this->_user . "" )->find ();
        if (empty ( $data )) {
        	 return FALSE;
        }
        $arr = explode('-', $data['birthday']);
        $data['year'] = isset($arr[0])? $arr[0] : "";
        $data['month'] = isset($arr[1])? $arr[1] : "";
        $data['day'] = isset($arr[2])? $arr[2] : "";
        return $data;
    }
    /**
    * 保存个人信息
    * @param  int $user_id 用户id
    * @param  array $value 用户信息
    * @return array or string
    */
    public function saveUserInfo($user_id,$value) {
        if (! empty ( $value ) && isset ( $user_id )) {
                $obj = $this->model;
                return $obj->where ( "user_id = " . $user_id ) -> save ( $value );
        } else {
                return false;
        }
    }
    /**
    * 递增个人信息
    * @param  int $uid 用户id
    * @param  string $key 需要更新的关键�
    * @return string $value 需要累计的�
    */
    public function incUserInfo($uid, $key, $value) {
        if (isset ( $key ) && isset ( $uid ) && isset($value)) {
                $obj = $this->model;
                return $obj->where ( "user_id = " . $uid )  -> setInc($key,$value);
        } else {
                return false;
        }
    }
    /**
    * 编辑个人的基本信�
    * @param  array $data 用户信息
    * @return string
    */
    public function EditMyInfo($data) {
        $obj = $this->model;
        $datas ['sex'] = $data ['sex'];
        $datas ['mobile_phone'] = $data ['mobile_phone'];
        $datas ['birthday'] = $data ['birthyear'] . '-' . $data ['birthmonth'] . '-' . $data ['birthday'];
        $datas ['address'] = $data ['address'];
        $datas ['photo_add'] = $data ['photo_add'];

        $result = $obj->where ( "user_id = " . $this->_user . "" )->save ( $datas );
        if ($result > 0) {
            $userinfo = $obj->where ( "user_id = " . $this->_user . "" )->find();
            session('user',$userinfo);
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 获取交易人信息列�
    * @return array
    */
    public function getDealList() {
        $data = M ( "trader" )->field('id,trader_phone,trader_name,trader_city,trader_province,trader_address,is_person')->where ( "trader_belong = " . $this->_user . "" )->select ();
        
        if (! empty ( $data )) {
                return $data;
        }
        return FALSE;
    }
    /**
    * 添加个人交易人信�
    * @param  array $data 交易人信息
    * @return int
    */
    public function SaveTraderInfo($data) {
        $datas['trader_name'] = $data['t_name'];
        $datas['e_trader_name'] = $data['t_e_name'];
        $datas['trader_number'] = $data['t_num'];
        $datas['trader_province'] = $data['country'];
        $datas['trader_city'] = $data['province'];
        $datas['trader_address'] = $data['t_address'];
        $datas['e_trader_address'] = $data['t_e_address'];
        $datas['trader_photo'] = $data['s_url'];
        $datas['trader_fbfphoto'] = $data['y_url'];
        $datas['postcode'] = $data['t_dawk'];
        $datas['is_inland'] = $data['is_inland'];
        $datas['is_person'] = $data['is_person'];
        $datas['trader_belong'] = $this->_user;
        $datas['trader_phone'] = $data['t_phone'];
        if($data['is_inland'] == 2){
            $datas['in_accept_name'] = $data['t_ac_name'];
            $datas['in_accept_address'] = $data['t_ac_adress'];
            $datas['in_accept_postcode'] = $data['t_ac_dwak'];
        }
        $result = M ( 'trader' )->add ( $datas );
        if ($result > 0) {
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 通过ID获取交易人信息
    * @param  int $id 交易人信息ID
    * @return array
    */
    public function getTraderInfo($id) {
        return M( 'trader' )->field('id,trader_phone,trader_name,e_trader_name,trader_number,trader_belong,trader_photo,trader_fbfphoto,trader_city,trader_province,trader_address,postcode,in_accept_name,in_accept_address,in_accept_postcode,is_inland,is_person,e_trader_address')->where ( "id = " . $id . "" )->find ();
        
    }
    /**
    * 修改交易人信息
    * @param  array $data 个人交易人信息
    * @return string
    */
    public function EditTraderInfo($data) {
        $datas['trader_name'] = isset($data['t_name']) ? $data['t_name'] : "";
        $datas['e_trader_name'] = isset($data['t_e_name']) ? $data['t_e_name'] : "";
        $datas['trader_number'] = isset($data['t_num']) ? $data['t_num'] : "";
        $datas['trader_province'] = isset($data['country']) ? $data['country'] : "";
        if($data['country'] != '国家' && $data['province'] != '省份、州'){
            $datas['trader_city'] = isset($data['province']) ? $data['province'] : "";
            $datas['trader_address'] = isset($data['t_address']) ? $data['t_address'] : "";
        }
        $datas['e_trader_address'] = isset($data['t_e_address']) ? $data['t_e_address'] : "";
        if($data['s_url'] != ""){
            $datas['trader_photo'] = isset($data['s_url']) ? $data['s_url'] : "";
        }
        if($data['y_url'] != ""){
            $datas['trader_fbfphoto'] = isset($data['y_url']) ? $data['y_url'] : "";
        }
        $datas['postcode'] = isset($data['t_dawk']) ? $data['t_dawk'] : "";
        $datas['is_inland'] = isset($data['is_inland']) ? $data['is_inland'] : "";
        $datas['is_person'] = isset($data['is_person']) ? $data['is_person'] : "";
        $datas['trader_belong'] = $this->_user;
        $datas['trader_phone'] = isset($data['t_phone']) ? $data['t_phone'] : "";
        if($data['t_ac_name'] != "" && $data['t_ac_name'] != "" && $data['t_ac_dwak'] != ""){
            $datas['in_accept_name'] = isset($data['t_ac_adress']) ? $data['t_ac_name'] : "";
            $datas['in_accept_address'] = isset($data['t_ac_adress']) ? $data['t_ac_adress'] : "";
            $datas['in_accept_postcode'] = isset($data['t_ac_dwak']) ? $data['t_ac_dwak'] : "";
        }
        $result = M ( 'trader' )->where("id = ".$data['tid']."")->save ( $datas );
        if ($result > 0) {
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 获取所有收货地址信息
    * @return array
    */
    public function getShipAdressInfo() {
        $data = M ( 'address' )->where("user_id = " . $this->_user . "")->select ();
        if (! empty ( $data )) {
                return $data;
        }
        return FALSE;
    }
    /**
    * 添加收货地址信息
    * @param  array $data 收货地址信息
    * @return string
    */
    public function addsAddress($data) {
        $datas ['sh_province'] = $data ['provinces'];
        $datas ['sh_city'] = $data ['citys'];
        $datas ['sh_minute_address'] = $data ['minute_address'];
        $datas ['sh_name'] = $data ['name'];
        $datas ['sh_phone'] = $data ['phone'];
        $datas ['user_id'] = $this->_user;
        if (isset ( $data ['is_check'] ) && $data ['is_check'] == 1) {
                $arr ['is_check'] = 0;
                M ( "address" )->where ( "user_id = " . $this->_user . "" )->save ( $arr );
        }
        $datas ['is_check'] = $data ['is_check'];
        $datas ['zip_code'] = $data ['zip_code'];
        $datas ['tel'] = $data ['tel'];
        $result = M ( 'address' )->add ( $datas );
        if ($result > 0) {
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 获取详细收货地址信息
    * @param  int $id 收货地址id
    * @return array
    */
    public function getAddress($id) {
        $data = M ( 'address' )->where ( "id = " . $id . "" )->find ();
        $data ['province'] = $data ['sh_province'];
        $data ['city'] = $data ['sh_city'];
        $data ['minute_address'] = $data ['sh_minute_address'];
        $data ['name'] = $data ['sh_name'];
        $data ['phone'] = $data ['sh_phone'];
        if (! empty ( $data )) {
                return $data;
        }
        return FALSE;
    }
    /**
    * 修改收货地址信息
    * @param  array $data 收货地址信息
    * @return string
    */
    public function EditsAddress($data) {
        $datas ['sh_province'] = $data ['province'];
        $datas ['sh_city'] = $data ['city'];
        $datas ['sh_minute_address'] = $data ['minute_address'];
        $datas ['sh_name'] = $data ['name'];
        $datas ['sh_phone'] = $data ['phone'];
        $datas ['user_id'] = $this->_user;
        $datas ['is_check'] = $data ['is_check'];
        $datas ['zip_code'] = $data ['zip_code'];
        $datas ['tel'] = $data ['tel'];
        $result = M ( 'address' )->where ( "id = " . $data ['aid'] . "" )->save ( $datas );
        if ($result > 0) {
                $datass ['is_check'] = 0;
                M('address')->where ( "id != " . $data ['aid'] . " and user_id = ".$this->_user."" )->save ( $datass );
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 修改收货地址信息
    * @param  array $data 收货地址信息
    * @return string
    */
    public function SiteAddress($id){
        $datas ['is_check'] = 1;
        $result = M ( 'address' )->where ( "id = " . $id . "" )->save ( $datas );
        if ($result > 0) {
                $datass ['is_check'] = 0;
                M('address')->where ( "id != " . $id. " and user_id = ".$this->_user."" )->save ( $datass );
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 获取到个人安全信�
    * @return string
    */
    public function getSelfInfo() {
        $obj = $this->model;
        $data = $obj->field ( "user_id,email,mobile_phone,last_login,bind_mobile" )->where ( "user_id = " . $this->_user . "" )->find ();
        if (! empty ( $data )) {
                return $data;
        }
        return FALSE;
    }
    /**
     * 验证旧密码是否正�
     * @param  string $old_pass 旧密�
     * @return string
     */
    public function CheckPassword($old_pass) {
        $username = $this->_username;
        require_once(APP_UC_PATH ."uc_config.php");
        require_once (APP_UC_PATH.'uc_client/client.php');
        $ucresult = uc_user_login($username, $old_pass);

        if ($ucresult[0] != -2) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    /**
    * 修改EC新的密码
    * @param  string $new_pass 新密�
    * @return string
    */
    public function EditPassword($old_pass,$new_pass) {
        $obj = $this->model;
        $username = $this->_username;
        $result = $this -> UpUc($username, $old_pass, $new_pass, '');
        if($result['status']==1){
            $data ['password'] = md5 ( $new_pass );
            $data ['last_time'] = date("Y-m-d H:i:s");
            $results = $obj->where ( "user_id = " . $this->_user . "" )->save ( $data );
            if ($results > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        }
        return FALSE;
    }
    /**
    * 修改uc新的密码
    * @return string
    */
    private function UpUc($username, $old_pass, $new_pass, $email)
    {
        require_once(APP_UC_PATH ."uc_config.php");
        require_once (APP_UC_PATH.'uc_client/client.php');
        $ucresult = uc_user_edit($username, $old_pass, $new_pass, $email);
        if($ucresult == -1) {
                return array('status'=>-1,'msg'=>'旧密码不正确');
        } elseif($ucresult == -4) {
                return array('status'=>-4,'msg'=>'Email 格式有误');
        } elseif($ucresult == -5) {
                return array('status'=>-5,'msg'=>'Email 不允许注测');
        } elseif($ucresult == -6) {
                return array('status'=>-6,'msg'=>'�Email 已经被注测');
        }elseif($ucresult == 1){
                return array('status'=>1,'msg'=>'更新成功');
        }
    }
    /**
    * 用户绑定手机
    * @param  string $phone 手机号码
    * @return string
    */
    public function BindMyPhone($phone) {
        $obj = $this->model;
        $data ['bind_mobile'] = $phone;
        $result = $obj->where ( "user_id = " . $this->_user . "" )->save ( $data );
        if ($result > 0) {
                return TRUE;
        } else {
                return FALSE;
        }
    }
    /**
    * 获取所有已支付订单
    * @return array
    */
    public function GetAllOrder() {
         $_goods = M ( 'goods' );
        $_package = M ( 'package' );
        $orderModel = M ( 'order' );
        $data = $orderModel -> where ( "status=1 AND user_id = " . $this->_user  )->order("createtime desc") ->select ();
        foreach ( $data as $key => $value ) {
            if (! empty ( $value ['goods_id'] ) && empty ( $value ['is_package'] ) && empty ( $value ['package_id'] )) {
                $n = $key;
                $a = explode ( ',', $value ['goods_id'] );
                foreach ( $a as $key => $val ) {
                    $list = $_goods->where ( "goods_id=" . $val . "" )->find ();
                    if (GetSerByGodsId ( $val ) == 'T001') {
                        $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $val . " and order_id = ".$value['order_id']."" )->find ();
                        $in = json_decode ( $info ['message'], true );
                        if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['style'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                $list ['is_wan'] = 1;
                                $list ['order_status'] = $info ['status'];
                        } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['style'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                $list ['is_wan'] = 2;
                                $list ['order_status'] = $info ['status'];
                        } else {
                                $list ['is_wan'] = 3;
                                $list ['order_status'] = $info ['status'];
                        }
                        $list ['type'] = 'T001';
                    } else if (GetSerByGodsId ( $val ) == 'T002') {
                        $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $val . " and order_id = ".$value['order_id']."" )->find ();
                        $in = json_decode ( $info ['message'], true );
                        if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                $list ['is_wan'] = 1;
                                $list ['order_status'] = $info ['status'];
                        } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                $list ['is_wan'] = 2;
                                $list ['order_status'] = $info ['status'];
                        } else {
                                $list ['is_wan'] = 3;
                                $list ['order_status'] = $info ['status'];
                        }
                        $list ['type'] = 'T002';
                    } else if (GetSerByGodsId ( $val ) == 'T003') {
                        $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $val . " and order_id = ".$value['order_id']."" )->find ();
                        $in = json_decode ( $info ['message'], true );
                        if ((! isset ( $in ['text'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                $list ['is_wan'] = 1;
                                $list ['order_status'] = $info ['status'];
                        } else if (isset ( $in ['text'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                $list ['is_wan'] = 2;
                                $list ['order_status'] = $info ['status'];
                        } else {
                                $list ['is_wan'] = 3;
                                $list ['order_status'] = $info ['status'];
                        }
                        $list ['type'] = 'T003';
                    }
                    $data [$n] ['order'] [] = $list;
                    }
                } else if (empty  ( $value ['goods_id'] ) && $value ['is_package'] == 1 && ! empty ( $value ['package_id'] )) {
                    $n = $key;
                    $a = explode ( ',', $value ['package_id'] );
                    foreach ( $a as $key => $val ) {
                        $list = $_package->where ( "package_id=" . $val . "" )->find ();
                        $b = explode ( ',', $list ['zuhe'] );
                        foreach ( $b as $k => $v ) {
                            $lists = $_goods->where ( "goods_id=" . $v . "" )->find ();
                            if (GetSerByGodsId ( $v ) == 'T001') {
                                $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $v . " and order_id = ".$value['order_id']."" )->find ();
                                $in = json_decode ( $info ['message'], true );
                                if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['style'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 1;
                                        $lists ['order_status'] = $info ['status'];
                                } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['style'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 2;
                                        $lists ['order_status'] = $info ['status'];
                                } else {
                                        $lists ['is_wan'] = 3;
                                        $lists ['order_status'] = $info ['status'];
                                }
                                $lists ['type'] = 'T001';
                            } else if (GetSerByGodsId ( $v ) == 'T002') {
                                $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $v . " and order_id = ".$value['order_id']."" )->find ();
                                $in = json_decode ( $info ['message'], true );
                                if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 1;
                                        $lists ['order_status'] = $info ['status'];
                                } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 2;
                                        $lists ['order_status'] = $info ['status'];
                                } else {
                                        $lists ['is_wan'] = 3;
                                        $lists ['order_status'] = $info ['status'];
                                }
                                $lists ['type'] = 'T002';
                            } else if (GetSerByGodsId ( $v ) == 'T003') {
                                $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $v . " and order_id = ".$value['order_id']."" )->find ();
                                $in = json_decode ( $info ['message'], true );
                                if ((! isset ( $in ['text'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 1;
                                        $lists ['order_status'] = $info ['status'];
                                } else if (isset ( $in ['text'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 2;
                                        $lists ['order_status'] = $info ['status'];
                                } else {
                                        $lists ['is_wan'] = 3;
                                        $lists ['order_status'] = $info ['status'];
                                }
                                $lists ['type'] = 'T003';
                            }
                            $data [$n] ['order'] [] = $lists;
                        }
                        $list ['type'] = 'T003';
                    }
                } else if (! empty ( $value ['goods_id'] ) && $value ['is_package'] == 1 && ! empty ( $value ['package_id'] )) {
                    $n = $key;
                    $a = explode ( ',', $value ['goods_id'] );
                    foreach ( $a as $key => $val ) {
                        $list = $_goods->where ( "goods_id=" . $val . "" )->find ();
                        if (GetSerByGodsId ( $val ) == 'T001') {
                            $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $val . " and order_id = ".$value['order_id']."" )->find ();
                            $in = json_decode ( $info ['message'], true );
                            if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['style'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                    $list ['is_wan'] = 1;
                                    $list ['order_status'] = $info ['status'];
                            } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['style'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                    $list ['is_wan'] = 2;
                                    $list ['order_status'] = $info ['status'];
                            } else {
                                    $list ['is_wan'] = 3;
                                    $list ['order_status'] = $info ['status'];
                            }
                            $list ['type'] = 'T001';
                        } else if (GetSerByGodsId ( $val ) == 'T002') {
                            $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $val . " and order_id = ".$value['order_id']."" )->find ();
                            $in = json_decode ( $info ['message'], true );
                            if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] ))  && $info ['status'] == 0) {
                                    $list ['is_wan'] = 1;
                                    $list ['order_status'] = $info ['status'];
                            } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                    $list ['is_wan'] = 2;
                                    $list ['order_status'] = $info ['status'];
                            } else {
                                    $list ['is_wan'] = 3;
                                    $list ['order_status'] = $info ['status'];
                            }
                            $list ['type'] = 'T002';
                        } else if (GetSerByGodsId ( $val ) == 'T003') {
                            $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $val . " and order_id = ".$value['order_id']."" )->find ();
                            $in = json_decode ( $info ['message'], true );
                            if ((! isset ( $in ['text'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                    $list ['is_wan'] = 1;
                                    $list ['order_status'] = $info ['status'];
                            } else if (isset ( $in ['text'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                    $list ['is_wan'] = 2;
                                    $list ['order_status'] = $info ['status'];
                            } else {
                                    $list ['is_wan'] = 3;
                                    $list ['order_status'] = $info ['status'];
                            }
                            $list ['type'] = 'T003';
                        }
                        $data [$n] ['order'] [] = $list;
                    }
                    $a = explode ( ',', $value ['package_id'] );
                    foreach ( $a as $key => $val ) {
                        $list = $_package->where ( "package_id=" . $val . "" )->find ();
                        $b = explode ( ',', $list ['zuhe'] );
                        foreach ( $b as $k => $v ) {
                            $lists = $_goods->where ( "goods_id=" . $v . "" )->find ();
                            if (GetSerByGodsId ( $v ) == 'T001') {
                                $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $v . " and order_id = ".$value['order_id']."" )->find ();
                                $in = json_decode ( $info ['message'], true );
                                if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['style'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 1;
                                        $lists ['order_status'] = $info ['status'];
                                } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['style'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 2;
                                        $lists ['order_status'] = $info ['status'];
                                } else {
                                        $lists ['is_wan'] = 3;
                                        $lists ['order_status'] = $info ['status'];
                                }
                                $lists ['type'] = 'T001';
                            } else if (GetSerByGodsId ( $v ) == 'T002') {
                                $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $v . " and order_id = ".$value['order_id']."" )->find ();
                                $in = json_decode ( $info ['message'], true );
                                if ((! isset ( $in ['text'] ) || ! isset ( $in ['name'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 1;
                                        $lists ['order_status'] = $info ['status'];
                                } else if (isset ( $in ['text'] ) && isset ( $in ['name'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 2;
                                        $lists ['order_status'] = $info ['status'];
                                } else {
                                        $lists ['is_wan'] = 3;
                                        $lists ['order_status'] = $info ['status'];
                                }
                                $lists ['type'] = 'T002';
                            } else if (GetSerByGodsId ( $v ) == 'T003') {
                                $info = M ( 'order_goods' )->field ( "message,status" )->where ( "user_id = " . $this->_user . " and goods_id=" . $v . " and order_id = ".$value['order_id']."" )->find ();
                                $in = json_decode ( $info ['message'], true );
                                if ((! isset ( $in ['text'] ) || ! isset ( $in ['j_info'] ) || ! isset ( $in ['address'] )) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 1;
                                        $lists ['order_status'] = $info ['status'];
                                } else if (isset ( $in ['text'] ) && isset ( $in ['j_info'] ) && isset ( $in ['address'] ) && $info ['status'] == 0) {
                                        $lists ['is_wan'] = 2;
                                        $lists ['order_status'] = $info ['status'];
                                } else {
                                        $lists ['is_wan'] = 3;
                                        $lists ['order_status'] = $info ['status'];
                                }
                                $lists ['type'] = 'T003';
                            }
                            $data [$n] ['order'] [] = $lists;
                        }
                    }
                }
        }
       
        if (! empty ( $data )) {
                return $data;
        }
        return FALSE;
    }
    
    /**
    * 获取所有未支付订单
    * @return array
    */
    public function GetAllOrderPay() {
        $_goods = M ( 'goods' );
        $_package = M ( 'package' );
        $day = 3600*24;
        $data = M ( 'order' )->where ( "user_id = " . $this->_user . " and status=0 and unix_timestamp() < (createtime+".$day.")" )->order("createtime desc")->select ();
        foreach ( $data as $key => $value ) {
                if (! empty ( $value ['goods_id'] ) && empty ( $value ['is_package'] ) && empty ( $value ['package_id'] )) {
                        $n = $key;
                        $a = explode ( ',', $value ['goods_id'] );
                        foreach ( $a as $key => $val ) {
                                $list = $_goods->where ( "goods_id=" . $val . "" )->find ();
                                $data [$n] ['order'] [] = $list;
                        }
                } else if (empty ( $value ['goods_id'] ) && $value ['is_package'] == 1 && ! empty ( $value ['package_id'] )) {
                        $n = $key;
                        $a = explode ( ',', $value ['package_id'] );
                        foreach ( $a as $key => $val ) {
                                $list = $_package->where ( "package_id=" . $val . "" )->find ();
                                $b = explode ( ',', $list ['zuhe'] );
                                foreach ( $b as $k => $v ) {
                                        $lists = $_goods->where ( "goods_id=" . $v . "" )->find ();	
                                        $data [$n] ['order'] [] = $lists;
                                }
                                $list ['type'] = 'T003';
                        }
                } else if (! empty ( $value ['goods_id'] ) && $value ['is_package'] == 1 && ! empty ( $value ['package_id'] )) {
                        $n = $key;
                        $a = explode ( ',', $value ['goods_id'] );
                        foreach ( $a as $key => $val ) {
                                $list = $_goods->where ( "goods_id=" . $val . "" )->find ();
                                $data [$n] ['order'] [] = $list;
                        }
                        $a = explode ( ',', $value ['package_id'] );
                        foreach ( $a as $key => $val ) {
                                $list = $_package->where ( "package_id=" . $val . "" )->find ();
                                $b = explode ( ',', $list ['zuhe'] );
                                foreach ( $b as $k => $v ) {
                                        $lists = $_goods->where ( "goods_id=" . $v . "" )->find ();
                                        $data [$n] ['order'] [] = $lists;
                                }
                        }
                }
        }
        if (! empty ( $data )) {
                return $data;
        }
        return FALSE;
    }
    /**
    * 交易人下拉框所需
    * @return array
    */
    public function GetJaoInfo() {
        $data = M ( "deal" )->where ( "user_id = " . $this->_user . "" )->select ();
        foreach ( $data as $key => $val ) {
                $ress = explode ( '|', $val ['address'] );
                $list [] = $val ['self_name'] . ' ' . $val ['company_name'] . ' ' . $ress [0] . $ress [1] . $ress [2] . ' ' .$val['phone'];
        }
        if (! empty ( $list )) {
                return $list;
        }
        return FALSE;
    }
    /**
    * 收货地址下拉框所需
    * @return array
    */
    public function GetResInfo() {
        $data = M ( 'address' )->where ( "user_id = " . $this->_user . "" )->select ();
        foreach ( $data as $key => $value ) {
                $arr [] = substr ( strtr ( $value ['content'], '|', ' ' ), 0, strrpos ( strtr ( $value ['content'], '|', ' ' ), '(' ) );
        }
        if (! empty ( $arr )) {
                return $arr;
        }
        return FALSE;
    }
    /**
    * 完善商品交易信息
    * @param  array $data 完善信息
    */
    public function UpdateMessage($data){
        $id = $data['id'];
        $order_id = $data['order_id'];
        unset($data['id']);
        unset($data['order_id']);
        if ( empty($data['text']) ){
                unset($data['text']);
        }
        if ( empty($data['name']) ){
                unset($data['name']);
        }
        if ( empty($data['j_info']) ){
                unset($data['j_info']);
        }
        if ( empty($data['style']) ){
                unset($data['style']);
        }
        if ( empty($data['styles']) ){
                unset($data['styles']);
        }
        if ( empty($data['address']) ){
                unset($data['address']);
        }
        if(isset($data['styles'])){
            $data['style'] = $data['styles'];
            unset($data['styles']);
        }else if(isset($data['styles'])){
            $data['style'] = $data['style'];
        }
        $data['short_title'] = $data['short_title'];
        $data['act'] = $data['act'];
        $list['message'] = json_encode($data);

        $resu = M ('order_goods') -> where( "user_id = " . $this->_user . " and goods_id=" . $id . " and order_id = ".$order_id."" ) -> save($list);
    }
    /**
    * 查看订单详细
    * @param  int $order_id 订单ID
    * @return array
    */
    public function LookOrder($order_id){
        $info = M('order') -> where("user_id = ".$this->_user." and order_id = ".$order_id."") -> find();
        return $info;
    }
    /**
    * 查看订单详细
    * @param  int $order_id 订单ID
    * @param  int $id 商品ID
    * @return array
    */
    public function LookOrders($order_id,$id){
        $info = M('order_goods') -> where("user_id = ".$this->_user." and order_id = ".$order_id." and goods_id=" . $id . "") -> find();
        $list = json_decode($info['message'],true);
        $list['goods_price'] = $info['goods_price'];
        $list['status'] = $info['status'];
        return $list;
    }
    /**
    * 得到商品信息
    * @param  int $id 商品ID
    * @return array
    */
    public function GetGods($id){
        return $info = M('goods') -> field('short_title') -> where("goods_id = ".$id."") -> find();
    }
    /**
    * ajax获取完善信息所需信息
    * @param  int] $order_id 订单ID
    * @param  int] $id 商品ID
    * @return string
    */
    public function GetMessageInfo($id,$order_id) {
        $data = M ( 'order_goods' )->field ( 'message' )->where ( "user_id = " . $this->_user . " and goods_id=" . $id . " and order_id = ".$order_id."" )->find ();
        return $data ['message'];
    }
    /**
    * 得到购物车所需信息
    * @return array
    */
    public function GetAllCartInfo() {
        $data = M ( 'cart' )->where ( "user_id = " . $this->_user . "" )->select ();
        $resu = M ( 'service' )->order("orderid")->select ();
        $obj = $this->getTree ( $resu );
        foreach ( $data as $key => $val ) {
                if (count ( explode ( ',', $val ['goods_id'] ) ) > 1) {
                        $data ['package'] ['username'] = '套餐组合';
                        $data ['package'] [] = $val;
                        unset ( $data [$key] );
                } else {
                        unset ( $data [$key] );
                        $arr = M ( 'goods' )->where ( "goods_id = " . $val ['goods_id'] . "" )->find ();
                        $val ['goods_name'] = $arr ['short_title'];
                       
                        foreach ( $obj as $key => $value ) {
                                $num = array ();
                                foreach ( $value ['child'] as $k => $v ) {
                                        $num [] = $v ['id'];
                                }
                                if (in_array ( $arr ['s_id'], $num )) {
                                        $data [$value ['server_id']] ['username'] = $value ['server_name'];
                                        $data [$value ['server_id']] [] = $val;
                                }
                        }
                }
        }
        return $data;
    }
    public function getTree($data, $pid = 0) {
        foreach ( $data as $k => $v ) {
                if ($v ['parent_id'] == $pid) {
                        $v ['child'] = $this->getTree ( $data, $v ['id'] );
                        $list [] = $v;
                }
        }
        return $list;
    }
    /**
    * 删除多个购物车信�
    * @param  string $id 购物车ID
    * @return string
    */
    public function DelCart($id) {
        $result = M ( 'cart' )->where ( "user_id = " . $this->_user . " and id in(" . $id . ")" )->delete ();
        if ($result > 0) {
                return TRUE;
        }
        return FALSE;
    }
    /**
    * 删除单个购物车信�
    * @param  int $id 购物车ID
    * @return string
    */
    public function DelSelfCart($id) {
        $result = M ( 'cart' )->where ( "user_id = " . $this->_user . " and id =" . $id . "" )->delete ();
        if ($result > 0) {
                return TRUE;
        }
        return FALSE;
    }
}