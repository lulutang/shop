<?php
/**
 * 编修模块总控制类
 * author  cjl
 * createtime 2015-3-2 13:41:20
 */
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\CompileModel;
use Page\Page;
include(COMMON_PATH."Class/Page.class.php");
class CompileController extends Controller{
    
    const A = 'A'; //搜索条件,A代表按订单编号搜索
    const B = 'B'; //搜索条件,B代表按合同编号搜索
    const C = 'C'; //搜索条件,C代表按商标名称搜索
    const D = 'D'; //搜索条件,D代表按服务项目搜索
    const E = 'E'; //搜索条件,E代表按申请人名称搜索
    const F = 'F'; //搜索条件,F代表按申请人地址搜索
    const G = 'G'; //搜索条件,G代表按联系人搜索
    const H = 'H'; //搜索条件,H代表按联系电话搜索
    const I = 'I'; //搜索条件,I代表按支付时间搜索
    const J = 'J'; //搜索条件,J代表按编修审核日期搜索
    const K = 'K'; //搜索条件,K代表按编修报件日期搜索
    const L = 'L'; //搜索条件,L代表按下发受理日期搜索
    const M = 'M'; //搜索条件,M代表按初审公告日期搜索
    const N = 'N'; //搜索条件,N代表按注册公告日期搜索
    const O = 'O'; //搜索条件,O代表按进入下发注册日期搜索
    const P = 'P'; //搜索条件,P代表按进入编修审核日期搜索
    const Q = 'Q'; //搜索条件,Q代表按照进入编修审核时间倒叙排序
    const R = 'R'; //搜索条件,R代表按照进入报件时间搜索
    const S = 'S'; //搜索条件,S代表按照进入下发受理搜索
    const T = 'T'; //搜索条件,T代表按照进入初审公告搜索
    
    private $store = "";
    private $cause = "";
    private $res_status = "";
    /**
     * 编修审核
     * @var array $style 四十五大类类别
     * @var string $sort 排序
     */
    public function combxsh(){
        $order = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "QQ";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.status = 1 and c.is_pass != 2', $order,$res_status, $store, $cause, '1');
        $style = C('SELF_STYLE');
        
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_BXSH');
    }
    /**
    * 编修详情
    * @var array $style 四十五大类类别
    * @var string $sort 排序
    * @var string $cause 不通过原因
    * @var string $web 网络注册号
    * @1 代表编修审核
    * @2 代表编修报件
    * @3 代表下发受理
    * @4 代表初审公告
    */
    public function comminute(){
        
        $oid = isset($_REQUEST['oid']) ? $_REQUEST['oid'] : "";
        $cid = isset($_GET['cid']) ? $_GET['cid'] : "";
        $type = isset($_GET['type']) ? $_GET['type'] : "";
        $order = isset($_GET['order_code']) ? $_GET['order_code'] : "";
        $rtype = isset($_GET['rtype']) ? $_GET['rtype'] : "";
        if(!is_numeric($oid) || !is_numeric($cid) || !is_numeric($type) || !is_numeric($rtype)){
            echo '网络繁忙，非法参数...';die;
        }
        $com = new CompileModel;
        $arr = $com -> GetComMinute($oid, $cid);
        $log = $com -> GetAuditLog($order);
        $cause = C('SELF_CAUSE');
        $ems = C('SELF_EMS');
        $this -> assign('ems',$ems); 
        $this -> assign('log',$log); 
        $this -> assign('cause',$cause);
        $this -> assign('minfo',$arr);
        switch ($type) {
            case 1:
                $this -> display('editManage_BXSH_detail');
                break;
            case 2:
                $this -> display('editManage_BXBJ_detail');
                break;
            case 3:
                $this -> display('editManage_XFSL_detail');
                break;
            case 4:
                $this -> display('editManage_CSGG_detail');
                break;
            case 5:
                if($rtype == 1 || $rtype == 2){
                    $this -> display('editManage_ZCXF_detail');
                }else if($rtype == 3){
                    $this -> display('editManage_ZCXF_detail2');
                } 
                break;
            case 6:
                $this -> display('editManage_FWJS_detail');
                break;
            case 7:
                $this -> display('editManage_SHSB_detail');
                break;
            default:
                break;
        }
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                
    }
    /**
    * 编修判定结果录入
    * @1 代表直接提交
    * @2 代表提交并返回列表
    * @3 代表提交并审核下一个
    * @4 代表返回，不做操作
    */
    public function combxshjudge(){
        $status = isset($_GET['type']) ? $_GET['type'] : 1;
        $oid = isset($_POST['oid']) ? $_POST['oid'] : "";
        if(!is_numeric($status)){
            echo '网络繁忙，非法参数...';die;
        }
        $com = new CompileModel;
        $resul = I('post.');
        //记录失败日志
        if($resul['is_pass'] == 2){
            $data['order_code'] = $resul['order_code'];
            $data['causes'] = $resul['causes'];
            $data['auditor'] = admin;
            $data['time'] = time();
            $com -> ComFailLog($data);
        }
        if($status == 1){
            $arr = $com -> SaveBxshData($resul);
            if($resul['is_pass'] == 1){
                $com -> ChangeStatus('1', '3', $oid);
            }
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/combxbj';</script>";die;
            }
            $this -> redirect('Admin/Compile/combxbj');
        }else if($status == 2){
            $arr = $com -> SaveBxshData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/combxsh';</script>";die;
            }
            $this -> redirect('Admin/Compile/combxsh');
        }else if($status == 3){
            $arr = $com -> SaveBxshData($resul);
            $str = $com -> GetFirstData(1);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/1/rtype/10';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/1/rtype/10");
        }else{
            $this -> redirect('/Admin/Compile/combxsh');
        }
        
    }
    
    /**
     * 编修报件
     * @var array $style 四十五大类类别
     * @var string $sort 排序
     */
    public function combxbj(){
        
        $order = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "QQ";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.status = 2', $order, $res_status, $store, $cause, '2');
        $style = C('SELF_STYLE');
        //dump($result);
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_BXBJ');
    }
    /**
     * 报件结果录入
     * @1 代表直接提交
     * @2 代表提交并返回列表
     * @3 代表提交并审核下一个
     * @4 代表返回，不做操作
     */
    public function combxbjjudge(){
        $status = isset($_GET['type']) ? $_GET['type'] : 1;
        $oid = isset($_POST['oid']) ? $_POST['oid'] : "";
        if(!is_numeric($status)){
            echo '网络繁忙，非法参数...';die;
        }
        $com = new CompileModel;
        $resul = I('post.');
        if($status == 1){
            $com -> ChangeStatus('3', '4', $oid);    
            $arr = $com -> SaveBxbjData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comxfsl';</script>";die;
            }
            $this -> redirect('Admin/Compile/comxfsl');
        }else if($status == 2){
            $arr = $com -> SaveBxbjData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/combxbj';</script>";die;
            }
            $this -> redirect('Admin/Compile/combxbj');
        }else if($status == 3){
            $arr = $com -> SaveBxbjData($resul);
            $str = $com -> GetFirstData(2);
            if(empty($arr)){
               echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/2/rtype/10';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/2/rtype/10");
        }else{
            $this -> redirect('/Admin/Compile/combxbj');
        }
        
    }

    /**
     * 下发受理
     * @var array $style 四十五大类类别
     * @var string $sort 排序
     */
    public function comxfsl(){
        
        $order = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "QQ";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.status = 3', $order,$res_status, $store, $cause, '3');
        $style = C('SELF_STYLE');
        //dump($result);
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_XFSL');
    }
    
    /**
     * 上传文件
     * @return string
     */
    public function uploadpic(){
        $extend = explode(".",$_FILES["file"]["name"]);
        $key = count($extend)-1;
        $ext = ".".$extend[$key];
        $newfile = time().$ext;
        $savePath = "./uploads/other/".date('Ymd',time())."/"; 
        if(is_dir($savePath)==FALSE){
              mkdir($savePath,0777);  
        } 
        $thumb_path=$savePath.$newfile;
        move_uploaded_file($_FILES['file']['tmp_name'] , $thumb_path);
        return substr($thumb_path, 1);

    }
    
    /**
     * 受理结果录入
     * @1 代表直接提交
     * @2 代表提交并返回列表
     * @3 代表提交并审核下一个
     * @4 代表返回，不做操作
     */
    public function  comxfsljudge(){
        $status = isset($_GET['type']) ? $_GET['type'] : 1;
        $oid = isset($_POST['oid']) ? $_POST['oid'] : "";
        if(!is_numeric($status)){
            echo '网络繁忙，非法参数...';die;
        }
        $com = new CompileModel;
        $resul = I('post.');
        if($status == 1){
            $com -> ChangeStatus('4', '9', $oid);
            $resul['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveXfslData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comcsgg';</script>";die;
            }
            $this -> redirect('Admin/Compile/comcsgg');
        }else if($status == 2){
            $arr['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveXfslData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comxfsl';</script>";die;
            }
            $this -> redirect('Admin/Compile/comxfsl');
        }else if($status == 3){
            $arr['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveXfslData($resul);
            $str = $com -> GetFirstData(3);
            if(empty($arr)){
               echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/3/rtype/10';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/3/rtype/10");
        }else{
            $this -> redirect('/Admin/Compile/comxfsl');
        }
    }

    /**
     * 初审公告
     * @var array $style 四十五大类类别
     */
    public function comcsgg(){
        
        $order = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "QQ";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.status = 4', $order,$res_status, $store, $cause, '4');
        $style = C('SELF_STYLE');
        //dump($result);
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_CSGG');
    }
    
    /**
     * 初审结果录入
     * @1 代表直接提交
     * @2 代表提交并返回列表
     * @3 代表提交并审核下一个
     * @4 代表返回，不做操作
     */
    public function comcsggjudge(){
        $status = isset($_GET['type']) ? $_GET['type'] : 1;
        $oid = isset($_POST['oid']) ? $_POST['oid'] : "";
        if(!is_numeric($status)){
            echo '网络繁忙，非法参数...';die;
        }
        $com = new CompileModel;
        $resul = I('post.');
        if($status == 1){
            $com -> ChangeStatus('9', '8', $oid);
            $resul['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveCsggData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comzcxf';</script>";die;
            }
            $this -> redirect('Admin/Compile/comzcxf');
        }else if($status == 2){
            $arr['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveCsggData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comcsgg';</script>";die;
            }
            $this -> redirect('Admin/Compile/comcsgg');
        }else if($status == 3){
            $arr['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveCsggData($resul);
            $str = $com -> GetFirstData(4);
            if(empty($arr)){
               echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/3/rtype/10';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/3/rtype/10");
        }else{
            $this -> redirect('/Admin/Compile/comcsgg');
        }
    }
    
    /**
     * 注册下发
     * @var array $style 四十五大类类别
     */
    public function comzcxf(){
        
        $order = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "QQ";
        $res_status = isset($_POST['res_status']) ? $_POST['res_status'] : "";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.status = 5', $order ,$res_status, $store, $cause, '5');
        $style = C('SELF_STYLE');
        //dump($result);
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_ZCXF');
    }
    
    /**
     * 服务结束
     * @var array $style 四十五大类类别
     */
    public function comfwjs(){
        $order = isset($_POST['sort']) ? $_POST['sort'] : "QQ";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.status = 6', $order,$res_status, $store, $cause, '2');
        $style = C('SELF_STYLE');
        //dump($result);
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_FWJS');
    }
    
    /**
     * 审核失败
     * @var array $style 四十五大类类别
     */
    public function comshsb(){
        $order = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : "QQ";
        $store = isset($_POST['store']) ? $_POST['store'] : "";
        $cause = isset($_POST['cause']) ? $_POST['cause'] : "";
        $count = isset($_PSOT['size']) ? $_POST['size'] : "";
        $str = I('post.');
        $result = $this ->comsearch($str['onetype'], $str['oneval'], $str['two'], $str['threetype'], $str['threeval'], $str['threevals'], '', $str['size'], 'c.is_pass = 2', $order,$res_status, $store, $cause, '2');
        $style = C('SELF_STYLE');
        $cause = C('SELF_CAUSE');
        $com = new CompileModel;
        $obj = $com -> findStore(); 
        $this -> assign('obj',$obj);
        $this -> assign('cause',$cause);
        $this -> assign('sort',$order);
        $this -> assign('map',$result['map']);
        $this -> assign('p',trim(I("p")));
        $this -> assign('size',trim(I("size")));
        $this -> assign('page' , $result['pagesize']);
        $this -> assign('compileinfo',$result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_SHSB');
    }
    
    /**
     * 注册证下发结果录入
     * @1 代表直接提交
     * @2 代表提交并返回列表
     * @3 代表提交并审核下一个
     * @4 代表返回，不做操作
     */
    public function comzcxfjudge(){
        $status = isset($_GET['type']) ? $_GET['type'] : 1;
        $oid = isset($_POST['oid']) ? $_POST['oid'] : "";
        if(!is_numeric($status)){
            echo '网络繁忙，非法参数...';die;
        }
        $com = new CompileModel;
        $resul = I('post.');
        if($status == 1){
            $com -> ChangeStatus('8', '6', $oid);
            $arr = $com -> SaveZcxfData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comshsb';</script>";die;
            }
            $this -> redirect('Admin/Compile/comzcxf');
        }else if($status == 2){
            $arr = $com -> SaveZcxfData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comzcxf';</script>";die;
            }
            $this -> redirect('Admin/Compile/comzcxf');
        }else if($status == 3){
            $arr = $com -> SaveZcxfData($resul);
            $str = $com -> GetFirstData(4);
            if(empty($arr)){
               echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/5/rtype/10';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/5/rtype/10");
        }else{
            $this -> redirect('/Admin/Compile/comzcxf');
        }
    }
    
    /**
     * 查看各种图片的图样
     */
    public function lookdraft(){
        $src = isset($_GET['src']) ? $_GET['src'] : "";
        dump($src);die;
        $data = array();
        if(empty($src)){
            $data['message'] = '您好，查看的数据源不存在...';
            $data['src'] = $src;
        }else{
            $data['src'] = $src;
        }
        $this->assign('data',$data);
        $this->display('lookdraft');
    }
    
    /**
     * 编修的总搜索
     * @param string $first 第一个搜索条件类型
     * @param string $firstval 第一个搜索条件值
     * @param string $sencond 第二个搜索条件值
     * @param string $third 第三个搜索条件类型
     * @param string $firstval 第三个搜索条件值
     * @param string $firstvals 第四个搜索条件值
     * @param int $count 每页显示的条数
     * @param int $page 跳的页数
     * @param int $factor 额外的搜索条件
     * @param string $order 排序
     * @param int $type 操作的模块类型
     * @var string $where 总的搜索条件
     * @return array
     */
    private function comsearch($first, $firstval, $sencond, $third, $thirdval, $thirdvals, $count, $page, $factor, $order, $res_status, $store, $cause, $type){
        $first = isset($first) ? $first : "";
        $firstval = isset($firstval) ? $firstval : "";
        $sencond = isset($sencond) ? $sencond : "";
        $third = isset($third) ? $third : "";
        $thirdval = isset($thirdval) ? $thirdval : "";
        $thirdvals = isset($thirdvals) ? $thirdvals : "";
        $where = $factor;
        if(!empty($res_status)){
            $where.= " and c.res_status = ".$res_status."";
        }
        if(!empty($store)){
            $where.= " and c.run_branch like '%".$store."%'";
        }
        if(!empty($cause)){
            $where.= " and c.why_pass like '%".$cause."%'";
        }
        if($first != "" && $firstval != "" ){
            switch ($first) {
                case A: $where .= " and o.order_code = '".$firstval."'";
                    break;
                case B: $where .= " and o.order_code = '".$firstval."'";
                    break;
                case C: $where .= " and o.yiji like '%".$firstval."%'";
                    break;
                case D: $where .= " and n.subd like '%".$firstval."%'";
                    break;
                case E: $where .= " and o.deal_name like '%".$firstval."%'";
                    break;
                case F: $where .= " and o.deal_address like '%".$firstval."%'";
                    break;
                case G: $where .= " and c.run_name like '%".$firstval."%'";
                    break;
                case H: $where .= " and c.run_phone like '%".$firstval."%'";
                    break;
                default: $where .= "";
                    break;
            }
        }
        
        if($sencond != ""){
            $where .= " and o.style_name like '%".$sencond."%'";
        }
        if($third != "" && $thirdval != ""){
            switch ($third) {
                case I: $where .= " and o.pay_time > '".strtotime($thirdval)."' and o.pay_time < '".strtotime($thirdvals)."'";
                    break;
                case J: $where .= " and c.com_time > '".strtotime($thirdval)."' and c.com_time < '".strtotime($thirdvals)."'";
                    break;
                case K: $where .= " and c.pieces_time > '".strtotime($thirdval)."' and c.pieces_time < '".strtotime($thirdvals)."'";
                    break;
                case L: $where .= " and c.trialtime > '".strtotime($thirdval)."' and c.trialtime < '".strtotime($thirdvals)."'";
                    break;
                case M: $where .= " and c.into_res > '".strtotime($thirdval)."' and c.into_res < '".strtotime($thirdvals)."'";
                    break;
                case N: $where .= " and c.into_res > '".strtotime($thirdval)."' and c.into_res < '".strtotime($thirdvals)."'";
                    break;
                case O: $where .= " and c.into_res > '".strtotime($thirdval)."' and c.into_res < '".strtotime($thirdvals)."'";
                    break;
                case P: $where .= " and c.into_time > '".strtotime($thirdval)."' and c.into_time < '".strtotime($thirdvals)."'";
                    break;
                case R: $where .= " and c.into_pieces > '".strtotime($thirdval)."' and c.into_pieces < '".strtotime($thirdvals)."'";
                    break;
                case S: $where .= " and c.into_accept_time > '".strtotime($thirdval)."' and c.into_accept_time < '".strtotime($thirdvals)."'";
                    break;
                case T: $where .= " and c.trialtime > '".strtotime($thirdval)."' and c.trialtime < '".strtotime($thirdvals)."'";
                    break;
                default: $where .= "";
                    break;
            }
        }
        
        $com = new CompileModel;
        
        $count = $com -> GetComPileNum($where);
        //setcookie('pagesize', $page, time()+3600);
        // $page = $_COOKIE['pagesize'];
        $page_count = 5; //每页显示条数
        $page = new Page($count, $page_count);// 实例化分页类 传入总记录数
        $map['onetype'] = $first;
        $map['oneval'] = $firstval;
        $map['two'] = $sencond;
        $map['threetype'] = $third;
        $map['threeval'] = $thirdval;
        $map['threevals'] = $thirdvals;
        $map['order'] = $order;
        $map['res_status'] = $res_status;
        $map['store'] = $store;
        $map['cause'] = $cause;
        foreach($map as $key=>$val) {   
                $p->parameter .= "$key=".urlencode($val)."&";   
        }
        $pagesize =$page->show(); //得到分页模板
        $cominfo = $com -> GetComPileInfo($page->firstRow , $page->listRows , $where , $order ,$type);
        
        $data = array('data' => $cominfo, 'map' => $map, 'pagesize' => $pagesize);
        
        return $data;
    }
}

