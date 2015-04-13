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
        $str = I('get.');
        $style = C('SELF_STYLE');
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    $str['size'],  //Page每页显示的条数    
                    $str['p'],      //Page跳的页数
                    'c.status = 1 and c.is_pass != 2', //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '1'                //当前操作的状态 1 编修审核
                );

        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
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
        $get = I('get.');
        $ems = C('SELF_EMS');
        $cause = C('SELF_CAUSE');
        $com = new CompileModel;
        
        $oid = isset( $get['oid'] ) ? intval( $get['oid'] ) : "";
        $cid = isset( $get['cid'] ) ? intval( $get['cid'] ) : "";
        $type = isset( $get['type'] ) ? intval( $get['type'] ) : "";
        $order = isset( $get['order_code'] ) ? $get['order_code'] : "";
        $rtype = isset( $get['rtype'] ) ? intval( $get['rtype'] ) : "";
        
        if(!is_numeric($oid) || !is_numeric($cid) || !is_numeric($type) || !is_numeric($rtype)){
            $this -> error('对不起，非法参数，请联系技术人员处理。。');die;
        }
       
        $arr = $com -> GetComMinute($cid);
        $log = $com -> GetAuditLog($order);
        
        $this -> assign('ems', $ems); 
        $this -> assign('log', $log); 
        $this -> assign('cause', $cause);
        $this -> assign('minfo',$arr);
        switch ($type) {
            case 1: $this -> display('editManage_BXSH_detail'); break;
            case 2: $this -> display('editManage_BXBJ_detail'); break;
            case 3: $this -> display('editManage_XFSL_detail'); break;
            case 4: $this -> display('editManage_CSGG_detail'); break;
            case 5: if($rtype == 1 || $rtype == 2){
                        $this -> display('editManage_ZCXF_detail');
                    }else if($rtype == 3){
                        $this -> display('editManage_ZCXF_detail2');
                    }  break;
            case 6: $this -> display('editManage_FWJS_detail'); break;
            case 7: $this -> display('editManage_SHSB_detail'); break;
            default: break;
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
        $get = I('get.');
        $resul = I('post.');
        $com = new CompileModel;
        $status = isset( $get['type'] ) ? intval( $get['type'] ) : 1;
        $oid = isset( $resul['oid'] ) ? intval( $resul['oid'] ) : "";
        
        if(!is_numeric($status)){
            $this -> error('对不起，非法参数，请联系技术人员处理。。');die;
        }

        //记录失败日志
        if($resul['is_pass'] == 2){
            $data['order_code'] = $resul['order_code'];
            $data['causes'] = $resul['causes'];
            $data['auditor'] = session('truename');
            $data['time'] = time();
            $com -> ComFailLog( $data );
            $com -> ChageOrderStatus( $oid );
        }
        
        if($status == 2){
            $arr = $com -> SaveBxshData($resul);
            if($resul['is_pass'] == 1){
                $com -> ChangeStatus('1', '3', $oid);
            }
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/combxsh';</script>";die;
            }
            $this -> redirect('Admin/Compile/combxsh');
        }else if($status == 3){
            $arr = $com -> SaveBxshData($resul);
            if($resul['is_pass'] == 1){
                $com -> ChangeStatus('1', '3', $oid);
            }
            $str = $com -> GetFirstData(1);
            
            if(empty($str)){
                echo "<script>alert('已无可提交的数据，返回列表！');location.href='/Admin/Compile/combxsh';</script>";die;
            }
            if($arr != 1){
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
        $str = I('get.');
        $style = C('SELF_STYLE');
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    '',                //Page每页显示的条数    
                    $str['size'],      //Page跳的页数
                    'c.status = 2',    //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '2'                //当前操作的状态 1 编修审核
                );

        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
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
        $get = I('get.');
        $resul = I('post.');
        $com = new CompileModel;
        
        $status = isset( $get['type'] ) ? intval( $get['type'] ) : 1;
        $oid = isset( $resul['oid'] ) ? $resul['oid'] : "";
        if(!is_numeric($status)){
            $this -> error('对不起，非法参数，请联系技术人员处理。。');die;
        }
        
        if($status == 2){
            
            $com -> ChangeStatus('3', '4', $oid);    
            $arr = $com -> SaveBxbjData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/combxbj';</script>";die;
            }
            $this -> redirect('Admin/Compile/combxbj');
        }else if($status == 3){
            
            $com -> ChangeStatus('3', '4', $oid);    
            $arr = $com -> SaveBxbjData($resul);
            $str = $com -> GetFirstData(2);
            if(empty($str)){
                echo "<script>alert('已无可提交的数据，返回列表！');location.href='/Admin/Compile/combxbj';</script>";die;
            }
            if($arr != 1){
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
        $str = I('get.');
        $style = C('SELF_STYLE');
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    '',                //Page每页显示的条数    
                    $str['size'],      //Page跳的页数
                    'c.status = 3',    //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '3'                //当前操作的状态 1 编修审核
                );

        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
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
        $get = I('get.');
        $resul = I('post.');
        $com = new CompileModel;
       
        $status = isset( $get['type'] ) ? intval( $get['type'] ) : 1;
        $oid = isset( $resul['oid'] ) ? $resul['oid'] : "";
        if(!is_numeric($status)){
            $this -> error('对不起，非法参数，请联系技术人员处理。。');die;
        }
        
        if( $status == 2 ){
            $resul['ac_url'] = $this -> uploadpic();
            $com -> ChangeStatus('4', '9', $oid);
            $arr = $com -> SaveXfslData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comxfsl';</script>";die;
            }
            $this -> redirect('Admin/Compile/comxfsl');
        }else if( $status == 3 ){
            $com -> ChangeStatus('4', '9', $oid);
            $resul['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveXfslData($resul);
            $str = $com -> GetFirstData(3);
            if( empty ( $str )){
                echo "<script>alert('已无可提交的数据，返回列表！');location.href='/Admin/Compile/comxfsl';</script>";die;
            }
            if( $arr != 1 ){
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
        $str = I('get.');
        $style = C('SELF_STYLE');
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    '',                //Page每页显示的条数    
                    $str['size'],      //Page跳的页数
                    'c.status = 4',    //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '4'                //当前操作的状态 1 编修审核
                );

        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
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
        $get = I('get.');
        $resul = I('post.');
        $com = new CompileModel;
        
        $status = isset( $get['type'] ) ? intval( $get['type'] ) : 1;
        $oid = isset( $resul['oid'] ) ? $resul['oid'] : "";
        if(!is_numeric($status)){
            $this -> error('对不起，非法参数，请联系技术人员处理。。');die;
        }
        
        if($status == 2){
            $com -> ChangeStatus('9', '8', $oid);
            $resul['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveCsggData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comcsgg';</script>";die;
            }
            $this -> redirect('Admin/Compile/comcsgg');
        }else if($status == 3){
            $com -> ChangeStatus('9', '8', $oid);
            $resul['ac_url'] = $this -> uploadpic();
            $arr = $com -> SaveCsggData($resul);
            $str = $com -> GetFirstData(4);
            if(empty($str)){
                echo "<script>alert('已无可提交的数据，返回列表！');location.href='/Admin/Compile/comcsgg';</script>";die;
            }
            if($arr != 1){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/4/rtype/10';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/4/rtype/10");
        }else{
            $this -> redirect('/Admin/Compile/comcsgg');
        }
    }
    
    /**
     * 注册下发
     * @var array $style 四十五大类类别
     */
    public function comzcxf(){
        $str = I('get.');
        $style = C('SELF_STYLE');
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $res_status = isset( $str['res_status'] ) ? intval( $str['res_status'] ) : "";
        
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    '',                //Page每页显示的条数    
                    $str['size'],      //Page跳的页数
                    'c.status = 5',    //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '5'                //当前操作的状态 1 编修审核
                );

        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_ZCXF');
    }
    
    /**
     * 服务结束
     * @var array $style 四十五大类类别
     */
    public function comfwjs(){
        $str = I('get.');
        $style = C('SELF_STYLE');
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    '',                //Page每页显示的条数    
                    $str['size'],      //Page跳的页数
                    'c.status = 6',    //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '2'                //当前操作的状态 1 编修审核
                );

        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
        $this -> assign('style', $style);
        $this -> display('editManage_FWJS');
    }
    
    /**
     * 审核失败
     * @var array $style 四十五大类类别
     */
    public function comshsb(){
        $str = I('get.');
        $style = C('SELF_STYLE');
        $com = new CompileModel;
        
        $order = isset( $str['sort'] ) ? $str['sort'] : "QQ";
        $store = isset( $str['store'] ) ? $str['store'] : "";
        $cause = isset( $str['cause'] ) ? $str['cause'] : "";
        $count = isset( $str['size'] ) ? $str['size'] : "";
        $result = $this -> comsearch(
                    $str['onetype'],   //View第一个下拉框值
                    $str['oneval'],    //View第二个文本框值
                    $str['two'],       //View第三个下拉框值
                    $str['threetype'], //View第四个下拉框值
                    $str['threeval'],  //View第五个文本框值
                    $str['threevals'], //View第六个文本框值
                    '',                //Page每页显示的条数    
                    $str['size'],      //Page跳的页数
                    'c.status = 1 and c.is_pass = 2',    //初始搜索条件，编修通过
                    $order,            //View右侧排序
                    $res_status,       //View注册证下发三种状态
                    $store,            //View审核失败 店铺搜索
                    $cause,            //view审核失败 失败原因搜索
                    '2'                //当前操作的状态 1 编修审核
                );
        $obj = $com -> findStore(); 
        
        $this -> assign('obj', $obj);
        $this -> assign('cause', $cause);
        $this -> assign('sort', $order);
        $this -> assign('map', $result['map']);
        $this -> assign('p', trim(I("p")));
        $this -> assign('size', trim(I("size")));
        $this -> assign('page', $result['pagesize']);
        $this -> assign('compileinfo', $result['data']);
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
        $get = I('get.');
        $resul = I('post.');
        $com = new CompileModel;
        
        $status = isset( $get['type'] ) ? intval( $get['type'] ) : 1;
        $oid = isset( $resul['oid'] ) ? $resul['oid'] : "";
        if(!is_numeric($status)){
           $this -> error('对不起，非法参数，请联系技术人员处理。。');die;
        }

        if($status == 2){
             $com -> ChangeStatus('8', '6', $oid);
            $arr = $com -> SaveZcxfData($resul);
            if(empty($arr)){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comzcxf';</script>";die;
            }
            $this -> redirect('Admin/Compile/comzcxf');
        }else if($status == 3){
            $com -> ChangeStatus('8', '6', $oid);
            $arr = $com -> SaveZcxfData($resul);
            $str = $com -> GetFirstValData($resul['res_status']);
            if(empty($str) || $res['res_status'] == 3){
                echo "<script>alert('已无可提交的数据，返回列表！');location.href='/Admin/Compile/comzcxf';</script>";die;
            }
            if($arr != 1){
                echo "<script>alert('网络故障，审核失败。。');location.href='/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/5/rtype/1';</script>";die;
            }
            $this -> redirect("/Admin/Compile/comminute/cid/".$str[0]['co_id']."/oid/".$str[0]['ordergoods_id']."/type/5/rtype/1");
        }else{
            $this -> redirect('/Admin/Compile/comzcxf');
        }
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
    private function comsearch($first, $firstval, $sencond, $third, $thirdval, $thirdvals, $size, $page, $factor, $order, $res_status, $store, $cause, $type){
        $first = isset( $first ) ? $first : "";
        $firstval = isset( $firstval ) ? $firstval : "";
        $sencond = isset( $sencond ) ? $sencond : "";
        $third = isset( $third ) ? $third : "";
        $thirdval = isset( $thirdval ) ? $thirdval : "";
        $thirdvals = isset( $thirdvals ) ? $thirdvals : "";
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
                case A: $where .= " and o.order_code like '%".$firstval."%'";
                    break;
                case B: $where .= " and o.order_code like '%".$firstval."%'";
                    break;
                case C: $where .= " and n.name like '%".$firstval."%'";
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
        if($third != "" && $thirdval != "" && $thirdvals != ""){
            $thirdval = strtotime($thirdval);
            $thirdvals = strtotime($thirdvals);
               switch ($third) {
                case I: $where .= " and o.pay_time > '".$thirdval."' and o.pay_time < '".$thirdvals."'";
                    break;
                case J: $where .= " and c.com_time > '".$thirdval."' and c.com_time < '".$thirdvals."'";
                    break;
                case K: $where .= " and c.pieces_time > '".$thirdval."' and c.pieces_time < '".$thirdvals."'";
                    break;
                case L: $where .= " and c.trialtime > '".$thirdval."' and c.trialtime < '".$thirdvals."'";
                    break;
                case M: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case N: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case O: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case P: $where .= " and c.into_time > '".$thirdval."' and c.into_time < '".$thirdvals."'";
                    break;
                case R: $where .= " and c.into_pieces > '".$thirdval."' and c.into_pieces < '".$thirdvals."'";
                    break;
                case S: $where .= " and c.into_accept_time > '".$thirdval."' and c.into_accept_time < '".$thirdvals."'";
                    break;
                case T: $where .= " and c.trialtime > '".$thirdval."' and c.trialtime < '".$thirdvals."'";
                    break;
                default: $where .= "";
                    break;
            }
        }else if($third != "" && $thirdval != "" && $thirdvals == ""){
            $thirdvals = strtotime(date('Y-m-d',strtotime($thirdval)).' 23:59:59');
            $thirdval = strtotime($thirdval);
            switch ($third) {
                case I: $where .= " and o.pay_time > '".$thirdval."' and o.pay_time < '".$thirdvals."'";
                    break;
                case J: $where .= " and c.com_time > '".$thirdval."' and c.com_time < '".$thirdvals."'";
                    break;
                case K: $where .= " and c.pieces_time > '".$thirdval."' and c.pieces_time < '".$thirdvals."'";
                    break;
                case L: $where .= " and c.trialtime > '".$thirdval."' and c.trialtime < '".$thirdvals."'";
                    break;
                case M: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case N: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case O: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case P: $where .= " and c.into_time > '".$thirdval."' and c.into_time < '".$thirdvals."'";
                    break;
                case R: $where .= " and c.into_pieces > '".$thirdval."' and c.into_pieces < '".$thirdvals."'";
                    break;
                case S: $where .= " and c.into_accept_time > '".$thirdval."' and c.into_accept_time < '".$thirdvals."'";
                    break;
                case T: $where .= " and c.trialtime > '".$thirdval."' and c.trialtime < '".$thirdvals."'";
                    break;
                default: $where .= "";
                    break;
            }
        }else if($third != "" && $thirdval == "" && $thirdvals != ""){
            
            $thirdval = strtotime(date('Y-m-d',strtotime($thirdvals)).' 00:00:00');
            $thirdvals = strtotime($thirdvals);
            switch ($third) {
                case I: $where .= " and o.pay_time > '".$thirdval."' and o.pay_time < '".$thirdvals."'";
                    break;
                case J: $where .= " and c.com_time > '".$thirdval."' and c.com_time < '".$thirdvals."'";
                    break;
                case K: $where .= " and c.pieces_time > '".$thirdval."' and c.pieces_time < '".$thirdvals."'";
                    break;
                case L: $where .= " and c.trialtime > '".$thirdval."' and c.trialtime < '".$thirdvals."'";
                    break;
                case M: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case N: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case O: $where .= " and c.into_res > '".$thirdval."' and c.into_res < '".$thirdvals."'";
                    break;
                case P: $where .= " and c.into_time > '".$thirdval."' and c.into_time < '".$thirdvals."'";
                    break;
                case R: $where .= " and c.into_pieces > '".$thirdval."' and c.into_pieces < '".$thirdvals."'";
                    break;
                case S: $where .= " and c.into_accept_time > '".$thirdval."' and c.into_accept_time < '".$thirdvals."'";
                    break;
                case T: $where .= " and c.trialtime > '".$thirdval."' and c.trialtime < '".$thirdvals."'";
                    break;
                default: $where .= "";
                    break;
            }
        }
        
        $com = new CompileModel;
        $count = $com -> GetComPileNum($where);
        //setcookie('pagesize', $page, time()+3600);
        // $page = $_COOKIE['pagesize'];
        $pageSize = isset( $size ) ? intval( $size ) : 10; //每页显示条数
        $page = new Page($count, $pageSize);// 实例化分页类 传入总记录数
        $map['onetype'] = $first;
        $map['oneval'] = $firstval;
        $map['two'] = $sencond;
        $map['threetype'] = $third;
        $map['threeval'] = date('Y-m-d H:i:s',$thirdval);
        $map['threevals'] = date('Y-m-d H:i:s',$thirdvals);
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

